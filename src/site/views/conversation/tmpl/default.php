<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout;

defined('_JEXEC') or die();

$threads           = $this->conversation->filteredThread;
$status            = $this->conversation->getStatus();
$statusStr         = OSHelpScout\Free\Helper::getConversationStatusStr($this->conversation);
$conversationIndex = 0;
$conversationCount = count($threads);
?>
<h3>Conversation</h3>

<?php if (!empty($this->conversation)) : ?>
    <div class="uk-grid">
        <div class="uk-width-1-1">
            <h2 class="oshs-conversation-subject">
                <?php echo $this->conversation->getSubject(); ?>
                <span class="uk-badge oshs-conversation-status <?php echo $status != 'closed' ? 'uk-badge-warning' : 'uk-badge-success'; ?>">
                    <?php echo JText::_($statusStr); ?>
                </span>
            </h2>
        </div>

        <?php foreach ($threads as $msg) : ?>
            <?php
                $conversationIndex++;
                $createdBy     = $msg->getCreatedBy();
                $createdByType = $createdBy->getType();
                $attachments   = $msg->getAttachments();
            ?>

            <div class="uk-width-1-1">
                <div class="oshs-message-block oshs-message-by-<?php echo $createdByType; ?>">
                    <div class="oshs-message-avatar">
                        <img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($createdBy->getEmail()))); ?>?size=80" width="40" />
                    </div>

                    <div class="oshs-message-head">
                        <div class="oshs-message-date">
                            <?php $date = new JDate($msg->getCreatedAt()); ?>
                            <?php if ($conversationIndex === $conversationCount) : ?>
                                <?php echo JText::_('COM_OSHELPSCOUT_CREATED_AT'); ?>:
                            <?php else : ?>
                                <?php echo JText::_('COM_OSHELPSCOUT_REPLIED_AT'); ?>:
                            <?php endif; ?>
                            <?php echo $date->format(JText::_('DATE_FORMAT_LC2')); ?>
                        </div>

                        <div class="oshs-message-by">
                            <?php if ($createdByType == 'customer') : ?>
                                <?php echo JText::_('COM_OSHELPSCOUT_BY'); ?>: <div class="uk-badge"><?php echo JText::_('COM_OSHELPSCOUT_YOU'); ?></div>
                            <?php else : ?>
                                <?php echo JText::_('COM_OSHELPSCOUT_BY'); ?>: <div class="uk-badge uk-badge-warning"><?php echo JText::_('COM_OSHELPSCOUT_STAFF'); ?></div>&nbsp;
                                <?php echo $createdBy->getFirstName() . ' ' . $createdBy->getLastName(); ?>&nbsp;
                            <?php endif; ?>
                        </div>

                    </div>

                    <div class="oshs-message-body">
                        <?php echo trim($msg->getBody()); ?>
                    </div>

                    <?php if (count($attachments) > 0) : ?>
                        <div class="oshs-message-attachments">
                            <i class="uk-icon-paperclip"></i>
                            <?php foreach ($attachments as $file) : ?>
                                <a href="<?php echo $file->getUrl(); ?>" target="_blank" class="oshs-message-attachment"><?php echo $file->getFileName(); ?></a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <div>
            <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversations'); ?>">
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
