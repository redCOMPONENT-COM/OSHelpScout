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
            <div class="oshs-message-block">
                <?php if (in_array($msg->getType(), array('message', 'customer'))) : ?>
                    <div class="oshs-message-head">
                        <?php $date = new JDate($msg->getCreatedAt()); ?>
                        <?php echo JText::_('COM_OSHELPSCOUT_CREATED_AT'); ?>: <?php echo $date->format(JText::_('DATE_FORMAT_LC2')); ?>
                    </div>

                    <div class="oshs-message-body">
                        <?php echo $msg->getBody(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div>
            <a href="<?php JRoute::_('index.php?option=com_oshelpscout&view=conversations'); ?>">
                <?php echo JText::_('COM_OSHELPSCOUT_BACK_TO_LIST'); ?>
            </a>
        </div>
    </div>
    <pre>
        <?php var_dump($this->conversation); ?>
    </pre>

<?php else : ?>
    <?php echo JText::_('COM_OSHELPSCOUT_NOT_FOUND'); ?>
<?php endif; ?>
