<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout;
use Alledia\Framework;

defined('_JEXEC') or die();

$extension = Framework\Factory::getExtension('oshelpscout', 'component');
$staticHash = md5($extension->manifest->version);

JHtml::stylesheet(JUri::base() . 'media/com_oshelpscout/css/frontend.css?' . $staticHash);
JHtml::script(Juri::base() . 'media/com_oshelpscout/js/ractive.min.js?' . $staticHash);
?>

<p>
    <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversation'); ?>" class="uk-button uk-button-primary oshs-new-button">
        <i class="uk-icon-question-circle"></i> <?php echo JText::_('COM_OSHELPSCOUT_NEW_CONVERSATION'); ?>
    </a>
</p>

<div id="oshs-conversations-container"></div>

<script id="list-template" type="text/ractive">
    {{#if foundError}}
        <div class="uk-alert uk-alert-danger">
            <?php echo JText::_('COM_OSHELPSCOUT_MSG_ERROR_FOUND'); ?>&nbsp;{{refreshCountDown}}&nbsp;<?php echo JText::_('COM_OSHELPSCOUT_SECONDS'); ?>
        </div>
    {{/if}}

    {{#if isLoading}}
        <div class="oshs-refresh-loader">
            <i class="uk-icon-refresh uk-icon-spin"></i>
        </div>
    {{/if}}

    <div class="uk-width-1-1 uk-overflow-container">
        {{^isLoading}}
            {{^conversations}}
                <div class="uk-alert">
                    <?php echo JText::_('COM_OSHELPSCOUT_NO_ITEMS'); ?>
                </div>
            {{/conversations}}
        {{/if}}

        <table class="uk-table uk-table-striped">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th width="10%">Messages</th>
                    <th width="10%">Status</th>
                </tr>
            </thead>
            <tbody>
                {{#conversations}}
                    <tr>
                        <td>
                            <a href="<?php echo JRoute::_('index.php?option=com_oshelpscout&view=conversation'); ?>&id={{this.id}}">
                                {{this.subject}}
                            </a>
                            <div class="oshs-preview-text">
                                <i class="uk-icon-angle-double-right"></i>
                                {{this.preview}}
                            </div>
                        </td>
                        <td class="uk-text-center">
                            <span class="uk-badge uk-badge-notification">{{this.threadCount}}</span>
                        </td>
                        <td class="uk-text-center">
                            <div class="uk-badge {{#if this.status != 'closed'}}uk-badge-warning{{else}}uk-badge-success{{/if}}">
                                {{this.statusLabel}}
                            </div>
                        </td>
                    </tr>
                {{/conversations}}
            </tbody>
        </table>
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

        return zeroString+n;
    }

    var ractive = new Ractive({
        el: '#oshs-conversations-container',
        template: '#list-template',
        data: {
            'conversations'          : [],
            'isLoading'              : false,
            'foundError'             : false,
            'successRefreshInterval' : 300, // 5 min
            'errorRefreshInterval'   : 10, // 10 s
            'refreshCountDown'       : 0,
            'refreshIntervalObj'     : null,
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

            self.set('isLoading', true);
            self.set('foundError', false);

            var data = {};

            self.set('isLoading', true);

            url = '<?php echo JRoute::_("index.php?option=com_oshelpscout&task=conversations.getItems&format=json&Itemid=" . $this->itemId); ?>';
            $.getJSON(url, data, function(result) {
                if (result.success === true) {
                    self.set('conversations', result.list);
                    self.set('foundError', false);
                    self.startSuccessCountDown();
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
        startSuccessCountDown: function()
        {
            var interval = this.get('successRefreshInterval');
            this.startCountDown(interval);
        },
        startErrorCountDown: function()
        {
            var interval = this.get('errorRefreshInterval');
            this.startCountDown(interval);
        }
    });

    ractive.on('load', function(e) {
        this.load();
    });

    window.ractive = ractive;
})(Ractive, jQuery);
</script>
