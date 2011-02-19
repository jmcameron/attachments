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

jimport('joomla.application.component.controller');


/**
 * Define the main attachments controller class
 *
 * @package Attachments
 */
class AttachmentsAdminController extends JController
{
	/** Supported save types for uploading/updating
	 */
	var $_legal_save_types = array('upload', 'update');


	/** Supported URI types for uploading/updating
	 */
	var $_legal_uri_types = array('file', 'url');


	/** Constructor
	 */
	function __construct( $default = array() )
	{
		parent::__construct( $default );
		$this->registerTask('apply', 'save');
		$this->registerTask('applyNew', 'saveNew');
		$this->registerTask('unpublish', 'publish');
	}


	/** List the attachments
	 */
	function listAttachments()
	{
		global $mainframe;

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Get the attachments list model
		$model =& $this->getModel('attachments');

		// Construct the HTML lists
		$lists = Array();

		$lists['order']		= $model->getState('filter_order');
		$lists['order_Dir'] = $model->getState('filter_order_Dir');

		// Determine types of parents for which attachments should be displayed
		$list_for_parents_default = 'ALL';
		$suppress_obsolete_attachments = $params->get('suppress_obsolete_attachments', false);
		if ( $suppress_obsolete_attachments ) {
			$list_for_parents_default = 'PUBLISHED';
			}
		$list_for_parents =
			$mainframe->getUserStateFromRequest('com_attachments.listAttachments.list_for_parents',
												'list_for_parents', $list_for_parents_default, 'word');
		$lists['list_for_parents'] = JString::strtolower($list_for_parents);

		// Add the drop-down menu to decide which attachments to show
		$list_for_parents_options = array();
		$list_for_parents_options[] = JHTML::_('select.option', 'ALL', JText::_( 'ALL_PARENTS' ) );
		$list_for_parents_options[] = JHTML::_('select.option', 'PUBLISHED', JText::_( 'PUBLISHED_PARENTS' ) );
		$list_for_parents_options[] = JHTML::_('select.option', 'UNPUBLISHED', JText::_( 'UNPUBLISHED_PARENTS' ) );
		$list_for_parents_options[] = JHTML::_('select.option', 'ARCHIVED', JText::_( 'ARCHIVED_PARENTS' ) );
		$list_for_parents_options[] = JHTML::_('select.option', 'NONE', JText::_( 'NO_PARENTS' ) );
		$list_for_parents_tooltip = JText::_('SHOW_FOR_PARENTS_TOOLTIP');
		$lists['list_for_parents_menu'] =
			JHTML::_('select.genericlist', $list_for_parents_options, 'list_for_parents',
					 'class="inputbox" onChange="document.adminForm.submit();" title="' .
					 $list_for_parents_tooltip . '"', 'value', 'text', $list_for_parents);

		// Add the drop-down menu to filter for types of entities
		$filter_entity = $model->getState('filter_entity');
		$filter_entity_options = array();
		$filter_entity_options[] = JHTML::_('select.option', 'ALL', JText::_( 'ALL_TYPES' ) );
		$apm =& $model->getAttachmentsPluginManager();
		$entity_info =& $apm->getInstalledEntityInfo();
		foreach ($entity_info as $einfo) {
			$filter_entity_options[] = JHTML::_('select.option', $einfo['id'], $einfo['name_plural']);
			}
		$filter_entity_tooltip = JText::_('FILTER_ENTITY_TOOLTIP');
		$lists['filter_entity_menu'] =
			JHTML::_('select.genericlist', $filter_entity_options, 'filter_entity',
					 'class="inputbox" onChange="document.adminForm.submit();" title="'.
					 $filter_entity_tooltip . '"', 'value', 'text', $filter_entity);

		// search filter
		$lists['search'] = $model->getState('search');

		// Set up the view
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'list'.DS.'view.html.php');
		$view = new AttachmentsViewList();
		$view->setModel($model, true);

		$view->assignRef('lists', $lists);
		$view->assignRef('params', $params);
		$view->assign('attachments', $model->getData());
		$view->assign('pagination', $model->getPagination());

		$view->version = AttachmentsComponentVersion;

		$view->display();
	}

	/**
	 * Add - create a new attachment
	 */
	function add()
	{
		global $mainframe, $option;

		$parent_entity = 'default';

		// Get the parent info
		if ( JRequest::getString('article_id') ) {
			$pidarr = explode(',', JRequest::getString('article_id'));
			$parent_type = 'com_content';
			}
		else {
			$pidarr = explode(',', JRequest::getString('parent_id'));
			$parent_type = AttachmentsAdminController::_getCmd2('parent_type', 'com_content');

			// If the entity is embedded in the parent type, split them
			if ( strpos($parent_type, ':') ) {
				$parts = explode(':', $parent_type);
				$parent_type = $parts[0];
				$parent_entity = $parts[1];
				}
			}

		// Get the parent id and see if the parent is new
		$parent_id = null;
		$new_parent = false;
		if ( is_numeric($pidarr[0]) ) {
			$parent_id = (int)$pidarr[0];
			}
		if ( (count($pidarr) == 1) AND ($pidarr[0] == '') ) {
			// Called from the [New] button
			$parent_id = null;
			}
		if ( count($pidarr) > 1 ) {
			if ( $pidarr[1] == 'new' ) {
				$new_parent = true;
				}
			}

		// Add the published selection
		$lists['published'] = JHTML::_('select.booleanlist', 'published',
									   'class="inputbox"', false);

		// Set up the "select parent" button
		JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
		$apm =& getAttachmentsPluginManager();
		$entity_info =& $apm->getInstalledEntityInfo();
		$parent =& $apm->getAttachmentsPlugin($parent_type);
		$parent->loadLanguage();

		$parent_entity_name = JText::_($parent->getEntityName($parent_entity));

		if ( !$parent_id ) {
			// Set up the necessary javascript
			$document =&  JFactory::getDocument();
			$document->addScript( $mainframe->getSiteURL() . 'media/system/js/mootools.js' );
			$document->addScript( $mainframe->getSiteURL() . 'media/system/js/modal.js' );
			$document->addScript( $mainframe->getSiteURL() . 'plugins/content/attachments_refresh.js' );

			$js = "
	   function jSelectArticle(id, title) {
		   document.getElementById('parent_id').value = id;
		   document.getElementById('parent_title').value = title;
		   document.getElementById('sbox-window').close();
		   }";
			$document->addScriptDeclaration($js);
			JHTML::_('behavior.modal', 'a.modal-button');
			}
		else {
			if ( !is_numeric($parent_id) ) {
				$errmsg = JText::sprintf('ERROR_INVALID_PARENT_ID_S', $parent_id) . ' (ERR 13)';
				JError::raiseError(500, $errmsg);
				}
			}

		// Use a component template for the iframe view (from the article editor)
		$from = JRequest::getWord('from');
		if ( $from == 'closeme' ) {
			JRequest::setVar('tmpl', 'component');
			}

		// Disable the main menu items
		JRequest::setVar( 'hidemainmenu', 1 );

		// Get the article title
		$parent_title = false;
		if ( !$new_parent ) {

			JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
			$apm =& getAttachmentsPluginManager();
			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				// Exit if there is no Attachments plugin to handle this parent_type
				$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 14)';
				JError::raiseError(500, $errmsg);
				}
			$parent =& $apm->getAttachmentsPlugin($parent_type);
			$parent->loadLanguage();
			$parent_title = $parent->getTitle($parent_id, $parent_entity);
			}

		// Determine the type of upload
		$default_uri_type = 'file';
		$uri_type = JRequest::getWord('uri', $default_uri_type);
		if ( !in_array( $uri_type, $this->_legal_uri_types ) ) {
			// Make sure only legal values are entered
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Set up the view
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'add'.DS.'view.php');
		$view = new AttachmentsViewAdd();

		$view->assignRef('uri_type',	  $uri_type);
		$view->assign('url',			  '');
		$view->assign('parent_id',		  $parent_id);
		$view->assignRef('parent_type',	  $parent_type);
		$view->assignRef('parent_entity', $parent_entity);
		$view->assignRef('parent_entity_name', $parent_entity_name);
		$view->assignRef('parent_title',  $parent_title);
		$view->assign('new_parent',		  $new_parent);
		$view->assignRef('entity_info',	  $entity_info);
		$view->assignRef('from',		  $from);

		// Handle user field 1
		$show_user_field_1 = false;
		$user_field_1_name = $params->get('user_field_1_name', '');
		if ( $user_field_1_name != '' ) {
			$show_user_field_1 = true;
			$view->assignRef('user_field_1_name', $user_field_1_name);
			}
		$view->assign('show_user_field_1', $show_user_field_1);

		// Handle user field 2
		$show_user_field_2 = false;
		$user_field_2_name = $params->get('user_field_2_name', '');
		if ( $user_field_2_name != '' ) {
			$show_user_field_2 = true;
			$view->assignRef('user_field_2_name', $user_field_2_name);
			}
		$view->assign('show_user_field_2', $show_user_field_2);

		// Handle user field 3
		$show_user_field_3 = false;
		$user_field_3_name = $params->get('user_field_3_name', '');
		if ( $user_field_3_name != '' ) {
			$show_user_field_3 = true;
			$view->assignRef('user_field_3_name', $user_field_3_name);
			}
		$view->assign('show_user_field_3', $show_user_field_3);

		// Set up to toggle between uploading file/urls
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
		AttachmentsHelper::add_view_urls($view, 'upload', $parent_id, $parent_type, null, $from);
		if ( $uri_type == 'file' ) {
			$upload_toggle_button_text = JText::_('ENTER_URL_INSTEAD');
			$upload_toggle_tooltip = JText::_('ENTER_URL_INSTEAD_TOOLTIP');
			$upload_toggle_url = 'index.php?option=com_attachments&amp;task=add&amp;uri=url';
			}
		else {
			$upload_toggle_button_text = JText::_('SELECT_FILE_TO_UPLOAD_INSTEAD');
			$upload_toggle_tooltip = JText::_('SELECT_FILE_TO_UPLOAD_INSTEAD_TOOLTIP');
			$upload_toggle_url = 'index.php?option=com_attachments&amp;task=add&amp;uri=file';
			}
		if ( $from == 'closeme' ) {
			$upload_toggle_url .= '&amp;tmpl=component';
			}
		if ( $from ) {
			$upload_toggle_url .= '&amp;from=' . $from;
			}

		// Update the toggle URL to not forget if the parent is not simply an article
		if ( !($parent_type == 'com_content' AND $parent_entity == 'default') ) {
			$upload_toggle_url .= "&amp;parent_type=$parent_type";
			if ( $parent_entity != 'default' ) {
				$upload_toggle_url .= ":$parent_entity";
				}
			}

		// If this is for an existing content item, modify the URL appropriately
		if ( $new_parent ) {
			$upload_toggle_url .= "&amp;parent_id=0,new";
			}
		elseif ( $parent_id AND ($parent_id != -1) ) {
			$upload_toggle_url .= "&amp;parent_id=$parent_id";
			}
		if ( JRequest::getWord('editor') ) {
			$upload_toggle_url .= "&amp;editor=" . JRequest::getWord('editor');
			}

		$view->assignRef('upload_toggle_button_text', $upload_toggle_button_text);
		$view->assignRef('upload_toggle_url',		  $upload_toggle_url);
		$view->assignRef('upload_toggle_tooltip',	  $upload_toggle_tooltip);

		// Set up the 'select parent' button
		$view->assign('selpar_label', JText::sprintf('SELECT_ENTITY_S_COLON', $parent_entity_name));
		$view->assign('selpar_btn_text', '&nbsp;' . JText::sprintf('SELECT_ENTITY_S', $parent_entity_name) . '&nbsp;');
		$view->assign('selpar_btn_tooltip', JText::sprintf('SELECT_ENTITY_S_TOOLTIP', $parent_entity_name));
		$view->assign('selpar_btn_url', $parent->getSelectEntityURL($parent_entity));

		$view->display();
	}


	/**
	 * Edit an attachment
	 */
	function edit()
	{
		global $option, $mainframe;

		$db =& JFactory::getDBO();

		$attachment =& JTable::getInstance('Attachments', 'Table');
		$cid = JRequest::getVar( 'cid', array(0), '', 'array');
		$change = JRequest::getWord('change', '');
		$change_parent = ($change == 'parent');
		$update_file = JRequest::getWord('change') == 'file';
		$attachment_id = $cid[0];
		$attachment->load($attachment_id);

		$from = JRequest::getWord('from');
		$layout = JRequest::getWord('tmpl');

		// set up lists for form controls
		$lists = array();
		$lists['published'] = JHTML::_('select.booleanlist', 'published',
									   'class="inputbox"', $attachment->published);
		$lists['url_valid'] = JHTML::_('select.booleanlist', 'url_valid',
									   'class="inputbox" title="' . JText::_('URL_IS_VALID_TOOLTIP') . '"',
									   $attachment->url_valid);

		// Construct the drop-down list for legal icon filenames
		$icon_filenames = array();
		require_once(JPATH_COMPONENT_SITE.DS.'file_types.php');
		foreach ( AttachmentsFileTypes::unique_icon_filenames() as $ifname) {
			$icon_filenames[] = JHTML::_('select.option', $ifname);
			}
		$lists['icon_filenames'] =
			JHTML::_('select.genericlist',	 $icon_filenames,
					 'icon_filename', 'class="inputbox" size="1"', 'value', 'text',
					 $attachment->icon_filename);

		// Get the uploaders name
		$query = "SELECT name FROM #__users WHERE id='".(int)$attachment->uploader_id."' LIMIT 1";
		$db->setQuery($query);
		$attachment->uploader_name = $db->loadResult();

		// Massage the data
		$attachment->size = (int)( 10 * $attachment->file_size / 1024.0 ) / 10.0;
		if ( $attachment->uri_type == 'file' ) {
			$attachment->url = $mainframe->getSiteURL() . $attachment->url;
			}

		// Get the parent handler
		$parent_id = $attachment->parent_id;
		$parent_type = $attachment->parent_type;
		$parent_entity = $attachment->parent_entity;
		JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			// Exit if there is no Attachments plugin to handle this parent_type
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 15)';
			JError::raiseError(500, $errmsg);
			}
		$entity_info =& $apm->getInstalledEntityInfo();
		$parent =& $apm->getAttachmentsPlugin($parent_type);
		$parent_entity = $parent->getCanonicalEntity( $attachment->parent_entity );
		$attachment->parent_entity = $parent_entity;

		// Get the parent name
		$parent->loadLanguage();
		$attachment->parent_entity_name = JText::_($parent->getEntityName($parent_entity));
		$parent_title = $parent->getTitle($parent_id, $parent_entity);
		if ( !$parent_title ) {
			$parent_title = JText::sprintf('NO_PARENT_S', $attachment->parent_entity_name);
			}
		$attachment->parent_title = $parent_title;
		$attachment->parent_published = $parent->isParentPublished($parent_id, $parent_entity);
		$update = JRequest::getWord('update');
		if ( $update AND !in_array($update, $this->_legal_uri_types) ) {
			$update = false;
			}

		$document =&  JFactory::getDocument();

		// Set up view for changing parent
		if ( $change_parent ) {
			$document->addScript( $mainframe->getSiteURL() . '/media/system/js/modal.js' );
			$js = "
	   function jSelectArticle(id, title) {
		   document.getElementById('parent_id').value = id;
		   document.getElementById('parent_title').value = title;
		   document.getElementById('sbox-window').close();
		   }";
			$document->addScriptDeclaration($js);
			JHTML::_('behavior.modal', 'a.modal-button');
			}

		JRequest::setVar( 'hidemainmenu', 1 );

		// See if a new type of parent was requested
		$new_parent_type = '';
		$new_parent_entity = 'default';
		$new_parent_entity_name = '';
		if ( $change_parent ) {
			$new_parent_type = AttachmentsAdminController::_getCmd2('new_parent_type');
			if ( $new_parent_type ) {
				if ( strpos($new_parent_type, ':') ) {
					$parts = explode(':', $new_parent_type);
					$new_parent_type = $parts[0];
					$new_parent_entity = $parts[1];
					}

				$new_parent =& $apm->getAttachmentsPlugin($new_parent_type);
				$new_parent_entity_name = JText::_($new_parent->getEntityName($new_parent_entity));

				// Set up the 'select parent' button
				$selpar_label = JText::sprintf('SELECT_ENTITY_S_COLON', $new_parent_entity_name);
				$selpar_btn_text = '&nbsp;' . JText::sprintf('SELECT_ENTITY_S', $new_parent_entity_name) . '&nbsp;';
				$selpar_btn_tooltip = JText::sprintf('SELECT_ENTITY_S_TOOLTIP', $new_parent_entity_name);

				$selpar_btn_url = $new_parent->getSelectEntityURL($new_parent_entity);
				$selpar_parent_title = '';
				$selpar_parent_id = '-1';
				}
			else {
				// Set up the 'select parent' button
				$selpar_label = JText::sprintf('SELECT_ENTITY_S_COLON', $attachment->parent_entity_name);
				$selpar_btn_text = '&nbsp;' .
					JText::sprintf('SELECT_ENTITY_S', $attachment->parent_entity_name) . '&nbsp;';
				$selpar_btn_tooltip = JText::sprintf('SELECT_ENTITY_S_TOOLTIP', $attachment->parent_entity_name);
				$selpar_btn_url = $parent->getSelectEntityURL($parent_entity);
				$selpar_parent_title = $attachment->parent_title;
				$selpar_parent_id = $attachment->parent_id;
				}
			}

		$change_parent_url = JURI::base(true) .
			"/index.php?option=com_attachments&amp;task=edit&amp;cid[]=$attachment_id&amp;change=parent";
		if ( $layout ) {
			$change_parent_url .= "&amp;from=$from&amp;tmpl=$layout";
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Set up the view
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'edit'.DS.'view.php');
		$view = new AttachmentsViewEdit();

		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
		AttachmentsHelper::add_view_urls($view, 'update', $parent_id,
										 $parent_type, $attachment_id, $from);

		// Update change URLS to remember if we want to change the parent
		if ( $change_parent ) {
			$view->change_file_url	 .= "&amp;change=parent&amp;new_parent_type=$new_parent_type";
			$view->change_url_url	 .= "&amp;change=parent&amp;new_parent_type=$new_parent_type";
			$view->normal_update_url .= "&amp;change=parent&amp;new_parent_type=$new_parent_type";
			if ( $new_parent_entity != 'default' ) {
				// ??? Needs cleanup
				$view->change_file_url	 .= ":$new_parent_entity";
				$view->change_url_url	 .= ":$new_parent_entity";
				$view->normal_update_url .= ":$new_parent_entity";
				}
			}

		// Add a few necessary things for iframe popups
		if ( $layout ) {
			$view->change_file_url	 .= "&amp;from=$from&amp;tmpl=$layout";
			$view->change_url_url	 .= "&amp;from=$from&amp;tmpl=$layout";
			$view->normal_update_url .= "&amp;from=$from&amp;tmpl=$layout";
			}

		// Suppress the display filename if we are switching from file to url
		$display_name = $attachment->display_name;
		if ( $update AND (($update == 'file') OR ($update != $attachment->uri_type)) ) {
			$display_name = '';
			}

		// Handle iframe popup requests
		$known_froms = array('editor', 'closeme');
		$in_popup = false;
		$save_url = 'index.php';
		if ( in_array( $from, $known_froms ) ) {
			$in_popup = true;
			$document->addScript( $mainframe->getSiteURL() . 'media/system/js/mootools.js' );
			$document->addScript( $mainframe->getSiteURL() . 'media/system/js/modal.js' );
			$document->addScript( $mainframe->getSiteURL() . 'plugins/content/attachments_refresh.js' );
			$save_url = 'index.php?option=com_attachments&amp;task=save';
			}
		$view->assignRef('save_url', $save_url);
		$view->assign('in_popup', $in_popup);

		// Set up view info
		$view->assign(	 'update',			  $update );
		$view->assign(	 'change_parent',	  $change_parent);
		$view->assignRef('new_parent_type',	  $new_parent_type);
		$view->assignRef('new_parent_entity', $new_parent_entity);
		$view->assignRef('change_parent_url', $change_parent_url);
		$view->assignRef('display_name',	  $display_name);
		$view->assignRef('entity_info',		  $entity_info);

		$view->assignRef('lists',			  $lists);
		$view->assignRef('attachment',		  $attachment);
		$view->assignRef('params',			  $params);

		$view->assignRef('from',			  $from);

		// Set up for selecting a new type of parent
		if ( $change_parent ) {
			$view->assignRef('selpar_label',		$selpar_label);
			$view->assignRef('selpar_btn_text',		$selpar_btn_text);
			$view->assignRef('selpar_btn_tooltip',	$selpar_btn_tooltip);
			$view->assignRef('selpar_btn_url',		$selpar_btn_url);
			$view->assignRef('selpar_parent_title', $selpar_parent_title);
			$view->assignRef('selpar_parent_id',	$selpar_parent_id);
			}

		$view->display();
	}



	/**
	 * Save an attachment (from editing)
	 */
	function save()
	{
		global $option;

		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token');

		// Make sure the article ID is valid
		$attachment_id = JRequest::getInt('id');
		$row =& JTable::getInstance('Attachments', 'Table');
		if ( !$row->load($attachment_id) ) {
			$errmsg = JText::sprintf('ERROR_CANNOT_UPDATE_ATTACHMENT_INVALID_ID_N', $id) . ' (ERR 16)';
			JError::raiseError(500, $errmsg);
			}

		// Note the old uri type
		$old_uri_type = $row->uri_type;

		// Get the data from the form
		if (!$row->bind(JRequest::get('post'))) {
			$errmsg = $row->getError() . ' (ERR 17)';
			JError::raiseError(500, $errmsg);
			}

		// See if the parent ID has been changed
		$parent_changed = false;
		$old_parent_id = JRequest::getString('old_parent_id');
		if ( $old_parent_id == '' ) {
			$old_parent_id = null;
			}
		else {
			$old_parent_id = JRequest::getInt('old_parent_id');
			}

		// parent_id===0 is the same as null for articles
		if ( $row->parent_type == 'com_content' AND
			 $row->parent_entity == 'default' AND
			 $row->parent_id == 0 ) {
			$row->parent_id = null;
			}

		// Deal with updating an orphaned attachment
		if ( ($old_parent_id == null) AND is_numeric($row->parent_id) ) {
			$parent_changed = true;
			}

		// Check for normal parent changes
		if ( $old_parent_id AND ( $row->parent_id != $old_parent_id ) ) {
			$parent_changed = true;
			}

		// See if we are updating a file or URL
		$new_uri_type = JRequest::getWord('update');
		if ( $new_uri_type AND !in_array( $new_uri_type, $this->_legal_uri_types ) ) {
			// Make sure only legal values are entered
			$new_uri_type = '';
			}

		// See if the parent type has changed
		$new_parent_type = AttachmentsAdminController::_getCmd2('new_parent_type');
		$new_parent_entity = AttachmentsAdminController::_getCmd2('new_parent_entity');
		$old_parent_type = AttachmentsAdminController::_getCmd2('old_parent_type');
		$old_parent_entity = AttachmentsAdminController::_getCmd2('old_parent_entity');
		if ( ($new_parent_type AND
			  (($new_parent_type != $old_parent_type) OR
			   ($new_parent_entity != $old_parent_entity))) ) {
			$parent_changed = true;
			}

		// If the parent has changed, make sure they have selected the new parent
		if ( $parent_changed AND ( (int)$row->parent_id == -1 ) ) {
			$errmsg = JText::sprintf('ERROR_MUST_SELECT_PARENT');
			echo "<script type=\"text/javascript\"> alert('$errmsg'); window.history.go(-1); </script>\n";
			exit();
			}

		// If the parent has changed, switch the parent, rename files if necessary
		if ( $parent_changed ) {
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');

			if ( ($new_uri_type == 'url') AND ($old_uri_type == 'file') ) {
				// If we are changing parents and converting from file to URL, delete the old file
				jimport('joomla.filesystem.file');

				// Load the attachment so we can get its filename_sys
				$db =& JFactory::getDBO();
				$query = "SELECT filename_sys, id FROM #__attachments WHERE id='".(int)$row->id."' LIMIT 1";
				$db->setQuery($query);
				$filename_sys = $db->loadResult();
				JFile::delete($filename_sys);
				AttachmentsHelper::clean_directory($filename_sys);
				}
			else {
				// Otherwise switch the file/url to the new parent
				if ( $old_parent_id == null ) {
					$old_parent_id = 0;
					// NOTE: When attaching a file to an article during creation,
					//       the article_id (parent_id) is initially null until
					//       the article is saved (at that point the
					//       parent_id/article_id updated).	 If the attachment is
					//       added and creating the article is canceled, the
					//       attachment exists but is orhpaned since it does not
					//       have a parent.  It's article_id is null, but it is
					//       saved in directory as if its article_id is 0:
					//       article/0/file.txt.  Therefore, if the parent has
					//       changed, we pretend the old_parent_id=0 for file
					//       renaming/moving.
					}

				$error_msg = AttachmentsHelper::switch_parent($row, $old_parent_id, $row->parent_id,
															  $new_parent_type, $new_parent_entity);
				if ( $error_msg != '' ) {
					$errmsg = JText::_($error_msg) . ' (ERR 18)';
					$link = 'index.php?option=com_attachments';
					$this->setRedirect($link, $errmsg, 'error');
					return;
					}
				}
			}

		// Update parent type/entity, if needed
		if ( $new_parent_type AND ($new_parent_type != $old_parent_type) ) {
			$row->parent_type = $new_parent_type;
			}
		if ( $new_parent_type AND ($new_parent_entity != $old_parent_entity) ) {
			$row->parent_entity = $new_parent_entity;
			}

		// Get the article/parent handler
		if ( $new_parent_type ) {
			$parent_type = $new_parent_type;
			$parent_entity = $new_parent_entity;
			}
		else {
			$parent_type = AttachmentsAdminController::_getCmd2('parent_type', 'com_content');
			$parent_entity = AttachmentsAdminController::_getCmd2('parent_entity', 'default');
			}
		JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 19)';
			JError::raiseError(500, $errmsg);
			}
		$parent =& $apm->getAttachmentsPlugin($parent_type);

		// Get the title of the article/parent
		$new_parent = JRequest::getBool('new_parent', false);
		$parent->new = $new_parent;
		if ( $new_parent ) {
			$row->parent_id = null;
			$parent->title = '';
			}
		else {
			$parent->loadLanguage();
			$parent->title = $parent->getTitle($row->parent_id, $parent_entity);
			}

		// Double-check to see if the URL changed
		$old_url = JRequest::getString('old_url');
		if ( !$new_uri_type AND $old_url AND $old_url != $row->url ) {
			$new_uri_type = 'url';
			}

		// If this is a URL, get settings
		$verify_url = false;
		$relative_url = false;
		if ( $new_uri_type == 'url' ) {
			// See if we need to verify the URL (if applicable)
			if ( JRequest::getWord('verify_url') == 'verify' ) {
				$verify_url = true;
				}
			// Allow relative URLs?
			if ( JRequest::getWord('relative_url') == 'relative' ) {
				$relative_url = true;
				}
			}

		// Compute the update time
		jimport( 'joomla.utilities.date' );
		$now = new JDate();
		$row->modification_date = $now->toMySQL();

		// Upload new file/url and create/update the attachment
		$msg = null;
		$msgType = 'message';
		if ( $new_uri_type == 'file' ) {

			// Upload a new file
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			$result = AttachmentsHelper::upload_file($row, $parent, $attachment_id, 'update');
			if ( is_object($result) ) {
				$msg = $result->error_msg . ' (ERR 93)';
				$msgType = 'error';
				}
			else {
				$msg = $result;
				}
			// NOTE: store() is not needed if upload_file() is called since it does it
			}

		elseif ( $new_uri_type == 'url' ) {

			// Upload/add the new URL
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			$result = AttachmentsHelper::add_url($row, $parent, $verify_url, $relative_url,
												 $old_uri_type, $attachment_id);

			// NOTE: store() is not needed if add_url() is called since it does it
			if ( is_object($result) ) {
				$msg = $result->error_msg. ' (ERR 94)';
				$msgType = 'error';
				}
			else {
				$msg = $result;
				}
			}

		else {

			// Set up the parent entity to save
			$row->parent_entity = $parent->getEntityname( $row->parent_entity );

			// Save the updated attachment info
			if ( !$row->store() ) {
				$errmsg = $row->getError() . ' (ERR 20)';
				JError::raiseError(500, $errmsg);
				}
			$msg = JText::_('ATTACHMENT_UPDATED');
			}

		switch ( JRequest::getWord('task') )  {
			case 'apply':
				if ( !$msg ) {
					$msg = JText::_('CHANGES_TO_ATTACHMENT_SAVED');
					}
				$link = 'index.php?option=com_attachments&task=edit&cid[]=' . (int)$row->id;
				break;

			case 'save':
			default:
				if ( !$msg ) {
					$msg = JText::_('ATTACHMENT_SAVED');
					}
			$link = 'index.php?option=com_attachments';
			break;
			}

		// If invoked from an iframe popup, close it and refresh the attachments list
		$from = JRequest::getWord('from');
		$known_froms = array('editor', 'closeme');
		if ( in_array( $from, $known_froms ) ) {

			// If there has been a problem, alert the user and redisplay
			if ( $msgType == 'error' ) {
			     	$errmsg = $msg;
				if ( DS == "\\" ) {
				    // Fix filename on Windows system so alert can display it
				    $errmsg = JString::str_ireplace("\\", "\\\\", $errmsg);
				    }
				$errmsg = JString::str_ireplace("'", "\'", $errmsg);
				$errmsg = JString::str_ireplace("<br />", "\\n", $errmsg);
				echo "<script type=\"text/javascript\"> alert('$errmsg');  window.history.go(-1); </script>";
				exit();
				}

			// Can only refresh the old parent
			if ( $parent_changed ) {
				$parent_type = $old_parent_type;
				$parent_entity = $old_parent_entity;
				$pid = $old_parent_id;
				}
			else {
				$pid = (int)$row->parent_id;
				}

			// Close the iframe and refresh the attachments list in the parent window
			$base_url = JURI::base(true);
			echo "<script type=\"text/javascript\">
				   window.parent.document.getElementById('sbox-window').close();
				   parent.refreshAttachments(\"$base_url\",\"$parent_type\",\"$parent_entity\",$pid,\"$from\");
				   </script>";
			exit();
			}


		$this->setRedirect($link, $msg, $msgType . " " . $from);
	}


	/**
	 * Save an new attachment
	 */
	function saveNew()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token');

		// Make sure we have a user
		$user =& JFactory::getUser();
		if ( $user->get('username') == '' ) {
			$errmsg = JText::_('ERROR_MUST_BE_LOGGED_IN_TO_UPLOAD_ATTACHMENT') . ' (ERR 21)';
			JError::raiseError(500, $errmsg);
			}

		// Get the article/parent handler
		$new_parent = JRequest::getBool('new_parent', false);
		$parent_type = AttachmentsAdminController::_getCmd2('parent_type', 'com_content');
		$parent_entity = JRequest::getCmd('parent_entity', 'default');

		// Exit if there is no Attachments plugin to handle this parent_type
		JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 22)';
			JError::raiseError(500, $errmsg);
			}
		$parent =& $apm->getAttachmentsPlugin($parent_type);

		// Make sure we have a valid parent ID
		$parent_id = JRequest::getInt('parent_id', null);

		if ( !$new_parent and (($parent_id === 0) or
							   ($parent_id == null) or
							   !$parent->parentExists($parent_id, $parent_entity)) ) {

			// Warn the user to select an article/parent in a popup
			$parent->loadLanguage();
			$entity_name = JText::_($parent->getEntityName($parent_entity));
			$errmsg = JText::sprintf('ERROR_MUST_SELECT_PARENT_S', $entity_name);
			echo "<script type=\"text/javascript\"> alert('$errmsg'); window.history.go(-1); </script>\n";
			exit();
			}

		// Make sure this user has permission to upload (should never fail with admin?)
		if ( !$parent->userMayAddAttachment($parent_id, $parent_entity, $new_parent) ) {
			$entity_name = JText::_($parent->getEntityName($parent_entity));
			$errmsg = JText::sprintf('ERROR_NO_PERMISSION_TO_UPLOAD_S', $entity_name) . ' (ERR 23)';
			JError::raiseError(500, $errmsg);
			}

		// Set up the new record
		$row =& JTable::getInstance('Attachments', 'Table');
		if (!$row->bind(JRequest::get('post'))) {
			$errmsg = $row->getError() . ' (ERR 24)';
			JError::raiseError(500, $errmsg);
			}
		$row->uploader_id = $user->get('id');
		$row->parent_type = $parent_type;
		$parent->new = $new_parent;

		// Note the parents id and title
		if ( $new_parent ) {
			$row->parent_id = null;
			$parent->title = '';
			}
		else {
			$row->parent_id = $parent_id;
			$parent->loadLanguage();
			$parent->title = $parent->getTitle($parent_id, $parent_entity);
			}

		// Upload the file!
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');

		// Handle 'from' clause
		$from = JRequest::getWord('from');
		// See if we are uploading a file or URL
		$new_uri_type = JRequest::getWord('uri_type');
		if ( $new_uri_type AND !in_array( $new_uri_type, $this->_legal_uri_types ) ) {
			// Make sure only legal values are entered
			$new_uri_type = '';
			}

		// If this is a URL, get settings
		$verify_url = false;
		$relative_url = false;
		if ( $new_uri_type == 'url' ) {
			// See if we need to verify the URL (if applicable)
			if ( JRequest::getWord('verify_url') == 'verify' ) {
				$verify_url = true;
				}
			// Allow relative URLs?
			if ( JRequest::getWord('relative_url') == 'relative' ) {
				$relative_url = true;
				}
			}

		// Upload new file/url and create/update the attachment
		$msg = '';
		$msgType = 'message';
		if ( $new_uri_type == 'file' ) {

			// Upload a new file
			$result = AttachmentsHelper::upload_file($row, $parent, false, 'upload');
			// NOTE: store() is not needed if upload_file() is called since it does it

			if ( is_object($result) ) {
				$msg = $result->error_msg . ' (ERR 95)';
				$msgType = 'error';
				}
			else {
				$msg = $result;
				}
			}

		elseif ( $new_uri_type == 'url' ) {

			// Upload/add the new URL
			$result = AttachmentsHelper::add_url($row, $parent, $verify_url, $relative_url);
			// NOTE: store() is not needed if add_url() is called since it does it

			if ( is_object($result) ) {
				$msg = $result->error_msg . ' (ERR 96)';
				$msgType = 'error';
				}
			else {
				$msg = $result;
				}
			}

		else {

			// Set up the parent entity to save
			$row->parent_entity = $parent->getEntityname( $row->parent_entity );

			// Save the updated attachment info
			if (!$row->store()) {
				$errmsg = $row->getError() . ' (ERR 25)';
				JError::raiseError(500, $errmsg);
				}
			$msg = JText::_('ATTACHMENT_UPDATED');
			}

		// See where to go to next
		global $option;
		switch (JRequest::getWord('task')) {
		case 'applyNew':
			$link = 'index.php?option=com_attachments&task=edit&cid[]=' . (int)$row->id;
			break;

		case 'saveNew':
		default:
			$link = 'index.php?option=com_attachments';
		break;
			}

		// If called from the editor, go back to it
		if ($from == 'editor') {
			$link = 'index.php?option=com_content&task=edit&cid[]=' . $parent_id;
			}

		// If we are supposed to close this iframe, do it now.
		if ( $from == 'closeme' ) {

			// If there has been a problem, alert the user and redisplay
			if ( $msgType == 'error' ) {
			     	$errmsg = $msg;
				if ( DS == "\\" ) {
				    // Fix filename on Windows system so alert can display it
				    $errmsg = JString::str_ireplace("\\", "\\\\", $errmsg);
				    }
				$errmsg = JString::str_ireplace("'", "\'", $errmsg);
				$errmsg = JString::str_ireplace("<br />", "\\n", $errmsg);
				echo "<script type=\"text/javascript\"> alert('$errmsg');  window.history.go(-1); </script>";
				exit();
				}

			// If there is no parent_id, the parent is being created, use the username instead
			if ( $new_parent ) {
				$pid = 0;
				}
			else {
				$pid = (int)$parent_id;
				}

			// Close the iframe and refresh the attachments list in the parent window
			$base_url = JURI::base(true);
			$parent_entity = $parent->getCanonicalEntity($parent_entity);
			echo "<script type=\"text/javascript\">
			   window.parent.document.getElementById('sbox-window').close();
			   parent.refreshAttachments(\"$base_url\",\"$parent_type\",\"$parent_entity\",$pid,\"$from\");
			</script>";
			exit();
			}

		$this->setRedirect($link, $msg, $msgType);
	}


	/**
	 * Cancel
	 */
	function myCancel()
	{
		// See if we have a special 'from' to handle
		$from = JRequest::getWord('from');

		if ( $from == 'editor' ) {

			// NOTE: This branch may not be necessary.	When you cancel from the article editor,
			//       the iframe is closed, but this function is not executed.  Retain in case
			//       we switch to iframes in the back end ???

			// Make sure we have a valid parent ID
			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
			$parent_id = AttachmentsHelper::valid_parent_id($_POST['parent_id']);
			if ( $parent_id == -1 ) {
				$this->execute('cancel'); // Give up
				}

			$link = 'index.php?option=com_content&task=edit&cid[]=' . $parent_id;
			$this->setRedirect($link, JText::_('UPLOAD_CANCELED'));
			}
		else {
			$link = 'index.php?option=com_attachments';
			$this->setRedirect($link);
			}

		$this->execute('cancel');
	}

	/**
	 * Download an attachment
	 */
	function download()
	{
		global $mainframe;
		if ( ! $mainframe->isAdmin() ) {
			$errmsg = JText::_('ERROR_MUST_BE_LOGGED_IN_AS_ADMIN') . ' (ERR 26)';
			JError::raiseError(500, $errmsg);
			}

		// Get the attachment ID
		$id = JRequest::getInt('id');
		if ( !is_numeric($id) ) {
			$errmsg = JText::sprintf('ERROR_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 27)';
			JError::raiseError(500, $errmsg);
			}

		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');

		AttachmentsHelper::download_attachment($id);
	}


	/**
	 * Delete an attachment
	 */
	function remove()
	{
		$cid = JRequest::getVar('cid', array(), '', 'array');
		// ??? Does this need further filtering?

		if (count($cid)) {
			global $option;
			jimport('joomla.filesystem.file');

			$cids = implode(',', $cid);

			$db =& JFactory::getDBO();
			$query = "SELECT * FROM #__attachments WHERE id IN ( $cids )";
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');

			// First delete the actual attachment files
			foreach ($rows as $row) {
				if ( JFile::exists($row->filename_sys) ) {
					JFile::delete($row->filename_sys);
					AttachmentsHelper::clean_directory($row->filename_sys);
					}
				}

			// Delete the entries in the attachments table
			$query = "DELETE FROM #__attachments WHERE id IN ( $cids )";
			$db->setQuery($query);
			if (!$db->query()) {
				$errmsg = $db->getErrorMsg() . ' (ERR 28)';
				JError::raiseError(500, $errmsg);
				}

			// Figure out how to redirect
			$from = JRequest::getWord('from');
			$known_froms = array('frontpage', 'article', 'editor', 'closeme');
			if ( in_array( $from, $known_froms ) ) {

				// Get the parent info from the first attachment
				$parent_id	   = $rows[0]->parent_id;
				$parent_type   = $rows[0]->parent_type;
				$parent_entity = $rows[0]->parent_entity;

				// Get the article/parent handler
				JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
				$apm =& getAttachmentsPluginManager();
				if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
					$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 103)';
					JError::raiseError(500, $errmsg);
					}
				$parent =& $apm->getAttachmentsPlugin($parent_type);

				// Make sure the parent exists
				// NOTE: $parent_id===null means the parent is being created
				if ( $parent_id !== null AND !$parent->parentExists($parent_id, $parent_entity) ) {
					$entity_name = JText::_($parent->getEntityName($parent_entity));
					$errmsg = JText::sprintf('ERROR_CANNOT_DELETE_INVALID_S_ID_N',
											 $entity_name, $parent_id) . ' (ERR 104)';
					JError::raiseError(500, $errmsg);
					}
				$parent_entity = $parent->getCanonicalEntity($parent_entity);

				// If there is no parent_id, the parent is being created, use the username instead
				if ( !$parent_id ) {
					$pid = 0;
					}
				else {
					$pid = (int)$parent_id;
					}

				// Close the iframe and refresh the attachments list in the parent window
				$base_url = JURI::base(true);
				echo "<script type=\"text/javascript\">
				   window.parent.document.getElementById('sbox-window').close();
				   parent.refreshAttachments(\"$base_url\",\"$parent_type\",\"$parent_entity\",$pid,\"$from\");
				   </script>";
				exit();
				}
			}

		$this->setRedirect( 'index.php?option=' . $option);
	}


	/**
	 * Show the warning for deleting an attachment
	 */
	function remove_warning()
	{
		global $option, $mainframe;

		// Meant to be shown in the iframe popup
		$document =&  JFactory::getDocument();

		// Add the regular css file
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
		AttachmentsHelper::addStyleSheet( $mainframe->getSiteURL() . 'plugins/content/attachments.css' );

		// Handle the RTL styling
		$lang =& JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			AttachmentsHelper::addStyleSheet( $mainframe->getSiteURL() . 'plugins/content/attachments_rtl.css' );
			}

		// ??? Not sure if this fix is still necessary
		$document->addStyleDeclaration(
			'div.componentheading { display: none; } * { overflow: hidden; };');

		// Make sure we have a valid attachment ID
		$id = JRequest::getInt('id');
		if ( is_numeric($id) ) {
			$id = (int)$id;
			}
		else {
			$errmsg = JText::sprintf('ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 105)';
			JError::raiseError(500, $errmsg);
			}

		// Get the attachment record
		$attachment =& JTable::getInstance('attachments', 'Table');
		if ( !$attachment->load($id) ) {
			$errmsg = JText::sprintf('ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 106)';
			JError::raiseError(500, $errmsg);
			}

		// Set up the URL
		$from = JRequest::getWord('from');
		$delete_url = "index.php?option=com_attachments&amp;task=remove&amp;cid[]=$id";
		$delete_url .= "&amp;from=$from";
?>
		<div class="deleteWarning">
			 <h1><?php echo JText::_('WARNING'); ?></h1>
			 <h2 id="warning_msg"><?php echo JText::_('REALLY_DELETE_ATTACHMENT'); ?><br />
			 (<?php if ( $attachment->uri_type == 'file' ) {
				 echo " " . $attachment->filename . " ";
				 }
			 else {
				 echo $attachment->url;
				 } ?>)</h2>
		  <form action="<?php echo $delete_url; ?>" name="warning_form" method="post">
			<div align="center">
			   <span class="left">&nbsp;</span>
			   <input type="submit" name="submit" value="<?php echo JText::_('DELETE'); ?>" />
			   <span class="right">
				  <input type="button" name="cancel" value="<?php echo JText::_('CANCEL'); ?>"
						 onClick="window.parent.document.getElementById('sbox-window').close();" />
			   </span>
			</div>
		  </form>
		 </div>
<?php

	}


	/**
	 * Publish attachment(s)
	 *
	 * Applied to any selected attachments
	 */
	function publish()
	{
		global $option;
		$cid = JRequest::getVar('cid', array(), '', 'array');
		if ($this->_task == 'publish') {
			$publish = 1;
			}
		else {
			$publish = 0;
			}
		$attachmentTable =& JTable::getInstance('attachments', 'Table');
		$attachmentTable->publish($cid, $publish);
		$this->setRedirect('index.php?option=' . $option);
	}


	/**
	 * Edit the component parameters
	 */
	function editParams()
	{
		$component = 'com_attachments';

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// load the component's language file
		$lang =&  JFactory::getLanguage();
		$lang->load( $component );

		// Set up the component table
		$table =& JTable::getInstance('component');
		if (!$table->loadByOption( $component )) {
			$errmsg = JText::sprintf('NOT_A_VALID_COMPONENT_S', $component) . ' (ERR 29)';
			JError::raiseWarning(500, $errmsg);
			return false;
			}

		// work out file path for the parameters
		if ($path = JRequest::getString( 'path' )) {
			$path = JPath::clean( JPATH_SITE.DS.$path );
			JPath::check( $path );
			}
		else {
			$option = preg_replace( '#\W#', '', $table->option );
			$path	= JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'config.xml';
			}

		// Read the current parameters
		if (file_exists( $path )) {
			$params = new JParameter( $table->params, $path );
			}
		else {
			$params = new JParameter( $table->params );
			}

		// Deactivate the main menu
		JRequest::setVar( 'hidemainmenu', 1 );

		// Set up the view
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'params'.DS.'view.php');
		$view = new AttachmentsViewParams( );
		$view->assignRef('component', $table);
		$view->assignRef('params', $params);

		$view->display();
	}

	/**
	 * Save the parameters
	 */
	function _saveParams()
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
	function applyParams()
	{
		$msg = AttachmentsAdminController::_saveParams();
		$this->setRedirect('index.php?option=com_attachments&task=editParams', $msg, 'message');
	}

	/**
	 * Save parameters and go back to the main listing display
	 */
	function saveParams()
	{
		$msg = AttachmentsAdminController::_saveParams();
		$this->setRedirect('index.php?option=com_attachments', $msg, 'message');
	}

	/**
	 * Display links for the admin Utility functions
	 */
	function adminUtils()
	{
		// Set up the tooltip behavior
		$opts = Array( 'hideDelay' => 0, 'showDelay' => 0 );
		JHTML::_('behavior.tooltip', '.hasTip', $opts);

		// Set up url/link/tooltip for each command
		$url_top = JURI::base(true) . "/index.php?option=com_attachments&amp;controller=special";
		$closeme = '&amp;tmpl=component&amp;close=1';

		// Set up the array of entries
		$entries = Array();

		// Set up the HTML for the 'Disable MySQL uninstallation' command
		$disable_mysql_uninstall_url =
			"$url_top&amp;task=disable_sql_uninstall" . $closeme;
		$disable_mysql_uninstall_tooltip =
			JText::_('DISABLE_MYSQL_UNINSTALLATION_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $disable_mysql_uninstall_tooltip, null, null, 'DISABLE_MYSQL_UNINSTALLATION' ,
							  $disable_mysql_uninstall_url );

		// Set up the HTML for the 'Regenerate attachment system filenames' command
		$regenerate_system_filenames_url =
			"$url_top&amp;task=regenerate_system_filenames" . $closeme;
		$regenerate_system_filenames_tooltip =
			JText::_('REGENERATE_ATTACHMENT_SYSTEM_FILENAMES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $regenerate_system_filenames_tooltip, null, null, 'REGENERATE_ATTACHMENT_SYSTEM_FILENAMES',
							  $regenerate_system_filenames_url);

		// Set up the HTML for the 'Update attachment system filenames' command
		$update_system_filenames_url =
			"$url_top&amp;task=update_system_filenames" . $closeme;
		$update_system_filenames_tooltip =
			JText::_('UPDATE_ATTACHMENT_SYSTEM_FILENAMES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $update_system_filenames_tooltip, null, null, 'UPDATE_ATTACHMENT_SYSTEM_FILENAMES',
							  $update_system_filenames_url);

		// Set up the HTML for the 'Remove spaces from system filenames' command
		$unspacify_system_filenames_url =
			"$url_top&amp;task=remove_spaces_from_system_filenames" . $closeme;
		$unspacify_system_filenames_tooltip =
			JText::_('DESPACE_ATTACHMENT_SYSTEM_FILENAMES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $unspacify_system_filenames_tooltip, null, null, 'DESPACE_ATTACHMENT_SYSTEM_FILENAMES',
							  $unspacify_system_filenames_url);

		// Set up the HTML for the 'Update attachment file sizes' command
		$update_file_sizes_url =
			"$url_top&amp;task=update_file_sizes" . $closeme;
		$update_file_sizes_tooltip =
			JText::_('UPDATE_ATTACHMENT_FILE_SIZES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $update_file_sizes_tooltip, null, null, 'UPDATE_ATTACHMENT_FILE_SIZES',
							  $update_file_sizes_url);

		// Set up the HTML for the 'Check Files' command
		$check_files_url = "$url_top&amp;task=check_files" . $closeme;
		$check_files_tooltip = JText::_('CHECK_FILES_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $check_files_tooltip, null, null, 'CHECK_FILES', $check_files_url);

		// Set up the HTML for the 'Validate URLs' command
		$validate_urls_url = "$url_top&amp;task=validate_urls" . $closeme;
		$validate_urls_tooltip = JText::_('VALIDATE_URLS_TOOLTIP');
		$entries[] = JHTML::_('tooltip', $validate_urls_tooltip, null, null, 'VALIDATE_URLS', $validate_urls_url);

		// Test ???
		// $utils_test_url = "$url_top&amp;controller=special&amp;task=test" . $closeme;
		// $utils_test_tooltip = 'Test';
		// $entries[] = JHTML::_('tooltip', $utils_test_tooltip, null, null, 'TEST', $utils_test_url);

		// Get the view
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'utils'.DS.'view.php');
		$view = new AttachmentsViewAdminUtils( );
		$view->assignRef('entries', $entries);
		$view->display();
	}

	/**
	 * Give the user a warning (like for deleting an attachment)
	 */
	function warning()
	{
		global $mainframe;
		$document =&  JFactory::getDocument();
		$document->addStyleSheet( $mainframe->getSiteURL() . 'plugins/content/attachments.css',
								  'text/css', null, array() );

		$lang =& JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			$document->addStyleSheet( $mainframe->getSiteURL() . 'plugins/content/attachments_rtl.css',
									  'text/css', null, array() );
			}

		$document->addStyleDeclaration(
			'div.componentheading { display: none; } * { overflow: hidden; };');

		echo '<div class="warning"><h1>' . JText::_('WARNING') . '</h1>';
		echo '<h2 id="warning_msg">';
		echo '<script type=\"text/javascript\">document.write(parent.document.warning_msg);</script>';
		echo '</h2></div>';
	}

	/**
	 * Set up to display the entity selection view
	 *
	 * This allows users to select entities (sections, categories, and other
	 * content items that are supported with Attachments plugins).
	 */
	function selectEntity()
	{
		global $mainframe;

		// Get the parent type
		$parent_type = AttachmentsAdminController::_getCmd2('parent_type');
		if ( !$parent_type ) {
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) .
				$db->getErrorMsg() . ' (ERR 33)';
			JError::raiseError(500, $errmsg);
			}

		// Parse the parent type and entity
		$parent_entity = JRequest::getCmd('parent_entity', 'default');
		if ( strpos($parent_type, ':') ) {
			$parts = explode(':', $parent_type);
			$parent_type = $parts[0];
			$parent_entity = $parts[1];
			}

		// Get the content parent object
		JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
		$apm =& getAttachmentsPluginManager();
		$parent =& $apm->getAttachmentsPlugin($parent_type);
		$parent->loadLanguage();
		$entity_name = JText::_($parent->getEntityName($parent_entity));

		// Get the URL to repost (for filtering)
		$post_url = $parent->getSelectEntityURL($parent_entity);

		// Set up the display lists
		$lists = Array();

		// table ordering
		$filter_order =
			$mainframe->getUserStateFromRequest('com_attachments.selectEntity.filter_order',
												'filter_order', '', 'cmd');
		$filter_order_Dir =
			$mainframe->getUserStateFromRequest('com_attachments.selectEntity.filter_order_Dir',
												'filter_order_Dir','',	'word');
		$lists['order_Dir'] = $filter_order_Dir;
		$lists['order']		= $filter_order;

		// search filter
		$search_filter = $mainframe->getUserStateFromRequest('com_attachments.selectEntity.search',
															 'search', '', 'string' );
		$lists['search'] = $search_filter;

		// Get the list of items to display
		$items = $parent->getEntityItems($parent_entity, $search_filter);

		// Set up the view
		require_once(JPATH_COMPONENT_ADMINISTRATOR.DS.'views'.DS.'entity'.DS.'view.php');
		$view = new AttachmentsViewEntity( );
		$view->assign('from', 'closeme');
		$view->assign('post_url', $post_url);
		$view->assignRef('parent_type', $parent_type);
		$view->assignRef('parent_entity', $parent_entity);
		$view->assignRef('entity_name', $entity_name);
		$view->assignRef('lists', $lists);
		$view->assignRef('items', $items);

		$view->display();
	}


	/**
	 * Return the attachments list as HTML (for use by Ajax)
	 */
	function attachmentsList()
	{
		$parent_id = JRequest::getInt('parent_id', false);
		$parent_type = JRequest::getWord('parent_type', '');
		$parent_entity = JRequest::getWord('parent_entity', 'default');
		$show_links = JRequest::getBool('show_links', true);
		$allow_edit = JRequest::getBool('allow_edit', true);
		$from = JRequest::getWord('from', 'closeme');
		$title = '';

		$response = '';

		if ( ($parent_id === false) OR ($parent_type == '') ) {
			return '';
			}

		require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'controllers'.DS.'attachments.php');
		$controller = new AttachmentsControllerAttachments();
		$response = $controller->display($parent_id, $parent_type, $parent_entity,
										 $title, $show_links, $allow_edit, false, $from);
		echo $response;
	}


	/**
	 * Filter out the request - Like JRequest::getCmd() but allows colons
	 *
	 * @param string $name name of the item to get from the request
	 *
	 * @return the filtered string
	 */
	function _getCmd2($name, $default='')
	{
		$source = JRequest::getString($name, $default);
		return (string) preg_replace( '/[^A-Z0-9_\.:-]/i', '', $source );
	}
}

?>
