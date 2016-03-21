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
        $hs       = OSHelpScout\Free\Helper::getAPIInstance();
        $user     = Framework\Factory::getUser();
        $app      = Framework\Factory::getApplication();
        $menuitem = $app->getMenu()->getActive();
        $params   = $menuitem->params;
        $id       = $app->input->get('id');

        $this->conversation = null;

        if (!$user->guest && !empty($id)) {
            // Locate the customer by email
            $customerId = OSHelpScout\Free\Helper::getCustomerIdByEmail($user->email);
            if (!empty($customerId)) {
                // Get the customer conversations
                $conversation = $hs->getConversation($id);

                // Validate the mailbox and user
                $mailbox = $conversation->getMailbox();
                if ($mailbox->getId() == $params->get('helpscout_mailbox')) {
                    // Mailbox is valid. Checking user
                    $customer = $conversation->getCustomer();
                    if ($customer->getEmail() === $user->email) {
                        // Same user, so we can display the conversation
                        $this->conversation = $conversation;
                    }
                }
            }
        }

        parent::display($tpl);
    }
}
