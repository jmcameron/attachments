<?php
/**
 * Attechments for extensions
 *
 * @package Attachments
 * @subpackage Attachments_Plugin_Framework
 *
 * @copyright Copyright (C) 2009-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
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
 * plugin.  For instance for content articles or categories, the parent type
 * is 'com_content'.  The parent type is simply the name of the component
 * involved (eg, 'com_content').  The derived attachments plugin class (such
 * as 'AttachmentsPlugin_com_content') should be defined in the main file for
 * the plugin (eg, attachments_for_conent.php).
 *
 * Derived attachments plugin classes must also include the following lines of
 * code after the class definition to register the derived class with the
 * Attachments plugin manager:
 *
 *   $apm =& getAttachmentsPluginManager();
 *   $apm->addParentType('com_content');
 *
 * where 'com_content' should be replaced by the name of the appropriate
 * parent type (component).
 *
 * @package Attachments
 */
class AttachmentsPlugin extends JObject
{
	/** Name of the extension (eg, 'attachments_for_content')
	 * (must set it in constructor to load language files)
	 */
	var $_name = null;

	/** Name for the default parent_entity type
	 *
	 * Note that this name will be used for a directory for attachments entries
	 * and should not contain any spaces.  It should correspond to the default
	 * entity.	For com_content, it will be 'article';
	 */
	var $_default_entity = null;

	/** Parent_type: com_content, com_quickfaq, etc
	 */
	var $_parent_type = null;

	/** known entities
	 */
	var $_entities = null;


	/** An associative array of entity names
	 */
	var $_entity_name = Array();

	/** An associative array of aliases for the default entity name
	 *
	 *	For each type of attachments plugin, there will be a default
	 *	entity types.  For com_content, the default is 'article'.  If
	 *	the $default value for the function calls below is omitted,
	 *	the entity is assumed to be 'article'.	In some cases, the
	 *	actual proper name of the entity will be availalbe and will be
	 *	passed in to the $default argument.	 It is important that the
	 *	plugin code recognizes that the entity 'article' is an alias
	 *	for 'default'.	This array allows a simple associative array
	 *	lookup to transform 'article' to 'default'.
	 */
	var $_entity_alias = null;

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


	/**
	 * Constructor - Load the informaton from the INI file
	 *
	 * @param string $extension_name Name of the extension (eg, 'extensions_for_content')
	 * @param string $parent_type Name of the parent type (eg, 'com_content')
	 * @param string $default_name Name of default entity for the parent_type
	 *
	 */
	function __construct($extension_name, $parent_type, $default_name)
	{
		parent::__construct();

		$this->_parent_type = $parent_type;
		$this->_name = $extension_name;
		$this->_default_entity = $default_name;

		// Set up the default alias
		$this->_entity_alias = Array( 'default' => 'default' );

		/* OBSOLETE

		// Since the registry loader does not process sections,
		// we must invoke the INI handler directly
		$handler =& JRegistryFormat::getInstance('INI');

		// Read the file
		jimport('joomla.filesystem.file');
		$file = dirname(__FILE__).DS.'plugins'.DS.$parent_type.'.ini';
		$data = JFile::read($file);

		// Construct an object with the info from the INI file
		$ns = $handler->stringToObject($data, $process_sections = true );

		// Process the supported entity types
		foreach ( array_keys(get_object_vars($ns)) as $et ) {

			$et = JString::strtolower($et);

			// Note it
			$this->_entities[] = $et;

			// Save basic info
			$this->_entity_name[$et]  = JString::trim($ns->$et->entity);
			$this->_entity_table[$et] = JString::trim($ns->$et->entity_table);
			$this->_entity_title_field[$et] = JString::trim($ns->$et->entity_title_field);

			// Add the entity id field name
			if ( isset($ns->$et->entity_id_field) ) {
				$this->_entity_id_field[$et] = JString::trim($ns->$et->entity_id_field);
				}
			else {
				$this->_entity_id_field[$et] = 'id';
				}

			// Process aliases if given
			if ( isset($ns->$et->alias) ) {
				foreach ( explode(',', JString::trim($ns->$et->alias)) as $alias ) {
					// Do not check for collisions
					$this->_entity_alias[ JString::trim(JString::strtolower($alias))] = $et;
					}
				}
			}
		*/
	}


	/**
	 * Loads the plugin language file
	 *
	 * @return	boolean True, if the file has successfully loaded.
	 */
	function loadLanguage()
	{
		if ( $this->_language_loaded ) {
			return true;
			}

		$lang =& JFactory::getLanguage();

		// Load the plugin-specifc language file
		$okay = $lang->load('plg_attachments_' . $this->_name,
							JPATH_PLUGINS.DS.'attachments'.DS.$this->_name );

		if ( $okay ) {
			$this->_language_loaded = true;
			}

		return $okay;
	}


	/**
	 * Return the parent entity / row ID
	 *
	 * This will only be called by the main attachments 'onPrepareContent'
	 * plugin if $row does not have an id
	 *
	 * @param object &row the article or content item (potential attachment parent)
	 *
	 * @return id if found, false if this is not a valid parent
	 */
	function getParentId(&$row)
	{
		return JRequest::getInt('id', false);
	}


	/**
	 * Return the component name for this parent object
	 *
	 * @return the component name for this parent object
	 */
	function getParentType()
	{
		return $this->_parent_type;
	}


	/**
	 * Return a string of the where clause for filter
	 *
	 * @param $parent_state string the state ('ALL', 'PUBLISHED', 'UNPUBLISHED', 'ARCHIVED', 'NONE')
	 * @param $filter_entity string the entity filter ('ALL', 'ARTICLE', 'CATEGORY', etc)
	 *
	 * @return an array of (join_clause, where_clause) items
	 */
	function getParentPublishedFilter($parent_state, $filter_entity)
	{
		return '';
	}


	/**
	 * Determine the parent entity
	 *
	 * From the view and the class of the parent (row of onPrepareContent plugin),
	 * determine what the entity type is for this entity.
	 *
	 * Derived classes must overrride this if they support more than 'default' entities.
	 *
	 * @param &object &$parent The object for the parent (row) that onPrepareContent gets
	 *
	 * @return the correct entity (eg, 'default', 'section', etc) or false if this entity should not be displayed.
	 */
	function determineParentEntity(&$parent)
	{
		return 'default';
	}


	/**
	 * Return the array of entity IDs for all content items supported by this parent object
	 *
	 * @return the array of entities supported by this parent object
	 */
	function getEntities()
	{
		return $this->_entity_name;
	}


	/**
	 * Get the default entity ID
	 * @return string the default entity ID
	 */
	function getDefaultEntity()
	{
		return $this->_default_entity;
	}


	/**
	 * Get the canonical extension entity name
	 *
	 * This is the canonical entity ID for content item to which attachments
	 * will be added.  Note that each content type ($option) may support
	 * several different entities (for attachments) and some entities may have
	 * more than one name (hence the 'alias' entry in the .ini file.
	 *
	 * @param string $parent_entity the type of entity for this parent type (potentially an alias)
	 *
	 * @return the canonical extension entity
	 */
	function getCanonicalEntity($parent_entity)
	{
		$parent_entity = JString::strtolower($parent_entity);

		// It it is a known entity, just return it
		if ( in_array($parent_entity, $this->_entities) ) {
			return $parent_entity;
			}

		// Check aliases
		if ( !array_key_exists($parent_entity, $this->_entity_alias) ) {
			$lang =& JFactory::getLanguage();
			$lang->load('plg_attachments_attachments_plugin_framework', JPATH_ADMINISTRATOR);
			$errmsg = JText::sprintf('ERROR_INVALID_ENTITY_S_FOR_PARENT_S',
									 $parent_entity, $parent_type) . ' (ERR 300)';
			JError::raiseError(500, $errmsg);
			}
		else {
			return $this->_entity_alias[$parent_entity];
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
	 * @return string the directory name for this entity (with trailing DS!)
	 */
	function getAttachmentPath($parent_entity, $parent_id, $attachment_id)
	{
		$parent_entity = $this->getCanonicalEntity($parent_entity);

		if ( $parent_entity == 'default' ) {
			$base = $this->_default_entity;
			}
		else {
			if ( array_key_exists($parent_entity, $this->_entity_alias) ) {
				$base = $this->_entity_alias[$parent_entity];
				}
			else {
				$base = $parent_entity;
				}
			}

		$path = sprintf("%s%s%d%s", $base, DS, $parent_id, DS);

		return JString::strtolower($path);
	}



	/**
	 * Get the proper extension entity name (eg, 'article' instead of 'default')
	 *
	 * This is the content item to which attachments will be added.
	 * For com_content, this would be 'article' by default
	 *
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return the proper extension entity name
	 */
	function getEntityName($parent_entity)
	{
		return $this->_entity_name[ $this->getCanonicalEntity($parent_entity) ];
	}



	/**
	 * Get the name or title for the specified object
	 *
	 * @param int $parent_id is the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return the name or title for the specified object
	 */
	function getTitle($parent_id, $parent_entity='default')
	{
		// Short-circuit if there is no parent ID
		if ( !is_numeric($parent_id) ) {
			return '';
			}

		$parent_entity = $this->getCanonicalEntity($parent_entity);

		$parent_entity_name = $this->_entity_name[$parent_entity];
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
		$db =& JFactory::getDBO();
		$query = "SELECT $entity_title_field FROM #__$entity_table WHERE $entity_id_field='".(int)$parent_id."'";
		$db->setQuery($query);
		if ( $db->getErrorNum() ) {
			$this->loadLanguage();
			$errmsg = JText::sprintf('ERROR_GETTING_PARENT_S_TITLE_FOR_ID_N',
									 $parent_entity_name, $parent_id) . ' (ERR 301)';
			JError::raiseError(500, $errmsg);
			}
		else {
			$title = $db->loadResult();
			}

		return $title;
	}


	/**
	 * Return an array of entity items (with id,title pairs for each item)
	 *
	 * @param string $parent_entity the type of entity to search for
	 * @param string $filter filter the results for matches for this filter string
	 *
	 * @return the array of entity id,title pairs
	 */
	function getEntityItems($parent_entity='default', $filter='')
	{
		$db =& JFactory::getDBO();

		$parent_entity = $this->getCanonicalEntity($parent_entity);
		$parent_entity_name = $this->_entity_name[$parent_entity];
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
		$query = "SELECT $entity_id_field,$entity_title_field FROM #__$entity_table";
		if ( $filter ) {
			$filter = $db->Quote( '%'.$db->getEscaped( $filter, true ).'%', false );
			$query .= ' WHERE ' . $entity_title_field . ' LIKE ' . $filter;
			}
		if ( $order ) {
			if ( $order == 'title' ) {
				$query .= " ORDER BY $entity_title_field " . $order_Dir;
				}
			else if ( $order == 'id' ) {
				$query .= " ORDER BY $entity_id_field " . $order_Dir;
				}
			else {
				// Ignore unrecognized columns
				}
			}

		// Do the query
		$db->setQuery($query);
		if ( $db->getErrorNum() ) {
			$this->loadLanguage();
			$errmsg = JText::sprintf('ERROR_GETTING_LIST_OF_ENTITY_S_ITEMS',
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
	function getSelectEntityURL($parent_entity='default')
	{
		// Add on the parent type and entity
		$entity = "&amp;parent_type=" . $this->_parent_type;
		if ( $parent_entity != 'default' ) {
			$entity .= ':' . $parent_entity;
			}

		return "index.php?option=com_attachments&amp;task=selectEntity" . $entity . "&amp;tmpl=component";
	}


	/**
	 * Get a URL to view the entity
	 *
	 * @param int $parent_id the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return a URL to view the entity (non-SEF form)
	 */
	function getEntityViewURL($parent_id, $parent_entity='default')
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
	function getEntityAddUrl($parent_id, $parent_entity='default', $from='closeme')
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
	 * Note: this function assumes that the parent_id's match
	 *
	 * @param string $parent_entity parent entity for the parent of the list
	 * @param string $rtitle_parent_entity the entity of the candidate attachment list title (from params)
	 *
	 * @return true if the custom title should be used
	 */
	function checkAttachmentsListTitle($parent_entity, $rtitle_parent_entity)
	{
		return false;
	}

	/**
	 * Get the title for the attachments list for this parent
	 *
	 * @param string $title The untranslated title token (either 'ATTACHMENTS_TITLE' or 'EXISTING_ATTACHMENTS')
	 * @param &object &$params The Attachments component parameters object
	 * @param int $parent_id the ID for the parent entity object (null if the parent does not exist)
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return the translated title string
	 */
	function attachmentsListTitle($title, &$params, $parent_id, $parent_entity='default')
	{
		$rtitle_str = $params->get('attachments_titles', '');
		if ( ($title != 'EXISTING_ATTACHMENTS') AND ($rtitle_str != '') ) {
			$rtitle_list = preg_split("[\n|\r]", $rtitle_str);
			foreach ($rtitle_list as $rtitle) {
				if ( preg_match('|^([0-9]+)\s*([^$]+)$|', $rtitle, $match) ) {
					// process:	 3 new title
					// NOTE: This form only applies to articles and will be ignored for anything else
					if ( (int)$parent_id != (int)$match[1] ) {
						continue;
						}
					if ( ($this->_parent_type == 'com_content') AND
						 (($parent_entity == 'default') OR ($parent_entity == 'article')) ) {
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
	function parentExists($parent_id, $parent_entity='default')
	{
		// Look up the parent
		$parent_entity = $this->getCanonicalEntity($parent_entity);
		$entity_table = $this->_entity_table[$parent_entity];
		$entity_id_field = $this->_entity_id_field[$parent_entity];
		$db =& JFactory::getDBO();
		$query = "SELECT $entity_id_field FROM #__$entity_table WHERE $entity_id_field='".(int)$parent_id."' LIMIT 1";
		$db->setQuery($query);
		if ( $db->loadResult() === null ) {
			return false;
			}
		else {
			return (int)$parent_id == (int)$db->loadResult();
			}
	}


	/**
	 * Check to see if the parent is published
	 *
	 * @param int $parent_id the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return true if the parent is published
	 */
	function isParentPublished($parent_id, $parent_entity='default')
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
	function isParentArchived($parent_id, $parent_entity='default')
	{
		return false;
	}


	/**
	 * May the parent be viewed by the user?
	 *
	 * This function should be called by derived class functions.
	 *
	 * Note that this base class function only determines necessary
	 * conditions. If this function returns FALSE, then viewing is definitely
	 * not permitted. If this function returns TRUE, then the derived classes
	 * also need to check whether viewing the specific content item (eg,
	 * article) is permitted.
	 *
	 * @param int $parent_id the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return true if the parent may be viewed by the user
	 */
	function userMayViewParent($parent_id, $parent_entity='default')
	{
		$user =& JFactory::getUser();

		// Admins can always see everything!
		$user_type = $user->get('usertype', false);
		if ( ($user_type == 'Super Administrator') OR ($user_type == 'Administrator') ) {
			return true;
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');
		$who_can_see = $params->get('who_can_see', 'logged_in');

		// Check the various options
		if ( $who_can_see == 'no_one' ) {
			return false;
			}

		if ( $who_can_see == 'anyone' ) {
			return true;
			}

		$logged_in = $user->get('username') <> '';
		if ( ($who_can_see == 'logged_in') AND $logged_in ) {
			return true;
			}

		return false;
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
	function attachmentsHiddenForParent(&$parent, $parent_id, $parent_entity, &$params)
	{
		// Check to see whether the attachments should be hidden on the front page
		$hide_attachments_for =
			JString::str_ireplace('-', '_', JString::trim($params->get('hide_attachments_for', '')));
		if ( $hide_attachments_for <> '' ) {
			$hide_specs = explode(',', $hide_attachments_for);
			foreach ( $hide_specs as $hide ) {
				if ( JString::trim($hide) == 'frontpage' ) {
					$view = JRequest::getCmd('view');
					if ( $view == 'frontpage' ) {
						return true;
						}
					}
				if ( JString::trim($hide) == 'blog' ) {
					$layout = JRequest::getCmd('layout');
					if ( $layout == 'blog' ) {
						return true;
						}
					}
				}
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
	 *
	 * @return true if this user add attachments to this parent
	 */
	function userMayAddAttachment($parent_id, $parent_entity, $new_parent=false)
	{
		return false;
	}



	/**
	 * Return true if this user may edit (modify/delete/update) this attachment for this parent
	 *
	 * (Note that all of the arguments are assumed to be valid; no sanity checking is done.
	 *	It is up to the caller to validate the arguments before calling this function.)
	 *
	 * @param &record &$attachment database record for the attachment
	 * @param int $parent_id The ID of the parent the attachment is attached to
	 * @param &object &$params The Attachments component parameters object
	 *
	 * @return true if this user may edit this attachment
	 */
	function userMayEditAttachment(&$attachment, $parent_id, &$params)
	{
		return false;
	}



	/** Check to see if the user may access (see/download) the attachments
	 *
	 * @param &record &$attachment database record for the attachment
	 *
	 * @return true if access is okay (false if not)
	 */
	function userMayAccessAttachment( &$attachment )
	{
		return false;
	}


	/**
	 * Add the permissions to the array of attachments data
	 *
	 * @param &array &$attachments An array of attachments for an parent from a DB query.
	 * @param int $parent_id the id of the parent
	 *
	 * @return true if some attachments should be visible, false if none should be visible
	 *
	 * This function adds the following boolean fields to each attachment row:
	 *	   - 'user_may_see'
	 *	   - 'user_may_edit'
	 */
	function addPermissions( &$attachments, $parent_id )
	{
		// Make sure we have a valid parent ID
		if ( $parent_id === null OR $parent_id === '' OR !is_numeric($parent_id) ) {
			$this->loadLanguage();
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_S_ID_N',
									 $this->_parent_type, $parent_id) . ' (ERR 303)';
			JError::raiseError(500, $errmsg);
			}

		// If there are no attachments, don't do anything
		if ( count($attachments) == 0 ) {
			return false;
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Process each attachment
		$user =& JFactory::getUser();
		$logged_in = $user->get('username') <> '';
		$some_visible = false;
		for ($i=0, $n=count($attachments); $i < $n; $i++) {
			$attach =& $attachments[$i];

			$attach->user_may_see = false;
			$attach->user_may_edit = false;

			// Determine if the user may edit this attachment
			//	(Nobody may edit attachments without being logged in)
			if ( $logged_in ) {
				if ( $parent_id === 0 ) {
					$attach->user_may_see = true;
					$attach->user_may_edit = true;
					}
				else {
					$attach->user_may_edit =
						$this->userMayEditAttachment($attach, $parent_id, $params);
					}
				}

			// Determine if the user may see the attachment
			$who_can_see = $params->get('who_can_see', 'logged_in');
			$secure = $params->get('secure', false);

			if ( ( $who_can_see == 'anyone' ) OR
				 ( ($who_can_see == 'logged_in') AND $logged_in ) OR
				 ( $secure AND ($who_can_see == 'logged_in') AND
				   $params->get('secure_list_attachments', false) ) ) {
				$attach->user_may_see = true;
				$some_visible = true;
				}
			}

		return $some_visible;
	}

}

?>
