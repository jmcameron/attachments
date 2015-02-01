<?php
/**
 * Attachment model definition
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/model.php');


/**
 * Attachment Model
 *
 * @package Attachments
 */
class AttachmentsModelAttachment extends JModelLegacy
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
			$user_levels = $user->getAuthorisedViewLevels();

			// If the user is not logged in, add extra view levels (if configured)
			if ( $user->get('username') == '' ) {

				// Get the component parameters
				jimport('joomla.application.component.helper');
				$params = JComponentHelper::getParams('com_attachments');

				// Add the specified access levels
				$guest_levels = $params->get('show_guest_access_levels', Array('1'));
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

			// Load the attachment data and make sure this user has access
			$db		= $this->getDbo();
			$query	= $db->getQuery(true);
			$query->select('a.*, a.id as id');
			$query->from('#__attachments as a');
			$query->where('a.id = '.(int)$this->_id);
			if ( !$user->authorise('core.admin') ) {
				$query->where('a.access in ('.$user_levels.')');
				}
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


	/**
	 * Increment the download cout
	 *
	 * @param int $attachment_id the attachment ID
	 */
	public function incrementDownloadCount()
	{
		// Update the download count
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__attachments')->set('download_count = (download_count + 1)');
		$query->where('id = ' .(int)$this->_id);
		$db->setQuery($query);
		if ( !$db->query() ) {
			$errmsg = $db->stderr() . ' (ERR 49)';
			JError::raiseError(500, $errmsg);
			}
	}

}
