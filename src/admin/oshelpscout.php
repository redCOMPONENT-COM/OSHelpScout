<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

jimport('legacy.controller.legacy');

require_once 'include.php';

$controller = JControllerLegacy::getInstance('oshelpscout');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
