<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Framework;
use Alledia\OSHelpScout;
use Carbon\Carbon;

defined('_JEXEC') or die();

$extension = Framework\Factory::getExtension('oshelpscout', 'component');
$staticHash = md5($extension->manifest->version);

JHtml::stylesheet(JUri::base() . 'media/com_oshelpscout/css/dropzone.css?' . $staticHash);
JHtml::script(Juri::base() . 'media/com_oshelpscout/js/dropzone.js?' . $staticHash);
JHtml::script(Juri::base() . 'media/com_oshelpscout/js/ractive.min.js?' . $staticHash);
?>

<div class="oshs-container">

    <div class="oshs-conversation-breadcrumbs">
        <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversations'); ?>">
            <i class="uk-icon-angle-double-left"></i>&nbsp;<?php echo JText::_('COM_OSHELPSCOUT_BACK_TO_LIST'); ?>
        </a>
    </div>

    <div id="oshs-conversation-container"></div>

    <div class="oshs-conversation-breadcrumbs">
        <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversations'); ?>">
            <i class="uk-icon-angle-double-left"></i>&nbsp;<?php echo JText::_('COM_OSHELPSCOUT_BACK_TO_LIST'); ?>
        </a>
    </div>
</div>

<script id="conversation-template" type="text/ractive">
    <div class="uk-width-1-1 uk-overflow-container">
        {{^isNewConversation}}
            <h4>Conversation</h4>
        {{/isNewConversation}}

        <h2 class="oshs-conversation-subject">
            {{#if isNewConversation}}
                <?php echo JText::_('COM_OSHELPSCOUT_NEW_CONVERSATION'); ?>
            {{else}}
                {{subject}}

                {{#statusLabel}}
                    <div class="uk-badge pull-right {{#if status != 'closed'}}uk-badge-warning{{else}}uk-badge-success{{/if}}">
                        {{statusLabel}}
                    </div>
                {{/statusLabel}}
            {{/if}}
        </h2>

        <div class="oshs-conversation-reply">
            <form class="uk-form" action="<?php echo JRoute::_('index.php?option=com_oshelpscout&task=conversation.reply'); ?>" method="POST" id="oshs-reply-form">
                {{#isNewConversation}}
                    <div class="uk-form-row">
                        <?php if (empty($this->subjects)) : ?>
                            <input type="text" name="subject" id="oshs-answer-subject" value="{{subject}}" placeholder="<?php echo JText::_('COM_OSHELPSCOUT_TYPE_SUBJECT'); ?>" />
                        <?php else : ?>
                            <select name="subject" id="oshs-answer-subject" value="{{subject}}">
                                <?php foreach ($this->subjects as $subject) : ?>
                                    <option value="<?php echo $subject; ?>"><?php echo $subject; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                {{/isNewConversation}}

                <div class="uk-form-row">
                    <textarea name="body" id="oshs-answer-body" placeholder="<?php echo JText::_('COM_OSHELPSCOUT_TYPE_MESSAGE'); ?>" value="{{replyBody}}"></textarea>
                </div>
            </form>

            <div class="oshs-upload-box">
                <form action="<?php echo JRoute::_('index.php?option=com_oshelpscout&task=conversation.upload&format=json'); ?>" class="dropzone uk-form" id="helpscout-upload">
                    <div class="fallback">
                        <input name="file" type="file" multiple />
                    </div>
                    <input type="hidden" name="conversationId" value="{{id}}" />
                    <?php echo JHTML::_('form.token'); ?>
                </form>
            </div>

            <div>
                <button type="button" on-click="reply" {{#isSubmitting}}disabled{{/isSubmitting}} id="oshs-reply-button" class="uk-button uk-button-primary">{{#if isSubmitting}}<?php echo JText::_('COM_OSHELPSCOUT_PLEASE_WAIT'); ?>{{else}}<?php echo JText::_('COM_OSHELPSCOUT_SUBMIT'); ?>{{/if}}</button>

                {{#if submissionError}}
                    <div class="uk-alert uk-alert-danger">
                        {{submissionError}}
                    </div>
                {{/if}}

                {{#if submissionSuccess}}
                    <div class="uk-alert uk-alert-info">
                        {{submissionSuccess}}
                    </div>
                {{/if}}
            </div>
        </div>


        {{#if foundError}}
            <div class="uk-alert uk-alert-danger">
                <?php echo JText::_('COM_OSHELPSCOUT_MSG_ERROR_FOUND'); ?>&nbsp;{{refreshCountDown}}&nbsp;<?php echo JText::_('COM_OSHELPSCOUT_SECONDS'); ?>
            </div>
        {{/if}}

        {{^isNewConversation}}
            <div id="oshs-messages-container">
                {{#if isLoading}}
                    <div class="oshs-refresh-loader">
                        <i class="uk-icon-refresh uk-icon-spin"></i>
                    </div>
                {{/if}}

                {{#if refreshCountDown > 0}}
                    {{^foundError}}
                        {{^isLoading}}
                            <div class="oshs-auto-refresh">
                                This list will auto-refresh in {{refreshCountDownLabel()}}
                            </div>
                        {{/isLoading}}
                    {{/foundError}}
                {{/if}}

                {{^isLoading}}
                    {{^thread}}
                        <div class="uk-alert">
                            <?php echo JText::_('COM_OSHELPSCOUT_NO_ITEMS'); ?>
                        </div>
                    {{/thread}}
                {{/if}}

                {{#thread:threadIndex}}
                    <div class="oshs-message-block oshs-message-by-{{this.type}}" intro="typewriter:{speed:400}">
                        <div class="oshs-message-avatar">
                            <img src="//www.gravatar.com/avatar/{{this.gravatarHash}}?size=80" width="40" />
                        </div>

                        <div class="oshs-message-head">
                            <div class="oshs-message-by">
                                {{this.creatorName}}&nbsp;
                                {{#if threadIndex == threadCount - 1}}
                                    <span class="uk-text-muted"><?php echo JText::_('COM_OSHELPSCOUT_STARTED_CONVERSATION'); ?></span>
                                {{else}}
                                    <span class="uk-text-muted"><?php echo JText::_('COM_OSHELPSCOUT_REPLIED'); ?></span>
                                {{/if}}
                            </div>
                            <div class="oshs-message-date uk-text-muted">
                                <span data-uk-tooltip="{pos:'top'}" title="{{this.createdAt}}">
                                    {{this.createdAtRelative}}
                                </span>
                            </div>
                        </div>

                        <div class="oshs-message-body {{#if this.isHtml}}oshs-message-html{{else}}oshs-message-txt{{/if}}">
                            {{{this.body}}}
                        </div>

                        {{#if this.attachments}}
                            <div class="oshs-message-attachments">
                                <i class="uk-icon-paperclip"></i>
                                {{#this.attachments}}
                                    <a href="{{this.url}}" target="_blank" class="oshs-message-attachment">{{this.filename}}</a>
                                {{/this.attachments}}
                            </div>
                        {{/if}}
                    </div>
                {{/thread}}
            </div>
        {{/isNewConversation}}
    </div>
</script>

<script>
(function(Ractive, $) {
    Ractive.DEBUG = false;

    Number.prototype.leftZeroPad = function(numZeros) {
        var n = Math.abs(this);
        var zeros = Math.max(0, numZeros - Math.floor(n).toString().length );
        var zeroString = Math.pow(10,zeros).toString().substr(1);
        if( this < 0 ) {
            zeroString = '-' + zeroString;
        }

        return zeroString + n;
    }

    var $replyForm = $('#oshs-reply-form'   );

    var ractive = new Ractive({
        el: '#oshs-conversation-container',
        template: '#conversation-template',
        data: {
            'conversationId'         : '<?php echo $this->conversationId; ?>',
            'itemId'                 : '<?php echo $this->itemId; ?>',
            'thread'                 : null,
            'isNewConversation'      : <?php echo ($this->isNewConversation) ? 'true' : 'false'; ?>,
            'status'                 : null,
            'statusLabel'            : null,
            'subject'                : null,
            'threadCount'            : null,
            'isLoading'              : false,
            'isSubmitting'           : false,
            'foundError'             : false,
            'successRefreshInterval' : 300, // 5 min
            'errorRefreshInterval'   : 10, // 10 s
            'refreshCountDown'       : 0,
            'refreshIntervalObj'     : null,
            'replyBody'              : '',
            'submissionError'        : false,
            'submissionSuccess'      : null,
            'refreshCountDownLabel'  : function() {
                var value = this.get('refreshCountDown'),
                    min   = Math.floor(value / 60),
                    sec   = value % 60,
                    str   = '';

                return min.leftZeroPad(1) + ':' + sec.leftZeroPad(2);
            }
        },
        load: function() {
            self = this;

            // Avoid look for a thread in new conversations
            if (self.get('isNewConversation')) {
                return;
            }

            self.set('isLoading', true);
            self.set('foundError', false);

            var data = {
                'conversationId': self.get('conversationId')
            };

            self.set('isLoading', true);

            url = 'index.php?option=com_oshelpscout&task=conversation.getItem&format=json&Itemid=<?php echo $this->itemId; ?>';
            $.getJSON(url, data, function(result) {
                if (result.success === true) {
                    self.set('itemId', result.itemId);
                    self.set('conversationId', result.conversationId);
                    self.set('subject', result.subject);
                    self.set('thread', result.thread);
                    self.set('isNewConversation', result.isNewConversation);
                    self.set('status', result.status);
                    self.set('statusLabel', result.statusLabel);
                    self.set('threadCount', result.threadCount);
                    self.set('foundError', false);
                    self.set('isNewConversation', false);

                    self.startSuccessCountDown();

                    self.fixAttachmentLinks();
                } else {
                    self.set('foundError', true);

                    self.startErrorCountDown();
                }
            }).fail(function() {
                self.set('foundError', true);

                self.startErrorCountDown();
            }).always(function() {
                self.set('isLoading', false);
            });
        },
        oninit: function() {
            this.load();
        },
        startCountDown: function(limit) {
            var self = this;

            self.set('refreshCountDown', limit);
            var interval = self.get('refreshIntervalObj');
            if (interval !== null) {
                clearInterval(interval);
            }

            interval = setInterval(function() {
                currentTime = self.get('refreshCountDown');

                if (currentTime == 0) {
                    clearInterval(interval);
                    interval = null;

                    self.set('refreshCountDown', 0);
                    self.load();

                    return;
                }

                self.set('refreshCountDown', currentTime - 1);
            }, 1000);
            self.set('refreshIntervalObj', interval);
        },
        startSuccessCountDown: function() {
            var interval = this.get('successRefreshInterval');
            this.startCountDown(interval);
        },
        startErrorCountDown: function() {
            var interval = this.get('errorRefreshInterval');
            this.startCountDown(interval);
        },
        fixAttachmentLinks: function() {
            $('.oshs-message-attachments a').each(function() {
                $this = $(this);

                $this.attr('href', $this.attr('href').replace(/^\//, ''));
            });
        },
        reply: function() {
            self.set('submissionError', false);

            var body = this.get('replyBody');
            if (body.trim() != '') {
                self.set('isSubmitting', true);

                $.ajax({
                    type    : 'POST',
                    url     : '<?php echo JRoute::_('index.php?option=com_oshelpscout&task=conversation.reply&format=json'); ?>',
                    data    : {
                        'body': body,
                        '<?php echo JSession::getFormToken(); ?>': 1,
                        'subject': self.get('subject'),
                        'conversationId': self.get('conversationId'),
                        'itemId': self.get('itemId')
                    },
                    dataType: 'json'
                }).success(function(data) {
                    if (data.success) {
                        self.set('conversationId', data.conversationId);

                        if (self.get('isNewConversation'))  {
                            var newUrl = '<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversation'); ?>&id=' + data.conversationId,
                                supportPushState = (typeof window.history != 'undefined')
                                && (typeof window.history.pushState != 'undefined');

                            // If is new conversation, we need to update the window url to insert the new id
                            if (supportPushState) {
                                window.history.pushState(null, window.document.title, newUrl);
                            } else {
                                // Fallback for older browsers, redirecting
                                window.location = newUrl + '&msg=1';
                            }

                            self.set('isNewConversation', false);
                        }

                        if (!self.get('isNewConversation')) {
                            // If is not a new conversation, reload the thread
                            self.set('replyBody', '');
                            self.set('isLoading', true);
                            self.set('submissionSuccess', data.message);
                            self.set('isNewConversation', false);
                            self.load();
                        }
                    } else {
                        self.set('submissionError', data.message);
                    }
                }).fail(function() {
                    self.set('submissionError', 'Sorry, your message coudn\'t be sent');
                }).always(function() {
                    self.set('isSubmitting', false);
                    // Add timeout to hide form messages
                    setTimeout(function() {
                        self.set('submissionError', false);
                        self.set('submissionSuccess', false);
                    }, 6000);
                });

             } else {
                self.set('submissionError', 'Please, type a message and try again.');
            }
        },
        setEvents: function() {
            var self = this;

            $replyForm.on('submit', function(event) {
                event.preventDefault();

                self.reply();
            });
        }
    });

    ractive.on('load', function(e) {
        this.load();
        this.setEvents();
    });

    ractive.on('reply', function(e) {
        this.reply();
    });

    window.ractive = ractive;
})(Ractive, jQuery);



    // (function($, window, Dropzone) {
    //     // Event listener for the reply button
    //     $('#oshs-reply-button').on('click', function() {
    //         var body = $('#oshs-answer-body').val();
    //         if (body.trim() != '') {
    //             $('#oshs-reply-form').submit();
    //         } else {
    //             // msg
    //         }
    //     });

    //     // Configure the upload manager
    //     Dropzone.options.helpscoutUpload = {
    //         paramName: "file", // The name that will be used to transfer the file
    //         maxFilesize: 2, // MB
    //         uploadMultiple: true,
    //         autoProcessQueue: false,
    //         acceptedFiles: 'image/*,application/pdf,.psd,.zip,.tar,.gz,.bz2,.doc,.xml,.html,.txt,.docx,.xmlx',
    //         complete: function(t) {
    //             $('#oshs-reply-form').submit();
    //         },
    //         init: function(t) {
    //             var dropzoneInstance = Dropzone.instances[0];

    //             function getQueuedFilesCount() {
    //                 var queuedFiles = 0;

    //                 for (var i = 0; i < dropzoneInstance.files.length; i++) {
    //                     file = dropzoneInstance.files[i];

    //                     if (file.status == 'queued') {
    //                         queuedFiles++;
    //                     }
    //                 }

    //                 return queuedFiles;
    //             }

    //             // Only submit if there are no files to upload
    //             $('#oshs-reply-form').on('submit', function() {
    //                 var canSubmit = false,
    //                     queuedFiles;

    //                 try {
    //                     queuedFiles = getQueuedFilesCount();

    //                     if (queuedFiles > 0) {
    //                         dropzoneInstance.processQueue();
    //                     } else {
    //                         canSubmit = true;
    //                     }
    //                 }
    //                 catch(err) {
    //                     if (typeof(console) != 'undefined') {
    //                         console.log(err);
    //                     }

    //                     canSubmit = false;
    //                 }

    //                 return canSubmit;
    //             });
    //         }
    //     };
    // })(jQuery, window, Dropzone);
</script>
