<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die('Restricted access');
$threads = $this->conversation->getThreads();
?>
<h3>Conversation</h3>

<?php if (!empty($this->conversation)) : ?>
    <div class="uk-grid">
        <div class="uk-width-1-1">
            <h2><?php echo $this->conversation->getSubject(); ?></h2>
            <div class="uk-badge"><?php echo $this->conversation->getStatus(); ?></div>
        </div>

        <?php foreach ($threads as $msg) : ?>
        <div class="uk-width-1-1">
            <?php echo $msg->getBody(); ?>
        </div>
        <?php endforeach; ?>
    </div>
    <pre>
        <?php var_dump($this->conversation); ?>
    </pre>

<?php else : ?>
    <?php echo JText::_('COM_OSHELPSCOUT_NOT_FOUND'); ?>
<?php endif; ?>
