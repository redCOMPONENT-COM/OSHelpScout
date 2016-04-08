<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Framework;
use Alledia\OSHelpScout;

defined('_JEXEC') or die;

class OSHelpScoutViewConversation extends JViewLegacy
{
    public function display($tpl = null)
    {
        $app                  = Framework\Factory::getApplication();
        $this->itemId         = $app->input->get('Itemid', 0);
        $this->conversationId = $app->input->get('id', 0);
        $this->isGuest        = Framework\Factory::getUser()->guest;
        $showMessage          = $app->input->get('msg', 0);

        // Check if received a flag to show the success message after insert
        // This is required since is redirected by JS, not PHP
        if ($showMessage) {
            $app->enqueueMessage(JText::_("COM_OSHELPSCOUT_REPLIED_SUCCESSFULLY"), 'info');
        }

        if (empty($this->conversationId)) {
            // Try to recover a temporary ID from the session
            $this->conversationId = OSHelpScout\Free\Helper::getTmpConversationIdFromSession();
        }

        $this->isNewConversation = OSHelpScout\Free\Helper::isNewId($this->conversationId);

        // Make sure the tmp upload data is empty in the session
        OSHelpScout\Free\Helper::cleanUploadSessionData($this->conversationId);
        OSHelpScout\Free\Helper::cleanUploadTmpFiles($this->conversationId);

        // Get the list of subjects to display
        $this->subjects = OSHelpScout\Free\Helper::getSubjectsList();

        parent::display($tpl);
    }
}
