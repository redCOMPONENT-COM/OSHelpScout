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
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


abstract class Helper
{
    const UPLOAD_PREFIX_SESSION = 'oshs-uploads-';
    const UPLOAD_PREFIX         = 'oshs-up-';
    const ID_PREFIX_SESSION     = 'oshs-new-id-';

    static protected $apiInstance;

    /**
     * Get the HelpScout API Instance
     *
     * @return HelpScout\ApliClient
     */
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

    /**
     * Get the customer ID in HelpScout based on the sent email. The Id
     * is cached in the session, to reduce API calls.
     *
     * @param  string  $email  The customer's email address
     *
     * @return int
     */
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

    /**
     * Get the customer ID in HelpScout based on the current user.
     *
     * @return int|bool  Returns the ID if logged in and exists. If not, false.
     */
    public static function getCurrentCustomerId()
    {
        $user = Framework\Factory::getUser();

        if ($user->guest) {
            return false;
        }

        return static::getCustomerIdByEmail($user->email);
    }

    /**
     * Get Mailbox Id for the current menu. Returns false, if not found.
     *
     * @return int
     */
    public static function getCurrentMailboxId()
    {
        $app       = Framework\Factory::getApplication();
        $mailboxId = $app->getMenu()->getActive()->params->get('helpscout_mailbox');

        if (empty($mailboxId)) {
            return false;
        }

        return $mailboxId;
    }

    /**
     * Get the conversation based on ID, but validating the user's rights.
     * If the user's email is different from the email in the conversation,
     * return false. Check if the user owns the conversation.
     *
     * @param  int $conversationId  The conversation's Id
     * @param  int $mailboxId       If specified will compare the
     *                              conversation's mailbox with the given Id.
     *
     * @return HelpScout\Conversation
     */
    public static function getConversation($conversationId, $mailboxId = null)
    {
        $hs   = static::getAPIInstance();
        $user = Framework\Factory::getUser();

        // Validate the current user as customer
        $customerId = static::getCurrentCustomerId();
        if (!empty($customerId)) {
            // Get the customer conversations
            $conversation = $hs->getConversation($conversationId);

            // Check if the conversation is on the correct mailbox
            $mailbox = $conversation->getMailbox();
            if ($mailbox->getId() == $mailboxId) {
                // Check if the user owns the conversation
                $customer = $conversation->getCustomer();
                if ($customer->getId() === $customerId) {
                    static::filterThreadInConversation($conversation);
                    // Same user, so we can display the conversation
                    return $conversation;
                }
            }
        }

        return false;
    }

    /**
     * Filter the thread in the conversation to display only messages from
     * the customer and staff members. Ignores notes and other type of
     * messages.
     *
     * @param  HelpScout\Conversation  $conversation
     */
    protected static function filterThreadInConversation($conversation)
    {
        $thread         = $conversation->getThreads();
        $validMsgType   = array('message', 'customer');
        $filteredThread = array();

        foreach ($thread as $msg) {
            if (in_array($msg->getType(), $validMsgType)) {
                $filteredThread[] = $msg;
            }
        }

        $conversation->filteredThread = $filteredThread;
    }

    /**
     * Get the status string to display to users. This method avoids to
     * display: closed, as a status, since the ticket can be closed but
     * not resolved yet.
     *
     * @param  HelpScout\Conversation  $conversation  The conversation instance
     *
     * @return  string  The converted status
     */
    public static function getConversationStatusStr($conversation)
    {
        $status = $conversation->getStatus();

        if ($status === 'closed') {
            $status = 'replied';
        }

        return strtoupper('COM_OSHELPSCOUT_STATUS_' . $status);
    }

    public static function getUploadSessionData($conversationId)
    {
        $session = Framework\Factory::getSession();
        $key     = static::UPLOAD_PREFIX_SESSION . $conversationId;

        return $session->get($key, array());
    }

    public static function setUploadSessionData($conversationId, $data)
    {
        $session = Framework\Factory::getSession();
        $key     = static::UPLOAD_PREFIX_SESSION . $conversationId;

        $session->set($key, $data);
    }

    public static function cleanUploadSessionData($conversationId)
    {
        $session = Framework\Factory::getSession();
        $key     = static::UPLOAD_PREFIX_SESSION . $conversationId;

        $session->set($key, array());
    }

    public static function getTmpUploadFolder()
    {
        return Framework\Factory::getApplication()->getCfg('tmp_path') . '/oshelpscout/';
    }

    public static function cleanUploadTmpFiles($conversationId)
    {
        // Cleanup tmp uploaded files
        $tmpPath = static::getTmpUploadFolder();
        $tmpFiles = \JFolder::files($tmpPath, static::UPLOAD_PREFIX . $conversationId . '-', true);

        if (!empty($tmpFiles)) {
            foreach ($tmpFiles as $file) {
                \JFile::delete($tmpPath . $file);
            }
        }
    }

    public static function getUploadTmpFilename($conversationId)
    {
        $tmpPath = static::getTmpUploadFolder();

        return tempnam($tmpPath, static::UPLOAD_PREFIX . $conversationId . '-');
    }

    public static function isNewId($id)
    {
        return preg_match('/^new\-.*/', $id);
    }

    public static function getTmpConversationIdFromSession()
    {
        $session = Framework\Factory::getSession();

        $id = $session->get(static::ID_PREFIX_SESSION, 0);

        if (empty($id)) {
            $id = uniqid('new-');
            $session->set(static::ID_PREFIX_SESSION, $id);
        }

        return $id;
    }

    public static function cleanTmpConversationIdFromSession()
    {
        $session = Framework\Factory::getSession();

        $session->set(static::ID_PREFIX_SESSION, null);
    }
}
