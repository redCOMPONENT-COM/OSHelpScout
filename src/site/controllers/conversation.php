<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout;
use Alledia\Framework;

defined('_JEXEC') or die();

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


class OSHelpScoutControllerConversation extends JControllerLegacy
{
    public function reply()
    {
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        $app        = JFactory::getApplication();
        $user       = Framework\Factory::getUser();
        $customerId = OSHelpScout\Free\Helper::getCurrentCustomerId();
        $itemId     = $app->input->get('Itemid', 0);


        if (!empty($customerId) || !$user->guest) {
            try {
                $hs             = OSHelpScout\Free\Helper::getAPIInstance();
                $body           = $app->input->getHtml('body');
                $conversationId = $app->input->get('conversationId', 0);

                if (!empty($customerId)) {
                    $createdBy = new HelpScout\model\ref\PersonRef();
                    $createdBy->setId($customerId);
                    $createdBy->setType("customer");
                } else {
                    $createdBy = $hs->getCustomerRefProxy(null, $user->email);
                }

                $thread = new HelpScout\model\thread\Customer();
                $thread->setBody($body);
                $thread->setCreatedBy($createdBy);

                // Check if there are pending uploaded files to send to HelpScout
                $currentUploads = OSHelpScout\Free\Helper::getUploadSessionData($conversationId);

                // var_dump($currentUploads); die;
                if (!empty($currentUploads)) {
                    $attachments = array();

                    foreach ($currentUploads as $file) {
                        // Create the attachment uploading to HelpScout
                        if (JFile::exists($file->tmpPath)) {
                            $attachment = new HelpScout\model\Attachment;
                            $attachment->setFileName($file->name);
                            $attachment->setMimeType(mime_content_type($file->tmpPath));
                            $attachment->setData(file_get_contents($file->tmpPath));

                            $hs->createAttachment($attachment);
                            $attachments[] = $attachment;
                        }
                    }

                    // Link attachments to the thread
                    if (!empty($attachments)) {
                        $thread->setAttachments($attachments);
                    }
                }

                if (OSHelpScout\Free\Helper::isNewId($conversationId)) {
                    // New Conversation
                    $subject     = $app->input->getString('subject', 'Contact');
                    $user        = Framework\Factory::getUser();
                    $mailbox     = $hs->getMailboxProxy(OSHelpScout\Free\Helper::getCurrentMailboxId());

                    $conversation = new HelpScout\model\Conversation;
                    $conversation->setType('email');
                    $conversation->setSubject($subject);
                    $conversation->setCustomer($createdBy);
                    $conversation->setCreatedBy($createdBy);
                    $conversation->setMailbox($mailbox);
                    $conversation->addLineItem($thread);

                    // Default tags
                    $tags = OSHelpScout\Free\Helper::getTagsForSubject($subject);
                    $tags[] = JText::_(COM_OSHELPSCOUT_VIA_OSHELPSCOUT_TAG);
                    $conversation->setTags($tags);

                    $hs->createConversation($conversation);
                } else {
                    // Reply
                    $hs->createThread($conversationId, $thread);
                }

                // Cleanup
                OSHelpScout\Free\Helper::cleanUploadSessionData($conversationId);
                OSHelpScout\Free\Helper::cleanUploadTmpFiles($conversationId);

                // If a conversation is defined is because we just created one. Use the new ID to redirect
                if (isset($conversation)) {
                    $conversationId = $conversation->getId();
                }

                $message = JText::_("COM_OSHELPSCOUT_REPLIED_SUCCESSFULLY");
            } catch (Exception $e) {
                $message = JText::_("COM_OSHELPSCOUT_ERROR_REPLYING");
            }
        } else {
            $message = JText::_("COM_OSHELPSCOUT_ERROR_REPLYING");
        }

        $this->setRedirect(
            JRoute::_("index.php?option=com_oshelpscout&view=conversation&id=" . $conversationId . '&Itemid=' . $itemId),
            $message
        );
    }

    public function upload()
    {
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        $app            = JFactory::getApplication();
        $session        = JFactory::getSession();
        $conversationId = $app->input->get('conversationId');

        try {
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (!isset($_FILES['file']['error']) ||
                !is_array($_FILES['file']['error'])
            ) {
                throw new RuntimeException('Invalid parameters.');
            }

            // Check error values.
            foreach ($_FILES['file']['error'] as $error) {
                switch ($error) {
                    case UPLOAD_ERR_OK:
                        break;
                    case UPLOAD_ERR_NO_FILE:
                        throw new RuntimeException('No file sent.');
                    case UPLOAD_ERR_INI_SIZE:
                    case UPLOAD_ERR_FORM_SIZE:
                        throw new RuntimeException('Exceeded filesize limit.');
                    default:
                        throw new RuntimeException('Unknown errors.');
                }
            }

            // We do not need error list anymore
            unset($_FILES['file']['error']);

            // Create the tmp folder if not exists
            $tmpPath = OSHelpScout\Free\Helper::getTmpUploadFolder();

            // Get uploaded files from session
            $currentUploads = OSHelpScout\Free\Helper::getUploadSessionData($conversationId);

            // Move PHP tmp files to Joomla's tmp folder
            if (!empty($_FILES['file'])) {
                $filesCount = count($_FILES['file']['name']);

                for ($i = 0; $i < $filesCount; $i++) {
                    $tmpFile = new stdClass;
                    $tmpFile->name = $_FILES['file']['name'][$i];
                    $tmpFile->size = $_FILES['file']['size'][$i];
                    $tmpFile->tmpPath  = OSHelpScout\Free\Helper::getUploadTmpFilename($conversationId);

                    $safeFileOptions = array(
                        // Null byte in file name
                        'null_byte'                  => true,
                        // Forbidden string in extension (e.g. php matched .php, .xxx.php, .php.xxx and so on)
                        'forbidden_extensions'       => array(),
                        // <?php tag in file contents
                        'php_tag_in_content'         => false,
                        // <? tag in file contents
                        'shorttag_in_content'        => false,
                        // Which file extensions to scan for short tags
                        'shorttag_extensions'        => array(),
                        // Forbidden extensions anywhere in the content
                        'fobidden_ext_in_content'    => false,
                        // Which file extensions to scan for .php in the content
                        'php_ext_content_extensions' => array(),
                    );
                    JFile::upload($_FILES['file']['tmp_name'][$i], $tmpFile->tmpPath, false, false, $safeFileOptions);

                    // Add to the session, to be sent to HelpScout while saving the reply
                    $currentUploads[] = $tmpFile;
                }
            }

            OSHelpScout\Free\Helper::setUploadSessionData($conversationId, $currentUploads);

            echo 1;
        } catch (RuntimeException $e) {
            echo $e->getMessage();
        }

        jexit();
    }
}
