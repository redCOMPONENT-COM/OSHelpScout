<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout;
use Alledia\Framework;

defined('_JEXEC') or die();

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');


class OSHelpScoutControllerConversations extends OSHelpScout\Free\Joomla\Controller\Json
{
    /**
     * Return a list of conversations for the current user. If guest,
     * returns empty array.
     *
     * @return void
     */
    public function getItems()
    {
        $hs        = OSHelpScout\Free\Helper::getAPIInstance();
        $mailboxId = OSHelpScout\Free\Helper::getCurrentMailboxId();
        $list      = array();

        // Locate the customer by email
        $customerId = OSHelpScout\Free\Helper::getCurrentCustomerId();
        if (!empty($customerId)) {
            // Get the customer conversations
            $apiResult = $hs->getConversationsForCustomerByMailbox(
                $mailboxId,
                $customerId
            );
            // @todo: implement pagination

            $items = $apiResult->getItems();

            // Transform the result into a json parsable object
            if (!empty($items)) {
                foreach ($items as $item) {
                    // Ignore drafts
                    if (!$item->isDraft()) {
                        $statusLabel = OSHelpScout\Free\Helper::getConversationStatusStr($item->getStatus());

                        $conversation              = new stdClass;
                        $conversation->id          = $item->getId();
                        $conversation->type        = $item->getType();
                        $conversation->number      = $item->getNumber();
                        $conversation->threadCount = $item->getThreadCount();
                        $conversation->status      = $item->getStatus();
                        $conversation->statusLabel = JText::_($statusLabel);
                        $conversation->subject     = $item->getSubject();
                        $conversation->preview     = $item->getPreview();

                        $list[] = $conversation;
                    }
                }
            }
        }

        echo json_encode(
            array(
                'success' => true,
                'list'    => $list
            )
        );
    }
}
