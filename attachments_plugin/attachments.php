<?php
/**
 * Attachments plugin for inserting attachments lists into content
 *
 * @package     Attachments
 * @subpackage  Main_Attachments_Plugin
 *
 * @author      Jonathan M. Cameron <jmcameron@jmcameron.net>
 * @copyright   Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        http://joomlacode.org/gf/project/attachments/frs/
 */

defined('_JEXEC') or die('Restricted access');

/** Load the attachments helper */
require_once(JPATH_SITE.'/components/com_attachments/helper.php');
require_once(JPATH_SITE.'/components/com_attachments/javascript.php');

/** Load the Attachments defines (if available) */
if (file_exists(JPATH_SITE . '/components/com_attachments/defines.php'))
{
	require_once JPATH_SITE . '/components/com_attachments/defines.php';
}
else
{
	// Exit quietly if the attachments component has been removed
	return;
}


/**
 * Attachments plugin
 *
 * @package  Attachments
 * @since    1.3.4
 */
class plgContentAttachments extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param   object  &$subject  The object to observe
	 * @param   array   $config    An array that holds the plugin configuration
	 *
	 * @access  protected
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
		// $row->text .= $msg;
		// $row->introtext .= $msg;
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
		if ( $parent_type == 'com_content' ) {
			if (in_array($parent_entity, Array('featured', 'article'))) {
				return false;
				}
			if (($parent_entity == 'category') AND (isset($row->catid))) {
				// Ignore the callback for articles on category blogs
				return false;
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

		// Get the parent ID
		if(isset($row->id))
		{
			// If the $row has 'id', use it
			$parent_id = (int)$row->id;
		}
		else
		{
			$parent_id = JRequest::getInt('id', null);
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

		// Allow remapping of parent ID (eg, for Joomfish)
		if (jimport('attachments_remapper.remapper')) {
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

		// ??? $row->text .= " [AP text $context]";
		// ??? $row->introtext .= " [AP introtext $context]";

		return true;
	}


	/**
	 * The content plugin that inserts the attachments list into content items
	 *
	 * @param   string   $context  the context of the content being passed to the plugin.
	 * @param   &object  &$row     the content object (eg, article) being displayed
	 * @param   &object  &$params  the parameters
	 * @param   int      $page     the 'page' number
	 *
	 * @return true if anything has been inserted into the content object
	 */
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
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

		// ??? $row->text .= " [OCBD text $context]";
		// ??? $row->introtext .= " [OCBD introtext $context]";

		return;
	}




	/**
	 * Set the parent_id for all attachments that were added to this
	 * content before it was saved the first time.
	 *
	 * This method is called right after the content is saved.
	 *
	 * @param string The context of the content being passed to the plugin.
	 * @param object $article A JTableContent object
	 * @param bool $isNew If the content is newly created
	 *
	 * @return	void
	 *
	 * NOTE: Currently this only supports attachment parents being articles since
	 *		 this will only be invoked when articles are saved.
	 */
	function onContentAfterSave($context, $article, $isNew )
	{
		if ( !$isNew ) {
			// If the article is not new, this step is not needed
			return true;
			}

		$ctxinfo = explode('.', $context);
		$parent_type = $ctxinfo[0];
		$parent_entity = $ctxinfo[1];

		// Special handling for categories
		if ( $parent_type == 'com_categories' ) {
			$parent_type = 'com_content';
			}

		// Get the attachments associated with this newly created object
		// NOTE: We assume that all attachments that have parent_id=null
		//		 and are created by the current user are for this article.
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

		// Change the attachment to the new article!
		JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_attachments/tables');
		$atrow = JTable::getInstance('Attachment', 'AttachmentsTable');

		foreach ($attachments as $attachment) {

			// Change the filename/URL as necessary
			$error_msg = AttachmentsHelper::switch_parent($attachment, null, $article->id);
			if ( $error_msg != '' ) {
				$errmsg = JText::_($error_msg) . ' (ERR 201)';
				JError::raiseError(500, $errmsg);
				}

			// Update the parent info
			$atrow->load($attachment->id);
			$atrow->parent_id = $article->id;
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
