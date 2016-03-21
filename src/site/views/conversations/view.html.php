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

class OSHelpScoutViewConversations extends JViewLegacy
{
    public function display($tpl = null)
    {
        $hs        = OSHelpScout\Free\Helper::getAPIInstance();
        $mailboxId = OSHelpScout\Free\Helper::getCurrentMailboxId();

        $this->conversations = array();

        // Locate the customer by email
        $customerId = OSHelpScout\Free\Helper::getCurrentCustomerId();
        if (!empty($customerId)) {
            // Get the customer conversations
            $conversationsResult = $hs->getConversationsForCustomerByMailbox(
                $mailboxId,
                $customerId
            );
            // @todo: implement pagination

            $this->conversations = $conversationsResult->getItems();
        }

        parent::display($tpl);
    }
}
