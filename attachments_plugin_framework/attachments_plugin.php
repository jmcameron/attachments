<?php
/**
 * Attechments for extensions
 *
 * @package Attachments
 * @subpackage Attachments_Plugin_Framework
 *
 * @copyright Copyright (C) 2009-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


/**
 * Plugins for Attachments
 *
 * AttachmentsPlugin is the base class for all the plugins to allow
 * attaching files to various types of content entities
 *
 * The derived attachments plugin class must be in the main PHP file for that
 * plugin.	For instance for content articles or categories, the parent type
 * is 'com_content'.  The parent type is simply the name of the component
 * involved (eg, 'com_content').  The derived attachments plugin class (such
 * as 'AttachmentsPlugin_com_content') should be defined in the main file for
 * the plugin (eg, attachments_for_conent.php).
 *
 * Derived attachments plugin classes must also include the following lines of
 * code after the class definition to register the derived class with the
 * Attachments plugin manager:
 *
 *	 $apm = getAttachmentsPluginManager();
 *	 $apm->addParentType('com_content');
 *
 * where 'com_content' should be replaced by the name of the appropriate
 * parent type (component).
 *
 * @package Attachments
 */
class AttachmentsPlugin extends JPlugin
{
	/** Parent_type: com_content, com_quickfaq, etc
	 */
	var $_parent_type = null;

	/** Name for the default parent_entity type
	 *
	 * Note that this name will be used for a directory for attachments entries
	 * and should not contain any spaces.  It should correspond to the default
	 * entity.	For com_content, it will be 'article';
	 */
	var $_default_entity = null;

	/** known entities
	 */
	var $_entities = null;

	/** An associative array of entity names
	 *
	 *	For each type of attachments plugin, there will be a default
	 *	entity types.  For com_content, the default is 'article'.  If
	 *	the $default value for the function calls below is omitted,
	 *	the entity is assumed to be 'article'.	In some cases, the
	 *	actual proper name of the entity will be available and will be
	 *	passed in to the $default argument.	 It is important that the
	 *	plugin code recognizes that the entity 'default' is an alias
	 *	for 'article'.	This array allows a simple associative array
	 *	lookup to transform 'default' to 'article'.
	 */
	var $_entity_name = Array();

	/** An associative array of entity tables
	 */
	var $_entity_table = Array();

	/** An associative array of entity id fields
	 *	(in same table as the title)
	 */
	var $_entity_id_field = Array();

	/** An associative array of entity title fields
	 */
	var $_entity_title_field = Array();

	/** An associative array of parent creator user ID fields
	 */
	var $_parent_creator_id_field = Array();

	/** Flag indicating if the language file haas been loaded
	 */
	var $_language_loaded = false;

	/** Cache for parentExists check
	 */
	var $_parent_exists_cache = Array();

	/** Cache for parent titles
	 */
	var $_title_cache = Array();


	/**
	 * Constructor
	 *
	 * @param object $subject The object to observe
	 * @param array  $config  An optional associative array of configuration settings.
	 * Recognized key values include 'name', 'group', 'params', 'language'
	 * (this list is not meant to be comprehensive).
	 */
	public function __construct(&$subject, $config = array())
	{
		parent::__construct($subject, $config);

		// Save the plugin type
		$this->_type = 'attachments';
	}


	/**
	 * Return the parent entity / row ID
	 *
	 * This will only be called by the main attachments 'onPrepareContent'
	 * plugin if $attachment does not have an id
	 *
	 * @param object &row the article or content item (potential attachment parent)
	 *
	 * @return id if found, false if this is not a valid parent
	 */
	public function getParentId(&$attachment)
	{
		return JRequest::getInt('id', false);
	}


	/**
	 * Return the component name for this parent object
	 *
	 * @return the component name for this parent object
	 */
	public function getParentType()
	{
		return $this->_parent_type;
	}


	/**
	 * Return a string of the where clause for filtering the the backend list of attachments
	 *
	 * @param $parent_state string the state ('ALL', 'PUBLISHED', 'UNPUBLISHED', 'ARCHIVED', 'NONE')
	 * @param $filter_entity string the entity filter ('ALL', 'ARTICLE', 'CATEGORY', etc)
	 *
	 * @return an array of (join_clause, where_clause) items
	 */
	public function getParentPublishedFilter($parent_state, $filter_entity)
	{
		return '';
	}


	/**
	 * Determine the parent entity
	 *
	 * From the view and the class of the parent (row of onPrepareContent plugin),
	 * determine what the entity type is for this entity.
	 *
	 * Derived classes MUST overrride this
	 *
	 * @param &object &$parent The object for the parent (row) that onPrepareContent gets
	 *
	 * @return the correct entity (eg, 'default', 'category', etc) or false if this entity should not be displayed.
	 */
	public function determineParentEntity(&$parent)
	{
		return 'default';
	}


	/**
	 * Return the array of entity IDs for all content items supported by this parent object
	 *
	 * @return the array of entities supported by this parent object
	 */
	public function getEntities()
	{
		return $this->_entities;
	}


	/**
	 * Get the default entity ID
	 * @return string the default entity ID
	 */
	public function getDefaultEntity()
	{
		return $this->_default_entity;
	}


	/**
	 * Get the canonical extension entity Id (eg, 'article' instead of 'default')
	 *
	 * This is the canonical Id of content element/item to which attachments will be added.
	 *
	 * that each content type ($option) may support several different entities
	 * (for attachments) and some entities may have more than one name.
	 *
	 * Note, for com_content, the default is 'article'
	 *
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return the canonical extension entity
	 */
	public function getCanonicalEntityId($parent_entity)
	{
		// It it is a known entity, just return it
		if ( in_array($parent_entity, $this->_entities) ) {
			return $parent_entity;
			}

		// Check aliases
		if ( array_key_exists($parent_entity, $this->_entity_name) ) {
			return $this->_entity_name[$parent_entity];
			}
		else {
			$lang = JFactory::getLanguage();
			$lang->load('plg_attachments_attachments_plugin_framework', dirname(__FILE__));
			$errmsg = JText::sprintf('ATTACH_ERROR_INVALID_ENTITY_S_FOR_PARENT_S',
									 $parent_entity, $parent_type) . ' (ERR 300)';
			JError::raiseError(500, $errmsg);
			}
	}


	/**
	 * Get the path for the uploaded file (on the server file system)
	 *
	 * Note that this does not include the base directory for attachments.
	 *
	 * @param string $parent_entity the type of entity for this parent type
	 * @param int $parent_id the ID for the parent object
	 * @param int $attachment_id the ID for the attachment
	 *
	 * @return string the directory name for this entity (with trailing '/'!)
	 */
	public function getAttachmentPath($parent_entity, $parent_id, $attachment_id)
	{
		$parent_entity = $this->getCanonicalEntityId($parent_entity);

		$path = sprintf("%s/%d/", $parent_entity, $parent_id);

		return $path;
	}




	/**
	 * Get the name or title for the specified object
	 *
	 * @param int $parent_id is the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return the name or title for the specified object
	 */
	public function getTitle($parent_id, $parent_entity='default')
	{
		// Short-circuit if there is no parent ID
		if ( !is_numeric($parent_id) ) {
			return '';
			}

		$parent_entity = $this->getCanonicalEntityId($parent_entity);

		// Check the cache first
		$cache_key = $parent_entity.(int)$parent_id;
		if ( array_key_exists($cache_key, $this->_title_cache) ) {
			return $this->_title_cache[$cache_key];
			}

		$entity_table = $this->_entity_table[$parent_entity];
		$entity_title_field = $this->_entity_title_field[$parent_entity];
		$entity_id_field = $this->_entity_id_field[$parent_entity];

		// Make sure the parent exists
		if ( !$this->parentExists($parent_id, $parent_entity) ) {
			// Do not error out; this is most likely to occur in the backend
			// when an article with attachments has been deleted without
			// deleting the attachments.  But we still need list it!
			return '';
			}

		// Look up the title
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($entity_title_field)->from("#__$entity_table");
		$query->where("$entity_id_field=".(int)$parent_id);
		$db->setQuery($query);
		$title = $db->loadResult();
		if ( $db->getErrorNum() ) {
			$parent_entity_name = JText::_('ATTACH_' . $parent_entity);
			$errmsg = JText::sprintf('ATTACH_ERROR_GETTING_PARENT_S_TITLE_FOR_ID_N',
									 $parent_entity_name, $parent_id) . ' (ERR 301)';
			JError::raiseError(500, $errmsg);
			}

		$this->_title_cache[$cache_key] = $title;

		return $this->_title_cache[$cache_key];
	}


	/**
	 * Return an array of entity items (with id,title pairs for each item)
	 *
	 * @param string $parent_entity the type of entity to search for
	 * @param string $filter filter the results for matches for this filter string
	 *
	 * @return the array of entity id,title pairs
	 */
	public function getEntityItems($parent_entity='default', $filter='')
	{
		$parent_entity = $this->getCanonicalEntityId($parent_entity);

		$entity_table = $this->_entity_table[$parent_entity];
		$entity_title_field = $this->_entity_title_field[$parent_entity];
		$entity_id_field = $this->_entity_id_field[$parent_entity];

		// Get the ordering information
		$app = JFactory::getApplication();
		$order	   = $app->getUserStateFromRequest('com_attachments.selectEntity.filter_order',
												   'filter_order',		'', 'cmd');
		$order_Dir = $app->getUserStateFromRequest('com_attachments.selectEntity.filter_order_Dir',
												   'filter_order_Dir',	'', 'word');

		// Get all the items
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select("DISTINCT $entity_id_field,$entity_title_field");
		$query->from("#__$entity_table");
		if ( $filter ) {
			$filter = $db->quote( '%'.$db->getEscaped( $filter, true ).'%', false );
			$query->where($entity_title_field . ' LIKE ' . $filter);
			}
		if ( $order ) {
			if ( $order == 'title' ) {
				$query->order("$entity_title_field " . $order_Dir);
				}
			else if ( $order == 'id' ) {
				$query->order("$entity_id_field " . $order_Dir);
				}
			else {
				// Ignore unrecognized columns
				}
			}

		echo (string)$query . "<br/>";

		// Do the query
		$db->setQuery($query);
		if ( $db->getErrorNum() ) {
			$parent_entity_name = JText::_('ATTACH_' . $parent_entity);
			$errmsg = JText::sprintf('ATTACH_ERROR_GETTING_LIST_OF_ENTITY_S_ITEMS',
									 $parent_entity_name) . ' (ERR 302)';
			JError::raiseError(500, $errmsg);
			}
		else {
			$items = $db->loadObjectList();
			}

		if ( $items == null ) {
			return null;
			}

		// Make sure the the ids are called 'id' in the list
		if ( $entity_id_field != 'id' ) {
			foreach ( $items as $item ) {
				$item->id = $item->$entity_id_field;
				}
			}

		// Make sure the the titles are called 'title' in the list
		if ( $entity_title_field != 'title' ) {
			foreach ( $items as $item ) {
				$item->title = $item->$entity_title_field;
				}
			}

		return $items;
	}


	/**
	 * Return the URL that can be called to select a specific content item.
	 *
	 * @param string $parent_entity the type of entity to select from
	 *
	 * @return the URL that can be called to select a specific content item
	 */
	public function getSelectEntityURL($parent_entity='default')
	{
		// Add on the parent type and entity
		$entity = "&amp;parent_type=" . $this->_parent_type;
		if ( $parent_entity != 'default' ) {
			$entity .= '.' . $parent_entity;
			}

		return "index.php?option=com_attachments&amp;task=selectEntity" . $entity . "&amp;tmpl=component";
	}


	/**
	 * Return the ID of the creator/owner of the parent entity
	 *
	 * @param int $parent_id the ID for the parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return creators id if found, 0 otherwise
	 */
	public function getParentCreatorId($parent_id, $parent_entity='default')
	{
		JError::raiseError(501, JText::_('ATTACH_NOT_IMPLEMENTED'));
	}


	/**
	 * Get a URL to view the entity
	 *
	 * @param int $parent_id the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return a URL to view the entity (non-SEF form)
	 */
	public function getEntityViewURL($parent_id, $parent_entity='default')
	{
		return null;
	}


	/**
	 * Get a URL to add an attachment to a specific entity
	 *
	 * @param int $parent_id the ID for the parent entity object (null if the parent does not exist)
	 * @param string $parent_entity the type of entity for this parent type
	 * @param string $from where the call should return to
	 *
	 * @return the url to add a new attachments to the specified entity
	 */
	public function getEntityAddUrl($parent_id, $parent_entity='default', $from='closeme')
	{
		$app = JFactory::getApplication();

		if ( $app->isAdmin() ) {
			$task = 'add';
			}
		else {
			$task = 'upload';
			}

		$url = "index.php?option=com_attachments&task=$task";
		if ( $parent_id == null ) {
			$url .= "&parent_id=$parent_id,new";
			}
		else {
			$url .= "&parent_id=$parent_id";
			}
		$url .= "&parent_type=".$this->_parent_type."&from=$from";

		return $url;
	}

	/**
	 * Check to see if a custom title applies to this parent
	 *
	 * Note: this public function assumes that the parent_id's match
	 *
	 * @param string $parent_entity parent entity for the parent of the list
	 * @param string $rtitle_parent_entity the entity of the candidate attachment list title (from params)
	 *
	 * @return true if the custom title should be used
	 */
	public function checkAttachmentsListTitle($parent_entity, $rtitle_parent_entity)
	{
		return false;
	}

	/**
	 * Get the title for the attachments list for this parent
	 *
	 * @param string $title The untranslated title token (either 'ATTACH_ATTACHMENTS_TITLE' or 'ATTACH_EXISTING_ATTACHMENTS')
	 * @param &object &$params The Attachments component parameters object
	 * @param int $parent_id the ID for the parent entity object (null if the parent does not exist)
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return the translated title string
	 */
	public function attachmentsListTitle($title, &$params, $parent_id, $parent_entity='default')
	{
		$rtitle_str = $params->get('attachments_titles', '');
		if ( ($title != 'ATTACH_EXISTING_ATTACHMENTS') && ($rtitle_str != '') ) {
			$rtitle_list = preg_split("[\n|\r]", $rtitle_str);
			foreach ($rtitle_list as $rtitle) {
				if ( preg_match('|^([0-9]+)\s*([^$]+)$|', $rtitle, $match) ) {
					// process:	 3 new title
					// NOTE: This form only applies to articles and will be ignored for anything else
					if ( (int)$parent_id != (int)$match[1] ) {
						continue;
						}
					if ( ($this->_parent_type == 'com_content') &&
						 (($parent_entity == 'default') || ($parent_entity == 'article')) ) {
						$title = $match[2];
						}
					}
				elseif ( preg_match('|^([a-zA-Z0-9_/-]+):([0-9]+)\s*([^$]+)$|', $rtitle, $match) ) {
					// process:	  entity:3 new title
					if ( (int)$parent_id != (int)$match[2] ) {
						continue;
						}
					if ( $this->checkAttachmentsListTitle($parent_entity, $match[1]) ) {
						$title = $match[3];
						}
					}
				else {
					// With no entity/numeric prefix, the title applies to all attachments lists
					$rtitle = trim($rtitle);
					if ( $rtitle != '' ) {
						$title = $rtitle;
						}
					}
				}
			}

		return JText::_($title);
	}


	/**
	 * Does the parent exist?
	 *
	 * @param int $parent_id the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return true if the parent exists
	 */
	public function parentExists($parent_id, $parent_entity='default')
	{
		$parent_entity = $this->getCanonicalEntityId($parent_entity);

		// Check the cache first
		$cache_key = $parent_entity.(int)$parent_id;
		if ( array_key_exists($cache_key, $this->_parent_exists_cache) ) {
			return $this->_parent_exists_cache[$cache_key];
			}

		// First time, so look up the parent
		$entity_table = $this->_entity_table[$parent_entity];
		$entity_id_field = $this->_entity_id_field[$parent_entity];
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select($entity_id_field)->from("#__$entity_table");
		$query->where("$entity_id_field=".(int)$parent_id);
		$db->setQuery($query, 0, 1);
		$result = $db->loadResult();
		if ( $result === null ) {
			$this->_parent_exists_cache[$cache_key] = false;
			}
		else {
			$this->_parent_exists_cache[$cache_key] = (int)$parent_id == (int)$result;
			}

		return $this->_parent_exists_cache[$cache_key];
	}


	/**
	 * Check to see if the parent is published
	 *
	 * @param int $parent_id the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return true if the parent is published
	 */
	public function isParentPublished($parent_id, $parent_entity='default')
	{
		return false;
	}


	/**
	 * Check to see if the parent is archived
	 *
	 * @param int $parent_id the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return true if the parent is archived
	 */
	public function isParentArchived($parent_id, $parent_entity='default')
	{
		return false;
	}


	/**
	 * May the parent be viewed by the user?
	 *
	 * This public function should be called by derived class functions.
	 *
	 * Note that this base class function only determines necessary
	 * conditions. If this function returns FALSE, then viewing is definitely
	 * not permitted. If this function returns TRUE, then the derived classes
	 * also need to check whether viewing the specific content item (eg,
	 * article) is permitted.
	 *
	 * @param int $parent_id the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 * @param object $user_id the user_id to check (optional, primarily for testing)
	 *
	 * @return true if the parent may be viewed by the user
	 */
	public function userMayViewParent($parent_id, $parent_entity='default', $user_id=null)
	{
		JError::raiseError(501, JText::_('ATTACH_NOT_IMPLEMENTED'));
	}


	/** Return true if the attachments should be hidden for this parent
	 *
	 * @param &object &$parent The object for the parent that onPrepareContent gives
	 * @param int $parent_id The ID of the parent the attachment is attached to
	 * @param string $parent_entity the type of entity for this parent type
	 * @param &object &$params The Attachments component parameters object
	 *
	 * Note: this generic version only implements the 'frontpage' option.  All
	 *		 other options should be handled by the derived classes for other
	 *		 content types.
	 *
	 * @return true if the attachments should be hidden for this parent
	 */
	public function attachmentsHiddenForParent(&$parent, $parent_id, $parent_entity, &$params)
	{
		$layout = JRequest::getCmd('layout');

		// Check to see whether the attachments should be hidden on the front page
		$hide_on_frontpage = $params->get('hide_on_frontpage', false);
		if ( $hide_on_frontpage && (JRequest::getVar('view') == 'featured') ) {
			return true;
			}

		// Hide on blog pages?
		$hide_on_blogs = $params->get('hide_on_blogs', false);
		if ( $hide_on_blogs && ($layout == 'blog') ) {
			return true;
			}

		return false;
	}



	/**
	 * Return true if the user may add an attachment to this parent
	 *
	 * (Note that all of the arguments are assumed to be valid; no sanity checking is done.
	 *	It is up to the caller to validate these objects before calling this function.)
	 *
	 * @param int $parent_id The ID of the parent the attachment is attached to
	 * @param string $parent_entity the type of entity for this parent type
	 * @param bool $new_parent If true, the parent is being created and does not exist yet
	 * @param object $user_id the user_id to check (optional, primarily for testing)
	 *
	 * @return true if this user add attachments to this parent
	 */
	public function userMayAddAttachment($parent_id, $parent_entity, $new_parent=false, $user_id=null)
	{
		JError::raiseError(501, JText::_('ATTACH_NOT_IMPLEMENTED'));
	}



	/**
	 * Return true if this user may edit (modify/delete/update) this attachment for this parent
	 *
	 * (Note that all of the arguments are assumed to be valid; no sanity checking is done.
	 *	It is up to the caller to validate the arguments before calling this function.)
	 *
	 * @param &record &$attachment database record for the attachment
	 * @param object $user_id the user_id to check (optional, primarily for testing)
	 *
	 * @return true if this user may edit this attachment
	 */
	public function userMayEditAttachment(&$attachment, $user_id=null)
	{
		JError::raiseError(501, JText::_('ATTACH_NOT_IMPLEMENTED'));
	}



	/**
	 * Return true if this user may delete this attachment for this parent
	 *
	 * (Note that all of the arguments are assumed to be valid; no sanity checking is done.
	 *	It is up to the caller to validate the arguments before calling this function.)
	 *
	 * @param &record &$attachment database record for the attachment
	 * @param object $user_id the user_id to check (optional, primarily for testing)
	 *
	 * @return true if this user may delete this attachment
	 */
	public function userMayDeleteAttachment(&$attachment, $user_id=null)
	{
		JError::raiseError(501, JText::_('ATTACH_NOT_IMPLEMENTED'));
	}



	/**
	 * Return true if this user may change the state of this attachment
	 *
	 * (Note that all of the arguments are assumed to be valid; no sanity checking is done.
	 *	It is up to the caller to validate the arguments before calling this function.)
	 *
	 * @param int $parent_id the ID for the parent object
	 * @param string $parent_entity the type of entity for this parent type
	 * @param int $attachment_creator_id the ID of the creator of the attachment
	 * @param object $user_id the user_id to check (optional, primarily for testing)
	 *
	 * @return true if this user may change the state of this attachment
	 */
	public function userMayChangeAttachmentState($parent_id, $parent_entity,
												 $attachment_creator_id, $user_id=null)
	{
		JError::raiseError(501, JText::_('ATTACH_NOT_IMPLEMENTED'));
	}


	/** Check to see if the user may access (see/download) the attachments
	 *
	 * @param &record &$attachment database record for the attachment
	 * @param object $user_id the user_id to check (optional, primarily for testing)
	 *
	 * @return true if access is okay (false if not)
	 */
	public function userMayAccessAttachment( &$attachment, $user_id=null )
	{
		JError::raiseError(501, JText::_('ATTACH_NOT_IMPLEMENTED'));
	}


}
