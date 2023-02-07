 <?php
/**
 * Attachments component param controller
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/controller_form.php');

/** Load the class for the model component form and make it work for both 3.2 and less */
if (version_compare(JVERSION, '3.2', 'ge'))
{
	require_once(JPATH_SITE . '/components/com_config/model/cms.php');
	require_once(JPATH_SITE . '/components/com_config/model/form.php');
}
require_once(JPATH_ADMINISTRATOR.'/components/com_config/models/component.php');


/**
 * Attachment Controller
 *
 * @package Attachments
 */
class AttachmentsControllerParams extends JControllerFormLegacy
{

	/**
	 * Edit the component parameters
	 */
	public function edit($key = null, $urlVar = null)
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 117)');
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Get the component model/table
		$model = new ConfigModelComponent();
		$state = $model->getState();
		$state->set('component.option', 'com_attachments');
		$state->set('component.path', JPATH_ADMINISTRATOR.'/components/com_attachments');
		$model->setState($state);
		$form = $model->getForm();
		$component = JComponentHelper::getComponent('com_attachments');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors) . ' (ERR 118)');
			return false;
		}

		// Bind the form to the data.
		if ($form && $component->params) {
			$form->bind($component->params);
		}

		// Set up the view
		require_once(JPATH_COMPONENT_ADMINISTRATOR.'/views/params/view.html.php');
		$view = new AttachmentsViewParams( );
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
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();

		// Get the old component parameters
		jimport('joomla.application.component.helper');
		$old_params = JComponentHelper::getParams('com_attachments');
		$old_secure = JRequest::getInt('old_secure');

		// Set FTP credentials, if given.
		jimport('joomla.client.helper');
		JClientHelper::setCredentialsFromRequest('ftp');

		// Initialise variables.
		$model = new ConfigModelComponent();
		$form	= $model->getForm();
		$data	= JRequest::getVar('jform', array(), 'post', 'array');
		$id		= JRequest::getInt('id');
		$option	= JRequest::getCmd('component');

		// Get the new component parameters
		$new_secure = $data['secure'];

		// Check if the user is authorized to do this.
		if (!JFactory::getUser()->authorise('core.admin', $option))
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}

		// Validate the posted data.
		$return = $model->validate($form, $data);

		// Check for validation errors.
		if ($return === false) {

			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Save the data in the session.
			$app->setUserState('com_config.config.global.data', $data);

			// Redirect back to the edit screen.
			$this->setRedirect(JRoute::_('index.php?option=com_attachments&task=params.edit', false));
			return false;
		}

		// Attempt to save the configuration.
		$data	= array(
					'params'	=> $return,
					'id'		=> $id,
					'option'	=> $option
					);
		$return = $model->save($data);

		// Check the return value.
		if ($return === false)
		{
			// Save the data in the session.
			$app->setUserState('com_config.config.global.data', $data);

			// Save failed, go back to the screen and display a notice.
			$message = JText::sprintf('JERROR_SAVE_FAILED', $model->getError());
			$this->setRedirect(JRoute::_('index.php?option=com_attachments&task=params.edit'), $message, 'error');
			return false;
		}

		// Deal with any changes in the 'secure mode' (or upload directories)
		if ( $new_secure != $old_secure ) {

			// Check/update the security status
			require_once(JPATH_SITE.'/components/com_attachments/helper.php');
			$attach_dir = JPATH_SITE.'/'.AttachmentsDefines::$ATTACHMENTS_SUBDIR;
			AttachmentsHelper::setup_upload_directory($attach_dir, $new_secure == 1);

			$msg = JText::_('ATTACH_UPDATED_ATTACHMENTS_PARAMETERS_AND_SECURITY_SETTINGS');
			}
		else {
			$msg = JText::_( 'ATTACH_UPDATED_ATTACHMENTS_PARAMETERS' );
			}

		// Set the redirect based on the task.
		switch ($this->getTask())
		{
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
