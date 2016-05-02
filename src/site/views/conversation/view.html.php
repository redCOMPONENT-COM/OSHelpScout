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

class OSHelpScoutViewConversation extends JViewLegacy
{
    protected $params = null;

    /**
     * Constructor
     *
     * @param   array  $config  A named configuration array for object construction.
     *                          name: the name (optional) of the view (defaults to the view class name suffix).
     *                          charset: the character set to use for display
     *                          escape: the name (optional) of the function to use for escaping strings
     *                          base_path: the parent path (optional) of the views directory (defaults to the component folder)
     *                          template_plath: the path (optional) of the layout directory (defaults to base_path + /views/ + view name
     *                          helper_path: the path (optional) of the helper files (defaults to base_path + /helpers/)
     *                          layout: the layout (optional) to use to display the view
     *
     * @since   12.2
     */
    public function __construct($config = array())
    {
        parent::__construct($config);

        // Check if the config has custom params to set
        $params = null;
        if (isset($config['params'])) {
            $params = $config['params'];
        }
        $this->setParams($params);
    }

    protected function setParams($params = null)
    {
        if (!empty($params)) {
            // Set using the custom params using the given object
            if (is_object($params)
                && ((get_class($params) === 'JRegistry') || (get_class($params) === 'Joomla\Registry\Registry'))) {
                $this->params = $params;

                return true;
            }

            // Set using the custom params converting from array
            if (is_array($params)) {
                $reg = new JRegistry();
                $reg->loadArray($params);

                $this->params = $reg;

                return true;
            }
        }

        // Set to the current active menu's params
        $app  = Framework\Factory::getApplication();
        $menu = $app->getMenu()->getActive();

        $this->params = $menu->params;

        return true;
    }

    public function display($tpl = null)
    {
        $app                           = Framework\Factory::getApplication();
        $this->itemId                  = $app->input->get('Itemid', 0);
        $this->conversationId          = $app->input->get('id', 0);
        $this->isGuest                 = Framework\Factory::getUser()->guest;
        $showMessage                   = $app->input->get('msg', 0);
        $this->customTitle             = $this->params->get('custom_title', 'COM_OSHELPSCOUT_NEW_CONVERSATION');
        // Used if using as a post only form, not linked to a conversation. Usually as guest
        $redirectToMenuId              = $this->params->get('redirect_to', '');
        $this->showAdditionalSubjField = (bool)$this->params->get('show_additional_subject_field', false);
        $this->redirectTo              = '';
        $this->showBackLinks           = $this->params->get('show_back_links', true);

        // Check if we need to set a redirection after submit the form
        if (!empty($redirectToMenuId)) {
            $this->redirectTo = JRoute::_('index.php?Itemid=' . $redirectToMenuId);
        }

        // Check if received a flag to show the success message after insert
        // This is required since is redirected by JS, not PHP
        if ($showMessage) {
            $app->enqueueMessage(JText::_("COM_OSHELPSCOUT_REPLIED_SUCCESSFULLY"), 'info');
        }

        if (empty($this->conversationId)) {
            // Try to recover a temporary ID from the session
            $this->conversationId = OSHelpScout\Free\Helper::getTmpConversationIdFromSession();
        }

        $this->isNewConversation = OSHelpScout\Free\Helper::isNewId($this->conversationId);

        // Make sure the tmp upload data is empty in the session
        OSHelpScout\Free\Helper::cleanUploadSessionData($this->conversationId);
        OSHelpScout\Free\Helper::cleanUploadTmpFiles($this->conversationId);

        // Get the list of subjects to display
        $subjects = $this->params->get('subjects', null);

        $this->subjects = OSHelpScout\Free\Helper::getSubjectsList($subjects);

        // Store all the tags in the session to be recovered by the controller while replying
        $subjectsKey = md5($subjects . microtime());
        $session = Framework\Factory::getSession();
        $session->set($subjectsKey, $subjects);

        $this->subjectsKey = OSHelpScout\Free\Helper::signWithHash($subjectsKey);

        // Set the mailboxId but concatenate a hash for signature, avoiding manual manipulation
        $mailboxId = $this->params->get('helpscout_mailbox', 0);
        $this->mailboxIdHash = OSHelpScout\Free\Helper::signWithHash($mailboxId);

        // Check if needs to collect some environment data
        if ($this->params->get('collect_env_data', false)) {
            $this->extraInfo = base64_encode(
                json_encode(OSHelpScout\Free\Helper::getExtraInfo())
            );
        }

        parent::display($tpl);
    }
}
