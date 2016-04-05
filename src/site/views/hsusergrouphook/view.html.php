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

class OSHelpScoutViewHsusergrouphook extends JViewLegacy
{
    public function display($tpl = null)
    {
        // $input = @file_get_contents('php://input');
        // file_put_contents(JPATH_SITE . '/tmp/h.tmp', $input);

        $app       = JFactory::getApplication();
        $secretKey = $app->getMenu()->getActive()->params->get('helpscout_app_secret');
        $hs        = OSHelpScout\Free\Helper::getAPIInstance();
        $hook      = new HelpScout\Webhook($secretKey);

        if ($hook->isValid() || 1) {
            $customer     = $hook->getCustomer();
            $conversation = $hook->getConversation();
            $tags         = $conversation->getTags();

            if (is_object($customer)) {
                // Get the customer from the API to query more possible emails, not the only one that was sent
                $hs       = OSHelpScout\Free\Helper::getAPIInstance();
                $customer = $hs->getCustomer($customer->getId());
                $emails   = $customer->getEmails();

                foreach ($emails as $email) {
                    $user = OSHelpScout\Free\Helper::getUserByEmail($email->getValue());
                    if (is_object($user)) {
                        if (!$user->guest && !empty($user->groups)) {
                            foreach ($user->groups as $group) {
                                $tags[] = $group->title;
                            }
                        }
                    }
                }
            }

            if (empty($tags)) {
                $tags[] = JText::_('COM_OSHELPSCOUT_GUEST_TAG');
            }

            // Remove duplicated tags
            array_unique($tags);

            $conversation->setTags($tags);
            $hs->updateConversation($conversation);
        } else {
            echo 'Invalid Request';
        }

        jexit();
    }
}
