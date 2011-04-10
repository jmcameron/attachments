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

/**
 * Define the main attachments controller class
 *
 * @package Attachments
 */
class AttachmentsController extends JController
{

	/** List the attachments
	 *
	 * @return void
	 */
	function display($cachable = false)
	{
		// Set the default view (if not specified)
		JRequest::setVar('view', JRequest::getCmd('view', 'Attachments'));

		// Call parent to display
		parent::display($cachable);
	}

	/**
	 * Display links for the admin Utility functions
	 */
	function adminUtils()
	{
		// $this->loadLanguage();
		
		// Set up the tooltip behavior
		$opts = Array( 'hideDelay' => 0, 'showDelay' => 0 );
		JHTML::_('behavior.tooltip', '.hasTip', $opts);

		// Set up url/link/tooltip for each command
	    $uri = JFactory::getURI();
		$url_top = $uri->base(true) . "/index.php?option=com_attachments&amp;controller=special";
		$closeme = '&amp;tmpl=component&amp;close=1';

		// Set up the array of entries
		$entries = Array();

		// Set up the HTML for the 'Disable MySQL uninstallation' command
		$disable_mysql_uninstall_url =
			"$url_top&amp;task=utils.disable_sql_uninstall" . $closeme;
		$disable_mysql_uninstall_tooltip =
			JText::_('DISABLE_MYSQL_UNINSTALLATION') . '::' . JText::_('DISABLE_MYSQL_UNINSTALLATION_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $disable_mysql_uninstall_tooltip, null, null,
							  JText::_('DISABLE_MYSQL_UNINSTALLATION'),
							  $disable_mysql_uninstall_url );

		// Set up the HTML for the 'Regenerate attachment system filenames' command
		$regenerate_system_filenames_url =
			"$url_top&amp;task=utils.regenerate_system_filenames" . $closeme;
		$regenerate_system_filenames_tooltip =
			JText::_('REGENERATE_ATTACHMENT_SYSTEM_FILENAMES') . '::' .
			JText::_('REGENERATE_ATTACHMENT_SYSTEM_FILENAMES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $regenerate_system_filenames_tooltip, null, null,
							  JText::_('REGENERATE_ATTACHMENT_SYSTEM_FILENAMES'),
							  $regenerate_system_filenames_url);

		// Set up the HTML for the 'Remove spaces from system filenames' command
		$unspacify_system_filenames_url =
			"$url_top&amp;task=utils.remove_spaces_from_system_filenames" . $closeme;
		$unspacify_system_filenames_tooltip =
			JText::_('DESPACE_ATTACHMENT_SYSTEM_FILENAMES')  . '::' .
			JText::_('DESPACE_ATTACHMENT_SYSTEM_FILENAMES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $unspacify_system_filenames_tooltip, null, null,
							  JText::_('DESPACE_ATTACHMENT_SYSTEM_FILENAMES'),
							  $unspacify_system_filenames_url);

		// Set up the HTML for the 'Update attachment file sizes' command
		$update_file_sizes_url =
			"$url_top&amp;task=utils.update_file_sizes" . $closeme;
		$update_file_sizes_tooltip =
			JText::_('UPDATE_ATTACHMENT_FILE_SIZES') . '::' .
			JText::_('UPDATE_ATTACHMENT_FILE_SIZES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $update_file_sizes_tooltip, null, null,
							  JText::_('UPDATE_ATTACHMENT_FILE_SIZES'),
							  $update_file_sizes_url);

		// Set up the HTML for the 'Check Files' command
		$check_files_url = "$url_top&amp;task=utils.check_files" . $closeme;
		$check_files_tooltip = JText::_('CHECK_FILES') . '::' . JText::_('CHECK_FILES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $check_files_tooltip, null, null,
							  JText::_('CHECK_FILES'), $check_files_url);

		// Set up the HTML for the 'Validate URLs' command
		$validate_urls_url = "$url_top&amp;task=utils.validate_urls" . $closeme;
		$validate_urls_tooltip = JText::_('VALIDATE_URLS') . '::' . JText::_('VALIDATE_URLS_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $validate_urls_tooltip, null, null,
							  JText::_('VALIDATE_URLS'), $validate_urls_url);

		// Test ???
		// $utils_test_url = "$url_top&amp;controller=special&amp;task=test" . $closeme;
		// $utils_test_tooltip = 'Test';
		// $entries[] = JHTML::_('tooltip', $utils_test_tooltip, null, null, 'TEST', $utils_test_url);

		// Get the view
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'utils'.DS.'view.php');
		$view = new AttachmentsViewAdminUtils( );
		$view->entries = $entries;
		$view->display();
	}
	
	
}

