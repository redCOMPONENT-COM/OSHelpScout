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
        $hs       = OSHelpScout\Free\Helper::getAPIInstance();
        $user     = Framework\Factory::getUser();
        $app      = Framework\Factory::getApplication();
        $menuitem = $app->getMenu()->getActive();
        $params   = $menuitem->params;

        $this->conversations = array();

        if (!$user->guest) {
            // Locate the customer by email
            $customers = $hs->searchCustomersByEmail($user->email)->getItems();
            if (!empty($customers)) {
                $customer = $customers[0];

                // Get the customer conversations
                $conversationsResult = $hs->getConversationsForCustomerByMailbox(
                    $params->get('helpscout_mailbox'),
                    $customer->getId()
                );
                // @todo: implement pagination

                $this->conversations = $conversationsResult->getItems();
            }
        }

        parent::display($tpl);
    }
}
