<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout;
use Alledia\Framework;
use Carbon\Carbon;

defined('_JEXEC') or die();

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


class OSHelpScoutControllerConversation extends OSHelpScout\Free\Joomla\Controller\Json
{
    /**
     * Return a specific conversation and threads
     * returns empty array.
     *
     * @return void
     */
    public function getItem()
    {
        $app            = Framework\Factory::getApplication();
        $mailboxId      = OSHelpScout\Free\Helper::getCurrentMailboxId();
        $itemId         = $app->input->getInt('Itemid', 0);
        $conversationId = $app->input->getInt('conversationId', 0);
        $status         = null;
        $statusLabel    = null;
        $thread         = array();
        $threadCount    = 0;
        $success        = false;
        $subject        = '';
        $timezone       = JFactory::getUser()->getParam('timezone');
        if (empty($timezone)) {
            $timezone = JFactory::getConfig()->get('offset');
        }

        if (empty($conversationId)) {
            // Try to recover a temporary ID from the session
            $conversationId = OSHelpScout\Free\Helper::getTmpConversationIdFromSession();
        }

        $isNewConversation = OSHelpScout\Free\Helper::isNewId($conversationId);
        if (!$isNewConversation) {
            // Get the customer's conversation
            $conversation = OSHelpScout\Free\Helper::getConversation($conversationId, $mailboxId);

            if (is_object($conversation)) {
                $status       = $conversation->getStatus();
                $statusLabel  = JText::_(
                    OSHelpScout\Free\Helper::getConversationStatusStr($status)
                );
                $subject = $conversation->getSubject();

                $conversationThread = $conversation->filteredThread;
                foreach ($conversationThread as $item) {
                    $createdBy = $item->getCreatedBy();

                    $tmpItem = new stdClass;
                    $tmpItem->type         = $item->getType();
                    $tmpItem->gravatarHash = md5(strtolower(trim($createdBy->getEmail())));
                    $tmpItem->creatorName     = $createdBy->getFirstName() . ' ' . $createdBy->getLastName();
                    $tmpItem->creatorType  = $createdBy->getType();

                    // Get dates
                    $date = Carbon::parse($item->getCreatedAt());
                    $date->timezone = new DateTimeZone($timezone);
                    $tmpItem->createdAtRelative = $date->diffForHumans();
                    $tmpItem->createdAt = $date->format(JText::_('COM_OSHELPSCOUT_DATE_FORMAT'));

                    // Check if body has html code
                    $body            = trim($item->getBody());
                    $tmpItem->isHtml = $body != strip_tags($body);
                    $tmpItem->body   = $body;

                    // Get the attachments
                    $attachments          = $item->getAttachments();
                    $tmpItem->attachments = array();
                    if (!empty($attachments)) {
                        foreach ($attachments as $file) {
                            $tmpFile = new stdClass;
                            $tmpFile->filename = $file->getFileName();
                            $tmpFile->url      = $file->getUrl();
                            $tmpFile->size     = $file->getSize();

                            $tmpItem->attachments[] = $tmpFile;
                        }
                    }

                    $thread[] = $tmpItem;
                }

                $threadCount = count($conversationThread);

                $success = true;
            }
        }

        echo json_encode(
            array(
                'success'           => $success,
                'conversationId'    => $conversationId,
                'itemId'            => $itemId,
                'subject'           => $subject,
                'thread'            => $thread,
                'isNewConversation' => $isNewConversation,
                'status'            => $status,
                'statusLabel'       => $statusLabel,
                'threadCount'       => $threadCount
            )
        );
    }

    public function reply()
    {
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            JError::raiseError('401', 'Invalid method');
            jexit();
        }

        $app            = JFactory::getApplication();
        $user           = Framework\Factory::getUser();
        $hs             = OSHelpScout\Free\Helper::getAPIInstance();
        $customerId     = OSHelpScout\Free\Helper::getCurrentCustomerId();
        $itemId         = $app->input->getInt('Itemid', 0);
        $name           = $app->input->getString('name', null);
        $email          = $app->input->getString('email', null);
        $success        = false;
        $conversationId = '';
        $subject        = 'Contact';

        // Check if the email is already registered
        if (empty($customerId)) {
            if ($user->guest) {
                $createdBy = $hs->getCustomerRefProxy(null, $email);
            } else {
                $createdBy = $hs->getCustomerRefProxy(null, $user->email);
            }

            if (!is_object($createdBy)) {
                // Create a new user, since his email wasn't found
                $parts = explode(" ", $name);
                $lastName = array_pop($parts);
                $firstName = implode(" ", $parts);

                $createdBy = new HelpScout\model\Customer();
                $createdBy->setFirstName($firstName);
                $createdBy->setLastName($lastName);

                $emailEntry = new HelpScout\model\customer\EmailEntry;
                $emailEntry->setValue($email);

                $createdBy->setEmails(array($emailEntry));

                $hs->createCustomer($createdBy);

                $customerId = $createdBy->getId();
            }

            $customerId = $createdBy->getId();
        } else {
            // We have a specific customerid
            $createdBy = new HelpScout\model\ref\PersonRef();
            $createdBy->setId($customerId);
            $createdBy->setType("customer");
        }

        if (is_object($createdBy)) {
            try {
                $body           = htmlspecialchars($app->input->getRaw('body'), ENT_NOQUOTES);
                $conversationId = $app->input->getInt('conversationId', 0);

                $thread = new HelpScout\model\thread\Customer();
                $thread->setBody($body);
                $thread->setCreatedBy($createdBy);

                // Check if there are pending uploaded files to send to HelpScout
                $currentUploads = OSHelpScout\Free\Helper::getUploadSessionData($conversationId);
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
                    $tags   = OSHelpScout\Free\Helper::getTagsForSubject($subject);
                    $tags[] = JText::_('COM_OSHELPSCOUT_VIA_OSHELPSCOUT_TAG');
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
                $success = true;
            } catch (Exception $e) {
                $message = JText::_("COM_OSHELPSCOUT_ERROR_REPLYING");
                $success = false;
            }
        } else {
            $message = JText::_("COM_OSHELPSCOUT_ERROR_FINDING_CREATING_USER");
            $success = false;
        }

        $statusLabel = $statusLabel  = JText::_(
            OSHelpScout\Free\Helper::getConversationStatusStr('active')
        );

        echo json_encode(
            array(
                'success'        => $success,
                'message'        => $message,
                'itemId'         => $itemId,
                'conversationId' => $conversationId,
                'subject'        => $subject,
                'status'         => 'active',
                'statusLabel'    => $statusLabel
            )
        );
    }

    public function upload()
    {
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        $app            = JFactory::getApplication();
        $session        = JFactory::getSession();
        $conversationId = $app->input->get('conversationId');
        $message        = '';

        try {
            // Undefined | Multiple Files | $_FILES Corruption Attack
            // If this request falls under any of them, treat it invalid.
            if (!isset($_FILES['file']['error']) ||
                !is_array($_FILES['file']['error'])
            ) {
                if ($_FILES['file']['error'] !== 0) {
                    throw new RuntimeException('Invalid parameters.');
                }
            }

            // Check if file has arrays. If not, convert.
            if (!is_array($_FILES['file']['error'])) {
                $_FILES['file']['error']    = array($_FILES['file']['error']);
                $_FILES['file']['name']     = array($_FILES['file']['name']);
                $_FILES['file']['size']     = array($_FILES['file']['size']);
                $_FILES['file']['tmp_name'] = array($_FILES['file']['tmp_name']);
            }

            // Check error values
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

            $success = true;
        } catch (RuntimeException $e) {
            $success = false;
            $message = $e->getMessage();
        }

        echo json_encode(
            array(
                'success' => $success,
                'message' => $message
            )
        );
    }
}
