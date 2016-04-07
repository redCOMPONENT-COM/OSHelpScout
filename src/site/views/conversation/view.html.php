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
        $app       = Framework\Factory::getApplication();
        $mailboxId = OSHelpScout\Free\Helper::getCurrentMailboxId();
        $params    = OSHelpScout\Free\Helper::getMenuParams();

        $this->itemId         = $app->input->get('Itemid', 0);
        $this->conversationId = $app->input->get('id', 0);
        $this->conversation   = null;
        $this->statusLabel    = null;

        if (empty($this->conversationId)) {
            // Try to recover a temporary ID from the session
            $this->conversationId = OSHelpScout\Free\Helper::getTmpConversationIdFromSession();
        }

        if (!OSHelpScout\Free\Helper::isNewId($this->conversationId)) {
            // Get the customer's conversation
            $this->conversation = OSHelpScout\Free\Helper::getConversation($this->conversationId, $mailboxId);
            $this->statusLabel  = JText::_(
                OSHelpScout\Free\Helper::getConversationStatusStr($this->conversation->getStatus())
            );
        }

        // Make sure the tmp upload data is empty in the session
        OSHelpScout\Free\Helper::cleanUploadSessionData($this->conversationId);
        OSHelpScout\Free\Helper::cleanUploadTmpFiles($this->conversationId);

        // Get the list of subjects to display
        $this->subjects = OSHelpScout\Free\Helper::getSubjectsList();

        parent::display($tpl);
    }
}
