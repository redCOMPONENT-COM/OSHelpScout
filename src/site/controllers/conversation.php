<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout;

defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

class OSHelpScoutControllerConversation extends JControllerLegacy
{
    public function reply()
    {
        JSession::checkToken('request') or jexit(JText::_('JINVALID_TOKEN'));

        $customerId = OSHelpScout\Free\Helper::getCurrentCustomerId();

        if (!empty($customerId)) {
            $hs             = OSHelpScout\Free\Helper::getAPIInstance();
            $app            = JFactory::getApplication();
            $body           = $app->input->getHtml('body');
            $conversationId = $app->input->get('conversationId');
            $itemId         = $app->input->get('Itemid');

            $createdBy = new HelpScout\model\ref\PersonRef();
            $createdBy->setId($customerId);
            $createdBy->setType("customer");

            $thread = new HelpScout\model\thread\Customer();
            $thread->setBody($body);
            $thread->setCreatedBy($createdBy);

            $hs->createThread($conversationId, $thread);

            $message = JText::_("COM_OSHELPSCOUT_REPLIED_SUCCESSFULLY");
        } else {
            $message = JText::_("COM_OSHELPSCOUT_ERROR_REPLYING");
        }

        $this->setRedirect(
            "index.php?option=com_oshelpscout&view=conversation&id=" . $conversationId . '&Itemid=' . $itemId,
            $message
        );



        // $row->bind($post['jform']);

        // $text    = $post['jform']['description_1'];
        // $text    = str_replace('<br>', '<br />', $text);
        // $pattern = '#<hr\s+id=("|\')system-readmore("|\')\s*\/*>#i';
        // $tagPos  = preg_match($pattern, $text);
        // if ($tagPos == 0) {
        //     $row->brief = $text;
        //     $row->description_1 = "";
        // } else {
        //     list($row->brief, $row->description_1) = preg_split($pattern, $text, 2);
        // }

        // $row->require_email = (int) $row->require_email;
        // $row->require_agree = (int) $row->require_agree;

        // if (version_compare(JVERSION, '3.0', 'lt') && (!empty($post['id']))) {
        //     $row->id = $post['id'];
        // }

        // if (version_compare(JVERSION, '3.4', '<')) {
        //     $files       = JRequest::get('files');
        //     $file        = $files['jform'];
        //     $fileName    = $file["name"]['file'];
        //     $fileTmpName = $file["tmp_name"]['file'];
        // } else {
        //     $app = JFactory::getApplication();

        //     $files       = $app->input->files->get('jform', null, 'raw');
        //     if (isset($files['file'])) {
        //         $file        = $files['file'];
        //         $fileName    = $file["name"];
        //         $fileTmpName = $file["tmp_name"];
        //     } else {
        //         $fileName    = '';
        //         $fileTmpName = '';
        //     }
        // }

        // if (!empty($fileName)) {
        //     $fileName = JFile::makeSafe($fileName);

        //     if (isset($fileName) && $fileName) {
        //         $uploadDir = JPATH_SITE . "/media/com_osdownloads/files/";

        //         if (isset($post["old_file"]) && JFile::exists(JPath::clean($uploadDir . $post["old_file"]))) {
        //             unlink(JPath::clean($uploadDir . $post["old_file"]));
        //         }

        //         if (!JFolder::exists(JPath::clean($uploadDir))) {
        //             JFolder::create(JPath::clean($uploadDir));
        //         }

        //         $timestamp = md5(microtime());
        //         $filepath = JPath::clean($uploadDir . $timestamp . "_" . $fileName);
        //         $row->file_path = $timestamp . "_" . $fileName;

        //         if (version_compare(JVERSION, '3.4', '<')) {
        //             JFile::upload($fileTmpName, $filepath);
        //         } else {
        //             $safeFileOptions = array(
        //                 // Null byte in file name
        //                 'null_byte'                  => true,
        //                 // Forbidden string in extension (e.g. php matched .php, .xxx.php, .php.xxx and so on)
        //                 'forbidden_extensions'       => array(),
        //                 // <?php tag in file contents
        //                 'php_tag_in_content'         => false,
        //                 // <? tag in file contents
        //                 'shorttag_in_content'        => false,
        //                 // Which file extensions to scan for short tags
        //                 'shorttag_extensions'        => array(),
        //                 // Forbidden extensions anywhere in the content
        //                 'fobidden_ext_in_content'    => false,
        //                 // Which file extensions to scan for .php in the content
        //                 'php_ext_content_extensions' => array(),
        //             );
        //             JFile::upload($fileTmpName, $filepath, false, false, $safeFileOptions);
        //         }
        //     }
        // }

        // $row->store();
        // switch ($this->getTask()) {
        //     case "apply":
        //         $this->setRedirect("index.php?option=com_osdownloads&view=file&cid=" . $row->id, JText::_("COM_OSDOWNLOADS_DOCUMENT_IS_SAVED"));
        //         break;
        //     default:
        //         $this->setRedirect("index.php?option=com_osdownloads&view=files", JText::_("COM_OSDOWNLOADS_DOCUMENT_IS_SAVED"));
        // }
    }
}
