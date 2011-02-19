<?php
/**
 * Attachments plugins for QuickFAQ items
 *
 * @package Attachments
 * @subpackage Attachments_Plugin_for_QuickFAQ
 *
 * @copyright Copyright (C) 2009-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/** Set up to check QuickFAQ ACL */
require_once (JPATH_SITE.DS.'components'.DS.'com_quickfaq'.DS.'classes'.DS.'quickfaq.acl.php');

/**
 * The class for the Attachments plugin for QuickFAQ items
 *
 * @package Attachments
 */
class AttachmentsPlugin_com_quickfaq extends AttachmentsPlugin
{
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct('attachments_for_quickfaq', 'com_quickfaq', 'quickfaq');
	}


	/**
	 * Get a URL to view the FAQ item
	 *
	 * @param int $parent_id the ID for this parent object
	 * @param string $parent_entity the type of entity for this parent type
	 *
	 * @return a URL to view the Quick FAQ item (non-SEF form)
	 */
	function getEntityViewURL($parent_id, $parent_entity='default')
	{
		// Short-circuit if there is no parent ID
		if ( !is_numeric($parent_id) ) {
			return '';
			}

		// Get a suitable Quick FAQ category (pick the first one)
		$db =& JFactory::getDBO();
		$query = "SELECT catid FROM #__quickfaq_cats_item_relations WHERE itemid='".(int)$parent_id."' LIMIT 1";
		$db->setQuery($query);
		$obj = $db->loadObject();
		if ( $db->getErrorNum() ) {
			$this->loadLanguage();
			$errmsg = JText::sprintf('ERROR_UNABLE_TO_FIND_QUICKFAQ_CATEGORY_FOR_ITEM_ID_N', $parent_id) . ' (ERR 1000)';
			JError::raiseError(500, $errmsg);
			}

		// Construct the URL
		return JURI::base(true) . '/index.php?option=com_quickfaq&view=items&cid=' . $obj->catid . '&id=' . $parent_id;
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
		if ( $rtitle_parent_entity == 'quickfaq' ) {
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
	 * @return true if the attachments should be hidden for this parent
	 */
	function attachmentsHiddenForParent(&$parent, $parent_id, $parent_entity, &$params)
	{
		return false;
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
		return true;
	}


	/**
	 * Return true if this user may add an attachment to this parent
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

		// Exit if no one is allowed to add attachments!
		if ( $who_can_add == 'no_one' ) {
			return false;
			}

		//general access check
		$user	=& JFactory::getUser();
		if (!$user->authorize('com_quickfaq', 'add')) {
			return false;
			}

		return true;
	}



	/**
	 * Return true if this user may edit (modify/update/delete) this attachment for this parent
	 * (Note that all of the arguments are assumed to be valid; no sanity checking is done.
	 *	It is up to the caller to validate these objects before calling this function.)
	 *
	 * @param &object &$attachment database reocrd for the attachment
	 * @param int $parent_id The ID of the parent the attachment is attached to
	 * @param &object &$params The Attachments component parameters object
	 *
	 * @return true if this user may edit this attachment
	 */
	function userMayEditAttachment(&$attachment, $parent_id, &$params)
	{
		//general access check
		$user	=& JFactory::getUser();
		if (!($user->authorize('com_quickfaq', 'edit') OR
			  $user->authorize('com_content', 'edit', 'content', 'own'))) {
			return false;
			}

		return true;
	}



	/** Check to see if the user may access (see/download) the attachments
	 *
	 * @param &record &$attachment database record for the attachment
	 *
	 * @return true if access is okay (false if not)
	 */
	function userMayAccessAttachment( &$attachment )
	{
		// Assume anyone can see FAQ items
		return true;
	}

}

?>
