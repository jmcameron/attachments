<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * View for the special controller
 * (adapted from administrator/components/com_config/views/component/view.php)
 *
 * @package Attachments
 */
class AttachmentsViewAttachments extends JViewLegacy
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the list view
	 */
	public function display($tpl = null)
	{
		// Fail gracefully if the Attachments plugin framework plugin is disabled
		if ( !JPluginHelper::isEnabled('attachments', 'attachments_plugin_framework') ) {
			echo '<h1>' . JText::_('ATTACH_WARNING_ATTACHMENTS_PLUGIN_FRAMEWORK_DISABLED') . '</h1>';
			return;
			}

		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors) . ' (ERR 175)');
			return false;
		}

		// Get the params
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');
		$this->params = $params;

		// Get the access level names for the display
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__viewlevels');
		$db->setQuery($query);
		$levels = $db->loadObjectList();
		if ( $db->getErrorNum() ) {
			$errmsg = $db->stderr() . ' (ERR 176)';
			JError::raiseError(500, $errmsg);
			}
		$level_name = Array();
		foreach ($levels as $level) {
			// NOTE: We do not translate the access level title
			$level_name[$level->id] = $level->title;
			}
		$this->level_name = $level_name;

		// Construct the special HTML lists
		$lists = Array();

		// Determine types of parents for which attachments should be displayed
		$list_for_parents_default = 'ALL';
		$suppress_obsolete_attachments = $params->get('suppress_obsolete_attachments', false);
		if ( $suppress_obsolete_attachments ) {
			$list_for_parents_default = 'PUBLISHED';
			}
		$app = JFactory::getApplication();
		$list_for_parents =
			$app->getUserStateFromRequest('com_attachments.listAttachments.list_for_parents',
										  'list_for_parents', $list_for_parents_default, 'word');
		$lists['list_for_parents'] = JString::strtolower($list_for_parents);

		// Add the drop-down menu to decide which attachments to show
		$filter_parent_state = $this->state->get('filter.parent_state', 'ALL');
		$filter_parent_state_options = array();
		$filter_parent_state_options[] = JHtml::_('select.option', 'ALL', JText::_( 'ATTACH_ALL_PARENTS' ) );
		$filter_parent_state_options[] = JHtml::_('select.option', 'PUBLISHED', JText::_( 'ATTACH_PUBLISHED_PARENTS' ) );
		$filter_parent_state_options[] = JHtml::_('select.option', 'UNPUBLISHED', JText::_( 'ATTACH_UNPUBLISHED_PARENTS' ) );
		$filter_parent_state_options[] = JHtml::_('select.option', 'ARCHIVED', JText::_( 'ATTACH_ARCHIVED_PARENTS' ) );
		$filter_parent_state_options[] = JHtml::_('select.option', 'TRASHED', JText::_( 'ATTACH_TRASHED_PARENTS' ) );
		$filter_parent_state_options[] = JHtml::_('select.option', 'NONE', JText::_( 'ATTACH_NO_PARENTS' ) );
		$filter_parent_state_tooltip = JText::_('ATTACH_SHOW_FOR_PARENTS_TOOLTIP');
		$lists['filter_parent_state_menu'] =
			JHtml::_('select.genericlist', $filter_parent_state_options, 'filter_parent_state',
					 'class="inputbox" onChange="document.adminForm.submit();" title="' .
					 $filter_parent_state_tooltip . '"', 'value', 'text', $filter_parent_state);
		$this->filter_parent_state = $filter_parent_state;

		// Add the drop-down menu to filter for types of entities
		$filter_entity = $this->state->get('filter.entity', 'ALL');
		$filter_entity_options = array();
		$filter_entity_options[] = JHtml::_('select.option', 'ALL', JText::_( 'ATTACH_ALL_TYPES' ) );
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		$entity_info = $apm->getInstalledEntityInfo();
		foreach ($entity_info as $einfo) {
			$filter_entity_options[] = JHtml::_('select.option', $einfo['id'], $einfo['name_plural']);
			}
		$filter_entity_tooltip = JText::_('ATTACH_FILTER_ENTITY_TOOLTIP');
		$lists['filter_entity_menu'] =
			JHtml::_('select.genericlist', $filter_entity_options, 'filter_entity',
					 'class="inputbox" onChange="this.form.submit();" ' .
					 'title="'.$filter_entity_tooltip .'"', 'value', 'text', $filter_entity);

		$this->lists = $lists;

		// Figure out how many columns
		$num_columns = 10;
		if ( $params->get('user_field_1_name') ) {
			$num_columns++;
			}
		if ( $params->get('user_field_2_name') ) {
			$num_columns++;
			}
		if ( $params->get('user_field_3_name') ) {
			$num_columns++;
			}
		if ( $params->get('secure',false) ) {
			$num_columns++;
			}
		$this->num_columns = $num_columns;

		// get the version number
		require_once(JPATH_SITE.'/components/com_attachments/defines.php');
		$this->version = AttachmentsDefines::$ATTACHMENTS_VERSION;
		$this->project_url = AttachmentsDefines::$PROJECT_URL;

		// Add the style sheets
		JHtml::stylesheet('com_attachments/attachments_admin.css', Array(), true);
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_admin_rtl.css', Array(), true);
			}

		// Set the toolbar
		$this->addToolBar();

		// Display the attachments
		parent::display($tpl);
	}


	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		require_once(JPATH_COMPONENT_ADMINISTRATOR.'/permissions.php');
		$canDo = AttachmentsPermissions::getActions();

		$toolbar = JToolBar::getInstance('toolbar');

		JToolBarHelper::title(JText::_('ATTACH_ATTACHMENTS'), 'attachments.png');

		if ($canDo->get('core.create')) {
			JToolBarHelper::addNew('attachment.add');
			}

		if ($canDo->get('core.edit') OR $canDo->get('core.edit.own') ) {
			JToolBarHelper::editList('attachment.edit');
			}

		if ($canDo->get('core.edit.state') OR $canDo->get('attachments.edit.state.own')) {
			JToolBarHelper::divider();
			JToolBarHelper::publishList('attachments.publish');
			JToolBarHelper::unpublishList('attachments.unpublish');
			}

		if ($canDo->get('core.delete') OR $canDo->get('attachments.delete.own')) {
			JToolBarHelper::divider();
			JToolBarHelper::deleteList('', 'attachments.delete');
			}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::divider();
			JToolBarHelper::custom('params.edit', 'options', 'options', 'JTOOLBAR_OPTIONS', false);

			// Add a button for extra admin commands
			$toolbar->appendButton( 'Popup', 'adminUtils', $alt='ATTACH_UTILITIES',
									'index.php?option=com_attachments&amp;task=adminUtils&amp;tmpl=component',
									$width='800', $height='500' );

			}

		JToolBarHelper::divider();

		// Manually add a help button for the help view
		$url = 'index.php?option=com_attachments&amp;task=help&amp;tmpl=component';
		$help = JText::_('JTOOLBAR_HELP');
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$link = "<button class=\"btn btn-small\" rel=\"help\" href=\"#\" ";
			$link .= "onclick=\"Joomla.popupWindow('$url', 'Help', 800, 650, 1)\"> ";
			$link .= "<i class=\"icon-question-sign\"></i>$help</button>";
		}
		else
		{
			$link = '<a class="toolbar" rel="help" href="#" ';
			$link .= "onclick=\"Joomla.popupWindow('$url', 'Help', 800, 650, 1)\"> ";
			$link .= "<span class=\"icon-32-help\"> </span>$help</a>";
		}
		$toolbar->appendButton('Custom', $link, 'toolbar-help');
	}

}
