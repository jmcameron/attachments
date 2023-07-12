<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die('Restricted access');

// Access check.
$app = Factory::getApplication();
$user = $app->getIdentity();
if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 150)', 404);
	}

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/controller.php');

// Load the helper functions
require_once(JPATH_SITE.'/components/com_attachments/javascript.php');


/**
 * The controller for utils requests
 * (adapted from administrator/components/com_config/controllers/component.php)
 *
 * @package Attachments
 */
class AttachmentsControllerUtils extends BaseController
{
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
		echo "<h1>" . Text::_('ATTACH_ERROR_NO_UTILS_FUNCTION_SPECIFIED') . "</h1>";
		exit();
	}


	/**
	 *
	 */


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
		$app = Factory::getApplication();
		$app->enqueueMessage($msg, $type);

		// Not sure why I need the extra saving to the session below,
		// but it it seems necessary because I'm doing it from an iframe.
		$session = $app->getSession();
		$session->set('application.queue', $app->getMessageQueue());
	}


	/**
	 * Add icon filenames for attachments missing an icon
	 * (See AttachmentsUpdate::add_icon_filenames() in update.php for details )
	 */
	public function add_icon_filenames()
	{
		// Access check.
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 151)', 404);
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
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 152)', 404);
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$numUpdated = AttachmentsUpdate::update_null_dates();
		$msg = Text::sprintf( 'ATTACH_UPDATED_N_ATTACHMENTS', $numUpdated );
		$this->setRedirect('index.php?option=' . $this->option, $msg);
	}



	/**
	 * Disable SQL uninstall of existing attachments (when Attachments is uninstalled)
	 * (See AttachmentsUpdate::disable_sql_uninstall() in update.php for details )
	 */
	public function disable_sql_uninstall()
	{
		// Access check.
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 153)', 404);
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::disable_sql_uninstall();

		$input = $app->getInput();
		if ( $input->getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refresh the parent window
			AttachmentsJavascript::closeModal();
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
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user ===null || !$user->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 154)', 404);
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::regenerate_system_filenames();

		$input = $app->getInput();
		if ( $input->getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refresh the parent window
			AttachmentsJavascript::closeModal();
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
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 155)', 404);
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::remove_spaces_from_system_filenames();

		$input = $app->getInput();
		if ( $input->getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refresh the parent window
			AttachmentsJavascript::closeModal();
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
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 156)', 404);
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::update_file_sizes();

		$input = $app->getInput();
		if ( $input->getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refresh the parent window
			AttachmentsJavascript::closeModal();
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
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 157)', 404);
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::check_files_existance();

		$input = $app->getInput();
		if ( $input->getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refesh the parent window
			AttachmentsJavascript::closeModal();
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
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR' . ' (ERR 158)'), 404);
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::validate_urls();

		$input = $app->getInput();
		if ( $input->getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refesh the parent window
			AttachmentsJavascript::closeModal();
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
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 159)', 404);
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');

		$msg = AttachmentsUpdate::installAttachmentsPermissions();

		$input = $app->getInput();
		if ( $input->getBool('close') ) {

			$this->enqueueSystemMessage($msg);

			// Close this window and refresh the parent window
			AttachmentsJavascript::closeModal();
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
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 160)', 404);
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/import.php');

		$input = $app->getInput();
		$filename = $input->getString('filename', null);
		if ( $filename == null ) {
			$errmsg = Text::_('ATTACH_ERROR_MUST_ADD_FILENAME_TO_URL') . ' (ERR 161)';
			throw new Exception($errmsg, 500);
			}
		$verify_parent = $input->getBool('verify_parent', true);
		$update = $input->getBool('update', false);
		$dry_run = $input->getBool('dry_run', false);

		$status = AttachmentsImport::importAttachmentsFromCSVFile($filename, $verify_parent,
																  $update,	$dry_run);

		// Abort if it is an error message
		if ( is_string($status) ) {
			throw new Exception($status, 500);
			}

		// Otherwise, report the results
		if ( is_array($status) ) {
			$msg = Text::sprintf('ATTACH_ADDED_DATA_FOR_N_ATTACHMENTS', count($status));
			$this->setRedirect('index.php?option=com_attachments', $msg);
			}
		else {
			if ( $dry_run ) {
				$msg = Text::sprintf('ATTACH_DATA_FOR_N_ATTACHMENTS_OK', $status) . ' (ERR 162)';
				throw new Exception($msg, 500);
				}
			else {
				$errmsg = Text::sprintf('ATTACH_ERROR_IMPORTING_ATTACHMENTS_S', $status) . ' (ERR 163)';
				throw new Exception($errmsg, 500);
				}
			}
	}


	/**
	 * Test function
	 */
	public function test()
	{
		// Access check.
		if (!Factory::getApplication()->getIdentity()->authorise('core.admin', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR'), 404);
			}

		echo "Test!";

		exit();
	}

}
