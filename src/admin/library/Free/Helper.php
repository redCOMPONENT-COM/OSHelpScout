<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSHelpScout\Free;

use Alledia\Framework;
use HelpScout;

defined('_JEXEC') or die();

jimport('joomla.application.component.helper');


abstract class Helper
{
    static protected $apiInstance;

    public static function getAPIInstance()
    {
        if (empty(static::$apiInstance)) {
            $params = \JComponentHelper::getParams('com_oshelpscout');

            // Load the HelpScout API
            $hs = HelpScout\ApiClient::getInstance();
            $hs->setKey($params->get('helpscout_api_key'));

            static::$apiInstance = $hs;
        }

        return static::$apiInstance;
    }

    public static function getCustomerIdByEmail($email)
    {
        $hs       = static::getAPIInstance();
        $session  = Framework\Factory::getSession();
        $customerId = $session->get('oshelpscout_customer_id');

        if (empty($customerId)) {
            // Locate the customer by email
            $customers = $hs->searchCustomersByEmail($email, 1, 'id');
            if (!empty($customers->items)) {
                $customer = $customers->items[0];
                $customerId = $customer->id;

                $session->set('oshelpscout_customer_id', $customerId);
            }
        }

        return $customerId;
    }
}
