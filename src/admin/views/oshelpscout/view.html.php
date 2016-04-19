<?php
/**
 * @package   OSHelpScout
 * @contact   www.alledia.com, hello@alledia.com
 * @copyright 2016 Open Source Training, LLC. All rights reserved
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

defined('_JEXEC') or die;

class OSHelpScoutViewOshelpscout extends JViewLegacy
{
    public function display($tpl = null)
    {
        $this->addToolbar();
    }

    protected function addToolbar()
    {
        JToolBarHelper::title(JText::_('COM_OSHELPSCOUT') . ': ' . JText::_('COM_OSHELPSCOUT_DASHBOARD'));
        JToolBarHelper::preferences('com_oshelpscout', '450');
    }
}
