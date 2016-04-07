<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout;
use Carbon\Carbon;

defined('_JEXEC') or die();

// jimport('joomla.html.editor');

// $editor            = JEditor::getInstance(JFactory::getUser()->getParam("editor"));

JHtml::_('stylesheet', 'media/com_oshelpscout/css/dropzone.css');
JHtml::_('script', 'media/com_oshelpscout/js/dropzone.js');
?>

<div class="oshs-container">
    <div class="oshs-conversation-breadcrumbs">
        <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversations'); ?>">
            <i class="uk-icon-angle-double-left"></i>&nbsp;<?php echo JText::_('COM_OSHELPSCOUT_BACK_TO_LIST'); ?>
        </a>
    </div>

    <?php if (!empty($this->conversation)) : ?>
        <h4>Conversation</h4>
    <?php endif; ?>

    <h2 class="oshs-conversation-subject">
        <?php if (!empty($this->conversation)) : ?>
            <?php echo $this->conversation->getSubject(); ?>
            <span class="uk-badge oshs-conversation-status <?php echo $this->conversation->getStatus() != 'closed' ? 'uk-badge-warning' : 'uk-badge-success'; ?>">
                <?php echo $this->statusLabel; ?>
            </span>
        <?php else : ?>
            <?php echo JText::_('COM_OSHELPSCOUT_NEW_CONVERSATION'); ?>
        <?php endif; ?>
    </h2>

    <div class="oshs-conversation-reply">
        <form class="uk-form" action="<?php echo JRoute::_('index.php?option=com_oshelpscout&task=conversation.reply'); ?>" method="POST" id="oshs-reply-form">
            <?php if (empty($this->conversation)) : ?>
                <div class="uk-form-row">
                    <?php if (empty($this->subjects)) : ?>
                        <input type="text" name="subject" id="oshs-answer-subject" value="" placeholder="<?php echo JText::_('COM_OSHELPSCOUT_TYPE_SUBJECT'); ?>" />
                    <?php else : ?>
                        <select name="subject" id="oshs-answer-subject">
                            <?php foreach ($this->subjects as $subject) : ?>
                                <option value="<?php echo $subject; ?>"><?php echo $subject; ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
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
            <button type="button" id="oshs-reply-button" class="uk-button uk-button-primary"><?php echo JText::_('COM_OSHELPSCOUT_SUBMIT'); ?></button>
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

            <div class="oshs-message-block oshs-message-by-<?php echo $createdByType; ?>">
                <div class="oshs-message-avatar">
                    <img src="//www.gravatar.com/avatar/<?php echo md5(strtolower(trim($createdBy->getEmail()))); ?>?size=80" width="40" />
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
                        <?php
                        $timezone = JFactory::getUser()->getParam('timezone');
                        if (empty($timezone)) {
                            $timezone = JFactory::getConfig()->get('offset');
                        }
                        $date = Carbon::parse($msg->getCreatedAt());
                        $date->timezone = new DateTimeZone($timezone);
                        ?>
                        <span data-uk-tooltip="{pos:'top'}" title="<?php echo $date->format(JText::_('COM_OSHELPSCOUT_DATE_FORMAT')); ?>">
                            <?php echo $date->diffForHumans(); ?>
                        </span>
                    </div>
                </div>

                <?php
                $body = trim($msg->getBody());
                $htmlBody = $body != strip_tags($body);
                ?>
                <div class="oshs-message-body <?php echo $htmlBody ? 'oshs-message-html' : 'oshs-message-txt'; ?>">
                    <?php echo $body; ?>
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

    <div class="oshs-conversation-breadcrumbs">
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
