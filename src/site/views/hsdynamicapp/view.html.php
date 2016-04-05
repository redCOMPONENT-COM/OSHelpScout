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
                $db = JFactory::getDbo();
                // Look for an user id
                $query = $db->getQuery(true)
                    ->select('*')
                    ->from('#__users')
                    ->where('email = ' . $db->quote($email->getValue()));
                $db->setQuery($query);
                $userFromDB = $db->loadObject();

                // Get the user's data
                if (is_object($userFromDB)) {
                    $tmpUser = JFactory::getUser($userFromDB->id);

                    if (!$tmpUser->guest && !empty($tmpUser->groups)) {
                        $query = $db->getQuery(true)
                            ->select('title')
                            ->from('#__usergroups')
                            ->where('id IN ("' . implode('","', $tmpUser->groups) . '")');
                        $db->setQuery($query);
                        $tmpUser->groups = $db->loadObjectList();

                        $this->customers[] = $tmpUser;
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
