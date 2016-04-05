<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout;

defined('_JEXEC') or die;

require_once JPATH_ADMINISTRATOR . '/components/com_oshelpscout/include.php';

class OSHelpScoutViewHsdynamicapp extends JViewLegacy
{
    public function display($tpl = null)
    {
        $app       = JFactory::getApplication();
        $secretKey = $app->getMenu()->getActive()->params->get('helpscout_app_secret');
        $hsApp     = new HelpScoutApp\DynamicApp($secretKey);

        $this->customers = array();

        if ($hsApp->isSignatureValid()) {
            $customer = $hsApp->getCustomer();

            // Get the customer from the API to query more possible emails, not the only one that was sent
            $customerId = OSHelpScout\Free\Helper::getCustomerIdByEmail($customer->getEmail());
            $hs         = OSHelpScout\Free\Helper::getAPIInstance();
            $customer   = $hs->getCustomer($customerId);
            $emails     = $customer->getEmails();

            foreach ($emails as $email) {
                $user = OSHelpScout\Free\Helper::getUserByEmail($email->getValue());

                if (is_object($user)) {
                    if (!$user->guest && !empty($user->groups)) {
                        $this->customers[] = $user;
                    }
                }
            }

            ob_start();
            parent::display($tpl);
            $html = ob_get_clean();

            echo $hsApp->getResponse($html);
        } else {
            echo 'Invalid Request';
        }

        jexit();
    }
}
