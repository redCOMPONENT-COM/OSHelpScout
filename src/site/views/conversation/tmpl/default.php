<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout;

defined('_JEXEC') or die();

// jimport('joomla.html.editor');

// $editor            = JEditor::getInstance(JFactory::getUser()->getParam("editor"));

JHtml::_('stylesheet', 'media/com_oshelpscout/css/dropzone.css');
JHtml::_('script', 'media/com_oshelpscout/js/dropzone.js');
?>

<div class="uk-grid oshs-container">
    <div>
        <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversations'); ?>">
            <i class="uk-icon-angle-double-left"></i>&nbsp;<?php echo JText::_('COM_OSHELPSCOUT_BACK_TO_LIST'); ?>
        </a>
    </div>

    <div class="uk-width-1-1">
        <?php if (!empty($this->conversation)) : ?>
            <h3>Conversation</h3>
        <?php endif; ?>

        <h2 class="oshs-conversation-subject">
            <?php if (!empty($this->conversation)) : ?>
                <?php $statusStr = OSHelpScout\Free\Helper::getConversationStatusStr($this->conversation); ?>
                <?php echo $this->conversation->getSubject(); ?>
                <span class="uk-badge oshs-conversation-status <?php echo $status != 'closed' ? 'uk-badge-warning' : 'uk-badge-success'; ?>">
                    <?php echo JText::_($statusStr); ?>
                </span>
            <?php else : ?>
                <?php echo JText::_('COM_OSHELPSCOUT_NEW_CONVERSATION'); ?>
            <?php endif; ?>
        </h2>
    </div>

    <div class="uk-width-1-1 oshs-conversation-reply">
        <form class="uk-form" action="<?php echo JRoute::_('index.php?option=com_oshelpscout&task=conversation.reply'); ?>" method="POST" id="oshs-reply-form">
            <?php if (empty($this->conversation)) : ?>
                <div class="uk-form-row">
                    <input type="text" name="subject" id="oshs-answer-subject" value="" placeholder="<?php echo JText::_('COM_OSHELPSCOUT_TYPE_SUBJECT'); ?>" />
                </div>
            <?php endif; ?>
            <div class="uk-form-row">
                <?php //echo $editor->display('body', '', '550', '200', '60', '10', false); ?>
                <textarea name="body" id="oshs-answer-body" placeholder="<?php echo JText::_('COM_OSHELPSCOUT_TYPE_MESSAGE'); ?>"></textarea>
            </div>
            <input type="hidden" name="conversationId" value="<?php echo $this->conversationId; ?>" />
            <input type="hidden" name="itemId" value="<?php echo $this->itemId; ?>" />
            <?php echo JHTML::_('form.token'); ?>
        </form>

        <div class="oshs-upload-box">
            <form action="<?php echo JRoute::_('index.php?option=com_oshelpscout&task=conversation.upload'); ?>" class="dropzone uk-form" id="helpscout-upload">
                <div class="fallback">
                    <input name="file" type="file" multiple />
                </div>
                <input type="hidden" name="conversationId" value="<?php echo $this->conversationId; ?>" />
                <?php echo JHTML::_('form.token'); ?>
            </form>
        </div>

        <div>
            <button type="button" id="oshs-reply-button" class="uk-button"><?php echo JText::_('COM_OSHELPSCOUT_SUBMIT'); ?></button>
        </div>
    </div>

    <?php if (!empty($this->conversation)) : ?>
        <?php
            $threads           = $this->conversation->filteredThread;
            $status            = $this->conversation->getStatus();
            $conversationIndex = 0;
            $conversationCount = count($threads);
        ?>
        <?php foreach ($threads as $msg) : ?>
            <?php
                $conversationIndex++;
                $createdBy     = $msg->getCreatedBy();
                $createdByType = $createdBy->getType();
                $attachments   = $msg->getAttachments();
            ?>

            <div class="uk-width-1-1 oshs-message-block oshs-message-by-<?php echo $createdByType; ?>">
                <div class="oshs-message-avatar">
                    <img src="http://www.gravatar.com/avatar/<?php echo md5(strtolower(trim($createdBy->getEmail()))); ?>?size=80" width="40" />
                </div>

                <div class="oshs-message-head">
                    <div class="oshs-message-by">
                        <?php echo $createdBy->getFirstName() . ' ' . $createdBy->getLastName(); ?>&nbsp;
                        <?php if ($conversationIndex === $conversationCount) : ?>
                            <span class="uk-text-muted"><?php echo JText::_('COM_OSHELPSCOUT_STARTED_CONVERSATION'); ?></span>
                        <?php else : ?>
                            <span class="uk-text-muted"><?php echo JText::_('COM_OSHELPSCOUT_REPLIED'); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="oshs-message-date uk-text-muted">
                        <?php $date = new JDate($msg->getCreatedAt()); ?>
                        <?php echo $date->format(JText::_('DATE_FORMAT_LC2')); ?>
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
        <?php endforeach; ?>
    <?php endif; ?>

    <div>
        <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversations'); ?>">
            <i class="uk-icon-angle-double-left"></i>&nbsp;<?php echo JText::_('COM_OSHELPSCOUT_BACK_TO_LIST'); ?>
        </a>
    </div>
</div>

<script>
    (function($, window, Dropzone) {
        // Event listener for the reply button
        $('#oshs-reply-button').on('click', function() {
            var body = $('#oshs-answer-body').val();
            if (body.trim() != '') {
                $('#oshs-reply-form').submit();
            } else {
                // msg
            }
        });

        // Configure the upload manager
        Dropzone.options.helpscoutUpload = {
            paramName: "file", // The name that will be used to transfer the file
            maxFilesize: 2, // MB
            uploadMultiple: true,
            autoProcessQueue: false,
            acceptedFiles: 'image/*,application/pdf,.psd,.zip,.tar,.gz,.bz2,.doc,.xml,.html,.txt,.docx,.xmlx',
            complete: function(t) {
                $('#oshs-reply-form').submit();
            },
            init: function(t) {
                var dropzoneInstance = Dropzone.instances[0];

                function getQueuedFilesCount() {
                    var queuedFiles = 0;

                    for (var i = 0; i < dropzoneInstance.files.length; i++) {
                        file = dropzoneInstance.files[i];

                        if (file.status == 'queued') {
                            queuedFiles++;
                        }
                    }

                    return queuedFiles;
                }

                // Only submit if there are no files to upload
                $('#oshs-reply-form').on('submit', function() {
                    var canSubmit = false,
                        queuedFiles;

                    try {
                        queuedFiles = getQueuedFilesCount();

                        if (queuedFiles > 0) {
                            dropzoneInstance.processQueue();
                        } else {
                            canSubmit = true;
                        }
                    }
                    catch(err) {
                        if (typeof(console) != 'undefined') {
                            console.log(err);
                        }

                        canSubmit = false;
                    }

                    return canSubmit;
                });
            }
        };
    })(jQuery, window, Dropzone);
</script>
