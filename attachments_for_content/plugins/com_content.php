<?php
/**
 * Attachments plugins for content
 *
 * @package Attachments
 * @subpackage Attachments_Plugin_for_Content
 *
 * @copyright Copyright (C) 2009-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


/**
 * The class for the Attachments plugin for regular Joomla! content (articles, sections, categories)
 *
 * @package Attachments
 */
class AttachmentsPlugin_com_content extends AttachmentsPlugin
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct('attachments_for_content', 'com_content', 'article');
	}

	/**
	 * Determine the parent entity
	 *
	 * From the view and the class of the parent (row of onPrepareContent plugin),
	 * determine what the entity type is for this entity.
	 *
	 * @param &object &$parent The object for the parent (row) that onPrepareContent gets
	 *
	 * @return the correct entity (eg, 'default', 'section', etc)
	 */
	function determineParentEntity(&$parent)
	{
		static $seen_section = false;

		$view = JRequest::getCmd('view');

		// Handle sections
		if ( $view == 'section' AND get_class($parent) == 'JTableContent') {

			// For some reason, the content plugin can be called multiple
			// times for sections and for categories on the same page,
			// although they all look like sections.  So accept the first one
			// and ignore the rest.
			if ( $seen_section ) {
				return false;
				}
			$seen_section = true;

			return 'section';
			}

		// Handle category calls
		if ( $view == 'category' AND get_class($parent) == 'JTableContent') {
			return 'category';
			}

		// Handle everything else (articles)
		//	 (apparently this is called before parents are displayed so ignore those calls)
		if ( isset($parent->id) ) {
			return 'default';
			}

		return false;
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
		$parent_entity = $this->getCanonicalEntity($parent_entity);

		switch ($parent_entity) {

		case 'section':
		case 'category':
			return parent::getSelectEntityURL($parent_entity);
		break;

		default:
			return "index.php?option=com_content&amp;task=element&amp;tmpl=component";
			}
	}



	/**
	 * Get a URL to view the content article
	 *
	 * @param int $parent_id the ID for this parent object
	 *
	 * @return a URL to view the entity (non-SEF form)
	 */
	function getEntityViewURL($parent_id, $parent_entity = 'default')
	{
		global $mainframe;

		if ( $mainframe->isAdmin() ) {
			$base_url = $mainframe->getSiteURL();
			}
		else {
			$base_url = JURI::base(true) . "/";
			}

		// Return the right thing for each entity
		switch ( $parent_entity ) {

		case 'section' :
			return $base_url . 'index.php?option=com_content&view=section&id=' . $parent_id . '&layout=blog';
			break;

		case 'category':
			return $base_url . 'index.php?option=com_content&view=category&id=' . $parent_id;
			break;

		default:
			return $base_url . 'index.php?option=com_content&view=article&id=' . $parent_id;
			}
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
		global $mainframe;

		// Determine the task
		if ( $mainframe->isAdmin() ) {
			$task = 'add';
			}
		else {
			$task = 'upload';
			}

		// Handle article creation
		$url = "index.php?option=com_attachments&task=$task";
		if ( $parent_id == null ) {
			$url .= "&parent_id=$parent_id,new";
			}
		else {
			$url .= "&parent_id=$parent_id";
			}

		// Build the right URL for each entity
		switch ( $parent_entity ) {

		case 'section' :
		case 'category':
			$parent_entity = $this->getCanonicalEntity($parent_entity);
		$url .= "&parent_type=com_content:$parent_entity&from=$from";
		break;

		default:
			$url .= "&parent_type=com_content&from=$from";
			}

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
		if ( (($parent_entity == 'default') OR ($parent_entity == 'article')) AND
			 (($rtitle_parent_entity == 'default' ) OR ($rtitle_parent_entity == 'article')) ) {
			return true;
			}

		if ( (($parent_entity == 'section') OR ($parent_entity == 'category')) AND
			 ( $parent_entity == $rtitle_parent_entity ) ) {
			return true;
			}

		return false;
	}


	/**
	 * Check to see if the parent is published
	 *
	 * @param int $parent_id is the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return true if the parent is published
	 */
	function isParentPublished($parent_id, $parent_entity='default')
	{
		$published = false;

		$this->loadLanguage();

		$db =& JFactory::getDBO();

		$parent_entity = $this->getCanonicalEntity($parent_entity);
		$parent_entity_name = JText::_($this->getEntityname($parent_entity));

		// Return the right thing for each entity
		switch ( $parent_entity ) {

		case 'section' :
		case 'category':
			$entity_table = $this->_entity_table[$parent_entity];
			$query = "SELECT published FROM #__$entity_table WHERE id='".(int)$parent_id."'";
			$db->setQuery($query);
			$obj = $db->loadObject();
			if ( $db->getErrorNum() ) {
				$errmsg = JText::sprintf('ERROR_INVALID_PARENT_S_ID_N',
										 $parent_entity_name, $parent_id) . ' (ERR 400)';
				JError::raiseError(500, $errmsg);
				}
			if ( is_object( $obj ) ) {
				$published = $obj->published;
				}
			else {
				$published = false;
				}
		break;

		default:

			$query = "SELECT state, publish_up, publish_down FROM #__content WHERE id='".(int)$parent_id."'";
			$db->setQuery($query);
			$article = $db->loadObject();
			if ( $db->getErrorNum() ) {
				$errmsg = JText::sprintf('ERROR_INVALID_PARENT_S_ID_N',
										 $parent_entity_name,  $parent_id) . ' (ERR 401)';
				JError::raiseError(500, $errmsg);
				}
			else {
				// Do this in two steps to keep from upsetting PHP 4
				$now = JFactory::getDate();
				$now = $now->toUnix();

				// Do this in two steps to keep from upsetting PHP 4
				$nullDate = JFactory::getDate($db->getNullDate());
				$nullDate = $nullDate->toUnix();

				if ( $article ) {
					// Do this in two steps to keep from upsetting PHP 4
					$publish_up	  = JFactory::getDate($article->publish_up);
					$publish_up	  = $publish_up->toUnix();

					// Do this in two steps to keep from upsetting PHP 4
					$publish_down = JFactory::getDate($article->publish_down);
					$publish_down = $publish_down->toUnix();

					$published = ( ($article->state == 1) AND
								   ( $now >= $publish_up ) AND
								   ( ($publish_down == $nullDate) OR
									 ($now <= $publish_down ) )
								   );
					}
				else {
					$published = false;
					}
				}
			}

		return $published;
	}


	/**
	 * Check to see if the parent is archived
	 *
	 * @param int $parent_id is the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return true if the parent is archived
	 */
	function isParentArchived($parent_id, $parent_entity='default')
	{
		$archived = false;

		$this->loadLanguage();

		$db =& JFactory::getDBO();

		$parent_entity = $this->getCanonicalEntity($parent_entity);
		$parent_entity_name = JText::_($this->getEntityname($parent_entity));

		// Return the right thing for each entity
		switch ( $parent_entity ) {

		case 'section' :
		case 'category':
			// You apparently cannot archive sections or categories
			break;

		default:

			$query = "SELECT state FROM #__content WHERE id='".(int)$parent_id."'";
			$db->setQuery($query);
			$article = $db->loadObject();
			if ( $db->getErrorNum() ) {
				$errmsg = JText::sprintf('ERROR_INVALID_PARENT_S_ID_N',
										 $parent_entity_name,  $parent_id) . ' (ERR 402)';
				JError::raiseError(500, $errmsg);
				}
			else {
				if ( $article ) {
					$archived = $article->state == -1;
					}
				else {
					$archived = false;
					}
				}
			}

		return $archived;
	}


	/**
	 * May the parent be viewed by the user?
	 *
	 * @param int $parent_id the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return true if the parent may be viewed by the user
	 */
	function userMayViewParent($parent_id, $parent_entity='default')
	{
		// Check general attachments permissions first
		if ( parent::userMayViewParent($parent_id, $parent_entity) == false ) {
			return false;
			}

		$access = 0;
		$table = null;

		$this->loadLanguage();

		$db =& JFactory::getDBO();

		$parent_entity = $this->getCanonicalEntity($parent_entity);
		$parent_entity_name = JText::_($this->getEntityname($parent_entity));

		// Return the right thing for each entity
		switch ( $parent_entity ) {

		case 'section':
			$table = 'sections';
			break;

		case 'category':
			$table = 'categories';
			break;

		default:  // article
			$table = 'content';
			break;
			}

		// Get the item's access level
		$query = "SELECT access from #__$table WHERE id='".(int)$parent_id."'";
		$db->setQuery($query, 0, 1);
		$obj = $db->loadObject();
		if ( $db->getErrorNum() ) {
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_S_ID_N',
									 $parent_entity_name, $parent_id) . ' (ERR 403)';
			JError::raiseError(500, $errmsg);
			}
		if ( is_object( $obj ) ) {
			$access = (int)$obj->access;
			}
		// Assume access is 0 (public) unless specified

		$user =& JFactory::getUser();
		$aid  = $user->get('aid');

		return $access <= $aid;
	}



	/** Return true if the attachments should be hidden for this parent
	 *
	 * @param &object &$parent The object for the parent that onPrepareContent gives
	 * @param int $parent_id The ID of the parent the attachment is attached to
	 * @param string $parent_entity the type of entity for this parent type
	 * @param &object &$params The Attachments component parameters object
	 *
	 * @return true if the attachments should be hidden for this parent
	 */
	function attachmentsHiddenForParent(&$parent, $parent_id, $parent_entity, &$params)
	{
		static $seen_section2 = false;

		// Check for generic options
		if ( parent::attachmentsHiddenForParent($parent, $parent_id, $parent_entity, $params) ) {
			return true;
			}
		$pclass = get_class($parent);

		$this->loadLanguage();

		$parent_entity = $this->getCanonicalEntity($parent_entity);
		$parent_entity_name = JText::_($parent_entity);

		// Make sure we have a valid parent ID
		if ( !$parent_id AND ($parent_entity == 'section' OR $parent_entity == 'category') ) {
			$parent_id = JRequest::getInt('id');
			}
		if ( $parent_id !== 0 ) {
			// parent_id of 0 may be allowed for sections/categories, so don't abort
			if ( $parent_id == null OR $parent_id == '' OR !is_numeric($parent_id) ) {
				$errmsg = JText::sprintf('ERROR_BAD_ENTITY_S_ID', $parent_entity_name) . ' (ERR 404)';
				JError::raiseError(500, $errmsg);
				}
			}

		// Get the options and scan them
		$hide_attachments_for =
			JString::str_ireplace('-', '_', JString::trim($params->get('hide_attachments_for', '')));
		$all_but_article_views = false;
		$always_show_section_attachments = false;
		$always_show_category_attachments = false;
		if ( $hide_attachments_for <> '' ) {
			$hide_specs = explode(',', $hide_attachments_for);
			$view = JRequest::getCmd('view');
			foreach ( $hide_specs as $hide ) {
				if ( JString::trim($hide) == 'all_but_article_views' ) {
					$all_but_article_views = true;
					}
				elseif ( JString::trim($hide) == 'always_show_section_attachments' ) {
					$always_show_section_attachments = true;
					}
				elseif ( JString::trim($hide) == 'always_show_category_attachments' ) {
					$always_show_category_attachments = true;
					}
				}
			}

		// Make sure the parent is valid and get info about it
		$db =& JFactory::getDBO();

		if ( $parent_entity == 'section' AND $pclass == 'JTableContent' ) {

			if ( $seen_section2 ) {
				return true;
				}
			if ( $always_show_section_attachments ) {
				return false;
				}
			if ( $all_but_article_views ) {
				return true;
				}
			$seen_section2 = $parent_id;
			$description = $parent->text;
			$query = "SELECT id from #__sections "
				. "WHERE description=" . $db->Quote($description) . " AND id='".(int)$parent_id."'";
			$db->setQuery($query);
			if ( (int)$parent_id != (int)$db->loadResult() ) {
				return true;
				}
			}

		elseif ( $parent_entity == 'category' AND $pclass == 'JTableContent' ) {

			if ( $always_show_category_attachments ) {
				return false;
				}
			if ( $all_but_article_views ) {
				return true;
				}

			$description = $parent->text;
			$query = "SELECT id from #__categories "
				. "WHERE description=" . $db->Quote($description) . " AND id='".(int)$parent_id."'";
			$db->setQuery($query);
			if ( (int)$parent_id != (int)$db->loadResult() ) {
				return true;
				}
			}

		else {

			// Handle articles
			if ( $parent_id == 0 ) {
				return false;
				}

			// Make sure we have a valid article
			$query = "SELECT created_by, sectionid, catid from #__content "
				. "WHERE id='".(int)$parent_id."'";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			if ( count($rows) == 0 ) {
				$errmsg = JText::sprintf('ERROR_INVALID_PARENT_S_ID_N',
										 $parent_entity_name, $parent_id) . ' (ERR 405)';
				JError::raiseError(500, $errmsg);
				}

			// honor all_but_article_view option
			$view = JRequest::getCmd('view');
			if ( $all_but_article_views ) {
				if ( $view != 'article' ) {
					return true;
					}
				}

			// See if the options apply to this article
			$created_by = (int)$rows[0]->created_by;
			$sectionid = (int)$rows[0]->sectionid;
			$catid = (int)$rows[0]->catid;

			// First, check to see whether the attachments should be hidden for this parent
			if ( $hide_attachments_for <> '' ) {
				$hide_specs = explode(',', $hide_attachments_for);
				$ignore_specs = Array('frontpage', 'blog', 'all_but_article_views',
									  'always_show_section_attachments',
									  'always_show_category_attachments');
				foreach ( $hide_specs as $hide ) {
					if ( in_array(JString::trim($hide), $ignore_specs) ) {
						continue;
						}
					else {
						// We assume it must be section/category specs
						$sect_cat = explode('/', $hide);
						$hide_sect_id = (int)$sect_cat[0];
						$hide_cat_id = -1;
						if ( count($sect_cat) > 1 )
							$hide_cat_id = (int)$sect_cat[1];
						if ( ($hide_cat_id == -1) and ($sectionid == $hide_sect_id) ) {
							return true;
							}
						if ( ($sectionid == $hide_sect_id) and ($catid == $hide_cat_id) ) {
							return true;
							}
						}
					}
				}
			}

		// The default is: attachments are not hidden
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
		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Check who may add attachments
		$who_can_add = $params->get('who_can_add', 'author');

		// A user must be logged in to add attachments
		$user =& JFactory::getUser();
		if ( $user->get('username') == '' ) {
			return false;
			}

		// Exit if no one is allowed to add attachments (make an exception for admins)
		$user_type = $user->get('usertype', false);
		if ( $who_can_add == 'no_one' ) {
			return ($user_type == 'Super Administrator') OR ($user_type == 'Administrator');
			}

		// If who-can-add is 'Editor', do not allow anyone with lower permissions to add attachments
		if (( $who_can_add == 'editor' ) AND
			(( $user_type == 'Registered' ) OR ( $user_type == 'Author' ) )) {
			return false;
			}

		// Check everyone but authors if authors need to be handled separately.
		if ( ( $who_can_add == 'author' ) AND ($user_type != 'Author') AND
			 $user->authorize('com_content', 'add', 'content', 'all') ) {
			// If the user generally has permissions to add content, they qualify.
			// (editor, publisher, admin, etc)
			return true;
			}

		// Check everyone but editors if editors need to be handled separately.
		if ( ( $who_can_add == 'editor' ) AND ($user_type != 'Editor') AND
			 $user->authorize('com_content', 'add', 'content', 'all') ) {
			// If the user generally has permissions to add content, they qualify.
			// (editor, publisher, admin, etc)
			return true;
			}

		// If it is a new parent (article/section/category), check general permissions
		if ( $new_parent ) {
			return $user->authorize('com_content', 'add', 'content', 'all');
			}

		// Make sure the parent is valid
		if ( $parent_id == null OR $parent_id == '' OR !is_numeric($parent_id) ) {
			return false;
			}

		// Handle each entity type
		$parent_entity = $this->getCanonicalEntity($parent_entity);

		switch ( $parent_entity ) {

		case 'section':
		case 'category':
			// Assume only admins can add attachments to sections or categories
			return ($user_type == 'Super Administrator') OR ($user_type == 'Administrator');
			break;

		default:
			// For articles

			// If all logged in should be able to add articles, let them
			if ( $who_can_add == 'logged_in' ) {
				// Anyone who is logged in can add attachments
				// (Can't get here unless the user is logged in)
				return true;
				}

			// Editors can add attachments to any article
			if ( ( $who_can_add == 'editor' ) AND ( $user_type == 'Editor' ) ) {
				return true;
				}

			// Get the creator
			$db =& JFactory::getDBO();
			$query = "SELECT created_by from #__content WHERE id='".(int)$parent_id."'";
			$db->setQuery($query);
			$rows = $db->loadObjectList();
			if ( count($rows) == 0 ) {
				return false;
				}
			$created_by = $rows[0]->created_by;

			// Verify that this user can upload and attach to this parent
			if ( ($who_can_add == 'author') AND ( $user->get('id') == $created_by ) ) {
				// The author of the parent can add attachments.  (In this mode,
				// authors may not add attachments to parents they do not own)
				return true;
				}
			}

		// No one else is allowed to add attachments
		return false;
	}


	/**
	 * Return true if this user may edit (modify/update/delete) this attachment for this parent
	 *
	 * (Note that all of the arguments are assumed to be valid; no sanity checking is done.
	 *	It is up to the caller to validate these objects before calling this function.)
	 *
	 * @param &record &$attachment database reocrd for the attachment
	 * @param int $parent_id The ID of the parent the attachment is attached to
	 * @param &object &$params The Attachments component parameters object
	 *
	 * @return true if this user may edit this attachment
	 */
	function userMayEditAttachment(&$attachment, $parent_id, &$params)
	{
		// If the user generally has permissions to edit all content, they
		// may edit this attachment (editor, publisher, admin, etc)
		$user =& JFactory::getUser();
		if ( $user->authorize('com_content', 'edit', 'content', 'all') ) {
			return true;
			}

		// Handle each entity type

		switch ( $attachment->parent_entity ) {

		case 'section':
		case 'category':
			// Assume only admins can edit attachments to sections or categories
			$user_type = $user->get('usertype', false);
			return ($user_type == 'Super Administrator') OR ($user_type == 'Administrator');
			break;

		default:
			// Check permissions for non-special users
			$user_id = $user->get('id');
			$who_can_add = $params->get('who_can_add','author');
			$attachment_id = $attachment->id;

			if ( $parent_id == 0 ) {
				// Parent is being created, it is not in the content table yet!
				// (So verify that the current user is the one that uploaded the attachment)
				$created_by = $attachment->uploader_id;
				}
			else {
				// Load info about the parent
				$db =& JFactory::getDBO();
				$query = "SELECT created_by from #__content WHERE id='".(int)$parent_id."'";
				$db->setQuery($query);
				$rows = $db->loadObjectList();
				if ( count($rows) == 0 ) {
					return false;
					}
				$created_by = $rows[0]->created_by;
				}
				
			// Verify that this user can edit/delete this attachment for this parent
			if ( $who_can_add == 'logged_in' ) {
				if ( ($user_id == $attachment->uploader_id) OR
					 ($user_id == $created_by) ) {
					// Registered users and authors can only edit attachments if
					//	 they added the attachment or they own the parent
					return true;
					}
				}
			elseif ( $who_can_add == 'author' ) {
				if ( $user_id == $created_by ) {
					// Authors can edit ANY attachments that belong to their parent
					return true;
					}
				}
			}

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

}

?>
