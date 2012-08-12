<?php
/**
 * Attachments search plugin
 *
 * @package Attachments
 * @subpackage Attachments_Search_Plugin
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.plugin.plugin');


/**
 * Attachments Search plugin
 *
 * @package		Attachments
 */
class plgSearchAttachments extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access		protected
	 * @param		object	$subject The object to observe
	 * @param		array	$config  An array that holds the plugin configuration
	 * @since		1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}


	/**
	 * @return array An array of search areas
	 */
	public function onContentSearchAreas()
	{
		static $areas = array(
			'attachments' => 'ATTACH_ATTACHMENTS'
			);
		return $areas;
	}


	/**
	 * Attachments Search method
	 *
	 * The sql must return the following fields that are
	 * used in a common display routine: href, title, section, created, text,
	 * browsernav
	 * @param string Target search string
	 * @param string mathcing option, exact|any|all
	 * @param string ordering option, newest|oldest|popular|alpha|category
	 * @param mixed An array if restricted to areas, null if search all
	 */
	public function onContentSearch($text, $phrase='', $ordering='', $areas=null)
	{
		$user	 = JFactory::getUser();

		// Exit if the search does not include attachments
		if (is_array($areas)) {
			if (!array_intersect( $areas, array_keys( $this->onContentSearchAreas()))) {
				return array();
				}
			}

		// Make sure we have something to search for
		$text = JString::trim( $text );
		if ($text == '') {
			return array();
			}

		// load search limit from plugin params
		$limit = $this->params->def('search_limit', 50);

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$attachParams = JComponentHelper::getParams('com_attachments');
		$secure = $attachParams->get('secure', false);
		$user_field_1 = false;
		if ( JString::strlen($attachParams->get('user_field_1_name', '')) > 0 ) {
			$user_field_1 = true;
			$user_field_1_name = $attachParams->get('user_field_1_name');
			}
		$user_field_2 = false;
		if ( JString::strlen($attachParams->get('user_field_2_name', '')) > 0 ) {
			$user_field_2 = true;
			$user_field_2_name = $attachParams->get('user_field_2_name');
			}
		$user_field_3 = false;
		if ( JString::strlen($attachParams->get('user_field_3_name', '')) > 0 ) {
			$user_field_3 = true;
			$user_field_3_name = $attachParams->get('user_field_3_name');
			}

		$wheres = array();

		// Create the search query
		$db = JFactory::getDBO();

		switch ($phrase)  {

		case 'exact':
			$text	= $db->quote( '%'.$db->getEscaped( $text, true ).'%', false );
			$user_fields_sql = '';
			if ( $user_field_1 )
				$user_fields_sql .= " OR (LOWER(a.user_field_1) LIKE $text)";
			if ( $user_field_2 )
				$user_fields_sql .= " OR (LOWER(a.user_field_2) LIKE $text)";
			if ( $user_field_3 )
				$user_fields_sql .= " OR (LOWER(a.user_field_3) LIKE $text)";

			$where	= "((LOWER(a.filename) LIKE $text)" .
				" OR (LOWER(a.display_name) LIKE $text)" .
				$user_fields_sql .
				" OR (LOWER(a.description) LIKE $text))";
			break;

		default:
			$words	= explode( ' ', $text );
			$wheres = array();
			foreach ($words as $word) {
				$word		= $db->quote( '%'.$db->getEscaped( $word, true ).'%', false );
				$wheres2	= array();
				$wheres2[]	= "LOWER(a.filename) LIKE $word";
				$wheres2[]	= "LOWER(a.display_name) LIKE $word";
				$wheres2[]	= "LOWER(a.url) LIKE $word";
				if ( $user_field_1 )
					$wheres2[] = "LOWER(a.user_field_1) LIKE $word";
				if ( $user_field_2 )
					$wheres2[] = "LOWER(a.user_field_2) LIKE $word";
				if ( $user_field_3 )
					$wheres2[] = "LOWER(a.user_field_3) LIKE $word";
				$wheres2[]	= "LOWER(a.description) LIKE $word";
				$wheres[]	= implode( ' OR ', $wheres2 );
				}
			$where	= '(' . implode( ($phrase == 'all' ? ') AND (' : ') OR ('), $wheres ) . ')';
			break;
			}

		// Set up the sorting
		switch ( $ordering )
		{
		case 'oldest':
			$order = 'a.created ASC';
			break;

		case 'newest':
			$order = 'a.created DESC';
			break;

		case 'alpha':
		default:
			$order = 'a.filename DESC';
		}

		// Load the permissions functions
		$user = JFactory::getUser();
		$user_levels = implode(',', array_unique($user->authorisedLevels()));

		// Construct and execute the query
		$query = $db->getQuery(true);
		$query->select('*')->from('#__attachments AS a');
		$query->where("( $where ) AND a.state = 1");
		$query->where('a.access in ('.$user_levels.')');
		$query->order($order);
		$db->setQuery( $query, 0, $limit );
		$attachments = $db->loadObjectList();

		$count = count( $attachments );

		// See if we are done
		$results = Array();
		if ( $count <= 0 ) {
			return $results;
			}

		// Prepare to get parent info
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();

		// Add the result data to the results of the search
		$k = 0;
		for ( $i = 0; $i < $count; $i++ ) {

			$attachment = $attachments[$i];

			// Get the parent handler
			$parent_type = $attachment->parent_type;
			$parent_entity = $attachment->parent_entity;
			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				// Exit if there is no Attachments plugin to handle this parent_type, ignore it
				continue;
				}
			$parent = $apm->getAttachmentsPlugin($parent_type);

			// Ignore the attachment if the user may not see the parent
			if ( ! $parent->userMayViewParent($attachment->parent_id, $parent_entity) ) {
				continue;
				}

			// Ignore the attachment if the parent is not published
			if ( ! $parent->isParentPublished($attachment->parent_id, $parent_entity) ) {
				continue;
				}

			// Do not add the attachment if the user may not access it
			if ( !$parent->userMayAccessAttachment($attachment)) {
				continue;
				}

			// Add the parent title
			$attachment->parent_title = $parent->getTitle( $attachment->parent_id, $parent_entity );

			// Construct the download URL if necessary
			if ( $secure && $attachment->uri_type == 'file' ) {
				$attachment->href =
					JRoute::_("index.php?option=com_attachments&task=download&id=" . (int)$attachment->id);
				}
			else {
				$attachment->href = $attachment->url;
				}
			if ( $attachment->display_name && (JString::strlen($attachment->display_name) > 0) ) {
				$attachment->title = JString::str_ireplace('&#183;', '.', $attachment->display_name);
				}
			else {
				if ( $attachment->uri_type == 'file' ) {
					$attachment->title = $attachment->filename;
					}
				else {
					$attachment->title = $attachment->url;
					}
				}

			// Set the text to the string containing the search target
			if ( JString::strlen($attachment->display_name) > 0 ) {
				$text = $attachment->display_name .
					" (" . JText::_('ATTACH_FILENAME_COLON') . " " . $attachment->filename . ") ";
				}
			else {
				$text = JText::_('ATTACH_FILENAME_COLON') . " " . $attachment->filename;
				}

			if ( JString::strlen($attachment->description) > 0 ) {
				$text .= " | " . JText::_('ATTACH_DESCRIPTION_COLON') . $attachment->description;
				}

			if ( $user_field_1 && (JString::strlen($attachment->user_field_1) > 0) ) {
				$text .= " | " . $user_field_1_name	 . ": " . $attachment->user_field_1;
				}
			if ( $user_field_2 && (JString::strlen($attachment->user_field_2) > 0) ) {
				$text .= " | " . $user_field_2_name	 . ": " . $attachment->user_field_2;
				}
			if ( $user_field_3 && (JString::strlen($attachment->user_field_3) > 0) ) {
				$text .= " | " . $user_field_3_name	 . ": " . $attachment->user_field_3;
				}
			$attachment->text = $text;
			$attachment->created = $attachment->created;
			$attachment->browsernav = 2;

			$parent_entity_name = JText::_('ATTACH_' . $parent_entity);
			$parent_title = JText::_($parent->getTitle($attachment->parent_id, $parent_entity));

			$attachment->section = JText::sprintf('ATTACH_ATTACHED_TO_PARENT_S_TITLE_S',
										   $parent_entity_name, $parent_title);

			$results[$k] = $attachment;
			$k++;
			}

		return $results;
	}

}
