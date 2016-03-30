<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout;

defined('_JEXEC') or die();
?>
<div class="uk-grid oshs-container">
    <div class="uk-width-1-1">
        <h2>Conversations</h2>
    </div>

    <div class="uk-width-1-1">
        <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversation'); ?>" class="uk-button oshs-new-button">
            <?php echo JText::_('COM_OSHELPSCOUT_NEW_CONVERSATION'); ?>
        </a>
    </div>

    <?php if (!empty($this->conversations)) : ?>
        <div class="uk-overflow-container">
            <table class="uk-table uk-table-striped">
                <thead>
                    <tr>
                        <th>Subject</th>
                        <th>Messages</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($this->conversations as $conversation) : ?>
                        <?php $statusStr = OSHelpScout\Free\Helper::getConversationStatusStr($conversation); ?>
                        <tr>
                            <td>
                                <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversation&id=' . $conversation->getId()); ?>">
                                    <?php echo $conversation->getSubject(); ?>
                                </a>
                                <div class="oshs-preview-text">
                                    <i class="uk-icon-angle-double-right"></i>
                                    "<?php echo $conversation->getPreview(); ?>"
                                </div>
                            </td>
                            <td class="uk-text-center">
                                <span class="uk-badge uk-badge-notification"><?php echo $conversation->getThreadCount(); ?></span>
                            </td>
                            <td class="uk-text-center">
                                <div class="uk-badge <?php echo $conversation->getStatus() != 'closed' ? 'uk-badge-warning' : 'uk-badge-success'; ?>">
                                    <?php echo JText::_($statusStr); ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <?php echo JText::_('COM_OSHELPSCOUT_NO_ITEMS'); ?>
    <?php endif; ?>
</div>
