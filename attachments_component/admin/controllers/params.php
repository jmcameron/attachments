 <?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controllerform library
jimport('joomla.application.component.controllerform');

// ??? require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'defines.php');
 

/**
 * Attachment Controller
 */
class AttachmentsControllerParams extends JControllerForm
{
	/**
	 * Edit the component parameters
	 */
	public function edit()
	{
		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// load the component's language file
		// ??? $lang =&  JFactory::getLanguage();
		// ??? $lang->load( $component );

		// Get the component model/table
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_config'.DS.'models'.DS.'component.php');
		$model = new ConfigModelComponent();
		$model->setState('component.option', 'com_attachments');
		$model->setState('component.path', JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments');

		$form = $model->getForm();
		$component = JComponentHelper::getComponent('com_attachments');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
		// Bind the form to the data.
		if ($form && $component->params) {
			$form->bind($component->params);
		}

		// Set up the view
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'params'.DS.'view.html.php');
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
	protected function _save()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit( 'Invalid Token' );

		// Get a record for the componet table
		$component = 'com_attachments';
		$table =& JTable::getInstance('component');
		if (!$table->loadByOption( $component )) {
			$errmsg = JText::sprintf('NOT_A_VALID_COMPONENT_S', $component) . ' (ERR 30)';
			JError::raiseWarning( 500, $errmsg );
			return false;
			}

		// Load with data from the from
		$post = JRequest::get( 'post' );
		$post['option'] = 'com_attachments';
		$table->bind( $post );
		$new_params = new JParameter($table->params);

		// pre-save checks
		if (!$table->check()) {
			$errmsg = $table->getError() . ' (ERR 31)';
			JError::raiseWarning(500, $errmsg);
			return false;
			}

		// save the changes
		if (!$table->store()) {
			$errmsg = $table->getError() . ' (ERR 32)';
			JError::raiseWarning( 500, $errmsg );
			return false;
			}

		// Deal with any changes in the 'secure mode' (or upload directories)
		$old_secure = JRequest::getInt('old_secure');
		$new_secure = (int)$new_params->get('secure');
		$old_upload_dir = JRequest::getString('old_upload_dir');
		$new_upload_dir = $new_params->get('attachments_subdir', 'attachments');
		if ( ($new_secure != $old_secure) OR
			 ($new_upload_dir != $old_upload_dir) ) {

			// Check the security status
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			$dirs = AttachmentsHelper::get_upload_directories();
			foreach ($dirs as $dir) {
				$dir = JPATH_SITE.DS.$dir;
				AttachmentsHelper::setup_upload_directory($dir, $new_secure == 1);
				}

			$msg = JText::_('UPDATED_ATTACHMENTS_PARAMETERS_AND_SECURITY_SETTINGS');
			}
		else {
			$msg = JText::_( 'UPDATED_ATTACHMENTS_PARAMETERS' );
			}

		return $msg;
	}

	/**
	 * Save parameters and redirect back to the edit view
	 */
	public function apply()
	{
		$msg = AttachmentsControllerParams::_save();
		$this->setRedirect('index.php?option=com_attachments&task=params.edit', $msg, 'message');
	}


	/**
	 * Save parameters and go back to the main listing display
	 */
	function save()
	{
		$msg = AttachmentsControllerParams::_save();
		$this->setRedirect('index.php?option=com_attachments', $msg, 'message');
	}


	/**
	 * Save parameters and go back to the main listing display
	 */
	function cancel()
	{
		$this->setRedirect('index.php?option=com_attachments');
	}
	

}