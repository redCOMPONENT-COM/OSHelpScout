<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\OSDownloads\Free\Joomla\Component\Site as FreeComponentSite;
use Alledia\OSDownloads\Free\Joomla\Module\File as FreeFileModule;
use Alledia\OSDownloads\Pro\Joomla\Module\File as ProFileModule;
use Alledia\Framework\Joomla\Extension\Helper as ExtensionHelper;

defined('_JEXEC') or die;

include_once JPATH_ADMINISTRATOR . '/components/com_oshelpscout/include.php';

if (defined('OSHELPSCOUT_LOADED')) {
    require JModuleHelper::getLayoutPath('mod_oshelpscoutform', $params->get('layout', 'default'));
}
