<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 47)');
	}

jimport('joomla.application.component.controller');


/**
 * The controller for utils requests
 * (adapted from administrator/components/com_config/controllers/component.php)
 *
 * @package Attachments
 */
class AttachmentsControllerUtils extends JController
{
	/**
	 * Javascript script to close the pop-up window
	 */
	var $_close_script = '<script>var myparent = window.parent; window.parent.SqueezeBox.close(); myparent.location.reload();</script>';


	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 */
	public function __construct( $default = array())
	{
		$default['default_task'] = 'noop';
		parent::__construct( $default );
	}


	/**
	 * A noop function so this controller does not have a usable default
	 */
	public function noop()
	{
		echo "<h1>" . JText::_('ATTACH_ERROR_NO_UTILS_FUNCTION_SPECIFIED') . "</h1>";
		exit();
	}

	/**
	 * Enqueue a system message.
	 *
	 * @param	string	 $msg	The message to enqueue.
	 * @param	string	 $type	The message type. Default is message.
	 *
	 * @return	void
	 */
	protected function enqueueSystemMessage($msg, $type = 'message')
	{
		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $type);

		// Not sure why I need the extra saving to the session below,
		// but it it seems necessary because I'm doing it from an iframe.
		$session = JFactory::getSession();
		$session->set('application.queue', $app->getMessageQueue());
	}


	/**
	 * Add icon filenames for attachments missing an icon
	 * (See AttachmentsUpdate::add_icon_filenames() in update.php for details )
	 */
	public function add_icon_filenames()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 48)');
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');
		$msg = AttachmentsUpdate::add_icon_filenames();
		$this->setRedirect('index.php?option=' . $this->option, $msg);
	}


	/**
	 * Update any null dates in any attachments
	 * (See AttachmentsUpdate::update_null_dates() in update.php for details )
	 */
	public function update_null_dates()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 49)');
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$numUpdated = AttachmentsUpdate::update_null_dates();
		$msg = JText::sprintf( 'ATTACH_UPDATED_N_ATTACHMENTS', $numUpdated );
		$this->setRedirect('index.php?option=' . $this->option, $msg);
	}



	/**
	 * Disalbe SQL uninstall of existing attachments (when Attachments is uninstalled)
	 * (See AttachmentsUpdate::disable_sql_uninstall() in update.php for details )
	 */
	public function disable_sql_uninstall()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 50)');
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::disable_sql_uninstall();

		if ( JRequest::getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refesh the parent window
			echo $this->_close_script;
			}
		else {
			$this->setRedirect('index.php?option=com_attachments', $msg);
			}
	}


	/**
	 * Regenerate system filenames
	 * (See AttachmentsUpdate::regenerate_system_filenames() in update.php for details )
	 */
	public function regenerate_system_filenames()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 51)');
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::regenerate_system_filenames();

		if ( JRequest::getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refesh the parent window
			echo $this->_close_script;
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}


	/**
	 * Remove spaces from system filenames for all attachments
	 * (See AttachmentsUpdate::remove_spaces_from_system_filenames() in update.php for details )
	 */
	public function remove_spaces_from_system_filenames()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 52)');
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::remove_spaces_from_system_filenames();

		if ( JRequest::getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refesh the parent window
			echo $this->_close_script;
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}


	/**
	 * Update file sizes for all attachments
	 * (See AttachmentsUpdate::update_file_sizes() in update.php for details )
	 */
	public function update_file_sizes()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 53)');
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::update_file_sizes();

		if ( JRequest::getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refesh the parent window
			echo $this->_close_script;
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}


	/**
	 * Check all files in any attachments
	 * (See AttachmentsUpdate::check_files() in update.php for details )
	 */
	public function check_files()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 54)');
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::check_files_existance();

		if ( JRequest::getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refesh the parent window
			echo $this->_close_script;
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}

	/**
	 * Validate all URLS in any attachments
	 * (See AttachmentsUpdate::validate_urls() in update.php for details )
	 */
	public function validate_urls()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 55)');
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::validate_urls();

		if ( JRequest::getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refesh the parent window
			echo $this->_close_script;
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}


	/**
	 * Validate all URLS in any attachments
	 * (See AttachmentsUpdate::reinstall_permissions() in update.php for details )
	 */
	public function reinstall_permissions()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 56)');
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::installAttachmentsPermissions();

		if ( JRequest::getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refesh the parent window
			echo $this->_close_script;
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}


	/**
	 * Install attachments data from CSV file
	 */
	public function installAttachmentsFromCsvFile()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 57)');
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/import.php');

		$filename = JRequest::getString('filename', null);
		if ( $filename == null ) {
			$errmsg = JText::_('ATTACH_ERROR_MUST_ADD_FILENAME_TO_URL') . ' (ERR 58)';
			return JError::raiseWarning(500, $errmsg);
			}
		$verify_parent = JRequest::getBool('verify_parent', true);
		$update = JRequest::getBool('update', false);
		$dry_run = JRequest::getBool('dry_run', false);

		$status = AttachmentsImport::importAttachmentsFromCSVFile($filename, $verify_parent,
																  $update,	$dry_run);

		// Abort if it is an error message
		if ( is_string($status) ) {
			return JError::raiseWarning(500, $status);
			}

		// Otherwise, report the results
		if ( is_array($status) ) {
			$msg = JText::sprintf('ATTACH_ADDED_DATA_FOR_N_ATTACHMENTS', count($status));
			$this->setRedirect('index.php?option=com_attachments', $msg);
			}
		else {
			if ( $dry_run ) {
				$msg = JText::sprintf('ATTACH_DATA_FOR_N_ATTACHMENTS_OK', $status) . ' (ERR 59)';
				return JError::raiseNotice(200, $msg);
				}
			else {
				$errmsg = JText::sprintf('ATTACH_ERROR_IMPORTING_ATTACHMENTS_S', $status) . ' (ERR 60)';
				return JError::raiseWarning(500, $errmsg);
				}
			}
	}


	/**
	 * Test function
	 */
	public function test()
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
			}

		echo "Test!";

		exit();
	}

}
