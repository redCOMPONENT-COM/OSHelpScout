<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Framework\AutoLoader;

defined('_JEXEC') or die();

// Alledia Framework
if (!defined('ALLEDIA_FRAMEWORK_LOADED')) {
    $allediaFrameworkPath = JPATH_SITE . '/libraries/allediaframework/include.php';

    if (file_exists($allediaFrameworkPath)) {
        require_once $allediaFrameworkPath;
    } else {
        $app = JFactory::getApplication();

        if ($app->isAdmin()) {
            $app->enqueueMessage('[OSHelpScout] Alledia framework not found', 'error');
        }
    }
}

if (defined('ALLEDIA_FRAMEWORK_LOADED')) {
    require_once __DIR__ . '/vendor/autoload.php';

    define('OSHELPSCOUT_LIBRARY', __DIR__ . '/library');

    AutoLoader::register('Alledia\\OSHelpScout', OSHELPSCOUT_LIBRARY);

    define('OSHELPSCOUT_LOADED', 1);
}


JLog::addLogger(
   array(
        'text_file'         => 'com_oshelpscout.errors.php',
        'text_entry_format' => '{DATETIME} {PRIORITY} {CLIENTIP}: {MESSAGE}'
   ),
   JLog::ALL,
   array('com_oshelpscout')
);
