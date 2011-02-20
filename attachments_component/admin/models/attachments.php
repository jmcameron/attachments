<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2010-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/**
 * Attachments Component Attachments Model
 *
 * @package Attachments
 * @subpackage Attachments_Component
 */
class AttachmentsadminModelAttachments extends JModel
{
	/**
	 * Attachments data
	 *
	 * @var object
	 */
	var $_data = null;

	/**
	 * Items total
	 *
	 * @var integer
	 */
	var $_total = null;

	/**
	 * Pagination object
	 *
	 * @var object
	 */
	var $_pagination = null;

	/**
	 * The attachments plugin manager object
	 */
	var $_apm;


	/**
	 * Constructor
	 *
	 * @since 1.0
	 */
	function __construct()
	{
		parent::__construct();

		global $option;
		$db =& $this->getDBO();

		// // Get the component parameters
		// ??? Use this later when we filter on state
		// jimport('joomla.application.component.helper');
		// $params =& JComponentHelper::getParams('com_attachments');
		// 
		// // Get the desired state
		// $list_for_parents_default = 'ALL';
		// $suppress_obsolete_attachments = $params->get('suppress_obsolete_attachments', false);
		// if ( $suppress_obsolete_attachments ) {
		//	  $list_for_parents_default = 'PUBLISHED';
		// }
		// $state = $app->getUserStateFromRequest( 'com_attachments.listAttachments.list_for_parents',
		// 				   					       'list_for_parents', $list_for_parents_default, 'word' );

		// Get the limits
		$app = JFactory::getApplication();
		$limit = $app->getUserStateFromRequest( 'com_attachments.listAttachments.limit',
													  'limit', $app->getCfg('list_limit'), 'int');
		$limitstart = $app->getUserStateFromRequest('com_attachments.listAttachments.limitstart',
														  'limitstart', 0, 'int');
		// In case limit has been changed, adjust it
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		$this->setState('limit', $limit);
		$this->setState('limitstart', $limitstart);

		// Get the ordering information
		$order	   = $app->getUserStateFromRequest('com_attachments.selectEntity.filter_order',
												   'filter_order', '', 'cmd');
		$order_Dir = $app->getUserStateFromRequest('com_attachments.selectEntity.filter_order_Dir',
												   'filter_order_Dir', '', 'word');
		$this->setState('filter_order', $order);
		$this->setState('filter_order_Dir', $order_Dir);

		// Get the entity filter info
		$filter_entity = $app->getUserStateFromRequest('com_attachments.listAttachments.filter_entity',
															 'filter_entity', 'ALL', 'string' );
		$filter_entity = $db->getEscaped( trim(JString::strtoupper($filter_entity)) );
		$this->setState('filter_entity', $filter_entity);

		// Get the search string
		$search = $app->getUserStateFromRequest('com_attachments.listAttachments.search',
													  'search', '', 'string' );
		$search = $db->getEscaped( trim(JString::strtolower( $search ) ) );
		$this->setState('search', $search);
	}

	/**
	 * Method to get attachments data
	 *
	 * @access public
	 * @return object
	 */
	function &getData()
	{
		// Load the attachments if they do not already exist
		if (empty($this->_data))
		{
			$query = $this->_buildQuery();
			$db =& $this->getDBO();

			// Get the list of attachments
			$this->_data = $this->_getList($query, $this->getState('limitstart'), $this->getState('limit'));
			if ($db->getErrorNum()) {
				$errmsg = JText::_('ERROR_GETTING_LIST_OF_ATTACHMENTS') . $db->getErrorMsg() . ' (ERR 12)';
				JError::raiseError(500, $errmsg);
				}

			$apm =& $this->getAttachmentsPluginManager();

			// Go through the attachments and add some extra information
			$count = count($this->_data);

			for($i = 0; $i < $count; $i++)
			{
				$row =& $this->_data[$i];

				// Fix the relative URLs
				if ( $row->uri_type == 'url' ) {
					$app = JFactory::getApplication();
					if ( strpos($row->url, '://') === false ) {
						$row->url = $app->getSiteURL() . '/' . $row->url;
						}
					}

				// Get the content parent object
				$row->parent_exists = true;
				$parent =& $apm->getAttachmentsPlugin($row->parent_type);
				$entity = $row->parent_entity;

				if ( $parent ) {

					// Handle the normal case
					$entity = $parent->getCanonicalEntity( $entity );
					$row->parent_entity = $entity;
					$parent->loadLanguage();
					$row->parent_entity_type = JText::_($parent->getEntityName($entity));
					$title = $parent->getTitle($row->parent_id, $entity);
					if ( $title ) {
						$row->parent_title = $title;
						}
					else {
						$row->parent_title = JText::sprintf('NO_PARENT_S', $row->parent_entity_type);
						}
					$row->parent_exists = $parent->parentExists($row->parent_id, $row->parent_entity);
					$row->parent_published = $parent->isParentPublished($row->parent_id, $entity);
					$row->parent_archived = $parent->isParentArchived($row->parent_id, $entity);
					$row->parent_url =
						JFilterOutput::ampReplace( $parent->getEntityViewURL($row->parent_id, $entity) );
					}
				else {
					// Handle case where there is no parent handler
					// (eg, deleted component)
					$row->parent_exists = false;
					$row->parent_entity_type = $entity;
					$row->parent_title = JText::_('UNKNOWN');
					$row->parent_published = false;
					$row->parent_archived = false;
					$row->parent_url = '';
					}
				}
			}

		return $this->_data;
	}

	/**
	 * Method to get the total number of the Items
	 *
	 * @access public
	 * @return integer
	 */
	function getTotal()
	{
		// Lets load the Items if it doesn't already exist
		if (empty($this->_total))
		{
			$query = $this->_buildQuery();
			$this->_total = $this->_getListCount($query);
		}

		return $this->_total;
	}

	/**
	 * Method to get a pagination object for the Items
	 */
	function &getPagination()
	{
		// Lets load the Items if it doesn't already exist
		if (empty($this->_pagination))
		{
			jimport('joomla.html.pagination');

			// Get the pagination info
			$total = $this->getTotal();
			$limitstart = $this->getState('limitstart');
			$limit = $this->getState('limit');

			// Create the pagination object
			$this->_pagination = new JPagination( $total, $limitstart, $limit );
		}

		return $this->_pagination;
	}

	/**
	 * Method to get/build the attachments plugin manager
	 */
	function &getAttachmentsPluginManager()
	{
		if (empty($this->_apm))
		{
			// Get the attachments plugin manager object
			JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
			$this->_apm =& getAttachmentsPluginManager();
		}

		return $this->_apm;
	}

	/**
	 * Method to build the query for the Items
	 *
	 * @access private
	 * @return string
	 * @since 1.0
	 */
	function _buildQuery()
	{
		// Get the WHERE and ORDER BY clauses for the query
		$where		= $this->_buildContentWhere();
		$orderby	= $this->_buildContentOrderBy();

		// Create the query
		$query = "SELECT a.*, a.id as id, u.name as uploader_name "
			. "FROM #__attachments as a "
			. "LEFT JOIN #__users AS u ON u.id = a.uploader_id "
			. $where
			. $orderby
			;

		return $query;
	}


	/**
	 * Method to build the orderby clause of the query for the Items
	 *
	 * @access private
	 * @return string
	 * @since 1.0
	 */
	function _buildContentOrderBy()
	{
		global $option;

		// Get the ordering information
		$order	   = $this->getState('filter_order');
		$order_Dir = $this->getState('filter_order_Dir');

		// Construct the ORDER BY clause
		$order_by = " ORDER by a.parent_type, a.parent_entity, a.parent_id";
		if ( $order ) {
			$order_by = " ORDER BY $order $order_Dir, a.parent_entity, a.parent_id";
			}
		
		return $order_by;
	}

	/**
	 * Method to build the where clause of the query for the Items
	 *
	 * @access private
	 * @return string
	 * @since 1.0
	 */
	function _buildContentWhere()
	{
		global $option;

		$where = Array();

		// Set up the search
		$search = $this->getState('search');

		if ( $search ) {
			if ( ($search != '') AND is_numeric($search) ) {
				$where[] = 'a.id = ' . (int) $search . '';
				}
			else {
				$db =& $this->getDBO();
				$where[] = 'LOWER( a.filename ) LIKE ' .
					$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false ) .
					' OR LOWER( a.description ) LIKE ' .
					$db->Quote( '%'.$db->getEscaped( $search, true ).'%', false );
				}
			}

		// Get the entity filter info
		$filter_entity = $this->getState('filter_entity');
		if ( $filter_entity != 'ALL' ) {
			$where[] = "a.parent_entity = '$filter_entity'";
			}

		// Construct the WHERE clause
		$where = (count($where) ? ' WHERE '.implode(' AND ', $where) : '');

		return $where;
	}

}

?>