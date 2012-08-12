<?php
/**
 * Attachments component attachment model
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla modelform library
jimport('joomla.application.component.modeladmin');

/**
 * Attachment Model
 *
 * @package Attachments
 */
class AttachmentsModelAttachment extends JModelAdmin
{
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
	 * Override the getItem() command to get some extra info
	 *
	 * @param	integer	$pk	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 */
	public function getItem($pk = null)
	{
		$item = parent::getItem($pk);

		if ( $item->id != 0 ) {

			// If the item exists, get more info
			$db = $this->getDbo();

			// Get the creator name
			$query = $db->getQuery(true);
			$query->select('name')->from('#__users')->where('id = ' . (int)$item->created_by);
			$db->setQuery($query, 0, 1);
			$item->creator_name = $db->loadResult();
			if ( $db->getErrorNum() ) {
				$errmsg = $db->stderr() . ' (ERR 96)';
				JError::raiseError(500, $errmsg);
				}

			// Get the modifier name
			$query = $db->getQuery(true);
			$query->select('name')->from('#__users')->where('id = ' . (int)$item->modified_by);
			$db->setQuery($query, 0, 1);
			$item->modifier_name = $db->loadResult();
			if ( $db->getErrorNum() ) {
				$errmsg = $db->stderr() . ' (ERR 97)';
				JError::raiseError(500, $errmsg);
				}

			// Get the parent info (??? Do we really need this?)
			$parent_type = $item->parent_type;
			$parent_entity = $item->parent_entity;
			JPluginHelper::importPlugin('attachments');
			$apm = getAttachmentsPluginManager();
			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				$errmsg = JText::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 98)';
				JError::raiseError(500, $errmsg);
				}
			$item->parent = $apm->getAttachmentsPlugin($parent_type);

			}

		return $item;
	}

	/**
	 * Method to get the record form.
	 *
	 * @param		array	$data			Data for the form.
	 * @param		boolean $loadData		True if the form is to load its own data (default case), false if not.
	 * @return		mixed	A JForm object on success, false on failure
	 * @since		1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_attachments.attachment', 'attachment',
								array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}
		return $form;
	}


	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return		mixed	The data for the form.
	 * @since		1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_attachments.edit.attachment.data', array());
		if (empty($data))
		{
			$data = $this->getItem();
		}
		return $data;
	}
}
