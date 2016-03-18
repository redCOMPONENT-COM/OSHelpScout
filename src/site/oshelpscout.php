<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSHelpScout\Free;

defined('_JEXEC') or die;

include_once JPATH_ADMINISTRATOR . '/components/com_oshelpscout/include.php';

if (defined('OSHELPSCOUT_LOADED')) {
    Free\Joomla\Component\Site::getInstance()->init();
} else {
    JFactory::getApplication()->enqueueMessage('OSHelpScout Library not loaded', 'error');
}
