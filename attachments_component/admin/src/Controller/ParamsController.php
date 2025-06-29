<?php

/**
 * Attachments component param controller
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\Controller;

use JMCameron\Component\Attachments\Site\Helper\AttachmentsDefines;
use JMCameron\Component\Attachments\Site\Helper\AttachmentsHelper;
use Joomla\CMS\Client\ClientHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\Component\Config\Administrator\Model\ComponentModel;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * Attachment Controller
 *
 * @package Attachments
 */
class ParamsController extends FormController
{
    /**
     * Edit the component parameters
     */
    public function edit($key = null, $urlVar = null)
    {
        // Access check.
        $user = $this->app->getIdentity();
        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 117)', 404);
            return false;
        }

        // Get the component parameters
        $params = ComponentHelper::getParams('com_attachments');

        // Get the component model/table
        $model = new ComponentModel();
        $state = $model->getState();
        $state->set('component.option', 'com_attachments');
        $state->set('component.path', JPATH_ADMINISTRATOR . '/components/com_attachments');
        $model->setState($state);
        $form = $model->getForm();
        $component = ComponentHelper::getComponent('com_attachments');

        // Bind the form to the data.
        if ($form && $component->params) {
            $form->bind($component->params);
        }

        // Set up the view
        /** @var \JMCameron\Component\Attachments\Administrator\View\Params\HtmlView $view */
        $view = $this->getView('Params', 'html');
        $view->setModel($model);
        $view->params = $params;

        $view->form = $form;
        $view->component = $component;

        $view->display();
    }


    /**
     * Save the parameters
     */
    public function save($key = null, $urlVar = null)
    {
        // Check for request forgeries.
        Session::checkToken() or jexit(Text::_('JINVALID_TOKEN'));

        $input = $this->app->getInput();

        // Get the old component parameters
        $old_params = ComponentHelper::getParams('com_attachments');
        $old_secure = $input->getInt('old_secure');

        // Set FTP credentials, if given.
        ClientHelper::setCredentialsFromRequest('ftp');

        // Initialize variables.
        $model = new ComponentModel();
        $form   = $model->getForm();
        if ($input->getMethod() == 'POST') {
            $data   = $input->get('jform', array(), 'post', 'array');
        } else {
            $data = array();
        }
        $id     = $input->getInt('id');
        $option = $input->getCmd('component');

        // Get the new component parameters
        $new_secure = $data['secure'];

        // Check if the user is authorized to do this.
        $user = $this->app->getIdentity();
        if ($user === null || !$user->authorise('core.admin', $option)) {
            $this->app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'));
            $this->redirect('index.php');
            return;
        }

        // Validate the posted data.
        $return = $model->validate($form, $data);

        // Check for validation errors.
        if ($return === false) {
            // Get the validation messages.
            $errors = $model->getErrors();

            // Push up to three validation messages out to the user.
            for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
                $this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
            }

            // Save the data in the session.
            $this->app->setUserState('com_config.config.global.data', $data);

            // Redirect back to the edit screen.
            $this->setRedirect(Route::_('index.php?option=com_attachments&task=params.edit', false));
            return false;
        }

        // Attempt to save the configuration.
        $data   = array(
                    'params'    => $return,
                    'id'        => $id,
                    'option'    => $option
                    );
        $return = $model->save($data);

        // Check the return value.
        if ($return === false) {
            // Save the data in the session.
            $this->app->setUserState('com_config.config.global.data', $data);

            // Save failed, go back to the screen and display a notice.
            $message = Text::sprintf('JERROR_SAVE_FAILED', $model->getError());
            $this->setRedirect(Route::_('index.php?option=com_attachments&task=params.edit'), $message, 'error');
            return false;
        }

        // Deal with any changes in the 'secure mode' (or upload directories)
        if ($new_secure != $old_secure) {
            // Check/update the security status
            $attach_dir = JPATH_SITE . '/' . AttachmentsDefines::$ATTACHMENTS_SUBDIR;
            AttachmentsHelper::setup_upload_directory($attach_dir, $new_secure == 1);

            $msg = Text::_('ATTACH_UPDATED_ATTACHMENTS_PARAMETERS_AND_SECURITY_SETTINGS');
        } else {
            $msg = Text::_('ATTACH_UPDATED_ATTACHMENTS_PARAMETERS');
        }

        // Set the redirect based on the task.
        switch ($this->getTask()) {
            case 'apply':
                $this->setRedirect('index.php?option=com_attachments&task=params.edit', $msg, 'message');
                break;

            case 'save':
            default:
                $this->setRedirect('index.php?option=com_attachments', $msg, 'message');
                break;
        }

        return true;
    }


    /**
     * Save parameters and go back to the main listing display
     */
    public function cancel($key = null)
    {
        $this->setRedirect('index.php?option=com_attachments');
    }
}
