<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com hello@alledia.com
 * @copyright 2016 Open Source Training LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Carbon\Carbon;

defined('_JEXEC') or die();
?>

<?php if (!empty($this->customers)) : ?>
    <?php foreach ($this->customers as $customer) : ?>
        <p><strong><?php echo JText::_('COM_OSHELPSCOUT_USERNAME'); ?>: <?php echo $customer->username; ?></strong></p>
        <ul>
            <li><strong><?php echo JText::_('COM_OSHELPSCOUT_ID'); ?></strong>: <?php echo $customer->id; ?></li>
            <li><strong><?php echo JText::_('COM_OSHELPSCOUT_NAME'); ?></strong>: <?php echo $customer->name; ?></li>
            <li><strong><?php echo JText::_('COM_OSHELPSCOUT_EMAIL'); ?></strong>: <?php echo $customer->email; ?></li>
            <li>
                <?php
                $timezone = JFactory::getConfig()->get('offset');
                $date = Carbon::parse($customer->registerDate);
                $date->timezone = new DateTimeZone($timezone);
                ?>
                <strong><?php echo JText::_('COM_OSHELPSCOUT_REGISTER_DATE'); ?></strong>:&nbsp;
                <span data-uk-tooltip="{pos:'top'}" title="<?php echo $date->format(JText::_('COM_OSHELPSCOUT_DATE_FORMAT')); ?>">
                    <?php echo $date->diffForHumans(); ?>
                </span>
            </li>
            <?php if (!empty($customer->groups)) : ?>
                <li><strong><?php echo JText::_('COM_OSHELPSCOUT_GROUPS'); ?></strong>:
                    <ul>
                        <?php foreach ($customer->groups as $group) : ?>
                            <li><?php echo $group->title; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    <?php endforeach; ?>
<?php else : ?>
    <p><strong><?php echo JText::_('COM_OSHELPSCOUT_NOT_A_MEMBER'); ?></strong></p>
<?php endif; ?>
