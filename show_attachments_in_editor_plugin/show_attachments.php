<?php
/**
 * System plugin to display the existing attachments in the editor
 *
 * @package Attachments
 * @subpackage Show_Attachments_In_Editor_Plugin
 *
 * @copyright Copyright (C) 2009-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

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


jimport('joomla.plugin.plugin');


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

			$doc = JFactory::getDocument();
			$uri = JFactory::getURI();
			$base_url = $uri->root(true);

			$doc->addStyleSheet( $base_url . '/plugins/content/attachments/attachments.css',
								 'text/css', null, array() );
			$doc->addStyleSheet( $base_url . '/plugins/content/attachments/attachments1.css',
								 'text/css', null, array() );

			JHTML::_('behavior.mootools');
			JHTML::_('behavior.modal', 'a.modal-button');

			$js_path = $base_url . '/plugins/content/attachments/attachments_refresh.js';
			$doc->addScript( $js_path );
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

			// Load the code from the attachments plugin to create the list
			require_once(JPATH_SITE.'/components/com_attachments/helper.php');

			// Add the refresh Javascript
			$app = JFactory::getApplication();
			$uri = JFactory::getURI();
			$base_url = $uri->root(true);
			$doc = JFactory::getDocument();
			JHTML::_('behavior.mootools');
			if ( $app->isAdmin() ) {
				// ??? Is this line necessary?	If so, why?
				$base_url = str_replace('/administrator','', $base_url);
				}
			$js_path = $base_url . '/plugins/content/attachments/attachments_refresh.js';
			$doc->addScript( $js_path );

			$doc->addStyleSheet( $base_url . 'plugins/content/attachments/attachments.css',
								 'text/css', null, array() );

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
			$body = JResponse::getBody();
			$body = str_replace('<div id="editor-xtd-buttons">',
								$attachments . '<div id="editor-xtd-buttons">', $body);
			JResponse::setBody($body);
			}


		elseif ( $parent_id && ($view == 'category') ) {

			// Display attachments lists for category descriptions!

			$parent_entity = 'category';

			// Only dislay this in the front end
			$app = JFactory::getApplication();
			if ( $app->isAdmin() ) {
				return;
				}

			// ??? Temporary check.  Once Joomla bug is fixed this will no longer be necessary
			if ( AttachmentsDefines::$USE_ON_CONTENT_PREPARE_FOR_CATEGORY ) {
				return;
				}

			// Load the code from the attachments plugin to create the list
			require_once(JPATH_SITE.'/components/com_attachments/helper.php');

			// Add the refresh Javascript
			$uri = JFactory::getURI();
			$base_url = $uri->root(true);
			$doc = JFactory::getDocument();


			// Get the article/parent handler
			$parent = $apm->getAttachmentsPlugin($parent_type);

			// Figure out if the attachments list should be visible for this category
			jimport('joomla.application.component.helper');
			$params = JComponentHelper::getParams('com_attachments');

			$always_show_category_attachments = $params->get('always_show_category_attachments', false);
			$all_but_article_views = $params->get('hide_except_article_views', false);
			if ( $all_but_article_views && !$always_show_category_attachments ) {
				return;
				}

			$user_can_add = $parent->userMayAddAttachment($parent_id, $parent_entity);

			// Construct the attachment list
			$Itemid = JRequest::getInt( 'Itemid', 1);
			$from = 'frontpage';
			$attachments = AttachmentsHelper::attachmentsListHTML($parent_id, $parent_type, $parent_entity,
																 $user_can_add, $Itemid, $from, true, $user_can_add);

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
			$body = JResponse::getBody();
			$body = str_replace('<div class="clr"></div>',
								$attachments . '<div class="clr"></div>', $body);
			JResponse::setBody($body);
			}

	}
}
