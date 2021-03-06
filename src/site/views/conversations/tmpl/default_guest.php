<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die();

if (!empty($this->customStylesheets)) {
    foreach ($this->customStylesheets as $path) {
        JHtml::stylesheet(JUri::root() . $path, array(), false);
    }
}

if (!empty($this->customScripts)) {
    foreach ($this->customScripts as $path) {
        JHtml::script(JUri::root() . $path, false, false);
    }
}

if (!empty($this->customGuestContent)) {
    echo $this->customGuestContent;
}
