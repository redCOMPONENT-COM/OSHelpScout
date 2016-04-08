<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

use Alledia\Framework;
use Alledia\OSHelpScout;

defined('_JEXEC') or die;

class OSHelpScoutViewConversations extends JViewLegacy
{
    public function display($tpl = null)
    {
        $app           = Framework\Factory::getApplication();
        $doc           = Framework\Factory::getDocument();
        $menu          = $app->getMenu()->getActive();
        $title         = $menu->params->get('custom_title', 'COM_OSHELPSCOUT_CONVERSATIONS');
        $this->title   = JText::_($title);
        $this->itemId  = $app->input->get('Itemid', 0);
        $this->isGuest = Framework\Factory::getUser()->guest;

        // Render the modules for oshelpscout-content-top position
        $renderer = $doc->loadRenderer('modules');
        $options  = array('style' => 'raw');
        $this->modulesContentTop = $renderer->render('oshelpscout-content-top', $options, null);

        parent::display($tpl);
    }
}
