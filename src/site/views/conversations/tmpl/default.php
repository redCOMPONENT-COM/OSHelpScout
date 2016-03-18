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
    <table class="table table-striped">
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
                    <td><?php echo $conversation->getSubject(); ?></td>
                    <td><?php echo $conversation->getModifiedAt(); ?></td>
                    <td><?php echo ucfirst($conversation->getStatus()); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else : ?>
    <?php echo JText::_('COM_OSHELPSCOUT_NO_ITEMS'); ?>
<?php endif; ?>
