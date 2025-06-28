<?php
/**
 * Attachments component attachments model
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\Model;

use JMCameron\Plugin\AttachmentsPluginFramework\AttachmentsPluginManager;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\ParameterType;
use Joomla\Filter\OutputFilter;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');



/**
 * Attachments Model
 *
 * @package Attachments
 */
class AttachmentsModel extends ListModel
{
	/**
	 * Constructor
	 *
	 * @param	array	An optional associative array of configuration settings.
	 * @see		ListModel
	 * @since	1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = [
				'id',
				'a.state', 'state',
				'a.access',
				'a.filename',
				'a.description',
				'a.user_field_1',
				'a.user_field_2',
				'a.user_field_3',
				'a.file_type',
				'a.file_size',
				'a.created',
				'a.modified',
				'a.download_count',
				'a.created_by',
				'parent_state',
				'a.parent_id',
				'a.parent_type',
				'a.parent_entity', 'parent_entity',
				'a.display_name',
			];
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
		/** @var \Joomla\Database\DatabaseDriver $db */
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				$db->qn(['a.id',
						'a.filename',
						'a.filename_sys',
						'a.file_type',
						'a.file_size',
						'a.url',
						'a.uri_type',
						'a.url_valid',
						'a.url_relative',
						'a.url_verify',
						'a.display_name',
						'a.description',
						'a.icon_filename',
						'a.access',
						'a.state',
						'a.user_field_1',
						'a.user_field_2',
						'a.user_field_3',
						'a.parent_type',
						'a.parent_entity',
						'a.parent_id',
						'a.created',
						'a.created_by',
						'a.modified',
						'a.modified_by',
						'a.download_count',
					]
				)
			)
		)
			->select(
				[
					$db->qn('uc.name', 'editor_name'),
					$db->qn('ag.title', 'access_level'),
					$db->qn('ua.name', 'creator_name'),
				]
			)
			->from($db->qn('#__attachments', 'a'))
			->join('LEFT', $db->qn('#__users', 'uc'), $db->qn('uc.id') . ' = ' . $db->qn('a.modified_by'))
			->join('LEFT', $db->qn('#__viewlevels', 'ag'), $db->qn('ag.id') . ' = ' . $db->qn('a.access'))
			->join('LEFT', $db->qn('#__users', 'ua'), $db->qn('ua.id') . ' = ' . $db->qn('a.created_by'));

		$filterEntityParts = [];

		// Filter by published state
		$state = (string) $this->getState('filter.state');

		if ((int) $this->getState('filter.listforparent', 0)) {
			$parentId = $this->getState('filter.parent_id');
			$parentType = $this->getState('filter.parent_type');
			$parentEntity = $this->getState('filter.parent_entity');
			$query
				->where($db->qn('a.parent_id') . ' = :parent_id', 'AND')
				->where($db->qn('a.parent_type') . ' = :parent_type', 'AND')
				->where($db->qn('a.parent_entity') . ' = :parent_entity')
				->bind(':parent_id', $parentId, ParameterType::INTEGER)
				->bind(':parent_type', $parentType, ParameterType::STRING)
				->bind(':parent_entity', $parentEntity, ParameterType::STRING);
		}
		else {
			if (is_numeric($state))	{
				$state = (int) $state;
				$query
					->where($db->qn('a.state') . ' = :state')
					->bind(':state', $state, ParameterType::INTEGER);
			}
			elseif ($state === '') {
				$query->whereIn($db->qn('a.state'), [0, 1]);
			}

			// Filter by search in title.
			$search = $this->getState('filter.search');

			$params = ComponentHelper::getComponent('com_attachments')->getParams();

			if (!empty($search)) {
				if (stripos($search, 'id:') === 0) {
					$search = (int) substr($search, 3);
					$query
						->where($db->qn('a.id') . ' = :searchId')
						->bind(':searchId', $search, ParameterType::INTEGER);
				}
				else {
					$queryStr =
						'('
						. 'LOWER (a.filename) LIKE ' . $db->quote('%' . $db->escape($search, true) . '%', false) . ' OR '
						. 'LOWER (a.description) LIKE ' . $db->quote('%' . $db->escape($search, true) . '%', false) . ' OR '
						. 'LOWER (a.display_name) LIKE ' . $db->quote('%' . $db->escape($search, true) . '%', false);
					if (!empty($params->get('user_field_1_name', ''))) {
						$queryStr .= 'OR LOWER (a.user_field_1) LIKE ' . $db->quote('%' . $db->escape($search, true) . '%', false);
					}
					if (!empty($params->get('user_field_2_name', ''))) {
						$queryStr .= 'OR LOWER (a.user_field_2) LIKE ' . $db->quote('%' . $db->escape($search, true) . '%', false);
					}
					if (!empty($params->get('user_field_3_name', ''))) {
						$queryStr .= 'OR LOWER (a.user_field_3) LIKE ' . $db->quote('%' . $db->escape($search, true) . '%', false);
					}
					$queryStr .= ')';

					$query
						->where(
							$queryStr
						);
				}
			}

			$filterEntity = $this->getState('filter.parent_entity');

			if (($filterEntity != '') && ($filterEntity != 'ALL')) {
				$filterEntityParts = explode('.', $filterEntity);
				$parentType = $filterEntityParts[0];
				$parentEntity = $filterEntityParts[1];

				$query
					->where($db->qn('a.parent_type') . ' = ' . $db->quote($parentType))
					->where($db->qn('a.parent_entity') . ' = ' . $db->quote($parentEntity));
			}
		}

		$filterParentStateDefault = '';

		$suppressObsoleteAttachments = $params->get('suppress_obsolete_attachments', false);

		if ($suppressObsoleteAttachments)
		{
			$filterParentStateDefault = 'PUBLISHED';
		}

		$filterParentState = $this->getState('filter.parent_state', $filterParentStateDefault);

		if (!empty($filterParentState)) {
			$countFilterEntityParts = count($filterEntityParts);

			if (($filterParentState != '') && ($filterParentState != 'ALL') && ($countFilterEntityParts == 2 || $countFilterEntityParts == 0)) {
				$fpsWheres = [];

				// Get the contributions for all the known content types
				PluginHelper::importPlugin('attachments');
				$apm = AttachmentsPluginManager::getAttachmentsPluginManager();
				$knownParentTypes = $apm->getInstalledParentTypes();

				$pwheres = [];

				foreach ($knownParentTypes as $parentType) {
					$parent = $apm->getAttachmentsPlugin($parentType);

					if ($countFilterEntityParts == 2) {
						$pwheres = $parent->getParentPublishedFilter($filterParentState, $filterEntityParts[1]);
					}
					elseif ($countFilterEntityParts == 0) {
						$pwheres = $parent->getParentPublishedFilter($filterParentState, 'ALL');
					}

					foreach ($pwheres as $pw) {
						$fpsWheres[] = $pw;
					}
				}

				if ($filterParentState == 'NONE') {
					$fpsWheres = '( (a.parent_id = 0) OR (a.parent_id IS NULL) ' .
						(count($fpsWheres) ? ' OR (' . implode(' AND ', $fpsWheres) . ')' : '') . ')';
				}
				else {
					$fpsWheres = (count($fpsWheres) ? '(' . implode(' OR ', $fpsWheres) . ')' : '');
				}

				// Copy the new where clauses into our main list
				if ($fpsWheres) {
					$where[] = $fpsWheres;
					$query->where($where);
				}
			}
		}

		$user = Factory::getApplication()->getIdentity();

		if (!$user->authorise('core.admin')) {
			$userLevels = array_unique($user->getAuthorisedViewLevels());
			$query->whereIn('a.access', $userLevels);
		}

		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', 'a.id');
		$orderDirn = $this->state->get('list.direction', 'DESC');

		$orderBy = "a.parent_type, a.parent_entity, a.parent_id";

		if ($orderCol) {
			$orderBy = "$orderCol $orderDirn, a.parent_entity, a.parent_id";
			}
		$ordering = $db->escape($orderBy) . ' ' . $db->escape($orderDirn);

		$query->order($ordering);

		return $query;
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
		if ( $items === false )
		{
			return false;
		}

		$good_items = Array();

		// Update the attachments with information about their parents
		PluginHelper::importPlugin('attachments');
		$apm = AttachmentsPluginManager::getAttachmentsPluginManager();
		foreach ($items as $item) {
			$parent_id = $item->parent_id;
			$parent_type = $item->parent_type;
			$parent_entity = $item->parent_entity;
			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				$errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S',
										 $parent_type . ':' . $parent_entity .
										 ' (ID ' .(string)$item->id . ')') . ' (ERR 115)';
				$app = Factory::getApplication();
				$app->enqueueMessage($errmsg, 'warning');
				continue;
				}
			$parent = $apm->getAttachmentsPlugin($parent_type);

			if ( $parent ) {

				// Handle the normal case
				$item->parent_entity_type = Text::_('ATTACH_' . $parent_entity);
				$title = $parent->getTitle($parent_id, $parent_entity);
				$item->parent_exists = $parent->parentExists($parent_id, $parent_entity);
				if ( $item->parent_exists && $title ) {
					$item->parent_title = $title;
					$item->parent_url =
						OutputFilter::ampReplace( $parent->getEntityViewURL($parent_id, $parent_entity) );
					}
				else {
					$item->parent_title = Text::sprintf('ATTACH_NO_PARENT_S', $item->parent_entity_type);
					$item->parent_url = '';
					}
				}

			else {
				// Handle pathological case where there is no parent handler
				// (eg, deleted component)
				$item->parent_exists = false;
				$item->parent_entity_type = $parent_entity;
				$item->parent_title = Text::_('ATTACH_UNKNOWN');
				$item->parent_published = false;
				$item->parent_archived = false;
				$item->parent_url = '';
				}

			$good_items[] = $item;
			}

		// Return from the cache
		return $good_items;
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
	public function getTable($type = 'Attachment', $prefix = 'Administrator', $config = array())
	{
		/** @var \Joomla\CMS\MVC\Factory\MVCFactory $mvc */
		$mvc = Factory::getApplication()
			->bootComponent("com_attachments")
			->getMVCFactory();
		return $mvc->createTable($type, $prefix, $config);
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

		return $attachmentTable->publish($cid, $value);
	}

}
