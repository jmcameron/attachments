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

jimport('joomla.application.component.controller');


/**
 * Define the main attachments controller class
 *
 * @package Attachments
 */
class AttachmentsController extends JController
{

	/** List the attachments
	 *
	 * @param	bool   $cachable   If true, the view output will be cached
	 * @param	array  $urlparams  (ignored)
	 * @return void
	 */
	public function display($cachable = false, $urlparams = false)
	{
		// Set the default view (if not specified)
		JRequest::setVar('view', JRequest::getCmd('view', 'Attachments'));

		// Call parent to display
		parent::display($cachable);
	}


	/**
	 * Set up to display the entity selection view
	 *
	 * This allows users to select entities (sections, categories, and other
	 * content items that are supported with Attachments plugins).
	 */
	public function selectEntity()
	{
		// Get the parent type
		$parent_type = JRequest::getCmd('parent_type');
		if ( !$parent_type ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) .
				$db->getErrorMsg() . ' (ERR 26)';
			JError::raiseError(500, $errmsg);
			}

		// Parse the parent type and entity
		$parent_entity = JRequest::getCmd('parent_entity', 'default');
		if ( strpos($parent_type, '.') ) {
			$parts = explode('.', $parent_type);
			$parent_type = $parts[0];
			$parent_entity = $parts[1];
			}

		// Get the content parent object
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		$parent = $apm->getAttachmentsPlugin($parent_type);
		$parent_entity = $parent->getCanonicalEntityId($parent_entity);
		$parent_entity_name = JText::_('ATTACH_' . $parent_entity);

		// Get the URL to repost (for filtering)
		$post_url = $parent->getSelectEntityURL($parent_entity);

		// Set up the display lists
		$lists = Array();

		// table ordering
		$app = JFactory::getApplication();
		$filter_order =
			$app->getUserStateFromRequest('com_attachments.selectEntity.filter_order',
										  'filter_order', '', 'cmd');
		$filter_order_Dir =
			$app->getUserStateFromRequest('com_attachments.selectEntity.filter_order_Dir',
										  'filter_order_Dir','',	'word');
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$search_filter = $app->getUserStateFromRequest('com_attachments.selectEntity.search',
													   'search', '', 'string' );
		$lists['search'] = $search_filter;

		// Get the list of items to display
		$items = $parent->getEntityItems($parent_entity, $search_filter);

		// Set up the view
		require_once(JPATH_COMPONENT_ADMINISTRATOR.'/views/entity/view.html.php');
		$view = new AttachmentsViewEntity( );
		$view->option = JRequest::getCmd('option');
		$view->from = 'closeme';
		$view->post_url = $post_url;
		$view->parent_type = $parent_type;
		$view->parent_entity = $parent_entity;
		$view->parent_entity_name = $parent_entity_name;
		$view->lists = $lists;
		$view->items = $items;

		$view->display();
	}



	/**
	 * Display links for the admin Utility functions
	 */
	public function adminUtils()
	{
		// Access check
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 27)');
			}

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
			JText::_('ATTACH_DISABLE_MYSQL_UNINSTALLATION') . '::' . JText::_('ATTACH_DISABLE_MYSQL_UNINSTALLATION_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $disable_mysql_uninstall_tooltip, null, null,
							  JText::_('ATTACH_DISABLE_MYSQL_UNINSTALLATION'),
							  $disable_mysql_uninstall_url );

		// Set up the HTML for the 'Reinstall Attachments Permissions' command
		$reinstall_permissions_url = "$url_top&amp;task=utils.reinstall_permissions" . $closeme;
		$reinstall_permissions_tooltip = JText::_('ATTACH_REINSTALL_PERMISSIONS') . '::' . JText::_('ATTACH_REINSTALL_PERMISSIONS_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $reinstall_permissions_tooltip, null, null,
							  JText::_('ATTACH_REINSTALL_PERMISSIONS'), $reinstall_permissions_url);

		// Set up the HTML for the 'Regenerate attachment system filenames' command
		$regenerate_system_filenames_url =
			"$url_top&amp;task=utils.regenerate_system_filenames" . $closeme;
		$regenerate_system_filenames_tooltip =
			JText::_('ATTACH_REGENERATE_ATTACHMENT_SYSTEM_FILENAMES') . '::' .
			JText::_('ATTACH_REGENERATE_ATTACHMENT_SYSTEM_FILENAMES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $regenerate_system_filenames_tooltip, null, null,
							  JText::_('ATTACH_REGENERATE_ATTACHMENT_SYSTEM_FILENAMES'),
							  $regenerate_system_filenames_url);

		// Set up the HTML for the 'Remove spaces from system filenames' command
		$unspacify_system_filenames_url =
			"$url_top&amp;task=utils.remove_spaces_from_system_filenames" . $closeme;
		$unspacify_system_filenames_tooltip =
			JText::_('ATTACH_DESPACE_ATTACHMENT_SYSTEM_FILENAMES')	. '::' .
			JText::_('ATTACH_DESPACE_ATTACHMENT_SYSTEM_FILENAMES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $unspacify_system_filenames_tooltip, null, null,
							  JText::_('ATTACH_DESPACE_ATTACHMENT_SYSTEM_FILENAMES'),
							  $unspacify_system_filenames_url);

		// Set up the HTML for the 'Update attachment file sizes' command
		$update_file_sizes_url =
			"$url_top&amp;task=utils.update_file_sizes" . $closeme;
		$update_file_sizes_tooltip =
			JText::_('ATTACH_UPDATE_ATTACHMENT_FILE_SIZES') . '::' .
			JText::_('ATTACH_UPDATE_ATTACHMENT_FILE_SIZES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $update_file_sizes_tooltip, null, null,
							  JText::_('ATTACH_UPDATE_ATTACHMENT_FILE_SIZES'),
							  $update_file_sizes_url);

		// Set up the HTML for the 'Check Files' command
		$check_files_url = "$url_top&amp;task=utils.check_files" . $closeme;
		$check_files_tooltip = JText::_('ATTACH_CHECK_FILES') . '::' . JText::_('ATTACH_CHECK_FILES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $check_files_tooltip, null, null,
							  JText::_('ATTACH_CHECK_FILES'), $check_files_url);

		// Set up the HTML for the 'Validate URLs' command
		$validate_urls_url = "$url_top&amp;task=utils.validate_urls" . $closeme;
		$validate_urls_tooltip = JText::_('ATTACH_VALIDATE_URLS') . '::' . JText::_('ATTACH_VALIDATE_URLS_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $validate_urls_tooltip, null, null,
							  JText::_('ATTACH_VALIDATE_URLS'), $validate_urls_url);

		// Test ???
		// $utils_test_url = "$url_top&amp;task=utils.test" . $closeme;
		// $utils_test_tooltip = 'Test';
		// $entries[] = JHTML::_('tooltip', $utils_test_tooltip, null, null, 'TEST', $utils_test_url);

		// Get the view
		require_once(JPATH_COMPONENT_ADMINISTRATOR.'/views/utils/view.php');
		$view = new AttachmentsViewAdminUtils( );
		$view->entries = $entries;
		$view->display();
	}


	/**
	 * Return the attachments list as HTML (for use by Ajax)
	 */
	public function attachmentsList()
	{
		$parent_id = JRequest::getInt('parent_id', false);
		$parent_type = JRequest::getWord('parent_type', '');
		$parent_entity = JRequest::getWord('parent_entity', 'default');
		$show_links = JRequest::getBool('show_links', true);
		$allow_edit = JRequest::getBool('allow_edit', true);
		$from = JRequest::getWord('from', 'closeme');
		$title = '';

		$response = '';

		if ( ($parent_id === false) || ($parent_type == '') ) {
			return '';
			}

		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/controllers/list.php');
		$controller = new AttachmentsControllerList();
		$response = $controller->displayString($parent_id, $parent_type, $parent_entity,
											   $title, $show_links, $allow_edit, false, $from);
		echo $response;
	}


}

