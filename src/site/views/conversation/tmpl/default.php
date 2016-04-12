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

// Get a hash for static files, to avoid issues with old cached in browser versions
$extension = Framework\Factory::getExtension('oshelpscout', 'component');
$staticHash = md5($extension->manifest->version);

// Add the static files
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

    <!-- This container will be dynamically populated -->
    <div id="oshs-conversation-container"></div>

    <div class="oshs-conversation-breadcrumbs">
        <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversations'); ?>">
            <i class="uk-icon-angle-double-left"></i>&nbsp;<?php echo JText::_('COM_OSHELPSCOUT_BACK_TO_LIST'); ?>
        </a>
    </div>
</div>

<!-- Conversation UI template -->
<script id="conversation-template" type="text/ractive">
    <div class="uk-width-1-1">
        {{^isNewConversation}}
            <h4>Conversation</h4>
        {{/isNewConversation}}

        <!-- Conversation subject and status -->
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

        <!-- Form to reply the conversation -->
        <div class="oshs-conversation-reply">
            <form class="uk-form" action="<?php echo JRoute::_('index.php?option=com_oshelpscout&task=conversation.reply'); ?>" method="POST" id="oshs-reply-form">
                {{#isNewConversation}}
                    <!-- Subject field is only available if this is a new conversation -->
                    {{#isGuest}}
                        <!-- If not a member, ask for name and email -->
                        <div class="uk-form-row">
                            <input type="text" name="name" value="{{name}}" placeholder="<?php echo JText::_('COM_OSHELPSCOUT_NAME'); ?>" />
                        </div>

                        <div class="uk-form-row">
                            <input type="email" name="email" value="{{email}}" placeholder="<?php echo JText::_('COM_OSHELPSCOUT_EMAIL'); ?>" />
                        </div>
                    {{/isGuest}}

                    <div class="uk-form-row">
                        <?php if (empty($this->subjects)) : ?>
                            <!-- There is no list of subjects -->
                            <input type="text" name="subject" id="oshs-answer-subject" value="{{subject}}" placeholder="<?php echo JText::_('COM_OSHELPSCOUT_TYPE_SUBJECT'); ?>" />
                        <?php else : ?>
                            <!-- Use the pre-defined list of subjects -->
                            <select name="subject" id="oshs-answer-subject" value="{{subject}}">
                                <?php foreach ($this->subjects as $subject) : ?>
                                    <option value="<?php echo $subject; ?>"><?php echo $subject; ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php endif; ?>
                    </div>
                {{/isNewConversation}}

                <!-- The field for the answer's body -->
                <div class="uk-form-row">
                    <textarea
                        name="body"
                        id="oshs-answer-body"
                        placeholder="<?php echo JText::_('COM_OSHELPSCOUT_TYPE_MESSAGE'); ?>"
                        value="{{replyBody}}"></textarea>
                </div>
            </form>

            <!-- Box to upload files -->
            <div class="oshs-upload-box">
                <form
                    action="<?php echo JRoute::_('index.php?option=com_oshelpscout&task=conversation.upload&format=json'); ?>"
                    class="dropzone uk-form"
                    id="helpscout-upload">

                    <div class="fallback">
                        <input name="file" type="file" multiple />
                    </div>

                    <input type="hidden" name="conversationId" value="{{conversationId}}" />
                    <?php echo JHTML::_('form.token'); ?>
                </form>
            </div>

            <!-- Form action bar -->
            <div>
                <button
                    type="button"
                    on-click="reply"
                    {{#startedSubmission}}disabled{{/startedSubmission}}
                    id="oshs-reply-button"
                    class="uk-button uk-button-primary">

                    {{#if startedSubmission}}
                        <?php echo JText::_('COM_OSHELPSCOUT_PLEASE_WAIT'); ?>
                    {{else}}
                        <?php echo JText::_('COM_OSHELPSCOUT_SUBMIT'); ?>
                    {{/if}}
                </button>

                {{#if submissionError}}
                    <!-- Display errors from the submission -->
                    <div class="uk-alert uk-alert-danger">
                        {{submissionError}}
                    </div>
                {{/if}}

                {{#if submissionSuccess}}
                    <!-- Display success message from the submission -->
                    <div class="uk-alert uk-alert-info">
                        {{submissionSuccess}}
                    </div>
                {{/if}}
            </div>
        </div>

        {{#if foundError}}
            <!-- Display error for the auto-refresh feature -->
            <div class="uk-alert uk-alert-danger">
                <?php echo JText::_('COM_OSHELPSCOUT_MSG_ERROR_FOUND'); ?>&nbsp;{{refreshCountDown}}&nbsp;<?php echo JText::_('COM_OSHELPSCOUT_SECONDS'); ?>
            </div>
        {{/if}}

        {{^isNewConversation}}
            <!-- Not a new converstion, so let's show the list of messages -->
            <div id="oshs-messages-container">
                {{#if isLoading}}
                    <div class="oshs-refresh-loader">
                        <i class="uk-icon-refresh uk-icon-spin"></i>
                    </div>
                {{/if}}

                {{#if refreshCountDown > 0}}
                    {{^foundError}}
                        {{^isLoading}}
                            <!-- Message about auto-refresh -->
                            <div class="oshs-auto-refresh">
                                This list will auto-refresh in {{refreshCountDownLabel()}}
                            </div>
                        {{/isLoading}}
                    {{/foundError}}
                {{/if}}

                {{^isLoading}}
                    {{^thread}}
                        <!-- Message about empty thread -->
                        <div class="uk-alert">
                            <?php echo JText::_('COM_OSHELPSCOUT_NO_ITEMS'); ?>
                        </div>
                    {{/thread}}
                {{/if}}

                <!-- If there is a thread, let's show all the messages -->
                {{#thread:threadIndex}}
                    <div class="oshs-message-block oshs-message-by-{{this.type}}">
                        <!-- User's avatar -->
                        <div class="oshs-message-avatar">
                            <img src="//www.gravatar.com/avatar/{{this.gravatarHash}}?size=80" width="40" />
                        </div>

                        <!-- Message's head -->
                        <div class="oshs-message-head">
                            <!-- Who sent the message? -->
                            <div class="oshs-message-by">
                                {{this.creatorName}}&nbsp;
                                {{#if threadIndex == threadCount - 1}}
                                    <span class="uk-text-muted"><?php echo JText::_('COM_OSHELPSCOUT_STARTED_CONVERSATION'); ?></span>
                                {{else}}
                                    <span class="uk-text-muted"><?php echo JText::_('COM_OSHELPSCOUT_REPLIED'); ?></span>
                                {{/if}}
                            </div>
                            <!-- Message's creation time -->
                            <div class="oshs-message-date uk-text-muted">
                                <span data-uk-tooltip="{pos:'top'}" title="{{this.createdAt}}">
                                    {{this.createdAtRelative}}
                                </span>
                            </div>
                        </div>

                        <!-- The message's body -->
                        <div class="oshs-message-body {{#if this.isHtml}}oshs-message-html{{else}}oshs-message-txt{{/if}}">
                            {{{this.body}}}
                        </div>

                        {{#if this.attachments}}
                            <!-- This message has attachments. Let's list them -->
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
(function mainClosure(Ractive, $, Dropzone) {
    Ractive.DEBUG = false;

    /*
     * Method to add zero pad at left of the number
     *
     * @param  {Number} numZeros  Total of zeros you want in the number
     */
    Number.prototype.leftZeroPad = function leftZeroPadMethod(numZeros) {
        var n = Math.abs(this),
            zeros = Math.max(0, numZeros - Math.floor(n).toString().length),
            zeroString = Math.pow(10, zeros).toString().substr(1);

        if(this < 0) {
            zeroString = '-' + zeroString;
        }

        return zeroString + n;
    }

    // Set a global var for the reply form
    var $replyForm = $('#oshs-reply-form');

    // Instantiate the ractive object
    var ractive = new Ractive({
        el: '#oshs-conversation-container',
        template: '#conversation-template',
        data: {
            'conversationId'         : '<?php echo $this->conversationId; ?>',
            'itemId'                 : '<?php echo $this->itemId; ?>',
            'thread'                 : null, // list of messages for this conversation
            'isNewConversation'      : <?php echo ($this->isNewConversation) ? 'true' : 'false'; ?>,
            'isGuest'                : <?php echo ($this->isGuest) ? 'true' : 'false'; ?>,
            'status'                 : null,
            'statusLabel'            : null,
            'subject'                : '<?php echo @trim($this->subjects[0]); ?>',
            'name'                   : null,
            'email'                  : null,
            'threadCount'            : null,
            'isLoading'              : false,
            'isSubmitting'           : false,
            'startedSubmission'      : false,
            'foundError'             : false,
            'successRefreshInterval' : 300, // 5 min
            'errorRefreshInterval'   : 10, // 10 s
            'refreshCountDown'       : 0,
            'refreshIntervalObj'     : null,
            'replyBody'              : '', // Answer's body
            'submissionError'        : false,
            'submissionSuccess'      : null,
            'dropzone'               : null,
            'isUploadingFiles'       : false,
            /*
             * Method to format the countdown remaining time in a time format:
             * 0:00
             *
             * @return  {String}
             */
            'refreshCountDownLabel': function refreshCountDownLabelMethod() {
                var value = this.get('refreshCountDown'),
                    min   = Math.floor(value / 60),
                    sec   = value % 60,
                    str   = '';

                return min.leftZeroPad(1) + ':' + sec.leftZeroPad(2);
            }
        },
        /*
         * Method to load the conversation using asynchronously
         */
        load: function ractiveLoadMethod() {
            self = this;

            // Avoid look for messages in new conversations
            if (self.get('isNewConversation')) {
                return;
            }

            // Set the initial state
            self.set('isLoading', true);
            self.set('foundError', false);

            // Make the request to load the thread and conversation's data
            $.getJSON(
                '<?php echo JRoute::_("index.php?option=com_oshelpscout&task=conversation.getItem&format=json&Itemid=" . $this->itemId); ?>',
                {
                    'conversationId': self.get('conversationId')
                },
                /*
                 * Success callback
                 *
                 * @param  {Object} result
                 */
                function loadSuccessCallback(result) {
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
                }
            ).fail(
                /*
                 * Fail callback
                 */
                function loadFailCallback() {
                    self.set('foundError', true);
                    self.startErrorCountDown();
                }
            ).always(
                /*
                 * Always run this callback, for success or fail
                 */
                function loadAlwaysCallback() {
                    self.set('isLoading', false);
                }
            );
        },
        /*
         * Method executed after ractive is initialized. The first method
         * which will run.
         */
        oninit: function reactOnInit() {
            this.setEvents();
            this.setDropZone();
            this.load();
        },
        /*
         * Method to start the countdown (reseting if already started)
         * You can set the limit or interval for countdown.
         *
         * @param  {Number} limit
         */
        startCountDown: function startCountDownMethod(limit) {
            var self = this;

            // Start the countdown from the given limit
            self.set('refreshCountDown', limit);
            // Check if we already have an instantiated interval.
            // If true, let's clear it
            var interval = self.get('refreshIntervalObj');
            if (interval !== null) {
                clearInterval(interval);
            }

            // Instantiate a new interval
            interval = setInterval(
                /*
                 * This method decrements the current time in the countdown.
                 * If the limit is reached, triggers the load method to
                 * refresh the thread and conversation.
                 */
                function countDownIntervalCallback() {
                    currentTime = self.get('refreshCountDown');

                    // Countdown has finished. Triggers the load event
                    if (currentTime == 0) {
                        clearInterval(interval);
                        interval = null;

                        self.set('refreshCountDown', 0);
                        self.load();

                        return;
                    }

                    self.set('refreshCountDown', currentTime - 1);
                },
                1000
            );
            // Store the interval instance
            self.set('refreshIntervalObj', interval);
        },
        /*
         * Starts the countdown with the specific limit set for when the load
         * event runned well
         */
        startSuccessCountDown: function startSuccessCountDownMethod() {
            var interval = this.get('successRefreshInterval');
            this.startCountDown(interval);
        },
        /*
         * Starts the countdown with the specific limit set for when the load
         * event didn't runned well and found an error
         */
        startErrorCountDown: function startErrorCountDownMethod() {
            var interval = this.get('errorRefreshInterval');
            this.startCountDown(interval);
        },
        /*
         * Fixes the url in the attachments links. For any unknown reason all
         * links for attachments had a slash as the first char, what breaks
         * the url and don't allow users to download them.
         * We try here to fix removing that first char
         */
        fixAttachmentLinks: function fixAttachmentLinksMethod() {
            $('.oshs-message-attachments a').each(function eachAttachmentCallback() {
                $this = $(this);
                $this.attr('href', $this.attr('href').replace(/^\//, ''));
            });
        },
        /*
         * Method that check if the form has valid data
         *
         * @result {Boolean}
         */
        validateForm: function validateFormMethod() {
            var self = this;

            var body = this.get('replyBody'),
                valid,
                name,
                email;

            if (self.get('isGuest')) {
                var name = self.get('name');
                if (name == '' || name == null) {
                    self.set('submissionError', '<?php echo JText::_('COM_OSHELPSCOUT_MSG_TYPE_NAME'); ?>');

                    return false;
                }

                var email = self.get('email');
                if (email == '' || email == null) {
                    self.set('submissionError', '<?php echo JText::_('COM_OSHELPSCOUT_MSG_TYPE_EMAIL'); ?>');

                    return false;
                }

                // Check if it is a valid email
                var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                if (!re.test(email)) {
                    self.set('submissionError', '<?php echo JText::_('COM_OSHELPSCOUT_MSG_TYPE_EMAIL'); ?>');

                    return false
                }
            }

            if (body.trim() == '') {
                self.set('submissionError', '<?php echo JText::_('COM_OSHELPSCOUT_MSG_TYPE_MESSAGE'); ?>');

                self.set('startedSubmission', false);

                return false;
            }

            return true;
        },
        /*
         * Method triggered to reply the conversation. Should only be called
         * if all files were already uploaded.
         */
        reply: function replyMethod() {
            /*
             * Method to set flags to say the submission has finished,
             * if successful or fail
             */
            function setSubmissionAsFinished() {
                self.set('isSubmitting', false);
                self.set('startedSubmission', false);

                // Add timeout to hide form messages
                setTimeout(
                    /*
                     * Hide success and fail messages
                     */
                    function hideMessagesTimeout() {
                        self.set('submissionError', false);
                        self.set('submissionSuccess', false);
                    },
                    6000
                );
            };

            // Avoid to submit twice
            if (!self.get('isSubmitting')) {
                // Check if the form is valid before reply
                if (self.validateForm()) {
                    self.set('submissionError', false);

                    // Marking submission as started
                    self.set('isSubmitting', true);

                    // POST data
                    var data = {
                        'body': self.get('replyBody'),
                        '<?php echo JSession::getFormToken(); ?>': 1,
                        'subject': self.get('subject'),
                        'conversationId': self.get('conversationId'),
                        'itemId': self.get('itemId')
                    };

                    // Add additional fields for guests
                    if (self.get('isGuest')) {
                        data.name = self.get('name');
                        data.email = self.get('email');
                    }

                    // Make the request
                    $.ajax(
                        {
                            type    : 'POST',
                            url     : '<?php echo JRoute::_('index.php?option=com_oshelpscout&task=conversation.reply&format=json'); ?>',
                            data    : data,
                            dataType: 'json'
                        }
                    ).success(
                        /*
                         * Success callback
                         *
                         * @param {Object} data
                         */
                        function replySuccessCallback(data) {
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

                                // Files were already uploaded. Remove all
                                var dropzone = self.get('dropzone');
                                dropzone.removeAllFiles();

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

                            setSubmissionAsFinished();
                        }
                    ).fail(
                        /*
                         * Fail callback
                         */
                        function replyFailCallback() {
                            self.set('submissionError', '<?php echo JText::_('COM_OSHELPSCOUT_MSG_SUBMISSION_ERROR'); ?>');

                            setSubmissionAsFinished();
                        }
                    );
                } else {
                    setSubmissionAsFinished();
                }
            }
        },
        /*
         * Method to set the events to elements
         */
        setEvents: function setEventsMethod() {
            var self = this;

            // Avoid to run native form submit
            $replyForm.on('submit', function replyFormOnSubmitCallback(event) {
                event.preventDefault();
                self.fire('reply');
            });
        },
        /*
         * Method that returns the count of queued files to upload
         *
         * @return {Number}
         */
        getQueuedFilesCount: function getQueuedFilesCountMethod() {
            var queuedFiles = 0,
                dropzone = this.get('dropzone');

            for (var i = 0; i < dropzone.files.length; i++) {
                file = dropzone.files[i];

                if (file.status == 'queued') {
                    queuedFiles++;
                }
            }

            return queuedFiles;
        },
        /*
         * Configure the dropzone for uploads
         */
        setDropZone: function setDropZoneMethod() {
            var self = this;

            // Set the default options for Dropzone
            Dropzone.options.helpscoutUpload = {
                paramName: "file", // The name that will be used to transfer the file
                maxFilesize: 2, // MB
                uploadMultiple: true,
                autoProcessQueue: false,
                acceptedFiles: 'image/*,application/pdf,.psd,.zip,.tar,.gz,.bz2,.doc,.xml,.html,.txt,.docx,.xmlx',
                /*
                 * This method runs after finish the upload, in success or fail.
                 */
                complete: function dropzoneCompletCallback(t) {
                    self.set('isUploadingFiles', false);

                    // In success or fail, submit
                    self.reply();
                },
                /*
                 * This method runs just before each file is sent. We want to
                 * use it to mark the flag isUploadingFiles as true
                 */
                sending: function dropzoneSendingCallback() {
                    self.set('isUploadingFiles', true);
                },
                /*
                 * This method runs right after Dropzone is initialized.
                 */
                init: function dropzoneInitCallback(t) {
                    // Store the dropzone instance since it is now available
                    self.set('dropzone', Dropzone.instances[0]);
                }
            };
        }
    });

    /*
     * Event triggered by the "reply" button
     *
     * @param  {Object} e
     */
    ractive.on('reply', function onReplyCallback(e) {
        var self = this;

        self.set('startedSubmission', true);

        // Only directly submit if there are no files to upload
        var canSubmitNow = false,
            dropzone = self.get('dropzone');

        try {
            // Check if we have queued files. If positive, try to start the upload
            if (self.getQueuedFilesCount() > 0) {
                // If there is no upload running and form is valid, process the file queue
                if (!self.get('isUploadingFiles')) {
                    // Check if the form is valid before process the queue
                    if (self.validateForm()) {
                        dropzone.processQueue();
                    }
                }
            } else {
                // No files. We can submit
                canSubmitNow = true;
            }
        }
        catch(error) {
            self.set('submissionError', error);

            canSubmitNow = false;
        }

        // Yeah, we can submit
        if (canSubmitNow) {
            self.reply();
        }
    });

    // Publish ractive to the global scope
    window.ractive = ractive;
})(Ractive, jQuery, Dropzone);
</script>
