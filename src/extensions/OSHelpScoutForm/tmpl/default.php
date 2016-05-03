<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */
defined('_JEXEC') or die;

// Path to the users component
$basePath = JPATH_SITE . '/components/com_oshelpscout';

// Load the language file
$lang = JFactory::getLanguage();
$lang->load('com_oshelpscout', $basePath);

// Get/configure the users controller
if (!class_exists('OSHelpScoutController')) {
    require($basePath . '/controller.php');
}

$config['base_path'] = $basePath;
$controller = new OSHelpScoutController($config);

// Set which view to display and add appropriate paths
JRequest::setVar('view', 'conversation');
JForm::addFormPath($basePath . '/models/forms');
JForm::addFieldPath($basePath . '/models/fields');

// Force to hide the back navigation links
$params->set('show_back_links', false);
$params->set('is_module', true);

// Get the view passing the module params and add the correct template path
$view = $controller->getView('conversation', 'html', '', array('params' => $params));
$view->addTemplatePath($basePath . '/views/conversation/tmpl');

$useSlider = $params->get('use_slider_effect', false);
?>

<div class="mod_oshelpscoutform<?php echo $params->get('moduleclass_sfx'); ?>" id="mod_oshelpscoutform_<?php echo $module->id; ?>">
    <?php if ($useSlider) : ?>
        <h3><?php echo JText::_($params->get('custom_title', 'COM_OSHELPSCOUT_NEW_CONVERSATION')); ?></h3>

        <div class="mod_oshelpscoutform_link">
            <a data-module-id="<?php echo $module->id; ?>">
                <?php echo JText::_($params->get('toggle_button_label', 'MOD_OSHELPSCOUTFORM_SHOW_FORM')); ?>
            </a>
        </div>
    <?php endif; ?>

    <div
        class="mod_oshelpscoutform_form <?php if ($useSlider) : ?>mod_oshelpscoutform_form<?php endif; ?>"
        data-module-id="<?php echo $module->id; ?>"
        <?php if ($useSlider) : ?>style="display: none;"<?php endif; ?>">

        <?php $controller->display(false); ?>
    </div>
</div>

<script>
;(function($) {
    $('.mod_oshelpscoutform_link a').click(function formLinkClick() {
        $('div.mod_oshelpscoutform_form[data-module-id="<?php echo $module->id; ?>"]').slideToggle();
    });

    window.OSHelpScoutFormAfterReplyCallback = function() {
        setTimeout(function afterReplyCallback() {
            $('div.mod_oshelpscoutform_form[data-module-id="<?php echo $module->id; ?>"]').slideUp();
        }, 4000);
    }
})(jQuery);
</script>
