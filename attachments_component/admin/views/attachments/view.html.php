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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarFactoryInterface;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\String\StringHelper;

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
class AttachmentsViewAttachments extends HtmlView
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
		if ( !PluginHelper::isEnabled('attachments', 'attachments_plugin_framework') ) {
			echo '<h1>' . Text::_('ATTACH_WARNING_ATTACHMENTS_PLUGIN_FRAMEWORK_DISABLED') . '</h1>';
			return;
			}

		$this->items = $this->get('Items');
		$this->state = $this->get('State');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new Exception(implode("\n", $errors) . ' (ERR 175)', 500);
			return false;
		}

		// Get the params
		$params = ComponentHelper::getParams('com_attachments');
		$this->params = $params;

		// Get the access level names for the display
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('*')->from('#__viewlevels');
		try {
			$db->setQuery($query);
			$levels = $db->loadObjectList();
		} catch (RuntimeException $e) {
			$errmsg = $e->getMessage() . ' (ERR 176)';
			throw new Exception($errmsg, 500);
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
		$app = Factory::getApplication();
		$list_for_parents =
			$app->getUserStateFromRequest('com_attachments.listAttachments.list_for_parents',
										  'list_for_parents', $list_for_parents_default, 'word');
		$lists['list_for_parents'] = StringHelper::strtolower($list_for_parents);

		// Add the drop-down menu to decide which attachments to show
		$filter_parent_state = $this->state->get('filter.parent_state', 'ALL');
		$filter_parent_state_options = array();
		$filter_parent_state_options[] = HTMLHelper::_('select.option', 'ALL', Text::_( 'ATTACH_ALL_PARENTS' ) );
		$filter_parent_state_options[] = HTMLHelper::_('select.option', 'PUBLISHED', Text::_( 'ATTACH_PUBLISHED_PARENTS' ) );
		$filter_parent_state_options[] = HTMLHelper::_('select.option', 'UNPUBLISHED', Text::_( 'ATTACH_UNPUBLISHED_PARENTS' ) );
		$filter_parent_state_options[] = HTMLHelper::_('select.option', 'ARCHIVED', Text::_( 'ATTACH_ARCHIVED_PARENTS' ) );
		$filter_parent_state_options[] = HTMLHelper::_('select.option', 'TRASHED', Text::_( 'ATTACH_TRASHED_PARENTS' ) );
		$filter_parent_state_options[] = HTMLHelper::_('select.option', 'NONE', Text::_( 'ATTACH_NO_PARENTS' ) );
		$filter_parent_state_tooltip = Text::_('ATTACH_SHOW_FOR_PARENTS_TOOLTIP');
		$lists['filter_parent_state_menu'] =
			HTMLHelper::_('select.genericlist', $filter_parent_state_options, 'filter_parent_state',
					 'class="inputbox" onChange="document.adminForm.submit();" title="' .
					 $filter_parent_state_tooltip . '"', 'value', 'text', $filter_parent_state);
		$this->filter_parent_state = $filter_parent_state;

		// Add the drop-down menu to filter for types of entities
		$filter_entity = $this->state->get('filter.entity', 'ALL');
		$filter_entity_options = array();
		$filter_entity_options[] = HTMLHelper::_('select.option', 'ALL', Text::_( 'ATTACH_ALL_TYPES' ) );
		PluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		$entity_info = $apm->getInstalledEntityInfo();
		foreach ($entity_info as $einfo) {
			$filter_entity_options[] = HTMLHelper::_('select.option', $einfo['id'], $einfo['name_plural']);
			}
		$filter_entity_tooltip = Text::_('ATTACH_FILTER_ENTITY_TOOLTIP');
		$lists['filter_entity_menu'] =
			HTMLHelper::_('select.genericlist', $filter_entity_options, 'filter_entity',
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
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin.css');
		$lang = $app->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin_rtl.css');
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

		$toolbar = Toolbar::getInstance('toolbar');

		ToolbarHelper::title(Text::_('ATTACH_ATTACHMENTS'), 'attachments.png');

		if ($canDo->get('core.create')) {
			ToolBarHelper::addNew('attachment.add');
			}

		if ($canDo->get('core.edit') OR $canDo->get('core.edit.own') ) {
			ToolBarHelper::editList('attachment.edit');
			}

		if ($canDo->get('core.edit.state') OR $canDo->get('attachments.edit.state.own')) {
			ToolBarHelper::divider();
			ToolBarHelper::publishList('attachments.publish');
			ToolBarHelper::unpublishList('attachments.unpublish');
			}

		if ($canDo->get('core.delete') OR $canDo->get('attachments.delete.own')) {
			ToolBarHelper::divider();
			ToolBarHelper::deleteList('', 'attachments.delete');
			}

		if ($canDo->get('core.admin')) {
			ToolBarHelper::divider();
			ToolBarHelper::custom('params.edit', 'options', 'options', 'JTOOLBAR_OPTIONS', false);

			$icon_name = 'adminUtils';
			if (version_compare(JVERSION, '3.0', 'ge')) {
				$icon_name = 'wrench';
				}

			// Add a button for extra admin commands
			$toolbar->appendButton('Popup', $icon_name, 'ATTACH_UTILITIES',
								   'index.php?option=com_attachments&amp;task=adminUtils&amp;tmpl=component',
								   800, 500);
			}

		ToolBarHelper::divider();

		// Manually add a help button for the help view
		$url = 'index.php?option=com_attachments&amp;task=help&amp;tmpl=component';
		$help = ' ' . Text::_('JTOOLBAR_HELP') . ' ';
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
