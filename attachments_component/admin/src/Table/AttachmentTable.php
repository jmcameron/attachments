<?php
/**
 * Attachments component attachment table
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\Table;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseDriver;
use Joomla\Utilities\ArrayHelper;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Attachments table class
 *
 * @package Attachments
 */
class AttachmentTable extends Table
{
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 */
	public function __construct(DatabaseDriver &$db)
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
		ArrayHelper::toInteger($pks);
		$userId = (int) $userId;
		$state	= (int) $state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks)) {
			if ($this->$k) {
				$pks = array($this->$k);
			}
			// Nothing to set publishing state on, return false.
			else {
				throw new \Exception(Text::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		// Get the article/parent handler
		PluginHelper::importPlugin('attachments');
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
			try {
				$attachment = $this->_db->loadObject();
			} catch (\Exception $e) {
				throw new \Exception($e->getMessage() . ' (ERR 108)');
			}

			$parent_id = $attachment->parent_id;
			$parent_type = $attachment->parent_type;
			$parent_entity = $attachment->parent_entity;

			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				$errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 109)';
				throw new \Exception($errmsg, 500);
				}
			$parent = $apm->getAttachmentsPlugin($parent_type);

			// If we may not change it's state, complain!
			if ( !$parent->userMayChangeAttachmentState($parent_id, $parent_entity,
														$attachment->created_by) )
			{
				// Note the bad ID
				$bad_ids[] = $id;

				// If the user is not authorized, complain
				$app = Factory::getApplication();
				$parent_entity = $parent->getCanonicalEntityId($parent_entity);
				$errmsg = Text::sprintf('ATTACH_ERROR_NO_PERMISSION_TO_PUBLISH_S_ATTACHMENT_S_ID_N',
										 $parent_entity, $attachment->filename, $id) . ' (ERR 110)';
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
		try {
			$this->_db->execute();
		} catch (\Exception $e) {
			throw new \Exception(Text::sprintf('JLIB_DATABASE_ERROR_PUBLISH_FAILED',
													get_class($this), $e->getMessage()) . ' (ERR 111)');
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
		$this->description	= $this->_db->escape($this->description);

		$this->user_field_1 = $this->_db->escape($this->user_field_1);
		$this->user_field_2 = $this->_db->escape($this->user_field_2);
		$this->user_field_3 = $this->_db->escape($this->user_field_3);

		// Let the parent class do the real work!
		return parent::store($updateNulls);
	}

}

