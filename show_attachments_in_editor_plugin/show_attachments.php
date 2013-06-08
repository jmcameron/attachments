<?php
/**
 * System plugin to display the existing attachments in the editor
 *
 * @package Attachments
 * @subpackage Show_Attachments_In_Editor_Plugin
 *
 * @copyright Copyright (C) 2009-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

jimport('joomla.plugin.plugin');

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

/* Load the attachments helper */
require_once(JPATH_SITE.'/components/com_attachments/helper.php');
require_once(JPATH_SITE.'/components/com_attachments/javascript.php');


/**
 * Show Attachments in Editor system plugin
 *
 * @package Attachments
 */
class plgSystemShow_attachments extends JPlugin
{

	/**
	 * Attach the Attachments CSS sheets for category pages
	 *
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.	 Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 *
	 * @return	void
	 */
	public function onContentPrepare($context, &$row, &$params, $page = 0)
	{  
		$view = JRequest::getCmd('view');
		$layout = JRequest::getWord('layout');

		if ( $view == 'category' ) {

			$app = JFactory::getApplication();
			if ( $app->isAdmin() ) {
				return;
				}

			// Note if this is a category view, we must add attachment
            // javascript and CSS whether we know there are going to be
			// attachments later or not because when the attachments list is
            // created it, it is in the onAfterRender() callback, which means
            // that the headers have already been rendered, so we cannot go
			// go back and add headers (easily)

			// Not necessary in more recent versions of Joomla since it can
			// handled by the normal Attachments onContentPrepare callback
			if (version_compare(JVERSION, '3.1', 'ge') OR version_compare(JVERSION, '2.5.10', 'ge')) {
				return;
				}

			$uri = JFactory::getURI();
			$base_url = $uri->root(true);
			AttachmentsJavascript::setupJavascript();
			AttachmentsJavascript::setupModalJavascript();

			// Add the style sheets
			JHtml::stylesheet('com_attachments/attachments_list.css', array(), true);
			JHtml::stylesheet('com_attachments/attachments_hide.css', array(), true);
			$lang = JFactory::getLanguage();
			if ( $lang->isRTL() ) {
				JHtml::stylesheet('com_attachments/attachments_list_rtl.css', array(), true);
				}
			}
	}


	/**
	 * Inserts the attachments list above the row of xtd-buttons
	 *
	 * @access	public
	 * @since	1.5
	 */
	public function onAfterRender()
	{
		$task = JRequest::getCmd('task');
		$view = JRequest::getCmd('view');
		$layout = JRequest::getWord('layout');

		// Make sure this we should handle this
		$parent_type = JRequest::getCMD('option');
		if (!$parent_type) {
			return;
			}

		// Handle the special case of Global Config for Attachments 3.x
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			if (($parent_type == 'com_config') AND ($task == '') AND ($view == ''))
			{
				// Force use of the Attachments options editor

				// option=com_config&view=component&component=com_attachments
				$body = JResponse::getBody();
				$body = str_replace('option=com_config&view=component&component=com_attachments',
									'option=com_attachments&task=params.edit', $body);
				JResponse::setBody($body);
			}
		}

		// Handle attachments
		$parent_entity = 'default';
		$editor = 'article';

		// Handle categories specially (since they are really com_content)
		if ($parent_type == 'com_categories') {
			$parent_type = 'com_content';
			$parent_entity = 'category';
			$editor = 'category';
			}

		// Get the article/parent handler
		if ( !JPluginHelper::importPlugin('attachments') ) {
			// Exit if the framework does not exist (eg, during uninstallaton)
			return false;
			}
		if ( !function_exists('getAttachmentsPluginManager') ) {
			// Exit if the function does not exist (eg, during uninstallaton)
			return false;
			}
		$apm = getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			// Exit if there is no Attachments plugin to handle this parent_type
			return false;
			}

		// Get the article ID (strip off any SEF name appended with a colon)
		$a_id = JRequest::getVar('a_id');
		$parent_id = $a_id;
		if ( empty($parent_id) AND ($view != 'category') ) {
			$parent_id = JRequest::getVar('id');
			}

		if ( strpos($parent_id, ':') > 0 ) {
			$parent_id = substr($parent_id,0,strpos($parent_id,':'));
			if ( is_numeric($parent_id) ) {
				$parent_id = (int)$parent_id;
				}
			else {
				$parent_id = null;
				}
			}

		// If that fails, try to get the id via 'cid'
		if (!$parent_id) {
			$cid = JRequest::getVar( 'cid' , array() , '' , 'array' );
			@$parent_id = $cid[0];
			if ( is_numeric($parent_id) ) {
				$parent_id = (int)$parent_id;
				}
			else {
				$parent_id = null;
				}
			}

		// Check for the special case where we are creating an article from a category list
		$item_id = JRequest::getInt('Itemid');
		$application = JFactory::getApplication();
		$menu = $application->getMenu();
		$menu_item = $menu->getItem($item_id);
		if ( $menu_item AND	 ($menu_item->query['view'] == 'category') AND empty($a_id) ) {
			$parent_entity = 'article';
			$parent_id = NULL;
			}

		// Deal with the case where are editing a category
		if ( $view == 'category' AND $layout == 'edit' ) {
			$parent_entity = 'category';
			$parent_id = JRequest::getInt('id', null);
			}

		// Deal with the case where we are displaying a category blog or list
		if ( $view == 'category' ) {
			$parent_entity = 'category';
			$parent_id = JRequest::getInt('id', null);
			}

		$parent = $apm->getAttachmentsPlugin($parent_type);
		$parent_entity = $parent->getCanonicalEntityId($parent_entity);

		// Front end:
		//	  - Edit existing article:	com_content,view=form,layout=edit,a_id=#
		//	  - Create new article: same but no a_id

		// Back end
		//	  - Edit existing article: com_content,view=article,layout=edit,id=#
		//	  - Create new article: same but no id
		///
		//	  - Edit existing category: com_categories,view=category,layout=edit,id=#
		//	  - Create new category: same but no id

		if ( ($parent_type == 'com_content') && ($layout =='edit') &&
			 (($view == 'form') || ($view == 'article') || ($view == 'category') ) ) {

			// If we cannot determine the article ID
			if (!$parent_id) {
				if ( ($task == 'add') || (($view == 'article') && ( $layout=='edit'))
					 || (($view == 'form') && ( $layout=='edit')) ) {
					// If we are creating an article, note that
					$parent_id = 0;
					}
				elseif ( ($view == 'category') && ($layout == 'edit') ) {
					// If we are creating an category, note that
					$parent_id = 0;
					}
				else {
					// Otherwise do not show attachments
					return;
					}
				}

			// Get the article/parent handler
			$user_can_add = $parent->userMayAddAttachment($parent_id, $parent_entity);

			// Construct the attachment list
			$Itemid = JRequest::getInt( 'Itemid', 1);
			$from = 'editor';
			$attachments = AttachmentsHelper::attachmentsListHTML($parent_id, $parent_type, $parent_entity,
																  $user_can_add, $Itemid, $from, true, true);

			// Embed the username in liu of the parent_id (if the parent_id is missing)
			// (eg, when articles are being created)
			if ( !$parent_id ) {
				$parent_id = 0;
				}

			// If the attachments list is empty, insert an empty div for it
			if ( $attachments == '' ) {
				jimport('joomla.application.component.helper');
				$params = JComponentHelper::getParams('com_attachments');
				$class_name = $params->get('attachments_table_style', 'attachmentsList');
				$div_id = 'attachmentsList' . '_' . $parent_type . '_' . $parent_entity	 . '_' . (string)$parent_id;
				$attachments = "\n<div class=\"$class_name\" id=\"$div_id\"></div>\n";
				}

			// Insert the attachments above the editor buttons
			// NOTE: Assume that anyone editing the article can see its attachments
			$reptag = '<div id="editor-xtd-buttons"';
			$body = JResponse::getBody();
			$body = str_replace($reptag, $attachments . $reptag, $body);
			JResponse::setBody($body);
			}

		elseif ( $parent_id && ($view == 'category') ) {

			// Only dislay this in the front end
			$app = JFactory::getApplication();
			if ( $app->isAdmin() ) {
				return;
				}

			// More recent versions of Joomla allow this to be handled better
			// by the normal Attachments onContentPrepare callback
			if (version_compare(JVERSION, '3.1', 'ge') OR
				(version_compare(JVERSION, '2.5.10', 'ge') AND version_compare(JVERSION, '3.0', 'lt'))) {
				return;
				}

			// Display attachments lists for category descriptions
			$parent_entity = 'category';

			// Add the refresh Javascript
			$uri = JFactory::getURI();
			$base_url = $uri->root(true);
			$doc = JFactory::getDocument();

			// Allow remapping of parent ID (eg, for Joomfish)
			if (jimport('attachments_remapper.remapper'))
			{
				$parent_id = AttachmentsRemapper::remapParentID($parent_id, $parent_type, $parent_entity);
			}

			// Figure out if the attachments list should be visible for this category
			jimport('joomla.application.component.helper');
			$params = JComponentHelper::getParams('com_attachments');

			$always_show_category_attachments = $params->get('always_show_category_attachments', false);
			$all_but_article_views = $params->get('hide_except_article_views', false);
			if ( $all_but_article_views && !$always_show_category_attachments ) {
				return;
				}

			// Get the article/parent handler
			$parent = $apm->getAttachmentsPlugin($parent_type);

			// Construct the attachment list
			$Itemid = JRequest::getInt( 'Itemid', 1);
			$from = 'frontpage';
			$user_can_add = $parent->userMayAddAttachment($parent_id, $parent_entity);
			$attachments = AttachmentsHelper::attachmentsListHTML($parent_id, $parent_type, $parent_entity,
																 $user_can_add, $Itemid, $from, true, $user_can_add);

			// If the attachments list is empty, insert an empty div for it
			if ( $attachments == '' ) {
				jimport('joomla.application.component.helper');
				$class_name = $params->get('attachments_table_style', 'attachmentsList');
				$div_id = 'attachmentsList' . '_' . $parent_type . '_' . $parent_entity	 . '_' . (string)$parent_id;
				$attachments = "\n<div class=\"$class_name\" id=\"$div_id\"></div>\n";
				}

			// Insert the attachments after the category description
			$reptag = '<div class="clr"></div>';
			$body = JResponse::getBody();
			$body = str_replace($reptag, $attachments . $reptag, $body);
			JResponse::setBody($body);
			}
	}
}
