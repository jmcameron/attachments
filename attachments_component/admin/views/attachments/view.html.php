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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * View for the special controller
 * (adapted from administrator/components/com_config/views/component/view.php) 
 *
 * @package Attachments
 */
class AttachmentsViewAttachments extends JView
{
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the list view
	 */
	function display($tpl = null)
	{
		$this->pagination = $this->get('Pagination');
		$this->state = $this->get('State');
		$this->items = $this->get('Items');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Get the params
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');
		$this->params = $params;

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
		$filter_parent_state_options[] = JHTML::_('select.option', 'ALL', JText::_( 'ALL_PARENTS' ) );
		$filter_parent_state_options[] = JHTML::_('select.option', 'PUBLISHED', JText::_( 'PUBLISHED_PARENTS' ) );
		$filter_parent_state_options[] = JHTML::_('select.option', 'UNPUBLISHED', JText::_( 'UNPUBLISHED_PARENTS' ) );
		$filter_parent_state_options[] = JHTML::_('select.option', 'ARCHIVED', JText::_( 'ARCHIVED_PARENTS' ) );
		$filter_parent_state_options[] = JHTML::_('select.option', 'TRASHED', JText::_( 'TRASHED_PARENTS' ) );
		$filter_parent_state_options[] = JHTML::_('select.option', 'NONE', JText::_( 'NO_PARENTS' ) );
		$filter_parent_state_tooltip = JText::_('SHOW_FOR_PARENTS_TOOLTIP');
		$lists['filter_parent_state_menu'] =
			JHTML::_('select.genericlist', $filter_parent_state_options, 'filter_parent_state',
					 'class="inputbox" onChange="document.adminForm.submit();" title="' .
					 $filter_parent_state_tooltip . '"', 'value', 'text', $filter_parent_state);
		$this->filter_parent_state = $filter_parent_state;

		// Add the drop-down menu to filter for types of entities
		$filter_entity = $this->state->get('filter.entity', 'ALL');
		$filter_entity_options = array();
		$filter_entity_options[] = JHTML::_('select.option', 'ALL', JText::_( 'ALL_TYPES' ) );
		JPluginHelper::importPlugin('attachments');
		$apm =& getAttachmentsPluginManager();
		$entity_info =& $apm->getInstalledEntityInfo();
		foreach ($entity_info as $einfo) {
			$filter_entity_options[] = JHTML::_('select.option', $einfo['id'], $einfo['name_plural']);
			}
		$filter_entity_tooltip = JText::_('FILTER_ENTITY_TOOLTIP');
		$lists['filter_entity_menu'] =
			JHTML::_('select.genericlist', $filter_entity_options, 'filter_entity',
					 'class="inputbox" onChange="this.form.submit();" ' .
					 'title="'.$filter_entity_tooltip .'"', 'value', 'text', $filter_entity);

		$this->lists = $lists;

		// Figure out how many columns
		$num_columns = 9;
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
		require_once(JPATH_COMPONENT_SITE.DS.'defines.php');
		$this->version = AttachmentsDefines::$ATTACHMENTS_VERSION;
		$this->project_url = AttachmentsDefines::$PROJECT_URL;

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
		JToolBarHelper::title(JText::_('ATTACHMENTS'), 'attachments.png');
		JToolBarHelper::addNewX('attachment.add');
		JToolBarHelper::editListX('attachment.edit');

		JToolBarHelper::divider();

		JToolBarHelper::publishList('attachments.publish');
		JToolBarHelper::unpublishList('attachments.unpublish');

		JToolBarHelper::divider();

		JToolBarHelper::deleteListX('', 'attachments.delete');

		JToolBarHelper::divider();
		JToolBarHelper::custom('params.edit', 'options', 'options', 'JTOOLBAR_OPTIONS', false);

		// Add a button for extra admin commands
		$bar =&  JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Popup', 'adminUtils', $alt='UTILITIES',
							'index.php?option=com_attachments&amp;task=adminUtils&amp;tmpl=component',	
							$width='600', $height='400' );

		JToolBarHelper::divider();
		JToolBarHelper::help('help', true);
	}

}
