<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

namespace Alledia\OSHelpScout\Free\Joomla\Component;

use Alledia\Framework;

defined('_JEXEC') or die();

class Site extends Framework\Joomla\Extension\AbstractComponent
{
    protected static $instance;

    public static function getInstance($namespace = null)
    {
        return parent::getInstance('OSHelpScout');
    }
}
