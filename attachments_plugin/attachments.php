<?php
/**
 * Attachments plugin for inserting attachments lists into content
 *
 * @package Attachments
 * @subpackage Main_Attachments_Plugin
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

/** Load the Attachments defines (if available) */
if (file_exists(JPATH_SITE.'/components/com_attachments/defines.php'))
{
	require_once(JPATH_SITE.'/components/com_attachments/defines.php');
}
else
{
	// Exit quietly if the attachments component has been removed
	return;
}


/**
 * Attachments plugin
 *
 * @package		Attachments
 */
class plgContentAttachments extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @access		protected
	 * @param		object	$subject The object to observe
	 * @param		array	$config	 An array that holds the plugin configuration
	 * @since		1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
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
	public function onContentBeforeDisplay($context, &$row, &$params, $page = 0)
	{
		$uri = JFactory::getURI();

		list ($parent_type, $parent_entity) = explode('.', $context, 2);

		// Figure out the name of the text field
		if ( isset($row->text) ) {
			$text_field_name = 'text';
			}
		elseif ( isset($row->fulltext) ) {
			$text_field_name = 'fulltext';
			}
		elseif ( isset($row->introtext) ) {
			$text_field_name = 'introtext';
			}
		else {
			// Unrecognized
			return false;
			}

		// In some cases, we know what the text_field_name should be
		$option = JRequest::getCmd('option');
		$view = JRequest::getCmd('view');
		$layout = JRequest::getCmd('layout');
		if (isset($row->introtext) AND $option == 'com_content' AND $view == 'category' AND $layout == 'blog')
		{
			$text_field_name = 'introtext';
		}
		if (isset($row->introtext) AND $option == 'com_content' AND $view == 'featured')
		{
			$text_field_name = 'introtext';
		}

		// Load the language
		$lang = JFactory::getLanguage();
		$lang->load('plg_content_attachments', dirname(__FILE__));

		// Always include the hide rule (since it may be needed to hide the custom tags)
		require_once(JPATH_SITE.'/components/com_attachments/helper.php');
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments1.css' );

		// Add the refresh javascript
		$doc = JFactory::getDocument();
		JHTML::_('behavior.mootools');
		$js_path = $uri->root(true) . '/plugins/content/attachments/attachments_refresh.js';
		$doc->addScript( $js_path );

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

		// Figure out the parent entity
		$parent_entity = $parent->determineParentEntity($row);
		if ( !$parent_entity ) {
			return false;
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$attachParams = JComponentHelper::getParams('com_attachments');

		// Get the desired placement
		$attachments_placement = $attachParams->get('attachments_placement', 'end');
		if ( $attachments_placement == 'disabled_nofilter' ) {
			return false;
			}

		// Get some of the options
		$user = JFactory::getUser();
		$logged_in = $user->get('username') <> '';
		$user_type = $user->get('usertype', false);
		$parent_id = null;
		if ( isset( $row->id ) && ($row->id > 0) ) {
			$parent_id = (int)$row->id;
			}
		else {
			$parent_id = $parent->getParentId($row);
			}
		if ( $parent_id === false ) {
			return false;
			}

		// exit if we should not display attachments for this parent
		if ( $parent->attachmentsHiddenForParent($row, $parent_id, $parent_entity, $attachParams) ) {
			return false;
			}

		// See whether we can display the links to add attachments
		$user_can_add = $parent->userMayAddAttachment($parent_id, $parent_entity);

		// Determine where we are
		$from = JRequest::getCmd('view');
		$Itemid = JRequest::getInt( 'Itemid', 1);

		// Get the attachments tag, if present
		$attachments_tag = '';
		$attachments_tag_args = '';
		$match = false;
		if ( JString::strpos($row->$text_field_name, '{attachments') ) {
			if ( preg_match('@(<span class="hide">)?{attachments([ ]*:*[^}]+)?}(</span>)?@',
							$row->$text_field_name, $match) ) {
				$attachments_tag = true;
				}
			if ( isset($match[1]) && $match[1] ) {
				$attachments_tag_args_raw = $match[1];
				$attachments_tag_args = ltrim($attachments_tag_args_raw, ' :');
				}
			if ( $attachments_tag ) {
				$attachments_tag = $match[0];
				}
			}

		// Construct the attachment list (if appropriate)
		$html = '';
		$attachments_list = false;
		$add_attachement_btn = false;
		$attachments_list =
			$this->_attachmentsListHTML($parent_type, $parent_id, $parent_entity, $user_can_add, $Itemid, $from);

		// If the attachments list is empty, insert an empty div for it
		if ( $attachments_list == '' ) {
			$class_name = $attachParams->get('attachments_table_style', 'attachmentsList');
			$div_id = 'attachmentsList' . '_' . $parent_type . '_' . $parent_entity	 . '_' . (string)$parent_id;
			$attachments_list = "\n<div class=\"$class_name\" id=\"$div_id\"></div>\n";
			}

		$html .= $attachments_list;

		if ( $html || $user_can_add ) {
			// Add the style sheet
			AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css' );
			}

		// Construct the add-attachments button, if appropriate
		$hide_add_attachments_link = $attachParams->get('hide_add_attachments_link', 0);
		if ( $user_can_add && !$hide_add_attachments_link ) {
			$add_attachments_btn =
				$this->_attachmentButtonsHTML($parent_type, $parent_id, $parent_entity, $Itemid, $from);
			$html .= $add_attachments_btn;
			}

		// Wrap both list and the Add Attachments button in another div
		if ( $html ) {
			$html = "<div class=\"attachmentsContainer\">\n" . $html . "\n</div>";
			}

		// Finally, add the attachments

		// NOTE: Hope str_replace() below is UTF8 safe...

		switch ( $attachments_placement ) {

		case 'beginning':
			// Put the attachments list at the beginning of the article/entity
			if ( $attachments_list || $user_can_add ) {
				if ( $attachments_tag ) {
					$row->$text_field_name = $html . $row->$text_field_name;
					}
				else {
					$row->$text_field_name = $html . str_replace($attachments_tag, '', $row->$text_field_name);
					}
				}
			break;

		case 'custom':
			// Insert the attachments at the desired location
			if ( $attachments_list || $user_can_add ) {
				if ( $attachments_tag ) {
					$row->$text_field_name = str_replace($attachments_tag, $html, $row->$text_field_name);
					}
				else {
					// If there is no tag, insert the attachments at the end
					$row->$text_field_name .= $html;
					}
				}
			break;

		case 'disabled_filter':
			// Disable and strip out any attachments tags
			if ( $attachments_tag ) {
				$row->$text_field_name = str_replace($attachments_tag, '', $row->$text_field_name);
				}
			break;

		default:
			// Add the attachments to the end of the article
			if ( $attachments_list || $user_can_add ) {
				if ( $attachments_tag ) {
					$row->$text_field_name = str_replace($attachments_tag, '', $row->$text_field_name) . $html;
					}
				else {
					$row->$text_field_name .= $html;
					}
				}
			break;
			}

		return;
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
		$uri = JFactory::getURI();

		// CURRENTLY, only display for category blogs
		$view = JRequest::getCmd('view');
		if ( $view != 'category' ) {
			return false;
			}

		// Ignore articles
		if ( $context != 'com_content.category' ) {
			return false;
			}

		// ??? Temporary check.	 Once Joomla bug is fixed this will no longer be necessary
		if ( !AttachmentsDefines::$USE_ON_CONTENT_PREPARE_FOR_CATEGORY ) {
			return false;
			}

		// Ignore items without the normal 'text' field
		if ( isset($row->text) ) {
			$text_field_name = 'text';
			}
		elseif ( isset($row->introtext) ) {
			$text_field_name = 'introtext';
			}
		else {
			// Unrecognized
			return false;
			}

		// Set the parent info
		$parent_type = 'com_content';
		$parent_entity = 'category';

		// In the case of a blog, we know what text_field_name should be
		$layout = JRequest::getCmd('layout');
		if ( isset($row->introtext) AND $layout == 'blog' ) {
			$text_field_name = 'introtext';
			}

		// Get the parent ID
		$parent_id = JRequest::getInt('id', null);
		if ( $parent_id === null ) {
			return false;
			}

		// Load the language
		$lang = JFactory::getLanguage();
		$lang->load('plg_content_attachments', dirname(__FILE__));

		// Always include the hide rule (since it may be needed to hide the custom tags)
		require_once(JPATH_SITE.'/components/com_attachments/helper.php');
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments1.css' );

		$doc = JFactory::getDocument();
		JHTML::_('behavior.mootools');
		$js_path = $uri->root(true) . '/plugins/content/attachments/attachments_refresh.js';
		$doc->addScript( $js_path );

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

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$attachParams = JComponentHelper::getParams('com_attachments');

		// Get the desired placement
		$attachments_placement = $attachParams->get('attachments_placement', 'end');
		if ( $attachments_placement == 'disabled_nofilter' ) {
			return false;
			}
		
		// Get some of the options
		$user = JFactory::getUser();
		$logged_in = $user->get('username') <> '';
		$user_type = $user->get('usertype', false);

		// exit if we should not display attachments for this parent
		if ( $parent->attachmentsHiddenForParent($row, $parent_id, $parent_entity, $attachParams) ) {
			return false;
			}

		// See whether we can display the links to add attachments
		$user_can_add = $parent->userMayAddAttachment($parent_id, $parent_entity);

		// Make sure we should be showing the category attachments
		$always_show_category_attachments = $attachParams->get('always_show_category_attachments', false);
		$all_but_article_views = $attachParams->get('hide_except_article_views', false);
		if ( $all_but_article_views && !$always_show_category_attachments ) {
			return false;
			}

		// Determine where we are
		$from = JRequest::getCmd('view');
		$Itemid = JRequest::getInt( 'Itemid', 1);

		// Get the attachments tag, if present
		$attachments_tag = '';
		$attachments_tag_args = '';
		$match = false;
		if ( JString::strpos($row->$text_field_name, '{attachments') ) {
			if ( preg_match('@(<span class="hide">)?{attachments([ ]*:*[^}]+)?}(</span>)?@',
							$row->$text_field_name, $match) ) {
				$attachments_tag = true;
				}
			if ( isset($match[1]) && $match[1] ) {
				$attachments_tag_args_raw = $match[1];
				$attachments_tag_args = ltrim($attachments_tag_args_raw, ' :');
				}
			if ( $attachments_tag ) {
				$attachments_tag = $match[0];
				}
			}

		// Construct the attachment list (if appropriate)
		$html = '';
		$attachments_list = false;
		$add_attachement_btn = false;
		$attachments_list =
			$this->_attachmentsListHTML($parent_type, $parent_id, $parent_entity, $user_can_add, $Itemid, $from);

		// If the attachments list is empty, insert an empty div for it
		if ( $attachments_list == '' ) {
			$class_name = $attachParams->get('attachments_table_style', 'attachmentsList');
			$div_id = 'attachmentsList' . '_' . $parent_type . '_' . $parent_entity  . '_' . (string)$parent_id;
			$attachments_list = "\n<div class=\"$class_name\" id=\"$div_id\"></div>\n";
			}

		$html .= $attachments_list;

		if ( $html || $user_can_add ) {
			// Add the style sheet
			AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css' );
			}

		// Construct the add-attachments button, if appropriate
		$hide_add_attachments_link = $attachParams->get('hide_add_attachments_link', 0);
		if ( $user_can_add && !$hide_add_attachments_link ) {
			$add_attachments_btn =
				$this->_attachmentButtonsHTML($parent_type, $parent_id, $parent_entity, $Itemid, $from);
			$html .= $add_attachments_btn;
			}

		// Wrap both list and the Add Attachments button in another div
		if ( $html ) {
			$html = "<div class=\"attachmentsContainer\">\n" . $html . "\n</div>";
			}

		// Finally, add the attachments

		// NOTE: Hope str_replace() below is UTF8 safe...

		switch ( $attachments_placement ) {

		case 'beginning':
			// Put the attachments list at the beginning
			if ( $attachments_list || $user_can_add ) {
				if ( $attachments_tag ) {
					$row->$text_field_name = $html . $row->$text_field_name;
					}
				else {
					$row->$text_field_name = $html . str_replace($attachments_tag, '', $row->$text_field_name);
					}
				}
			break;

		case 'custom':
			// Insert the attachments at the desired location
			if ( $attachments_list || $user_can_add ) {
				if ( $attachments_tag ) {
					$row->$text_field_name = str_replace($attachments_tag, $html, $row->$text_field_name);
					}
				else {
					// If there is no tag, insert the attachments at the end
					$row->$text_field_name .= $html;
					}
				}
			break;

		case 'disabled_filter':
			// Disable and strip out any attachments tags
			if ( $attachments_tag ) {
				$row->$text_field_name = str_replace($attachments_tag, '', $row->$text_field_name);
				}
			break;

		default:
			// Add the attachments to the end
			if ( $attachments_list || $user_can_add ) {
				if ( $attachments_tag ) {
					$row->$text_field_name = str_replace($attachments_tag, '', $row->$text_field_name) . $html;
					}
				else {
					$row->$text_field_name .= $html;
					}
				}
			break;
			}

		return true;
	}



	/**
	 * Set the parent_id for all attachments that were added to this
	 * content before it was saved the first time.
	 *
	 * This method is called right after the content is saved.
	 *
	 * @param string The context of the content being passed to the plugin.
	 * @param &object &$article A JTableContent object
	 * @param bool $isNew If the content is newly created
	 *
	 * @return	void
	 *
	 * NOTE: Currently this only supports attachment parents being articles since
	 *		 this will only be invoked when articles are saved.
	 */
	function onContentAfterSave($context, &$article, $isNew )
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
		$query->where('created_by='.(int)$user_id.' AND parent_id IS NULL');
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

		/// Load the Attachments helper
		require_once(JPATH_SITE.'/components/com_attachments/helper.php');

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


	/**
	 * Return a list of attachments as HTML code.
	 *
	 * @param string $parent_type of the parent object
	 * @param int $parent_id ID of the parent object
	 * @param string $parent_entity type of the entity involved
	 * @param bool $user_can_add true if the user can add attachments to this parent object
	 * @param int $Itemid the menu item id for the display
	 * @param string $from where the control should return to
	 *
	 * @return a list of attachments as HTML code
	 */
	private function _attachmentsListHTML($parent_type, $parent_id, $parent_entity, $user_can_add, $Itemid, $from)
	{
		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Check the security status
		$attach_dir = JPATH_SITE.'/'.AttachmentsDefines::$ATTACHMENTS_SUBDIR;
		$secure = $params->get('secure', false);
		$hta_filename = $attach_dir.'/.htaccess';
		if ( ($secure && !file_exists($hta_filename)) ||
			 (!$secure && file_exists($hta_filename)) ) {
			require_once(JPATH_SITE.'/components/com_attachments/helper.php');
			AttachmentsHelper::setup_upload_directory($attach_dir, $secure);
			}

		// Get the html for the attachments list
		require_once(JPATH_SITE.'/components/com_attachments/controllers/attachments.php');
		$controller = new AttachmentsControllerAttachments();
		return $controller->displayString($parent_id, $parent_type, $parent_entity,
										  null, true, true, false, $from);
	}


	/**
	 * Return the HTML for the "Add Attachments" link
	 *
	 * @param int $parent_id ID of the parent object
	 * @param string $parent_entity type of the entity involved
	 * @param int $Itemid the menu item id for the display
	 * @param string $from where the control should return to
	 *
	 * @return the HTML for the "Add Attachments" link
	 */
	private function _attachmentButtonsHTML($parent_type, $parent_id, $parent_entity, $Itemid, $from)
	{
		$document = JFactory::getDocument();

		JHTML::_('behavior.modal', 'a.modal-button');

		// Generate the HTML for a	button for the user to click to get to a form to add an attachment
		if ( ($parent_type == 'com_content') && ($parent_entity == 'default') ) {
			$url = "index.php?option=com_attachments&task=upload&article_id=$parent_id&tmpl=component";
			}
		else {
			if ( $parent_entity != 'default' ) {
				$parent_type .= ':'.$parent_entity;
				}
			$url = "index.php?option=com_attachments&task=upload" .
				"&parent_id=$parent_id&parent_type=$parent_type&tmpl=component";
			}
		if ( $from ) {
			// Add a var to give a hint of where to return to
			// $url .= "&from=$from";
			$url .= "&from=closeme";
			}
		$url = JRoute::_($url);
		$uri = JFactory::getURI();
		$icon_url = $uri->root(true) . '/components/com_attachments/media/add_attachment.gif';

		$add_attachment_txt = JText::_('ATTACH_ADD_ATTACHMENT');
		$ahead = '<a class="modal-button" type="button" href="' . $url . '" ';
		$ahead .= "rel=\"{handler: 'iframe', size: {x: 900, y: 550}}\">";
		$links = "$ahead<img src=\"$icon_url\" alt=\"$add_attachment_txt\" /></a>";
		$links .= $ahead.$add_attachment_txt."</a>";
		return "\n<div class=\"addattach\">$links</div>\n";
	}

}
