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

        if ($this->isGuest) {
            // Prepare the custom content
            $this->customGuestContent = $menu->params->get('guest_custom_content', 'Restricted access');
            $this->customStylesheets  = array();
            $this->customScripts      = array();

            $prepareGuestContent = (bool)$menu->params->get('guest_prepare_custom_content', '0');
            if ($prepareGuestContent) {
                $this->customGuestContent = JHtml::_('content.prepare', $this->customGuestContent);
            }

            // If specified, add additional media to the page
            $customGuestMedia = $menu->params->get('guest_custom_media');

            if (!empty($customGuestMedia)) {
                $customGuestMedia = explode("\n", $customGuestMedia);

                if (!empty($customGuestMedia)) {
                    foreach ($customGuestMedia as $media) {
                        $media = trim($media);

                        // Check if the file exists and add to the specific list according to the extension
                        if (JFile::exists(JPATH_SITE . '/' . $media)) {
                            $extension = strtolower(JFile::getExt($media));
                            if ($extension === 'css') {
                                $this->customStylesheets[] = $media;
                            } elseif ($extension === 'js') {
                                $this->customScripts[] = $media;
                            }
                        }
                    }
                }
            }
        }

        // Render the modules for oshelpscout-content-top position
        $renderer = $doc->loadRenderer('modules');
        $options  = array('style' => 'raw');
        $this->modulesContentTop = $renderer->render('oshelpscout-content-top', $options, null);

        parent::display($tpl);
    }
}
