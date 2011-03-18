<?php
/**
 * Attachment model definition
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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
	function __construct()
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
	function setId($id=0)
	{
		$this->_id = $id;
		$this->_attachment = null;
	}

	
	/**
	 * Load the attachment data
	 */
	function _loadAttachment()
	{
		if ($this->_id == 0) {
			return false;
			}
		
		if ( empty($this->_attachment) ) {
				
			$query = "SELECT a.*, a.id as id, u.name as uploader_name " 
				. "FROM #__attachments as a "
				. "LEFT JOIN #__users AS u ON u.id = a.uploader_id "
				. "WHERE a.id = '".(int)$this->_id."'";
			
			$db =& $this->getDBO();
			$db->setQuery($query);
			$this->_attachment = $db->loadObject();
			
			if ( empty($this->_attachment) ) {
				return false;
				}

			// Retrieve the information about the parent
			$parent_type = $this->_attachment->parent_type;
			$parent_entity = $this->_attachment->parent_entity;
			JPluginHelper::importPlugin('attachments');
			$apm =& getAttachmentsPluginManager();
			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				$this->_attachment->parent_type = false;
				return false;
				}
			$parent =& $apm->getAttachmentsPlugin($parent_type);

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
	function _initAttachment()
	{
		echo "_initData not implemented yet <br />";	
		return null;	
	}
	
	/**
	 * Get the data
	 *
	 * @return object
	 */
	function getAttachment()
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
	function save($data)
	{
		// Get the table
		$table =& $this->getTable('Attachments');
		
		// Save the data
		if ( !$table->save($data) ) {
			// An error occured, save the model error message
			$this->setError($table->getError());
			return false;		
			}
			
		return true;
	}

}

?>
