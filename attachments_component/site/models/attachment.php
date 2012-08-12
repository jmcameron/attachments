<?php
/**
 * Attachment model definition
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.model');

/** 
 * Attachment Model
 *
 * @package Attachments
 */
class AttachmentsModelAttachment extends JModel
{

	/**
	 * Attachment ID
	 */
	var $_id = null;


	/** 
	 * Attachment object/data
	 *
	 * @var object
	 */
	var $_attachment = null;


	/** 
	 * Constructor, build object and determines its ID
	 */
	public function __construct()
	{
		parent::__construct();

		// Get the cid array from the request
		$cid = JRequest::getVar('cid', false, 'DEFAULT', 'array');

		if ($cid) {
			// Accept only the first id from the array
			$id = $cid[0];
			}
		else {
			$id = JRequest::getInt('id',0);
			}

		$this->setId($id);
	}


	/** 
	 * Reset the model ID and data
	 */
	public function setId($id=0)
	{
		$this->_id = $id;
		$this->_attachment = null;
	}

	
	/**
	 * Load the attachment data
	 *
	 * @return true if loaded successfully
	 */
	private function _loadAttachment()
	{
		if ($this->_id == 0) {
			return false;
			}
		
		if ( empty($this->_attachment) ) {
				
			$user	= JFactory::getUser();
			$user_levels = implode(',', array_unique($user->authorisedLevels()));

			$db		= $this->getDbo();
			$query	= $db->getQuery(true);

			$query->select('a.*, a.id as id');
			$query->from('#__attachments as a');

			$query->select('u1.name as creator_name');
			$query->leftJoin('#__users AS u1 ON u1.id = a.created_by');

			$query->select('u2.name as modifier_name');
			$query->leftJoin('#__users AS u2 ON u2.id = a.modified_by');
			
			$query->where('a.id = '.(int)$this->_id);

			$query->where('a.access in ('.$user_levels.')');

			$db->setQuery($query, 0, 1);
			$this->_attachment = $db->loadObject();
			
			if ( empty($this->_attachment) ) {
				return false;
				}

			// Retrieve the information about the parent
			$parent_type = $this->_attachment->parent_type;
			$parent_entity = $this->_attachment->parent_entity;
			JPluginHelper::importPlugin('attachments');
			$apm = getAttachmentsPluginManager();
			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				$this->_attachment->parent_type = false;
				return false;
				}
			$parent = $apm->getAttachmentsPlugin($parent_type);

			// Set up the parent info
			$parent_id = $this->_attachment->parent_id;
			$this->_attachment->parent_title = $parent->getTitle($parent_id, $parent_entity);
			$this->_attachment->parent_published =
				$parent->isParentPublished($parent_id, $parent_entity);
			}
				
		return true;
	}

	
	/**
	 * Create a new Attachment object  
	 */
	private function _initAttachment()
	{
		echo "_initData not implemented yet <br />";	
		return null;	
	}

	
	/**
	 * Get the data
	 *
	 * @return object
	 */
	public function getAttachment()
	{
		if ( !$this->_loadAttachment() ) {
			// If the load fails, create a new one
			$this->_initAttachment();
			}

		return $this->_attachment;
	}

	
	/**
	 * Save the attachment
	 * 
	 * @param object $data mixed object or associative array of data to save
	 *
	 * @return Boolean true on success
	 */
	public function save($data)
	{
		// Get the table
		$table = $this->getTable('Attachments');
		
		// Save the data
		if ( !$table->save($data) ) {
			// An error occured, save the model error message
			$this->setError($table->getError());
			return false;		
			}
			
		return true;
	}

}
