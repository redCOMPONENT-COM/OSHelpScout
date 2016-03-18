<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

namespace Alledia\OSHelpScout\Free\Joomla\Controller;

use Alledia\Framework;

defined('_JEXEC') or die();


class Site extends Framework\Joomla\Controller\Base
{
    public function display($cachable = false, $urlparams = false)
    {
        $app = \JFactory::getApplication();

        $view = $app->input->getCmd('view', 'conversations');
        $app->input->set('view', $view);

        parent::display($cachable, $urlparams);
    }
}
