<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

// ??? Add access check to make sure only Admins can run these commands

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
		echo "<h1>" . JText::_('ERROR_NO_UTILS_FUNCTION_SPECIFIED') . "</h1>";
		exit();
	}


	/**
	 * Add icon filenames for attachments missing an icon
	 * (See AttachmentsUpdate::add_icon_filenames() in update.php for details )
	 */
	public function add_icon_filenames()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');
		$msg = AttachmentsUpdate::add_icon_filenames();
		$this->setRedirect('index.php?option=' . $this->option, $msg);
	}


	/**
	 * Update any null dates in any attachments
	 * (See AttachmentsUpdate::update_null_dates() in update.php for details )
	 */
	public function update_null_dates()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$numUpdated = AttachmentsUpdate::update_null_dates();
		$msg = JText::sprintf( 'UPDATED_N_ATTACHMENTS', $numUpdated );
		$this->setRedirect('index.php?option=' . $this->option, $msg);
	}


	/**
	 * Update the attachments table
	 * (See AttachmentsUpdate::update_attachments_table() in update.php for details )
	 */
	public function update_attachments_table()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo "\n<html><head><title>Updating Attachments Tables</title></head><body>\n";
		echo "<h2>Updating Attachments Tables</h2>\n";

		AttachmentsUpdate::update_attachments_table();

		$uri = JFactory::getURI();
		$return_url = $uri->base(true);
		echo "<br />&nbsp;<br /><a href=\"$return_url\">Return to Admin page</a>\n";
		echo "</body>\n</html>";

		exit();
	}


	/**
	 * Disalbe SQL uninstall of existing attachments (when Attachments is uninstalled)
	 * (See AttachmentsUpdate::disable_sql_uninstall() in update.php for details )
	 */
	public function disable_sql_uninstall()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::disable_sql_uninstall();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);

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
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::regenerate_system_filenames();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
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
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::remove_spaces_from_system_filenames();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
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
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::update_file_sizes();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
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
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::check_files_existance();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
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
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		$msg = AttachmentsUpdate::validate_urls();

		if ( JRequest::getBool('close') ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			AttachmentsHelper::enqueueSystemMessage($msg);
			echo $this->_close_script;
			}
		else {
			$this->setRedirect('index.php?option=' . $this->option, $msg);
			}
	}


	/**
	 * Test function
	 */
	public function test()
	{
		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');

		exit();
	}

}

?>
