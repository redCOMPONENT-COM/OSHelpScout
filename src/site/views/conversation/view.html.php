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
        $id        = $app->input->get('id');
        $itemId    = $app->input->get('Itemid');
        $mailboxId = OSHelpScout\Free\Helper::getCurrentMailboxId();

        $this->conversation = null;
        $this->itemId       = $itemId;

        if (!empty($id)) {
            // Get the customer's conversation
            $this->conversation = OSHelpScout\Free\Helper::getConversation($id, $mailboxId);
        }

        parent::display($tpl);
    }
}
