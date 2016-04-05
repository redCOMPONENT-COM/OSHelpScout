<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com hello@alledia.com
 * @copyright 2016 Open Source Training LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();
?>

<?php if (!empty($this->customers)) : ?>
    <?php foreach ($this->customers as $customer) : ?>
        <p><?php echo JText::_('COM_OSHELPSCOUT_USERNAME'); ?>: <?php echo $customer->username; ?></p>
        <ul>
            <li><?php echo JText::_('COM_OSHELPSCOUT_ID'); ?>: <?php echo $customer->id; ?></li>
            <li><?php echo JText::_('COM_OSHELPSCOUT_NAME'); ?>: <?php echo $customer->name; ?></li>
            <li><?php echo JText::_('COM_OSHELPSCOUT_EMAIL'); ?>: <?php echo $customer->email; ?></li>
            <?php if (!empty($customer->groups)) : ?>
                <li><?php echo JText::_('COM_OSHELPSCOUT_GROUPS'); ?>:
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
    <p><b><?php echo JText::_('COM_OSHELPSCOUT_NOT_A_MEMBER'); ?></b></p>
<?php endif; ?>
