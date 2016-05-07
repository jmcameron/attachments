<?php
/**
 * Attachments plugin for inserting attachments lists into content
 *
 * @package		Attachments
 * @subpackage	Main_Attachments_Plugin
 *
 * @author		Jonathan M. Cameron <jmcameron@jmcameron.net>
 * @copyright	Copyright (C) 2007-2016 Jonathan M. Cameron, All Rights Reserved
 * @license		http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link		http://joomlacode.org/gf/project/attachments/frs/
 */

defined('_JEXEC') or die('Restricted access');

/** Load the Attachments defines (if available) */
if (file_exists(JPATH_SITE . '/components/com_attachments/defines.php'))
{
	require_once JPATH_SITE . '/components/com_attachments/defines.php';
	require_once(JPATH_SITE . '/components/com_attachments/helper.php');
	require_once(JPATH_SITE . '/components/com_attachments/javascript.php');
}
else
{
	// Exit quietly if the attachments component has been uninstalled or deleted
	return;
}


/**
 * Attachments plugin
 *
 * @package	 Attachments
 * @since	 1.3.4
 */
class plgContentAttachments extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param	object	&$subject  The object to observe
	 * @param	array	$config	   An array that holds the plugin configuration
	 *
	 * @access	protected
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);

		// Save this page's URL
		$uri= JFactory::getURI();
		$return = '&return=' . urlencode(base64_encode(JUri::current() . '?' . $uri->getQuery()));
		$app = JFactory::getApplication();
		$app->setUserState('com_attachments.current_url', $return);

		$this->loadLanguage();
	}

	/**
	 * The content plugin that inserts the attachments list into content items
	 *
	 * @param string The context of the content being passed to the plugin.
	 * @param &object &$row the content object (eg, article) being displayed
	 * @param &object &$params the parameters
	 * @param int $page the 'page' number
	 *
	 * @return true if anything has been inserted into the content object
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{
		// Enable the following four diagnostic lines to see if a component uses onContentPrepare
		// $msg = "<br/>onContentPrepare: CONTEXT: $context,  OBJ: " . get_class($row) . ", VIEW: " . JRequest::getCmd('view');
		// if (isset($row->text)) $row->text .= $msg;
		// if (isset($row->introtext)) $row->introtext .= $msg;
		// return;

		// Set the parent info from the context
		if (strpos($context, '.') === false)
		{
			// Assume the context is the parent_type
			$parent_type = $context;
			$parent_entity = 'default';
		}
		else
		{
			list ($parent_type, $parent_entity) = explode('.', $context, 2);
		}

		// This callback handles everything but articles
		if ( $parent_type == 'com_content' ) 
		{
			if (in_array($parent_entity, Array('featured', 'article'))) {
				return false;
				}
			if ($parent_entity == 'category.title') {
				// Do not add attachments to categtory titles (Joomla 3 only)
				return false;
				}
			if (($parent_entity == 'category') AND (isset($row->catid))) {
				// Ignore the callback for articles on category blogs
				if (version_compare(JVERSION, '3.4.0', 'lt')) {
					return false;
					}
				}

			$parent_entity = 'category';

			// Older versions of Joomla do not deal well with category lists and
			// it is necessary to use the show_attachments callback to display
			// category descriptions in those cases.
			if (version_compare(JVERSION, '2.5.10', 'lt') OR
				(version_compare(JVERSION, '3.0', 'ge') AND version_compare(JVERSION, '3.1', 'lt'))) {
				return false;
				}
		}

		$view = JRequest::getCmd('view');
		$layout = JRequest::getCmd('layout');

		if ( ($parent_type == 'mod_custom') AND ($parent_entity == 'content') AND ($view == 'category') )
		{
			// Do not add attachments to categtory titles (Joomla 3.4+)
			return false;
		}

		// Handle category blog articles specially
		if (($context == 'com_content.category') AND ($view == 'category') AND ($layout == 'blog')) {
			if (isset($row->id) and is_numeric($row->id)) {
				$parent_entity = 'article';
				}
			}

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			// Exit quietly if there is no Attachments plugin to handle this parent_type
			return false;
			}
		$parent = $apm->getAttachmentsPlugin($parent_type);

		// If this attachments plugin is disabled, skip it
		if ( ! $apm->attachmentsPluginEnabled($parent_type) ) {
			return false;
			}

		// Get the parent ID
		$parent_id = null;
		if (isset($row->id) and is_numeric($row->id))
		{
			// If the $row has 'id', just use it
			$parent_id = (int)$row->id;
		}
		else if ($parent_entity == 'category')
		{
			$db = JFactory::getDBO();
			$description = $row->text;
			$query = $db->getQuery(true);
			$query->select('id')->from('#__categories');
			$query->where('description=' . $db->quote($description));
			$db->setQuery($query, 0, 1);
			$result = $db->loadResult();
			if ($result) {
				$parent_id = (int)$result;
				}
		}

		// Let the attachment pluging try to figure out the id
		if ( $parent_id === null )
		{
			$parent_id = $parent->getParentId($row);
		}

		if ( $parent_id === null )
		{
			return false;
		}

		// Load the language
		$lang = JFactory::getLanguage();
		$lang->load('plg_content_attachments', dirname(__FILE__));

		// Set up the refresh behavior
		AttachmentsJavascript::setupJavascript();

		// Always include the hide rule (since it may be needed to hide the custom tags)
		JHtml::stylesheet('com_attachments/attachments_hide.css', Array(), true);

		// Allow remapping of parent ID (eg, for Joomfish)
		if (jimport('attachments_remapper.remapper'))
		{
			$parent_id = AttachmentsRemapper::remapParentID($parent_id, $parent_type, $parent_entity);
		}

		// Exit if we should not display attachments for this parent
		if ( $parent->attachmentsHiddenForParent($row, $parent_id, $parent_entity) ) {
			return false;
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$attachParams = JComponentHelper::getParams('com_attachments');

		// Make sure we should be showing the category attachments
		$always_show_category_attachments = $attachParams->get('always_show_category_attachments', false);
		$all_but_article_views = $attachParams->get('hide_except_article_views', false);
		if ( $all_but_article_views && !$always_show_category_attachments ) {
			return false;
			}

		// Add the attachments list
		$parent->insertAttachmentsList($row, $parent_id, $parent_entity);

		// if (isset($row->text)) $row->text .= " [AP text $context]";
		// if (isset($row->introtext)) $row->introtext .= " [AP introtext $context]";

		return true;
	}


	/**
	 * The content plugin that inserts the attachments list into content items
	 *
	 * @param	string	 $context  the context of the content being passed to the plugin.
	 * @param	&object	 &$row	   the content object (eg, article) being displayed
	 * @param	&object	 &$params  the parameters
	 * @param	int		 $page	   the 'page' number
	 *
	 * @return true if anything has been inserted into the content object
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		$view = JRequest::getCmd('view');
		$layout = JRequest::getCmd('layout');
		if (($context == 'com_content.category') AND ($view == 'category') AND ($layout == 'blog')) {
			// Use onContentPrepare for category blog articles for Joomla 3.4+
			if (version_compare(JVERSION, '3.4', 'ge')) {
				return false;
				}
			}

		// Set the parent info from the context
		if (strpos($context, '.') === false)
		{
			// Assume the context is the parent_type
			$parent_type = $context;
			$parent_entity = '';
		}
		else
		{
			list ($parent_type, $parent_entity) = explode('.', $context, 2);
		}

		// ??? Do we need to filter to ensure only articles use this callback?

		// Load the language
		$lang = JFactory::getLanguage();
		$lang->load('plg_content_attachments', dirname(__FILE__));

		// Add the refresh javascript
		AttachmentsJavascript::setupJavascript();

		// Always include the hide rule (since it may be needed to hide the custom tags)
		JHtml::stylesheet('com_attachments/attachments_hide.css', array(), true);

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();

		if (!$apm->attachmentsPluginInstalled($parent_type))
		{
			// Exit quietly if there is no Attachments plugin to handle this parent_type
			return false;
		}
		$parent = $apm->getAttachmentsPlugin($parent_type);

		// If this attachments plugin is disabled, skip it
		if (!$apm->attachmentsPluginEnabled($parent_type))
		{
			return false;
		}

		// Figure out the parent entity
		$parent_entity = $parent->determineParentEntity($row);

		if (!$parent_entity)
		{
			return false;
		}

		// Get the parent ID
		$parent_id = null;
		if (isset( $row->id ) && ($row->id > 0)) {
			$parent_id = (int) $row->id;
		} else {
			$parent_id = $parent->getParentId($row);
		}

		// Exit if there is no parent
		if ($parent_id === false)
		{
			return false;
		}

		// Allow remapping of parent ID (eg, for Joomfish)
		if (jimport('attachments_remapper.remapper'))
		{
			$parent_id = AttachmentsRemapper::remapParentID($parent_id, $parent_type, $parent_entity);
		}

		// Exit if we should not display attachments for this parent
		if ($parent->attachmentsHiddenForParent($row, $parent_id, $parent_entity))
		{
			return false;
		}

		// Add the attachments list
		$parent->insertAttachmentsList($row, $parent_id, $parent_entity);

		// ??? if (isset($row->text)) $row->text .= " [OCBD text $context]";
		// ??? if (isset($row->introtext)) $row->introtext .= " [OCBD introtext $context]";

		return;
	}




	/**
	 * Set the parent_id for all attachments that were added to this
	 * content before it was saved the first time.
	 *
	 * This method is called right after the content is saved.
	 *
	 * @param string The context of the content being passed to the plugin.
	 * @param object $item A JTableContent object
	 * @param bool $isNew If the content is newly created
	 *
	 * @return	void
	 */
	function onContentAfterSave($context, $item, $isNew )
	{
		if ( !$isNew ) {
			// If the item is not new, this step is not needed
			return true;
			}

		$ctxinfo = explode('.', $context);
		$parent_type = $ctxinfo[0];
		$parent_entity = $ctxinfo[1];

		// Special handling for categories
		if ( $parent_type == 'com_categories' ) {
			$parent_type = 'com_content';
			}

		// Get the attachments associated with this newly created item.
		// NOTE: We assume that all attachments that have parent_id=null
		//		 and are created by the current user are for this item.
		$user = JFactory::getUser();
		$user_id = $user->get('id');

		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__attachments');
		$query->where('created_by=' . (int) $user_id . ' AND parent_id IS NULL');
		$db->setQuery($query);
		$attachments = $db->loadObjectList();
		if ( $db->getErrorNum() ) {
			$errmsg = $db->stderr() . ' (ERR 200)';
			JError::raiseError(500, $errmsg);
			}

		// Exit if there are no new attachments
		if ( count($attachments) == 0 ) {
			return true;
			}

		// Change the attachment to the new content item!
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_attachments/tables');
		$atrow = JTable::getInstance('Attachment', 'AttachmentsTable');

		foreach ($attachments as $attachment) {

			// Fix for odd issue; on some systems, something is changing the
			// parent_type in or out of the database
			if ( ($attachment->parent_type) == 'com_media' AND
				 ($attachment->parent_entity) == 'article' ) {
				// Override/fix the incorrect parent type
				$attachment->parent_type = 'com_content';
				}

			// Change the filename/URL as necessary
			$error_msg = AttachmentsHelper::switch_parent($attachment, null, $item->id);
			if ( $error_msg != '' ) {
				$errmsg = JText::_($error_msg) . ' (ERR 201)';
				JError::raiseError(500, $errmsg);
				}

			// Update the parent info
			$atrow->load($attachment->id);
			$atrow->parent_id = $item->id;
			$atrow->parent_type = $parent_type;
			$atrow->filename_sys = $attachment->filename_sys;
			$atrow->url = $attachment->url;

			if ( !$atrow->store() ) {
				$errmsg = $attachment->getError() . ' (ERR 202)';
				JError::raiseError(500, $errmsg);
				}
			}

		return true;
	}


}
