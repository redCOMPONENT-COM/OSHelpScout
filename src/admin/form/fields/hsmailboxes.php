<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('JPATH_BASE') or die;

use Alledia\OSHelpScout;

jimport('joomla.html.html');
JFormHelper::loadFieldClass('list');

include_once JPATH_ADMINISTRATOR . '/components/com_oshelpscout/include.php';


class JFormFieldHSMailBoxes extends JFormFieldList
{
    protected $type = 'HSMailBoxes';

    protected function getOptions()
    {
        // Initialize variables.
        $options = parent::getOptions();

        $hs = OSHelpScout\Free\Helper::getAPIInstance();
        $mailboxes = $hs->getMailboxes()->getItems();

        if (!empty($mailboxes)) {
            foreach ($mailboxes as $mailbox) {
                // Create a new option object based on the <option /> element.
                $tmp = JHtml::_(
                    'select.option',
                    (string) $mailbox->getId(),
                    $mailbox->getName(),
                    'value',
                    'text'
                );

                // Add the option object to the result set.
                $options[] = $tmp;
            }

            reset($options);
        }

        return $options;
    }
}
