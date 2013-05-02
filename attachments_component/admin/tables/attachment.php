<?php
/**
 * Attachments component attachment table
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// import Joomla table library
jimport('joomla.database.table');

/**
 * Attachments table class
 *
 * @package Attachments
 */
class AttachmentsTableAttachment extends JTable
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__attachments', 'id', $db);
	}


	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 *
	 * @param	mixed	An optional array of primary key values to update.	If not
	 *					set the instance property value is used.
	 * @param	integer The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param	integer The user id of the user performing the operation.
	 * @return	int Number of attachments published ( false if 0 )
	 * @since	1.0.4
	 * @link	http://docs.joomla.org/JTable/publish
	 */
	public function publish($pks = null, $state = 1, $userId = 0)
	{
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state	= (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks)) {
			if ($this->$k) {
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else {
				$e = new JException(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				$this->setError($e);
				return false;
			}
		}

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();

		// Remove any attachments that the user may not publish/unpublish
		$bad_ids = Array();
		foreach ($pks as $id)
		{
			// Get the info about this attachment
			$query = $this->_db->getQuery(true);
			$query->select('*')->from($this->_tbl);
			$query->where('id='.(int)$id);
			$this->_db->setQuery($query);
			$attachment = $this->_db->loadObject();
			if ( $this->_db->getErrorNum() ) {
				$errmsg = $db->stderr() . ' (ERR 107)';
				JError::raiseError(500, $errmsg);
				}

			$parent_id = $attachment->parent_id;
			$parent_type = $attachment->parent_type;
			$parent_entity = $attachment->parent_entity;

			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				$errmsg = JText::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 108)';
				JError::raiseError(500, $errmsg);
				}
			$parent = $apm->getAttachmentsPlugin($parent_type);

			// If we may not change it's state, complain!
			if ( !$parent->userMayChangeAttachmentState($parent_id, $parent_entity,
														$attachment->created_by) )
			{
				// Note the bad ID
				$bad_ids[] = $id;

				// If the user is not authorized, complain
				$app = JFactory::getApplication();
				$parent_entity = $parent->getCanonicalEntityId($parent_entity);
				$errmsg = JText::sprintf('ATTACH_ERROR_NO_PERMISSION_TO_PUBLISH_S_ATTACHMENT_S_ID_N',
										 $parent_entity, $attachment->filename, $id) . ' (ERR 109)';
				$app->enqueueMessage($errmsg, 'error');
			}
		}

		// Remove any offending attachments
		$pks = array_diff($pks, $bad_ids);

		// Exit if there are no attachments the user can change the state of
		if ( empty($pks) )
		{
			// No warning needed because warnings already issued for attachments user cannot change
			return false;
		}

		// Update the publishing state for rows with the given primary keys.
		$query = $this->_db->getQuery(true);
		$query->update($this->_tbl);
		$query->set('state = '.(int) $state);

		// Determine if there is checkin support for the table.
		if (property_exists($this, 'checked_out') || property_exists($this, 'checked_out_time')) {
			$query->where('(checked_out = 0 OR checked_out = '.(int) $userId.')');
			$checkin = true;
		}
		else {
			$checkin = false;
		}

		// Build the WHERE clause for the primary keys.
		$query->where($k.' = '.implode(' OR '.$k.' = ', $pks));

		$this->_db->setQuery($query);

		// Check for a database error.
		if (!$this->_db->query()) {
			$e = new JException(JText::sprintf('JLIB_DATABASE_ERROR_PUBLISH_FAILED',
											   get_class($this), $this->_db->getErrorMsg()) . ' (ERR 110)');
			$this->setError($e);
			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
			// Checkin the rows.
			foreach($pks as $pk)
			{
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks)) {
			$this->state = $state;
		}

		$this->setError('');
		return count($pks);
	}


	/**
	 * Store the attachment into the database 
	 * 
	 * Extend base class function to encode description and display_name safely
	 *
	 * @param	boolean	 $updateNulls  True to update fields even if they are null.
	 *
	 * @return	boolean	 True on success.
	 *
	 * @link	http://docs.joomla.org/JTable/store
	 */
	public function store($updateNulls = false)
	{
		// make sure the display name and description are escaped since they may contain quotes
		$this->display_name = $this->_db->escape($this->display_name);
		$this->description = $this->_db->escape($this->description);

		// Let the parent class do the real work!
		return parent::store($updateNulls);
	}


	/**
	 * Add the user names for creator, modifier, etc
	 *
	 * This is needed for attachments that are created from scratch
	 * (eg, via form processing)
	 */
	public function addUserNames()
	{
		// Get information about this user
		$user	= JFactory::getUser();
		$user_levels = $user->getAuthorisedViewLevels();

		// If the user is not logged in, add extra view levels (if configured)
		if ( $user->get('username') == '' ) {

			// Get the component parameters
			jimport('joomla.application.component.helper');
			$params = JComponentHelper::getParams('com_attachments');

			// Add the specified access levels
			$guest_levels = $params->get('show_guest_access_levels', Array('1', '2'));
			if (is_array($guest_levels)) {
				foreach ($guest_levels as $glevel) {
					$user_levels[] = $glevel;
					}
				}
			else {
				$user_levels[] = $glevel;
				}
			}
		$user_levels = implode(',', array_unique($user_levels));

		// Get the names of the users from the database item for this attachment
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);
		
		$query->select('a.id, a.access');
		$query->from('#__attachments as a');

		$query->select('u1.name as creator_name');
		$query->leftJoin('#__users AS u1 ON u1.id = a.created_by');

		$query->select('u2.name as modifier_name');
		$query->leftJoin('#__users AS u2 ON u2.id = a.modified_by');

		$query->where('a.id = '.(int)$this->id);

		$query->where('a.access in ('.$user_levels.')');

		$db->setQuery($query, 0, 1);

		$result = $db->loadObject();

		// Copy the names to this attachment object
		$this->creator_name = $result->creator_name;
		$this->modifier_name = $result->modifier_name;
	}
	
}

