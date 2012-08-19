<?php
/**
 * Attachment list model definition
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
 * Attachment List Model for all attachments belonging to one
 * content article (or other content-related entity)
 *
 * @package Attachments
 */
class AttachmentsModelAttachments extends JModel
{
	/**
	 * ID of parent of the list of attachments
	 */
	var $_parent_id = null;

	/**
	 * type of parent
	 */
	var $_parent_type = null;

	/**
	 * type of parent entity (each parent_type can support several)
	 */
	var $_parent_entity = null;

	/**
	 * Parent class object (an Attachments extension plugin object)
	 */
	var $_parent = null;

	/**
	 * Parent title
	 */
	var $_parent_title = null;

	/**
	 * Parent entity name
	 */
	var $_parent_entity_name = null;

	/**
	 * Whether some of the attachments should be visible to the user
	 */
	var $_some_visible = null;

	/**
	 * Whether some of the attachments should be modifiable to the user
	 */
	var $_some_modifiable = null;

	/**
	 * The desired sort order
	 */
	var $_sort_order;


	/**
	 * The list of attachments for the specified article/content entity
	 */
	var $_list = null;

	/**
	 * Number of attachments
	 *
	 * NOTE: After the list of attachments has been retrieved, if it is empty, this is set to zero.
	 *		 But _list remains null.   You can use this to check to see if the list has been loaded.
	 */
	var $_num_attachments = null;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}


	/**
	 * Set the parent id (and optionally the parent type)
	 *
	 * NOTE: If the $id is null, it will get both $id and $parent_id from JRequest
	 *
	 * @param int $id the id of the parent
	 * @param string $parent_type the parent type (defaults to 'com_content')
	 * @param string $parent_entity the parent entity (defaults to 'default')
	 */
	public function setParentId($id=null, $parent_type='com_content', $parent_entity='default')
	{
		// Get the parent id and type
		if ( is_numeric($id) ) {
			$parent_id = (int)$id;
			}
		else {
			// It was not an argument, so get parent id and type from the JRequest
			$parent_id	 = JRequest::getInt('article_id', null);

			// Deal with special case of editing from the front end
			if ( $parent_id == null ) {
				if ( (JRequest::getCmd('view') == 'article') &&
					 (JRequest::getCmd('task') == 'edit' )) {
					$parent_id = JRequest::getInt('id', null);
					}
				}

			// If article_id is not specified, get the general parent id/type
			if ( $parent_id == null ) {
				$parent_id = JRequest::getInt('parent_id', null);
				if ( $parent_id == null ) {
					$errmsg = JText::_('ATTACH_ERROR_NO_PARENT_ID_SPECIFIED') . ' (ERR 154)';
					JError::raiseError(500, $errmsg);
					}
				}
			}

		// Reset instance variables
		$this->_parent_id = $parent_id;
		$this->_parent_type = $parent_type;
		$this->_parent_entity = $parent_entity;

		$this->_parent = null;
		$this->_parent_class = null;
		$this->_parent_title = null;
		$this->_parent_entity_name = null;

		$this->_list = null;
		$this->_sort_order = null;
		$this->_some_visible = null;
		$this->_some_modifiable = null;
		$this->_num_attachments = null;
	}



	/**
	 * Get the parent id
	 *
	 * @return the parent id
	 */
	public function getParentId()
	{
		if ( $this->_parent_id === null ) {
			$errmsg = JText::_('ATTACH_ERROR_NO_PARENT_ID_SPECIFIED') . ' (ERR 155)';
			JError::raiseError(500, $errmsg);
			}
		return $this->_parent_id;
	}


	/**
	 * Get the parent type
	 *
	 * @return the parent type
	 */
	public function getParentType()
	{
		if ( $this->_parent_type == null ) {
			$errmsg = JText::_('ATTACH_ERROR_NO_PARENT_TYPE_SPECIFIED') . ' (ERR 156)';
			JError::raiseError(500, $errmsg);
			}
		return $this->_parent_type;
	}


	/**
	 * Get the parent entity
	 *
	 * @return the parent entity
	 */
	public function getParentEntity()
	{
		if ( $this->_parent_entity == null ) {
			$errmsg = JText::_('ATTACH_ERROR_NO_PARENT_ENTITY_SPECIFIED') . ' (ERR 157)';
			JError::raiseError(500, $errmsg);
			}

		// Make sure we have a good parent_entity value
		if ( $this->_parent_entity == 'default' ) {
			$parent = $this->getParentClass();
			$this->_parent_entity = $parent->getDefaultEntity();
			}

		return $this->_parent_entity;
	}


	/**
	 * Get the parent class object
	 *
	 * @return the parent class object
	 */
	public function &getParentClass()
	{
		if ( $this->_parent_type == null ) {
			$errmsg = JText::_('ATTACH_ERROR_NO_PARENT_TYPE_SPECIFIED') . ' (ERR 158)';
			JError::raiseError(500, $errmsg);
			}

		if ( $this->_parent_class == null ) {

			// Get the parent handler
			JPluginHelper::importPlugin('attachments');
			$apm = getAttachmentsPluginManager();
			if ( !$apm->attachmentsPluginInstalled($this->_parent_type) ) {
				$errmsg = JText::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 159)';
				JError::raiseError(500, $errmsg);
				}
			$this->_parent_class = $apm->getAttachmentsPlugin($this->_parent_type);
			}

		return $this->_parent_class;
	}


	/**
	 * Get the title for the parent
	 *
	 * @return the title for the parent
	 */
	public function getParentTitle()
	{
		// Get the title if we have not done it before
		if ( $this->_parent_title == null ) {

			$parent = $this->getParentClass();

			// Make sure we have an article ID
			if ( $this->_parent_id === null ) {
				$errmsg = JText::_('ATTACH_ERROR_UNKNOWN_PARENT_ID') . ' (ERR 160)';
				JError::raiseError(500, $errmsg);
				}

			$this->_parent_title = $parent->getTitle( $this->_parent_id, $this->_parent_entity );
			}

		return $this->_parent_title;
	}


	/**
	 * Get the EntityName for the parent
	 *
	 * @return the entity name for the parent
	 */
	public function getParentEntityName()
	{
		// Get the parent entity name if we have not done it before
		if ( $this->_parent_entity_name == null ) {

			// Make sure we have an article ID
			if ( $this->_parent_id === null ) {
				$errmsg = JText::_('ATTACH_ERROR_NO_PARENT_ID_SPECIFIED') . ' (ERR 161)';
				JError::raiseError(500, $errmsg);
				}

			$this->_parent_entity_name = JText::_('ATTACH_' . $this->getParentEntity());
			}

		return $this->_parent_entity_name;
	}


	/**
	 * Set the sort order (do this before doing getAttachmentsList)
	 *
	 * @param string $new_sort_order name of the new sort order
	 */
	public function setSortOrder($new_sort_order)
	{
		if ( $new_sort_order == 'filename' )
			$order_by = "filename";
		else if ( $new_sort_order == 'file_size' )
			$order_by = "file_size";
		else if ( $new_sort_order == 'file_size_desc' )
			$order_by = "file_size DESC";
		else if ( $new_sort_order == 'description' )
			$order_by = "description";
		else if ( $new_sort_order == 'description_desc' )
			$order_by = "description DESC";
		else if ( $new_sort_order == 'display_name' )
			$order_by = "display_name, filename";
		else if ( $new_sort_order == 'created' )
			$order_by = "created";
		else if ( $new_sort_order == 'created_desc' )
			$order_by = "created DESC";
		else if ( $new_sort_order == 'modified' )
			$order_by = "modified";
		else if ( $new_sort_order == 'modified_desc' )
			$order_by = "modified DESC";
		else if ( $new_sort_order == 'user_field_1' )
			$order_by = "user_field_1";
		else if ( $new_sort_order == 'user_field_2' )
			$order_by = "user_field_2";
		else if ( $new_sort_order == 'user_field_3' )
			$order_by = "user_field_3";
		else if ( $new_sort_order == 'id' )
			$order_by = "id";
		else
			$order_by = "filename";

		$this->_sort_order = $order_by;
	}



	/**
	 * Get or build the list of attachments
	 *
	 * @return the list of attachments for this parent
	 */
	public function &getAttachmentsList()
	{
		// Just return it if it has already been created
		if ( $this->_list != null ) {
			return $this->_list;
			}

		// Create the list

		// Get the parent id and type
		$parent_id	   = $this->getParentId();
		$parent_type   = $this->getParentType();
		$parent_entity = $this->getParentEntity();

		// Use parent entity corresponding to values saved in the attachments table
		$parent = $this->getParentClass();

		// Define the list order
		if ( ! $this->_sort_order ) {
			$this->_sort_order = 'filename';
			}

		// Construct the query
		$db = JFactory::getDBO();
		$user = JFactory::getUser();
		$user_levels = implode(',', array_unique($user->authorisedLevels()));

		$query = $db->getQuery(true);
		$query->select('a.*, u.name as creator_name')->from('#__attachments AS a');
		$query->leftJoin('#__users AS u ON u.id = a.created_by');

		if ( $parent_id == 0 ) {
			// If the parent ID is zero, the parent is being created so we have
			// do the query differently
			$user_id = $user->get('id');
			$query->where('a.parent_id IS NULL AND u.id=' . (int)$user_id);
			}
		else {
			$query->where('a.parent_id='.(int)$parent_id);

			// Handle the state part of the query
			if ( $user->authorise('core.edit.state', 'com_attachments') ) {
				// Do not filter on state since this user can change the state of any attachment
				}
			elseif ( $user->authorise('attachments.edit.state.own', 'com_attachments') ) {
				$query->where('((a.created_by = '.(int)$user->id.') OR (a.state = 1))');
				}
			elseif ( $user->authorise('attachments.edit.state.ownparent', 'com_attachments') ) {
				// The user can edit the state of any attachment if they created the article/parent
				$parent_creator_id = $parent->getParentCreatorId($parent_id, $parent_entity);
				if ( (int)$parent_creator_id == (int)$user->get('id') ) {
					// Do not filter on state since this user can change the state of any attachment on this article/parent
					}
				else {
					// Since the user is not the creator, they should only see published attachments
					$query->where('a.state = 1');
					}
				}
			else {
				// For everyone else only show published attachments
				$query->where('a.state = 1');
				}
			}

		$query->where('a.parent_type=' . $db->quote($parent_type) . ' AND a.parent_entity=' . $db->quote($parent_entity));
		$query->where('a.access IN ('.$user_levels.')');
		$query->order($this->_sort_order);

		// Do the query
		$db->setQuery($query);
		$attachments = $db->loadObjectList();
		if ( $db->getErrorNum() ) {
			$errmsg = $db->stderr() . ' (ERR 162)';
			JError::raiseError(500, $errmsg);
			}

		$this->_some_visible = false;
		$this->_some_modifiable = false;

		// Install the list of attachments in this object
		$this->_num_attachments = count($attachments);

		// The query only returns items that are visible/accessible for
		// the user, so if it contains anything, they will be visible
		$this->_some_visible = $this->_num_attachments > 0;

		// Add permissions for each attachment in the list
		if ( $this->_num_attachments > 0 ) {

			$this->_list = $attachments;

			// Add the permissions to each row
			$parent = $this->getParentClass();

			// Add permissions
			foreach ( $attachments as $attachment ) {
				$attachment->user_may_delete = $parent->userMayDeleteAttachment($attachment);
				$attachment->user_may_edit = $parent->userMayEditAttachment($attachment);
				if ( $attachment->user_may_edit ) {
					$this->_some_modifiable = true;
					}
				}

			// Fix relative URLs
			foreach ( $attachments as $attachment ) {
				if ( $attachment->uri_type == 'url' ) {
					$url = $attachment->url;
					if ( strpos($url, '://') === false ) {
						$uri = JFactory::getURI();
						$attachment->url = $uri->base(true) . '/' . $url;
						}
					}
				}

			}

		// Finally, return the list!
		return $this->_list;
	}


	/**
	 * Get the number of attachments
	 *
	 * @return the number of attachments for this parent
	 */
	public function numAttachments()
	{
		return $this->_num_attachments;
	}


	/**
	 * Are some of the attachments be visible?
	 *
	 * @return true if there are attachments and some should be visible
	 */
	public function someVisible()
	{
		// See if the attachments list has been loaded
		if ( $this->_list == null ) {

			// See if we have already loaded the attachements list
			if ( $this->_num_attachments === 0 ) {
				return false;
				}

			// Since the attachments have not been loaded, load them now
			$this->getAttachmentsList();
			}

		return $this->_some_visible;
	}


	/**
	 * Should some of the attachments be modifiable?
	 *
	 * @return true if there are attachments and some should be modifiable
	 */
	public function someModifiable()
	{
		// See if the attachments list has been loaded
		if ( $this->_list == null ) {

			// See if we have already loaded the attachements list
			if ( $this->_num_attachments === 0 ) {
				return false;
				}

			// Since the attachments have not been loaded, load them now
			$this->getAttachmentsList();
			}

		return $this->_some_modifiable;
	}



	/**
	 * Returns the types of attachments
	 *
	 * @return 'file', 'url', 'both', or false (if no attachments)
	 */
	public function types()
	{
		// Make sure the attachments are loaded
		if ( $this->_list == null ) {

			// See if we have already loaded the attachements list
			if ( $this->_num_attachments === 0 ) {
				return false;
				}

			// Since the attachments have not been loaded, load them now
			$this->getAttachmentsList();
			}

		// Scan the attachments
		$types = false;
		foreach ( $this->_list as $attachment ) {
			if ( $types ) {
				if ( $attachment->uri_type != $types ) {
					return 'both';
					}
				}
			else {
				$types = $attachment->uri_type;
				}
			}

		return $types;
	}

}
