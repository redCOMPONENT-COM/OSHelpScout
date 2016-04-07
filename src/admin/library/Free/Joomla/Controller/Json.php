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


class Json extends Framework\Joomla\Controller\Base
{
    /**
     * Standard token checking for json controllers
     *
     * @return void
     * @throws Exception
     */
    protected function checkToken()
    {
        if (!JSession::checkToken()) {
            throw new Exception(JText::_('JINVALID_TOKEN'), 403);
        }
    }
}
