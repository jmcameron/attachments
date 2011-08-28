<?php
/**
 * Attachments component attachments model
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import the Joomla modellist library
jimport('joomla.application.component.modellist');

/**
 * Attachments Model
 *
 * @package Attachments
 */
class AttachmentsModelAttachments extends JModelList
{
	/**
	 * Constructor
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id',
				'a.state',
				'a.access',
				'a.filename',
				'a.description',
				'a.user_field_1',
				'a.user_field_2',
				'a.user_field_3',
				'a.file_type',
				'a.file_size',
				'creator_name',
				'modifier_name',
				'u.name',
				'a.created',
				'a.modified',
				'a.download_count'
			);
		}

		parent::__construct($config);
	}



	/**
	 * Method to build an SQL query to load the attachments data.
	 *
	 * @return	JDatabaseQuery	 An SQL query
	 */
	protected function getListQuery()
	{

		// Create a new query object.
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);

		$query->select('a.*, a.id as id');
		$query->from('#__attachments as a');

		$query->select('u1.name as creator_name');
		$query->leftJoin('#__users AS u1 ON u1.id = a.created_by');

		$query->select('u2.name as modifier_name');
		$query->leftJoin('#__users AS u2 ON u2.id = a.modified_by');

		// Add the where clause
		$where = $this->_buildContentWhere($query);
		if ($where) {
			$query->where($where);
			}

		// Add the order-by clause
		$order_by = $this->_buildContentOrderBy();
		if ($order_by) {
			$query->order($db->getEscaped($order_by));
			}

		return $query;
	}



	/**
	 * Method to build the where clause of the query for the Items
	 *
	 * @access private
	 * @return string
	 * @since 1.0
	 */
	private function _buildContentWhere($query)
	{
		$where = Array();

		// Set up the search
		$search = $this->getState('filter.search');

		if ( $search ) {
			if ( ($search != '') && is_numeric($search) ) {
				$where[] = 'a.id = ' . (int) $search . '';
				}
			else {
				$db = $this->getDBO();
				$where[] = '(LOWER( a.filename ) LIKE ' .
					$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false ) .
					' OR LOWER( a.description ) LIKE ' .
					$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false ) .
					' OR LOWER( a.display_name ) LIKE ' .
					$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false ) . ')';
				}
			}

		// Get the entity filter info
		$filter_entity = $this->getState('filter.entity');
		if ( $filter_entity != 'ALL' ) {
			$where[] = "a.parent_entity = '$filter_entity'";
			}

		// Get the parent_state filter
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Get the desired state
		$filter_parent_state_default = 'ALL';
		$suppress_obsolete_attachments = $params->get('suppress_obsolete_attachments', false);
		if ( $suppress_obsolete_attachments ) {
			$filter_parent_state_default = 'PUBLISHED';
			}
		$filter_parent_state = $this->getState('filter.parent_state', $filter_parent_state_default);
		if ( $filter_parent_state != 'ALL' ) {

			$fps_wheres = Array();

			// Get the contributions for all the known content types
			JPluginHelper::importPlugin('attachments');
			$apm = getAttachmentsPluginManager();
			$known_parent_types = $apm->getInstalledParentTypes();
			foreach ($known_parent_types as $parent_type) {
				$parent = $apm->getAttachmentsPlugin($parent_type);
				$pwheres = $parent->getParentPublishedFilter($filter_parent_state, $filter_entity);
				foreach ($pwheres as $pw) {
					$fps_wheres[] = $pw;
					}
				}

			if ( $filter_parent_state == 'NONE' ) {
				$basic = '';
				$fps_wheres = '( (a.parent_id = 0) OR (a.parent_id IS NULL) ' .
					(count($fps_wheres) ?
					 ' OR (' . implode(' AND ', $fps_wheres) . ')' : '') . ')';
				}
			else {
				$fps_wheres = (count($fps_wheres) ? '(' . implode(' OR ', $fps_wheres) . ')' : '');
				}

			// Copy the new where clauses into our main list
			if ( $fps_wheres) {
				$where[] = $fps_wheres;
				}
			}

		// Make sure the user can only see the attachments they may access
		$user	= JFactory::getUser();
		$user_levels = implode(',', array_unique($user->authorisedLevels()));
		$where[] = 'a.access in ('.$user_levels.')';

		// Construct the WHERE clause
		$where = (count($where) ? implode(' AND ', $where) : '');

		return $where;
	}


	/**
	 * Method to build the orderby clause of the query for the Items
	 *
	 * @access private
	 * @return string
	 * @since 1.0
	 */
	private function _buildContentOrderBy()
	{
		// Get the ordering information
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');

		// Construct the ORDER BY clause
		$order_by = "a.parent_type, a.parent_entity, a.parent_id";
		if ( $orderCol ) {
			$order_by = "$orderCol $orderDirn, a.parent_entity, a.parent_id";
			}

		return $order_by;
	}


	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$entity = $this->getUserStateFromRequest($this->context.'.filter.entity', 'filter_entity', 'ALL');
		$this->setState('filter.entity', $entity);

		$parent_state = $this->getUserStateFromRequest($this->context.'.filter.parent_state', 'filter_parent_state', 'ALL');
		$this->setState('filter.parent_state', $parent_state);

		$state = $this->getUserStateFromRequest($this->context.'.filter.state', 'filter_state', '', 'string');
		$this->setState('filter.state', $state);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_attachments');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('', 'asc');
	}


	/**
	 * Method to get an array of data items.
	 *
	 * @return	mixed	An array of data items on success, false on failure.
	 * @since	1.6
	 */
	public function getItems()
	{
		$items = parent::getItems();

		// Update the attachments with information about thier parents
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		foreach ($items as $item) {
			$parent_id = $item->parent_id;
			$parent_type = $item->parent_type;
			$parent_entity = $item->parent_entity;
			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				$errmsg = JText::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 70)';
				JError::raiseError(500, $errmsg);
				}
			$parent = $apm->getAttachmentsPlugin($parent_type);

			if ( $parent ) {

				// Handle the normal case
				$item->parent_entity_type = JText::_('ATTACH_' . $parent_entity);
				$title = $parent->getTitle($parent_id, $parent_entity);
				$item->parent_exists = $parent->parentExists($parent_id, $parent_entity);
				if ( $item->parent_exists && $title ) {
					$item->parent_title = $title;
					$item->parent_url =
						JFilterOutput::ampReplace( $parent->getEntityViewURL($parent_id, $parent_entity) );
					}
				else {
					$item->parent_title = JText::sprintf('ATTACH_NO_PARENT_S', $item->parent_entity_type);
					$item->parent_url = '';
					}
				}

			else {
				// Handle pathalogical case where there is no parent handler
				// (eg, deleted component)
				$item->parent_exists = false;
				$item->parent_entity_type = $parent_entity;
				$item->parent_title = JText::_('ATTACH_UNKNOWN');
				$item->parent_published = false;
				$item->parent_archived = false;
				$item->parent_url = '';
				}
			}

		// Return from the cache
		return $items;
	}


	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param		type	The table type to instantiate
	 * @param		string	A prefix for the table class name. Optional.
	 * @param		array	Configuration array for model. Optional.
	 * @return		JTable	A database object
	 * @since		1.6
	 */
	public function getTable($type = 'Attachment', $prefix = 'AttachmentsTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}


	/**
	 * Publish attachment(s)
	 *
	 * Applied to any selected attachments
	 */
	public function publish($cid, $value)
	{
		// Get the ids and make sure they are integers
		$attachmentTable = $this->getTable();
		$attachmentTable = JTable::getInstance('Attachment', 'AttachmentsTable');

		return $attachmentTable->publish($cid, $value);
	}

}
