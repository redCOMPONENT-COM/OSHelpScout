<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
?>
<h2>Conversations</h2>

<?php if (!empty($this->conversations)) : ?>
    <div class="uk-overflow-container">
        <table class="uk-table uk-table-striped">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Modified</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($this->conversations as $conversation) : ?>
                    <tr>
                        <td>
                            <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversation&id=' . $conversation->getId()); ?>">
                                <?php echo $conversation->getSubject(); ?>
                            </a>
                        </td>
                        <td><?php echo $conversation->getModifiedAt(); ?></td>
                        <td><?php echo ucfirst($conversation->getStatus()); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php else : ?>
    <?php echo JText::_('COM_OSHELPSCOUT_NO_ITEMS'); ?>
<?php endif; ?>
