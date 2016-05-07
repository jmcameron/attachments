<?php
/**
 * System plugin to display the existing attachments in the editor
 *
 * @package Attachments
 * @subpackage Show_Attachments_In_Editor_Plugin
 *
 * @copyright Copyright (C) 2009-2016 Jonathan M. Cameron, All Rights Reserved
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
	require_once(JPATH_SITE . '/components/com_attachments/defines.php');
	require_once(JPATH_SITE . '/components/com_attachments/helper.php');
	require_once(JPATH_SITE . '/components/com_attachments/javascript.php');
}
else
{
	// Exit quietly if the attachments component has been uninstalled or deleted
	return;
}


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
	 * And in older versions, inserts the attachments list for category
	 * descriptions.
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

		// Handle categories specially (since they are really com_content)
		if ($parent_type == 'com_categories') {
			$parent_type = 'com_content';
			$parent_entity = 'category';
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
		$parent = $apm->getAttachmentsPlugin($parent_type);

		// Get the parent ID
		$parent_entity = $parent->getCanonicalEntityId($parent_entity);
		$parent_id = $parent->getParentIdInEditor($parent_entity, $view, $layout);

		// Exit if we do not have an parent (exiting or being created)
		if ($parent_id === false) {
			return;
			}

		// See if this type of content suports displaying attachments in its editor
		if ($parent->showAttachmentsInEditor($parent_entity, $view, $layout))
		{
			// Get the article/parent handler
			$user_can_add = $parent->userMayAddAttachment($parent_id, $parent_entity);

			// Allow remapping of parent ID (eg, for Joomfish)
			if (jimport('attachments_remapper.remapper'))
			{
				$parent_id = AttachmentsRemapper::remapParentID($parent_id, $parent_type, $parent_entity);
			}

			// Force the ID to zero when creating the entity
			if ( !$parent_id ) {
				$parent_id = 0;
				}

			// Construct the attachment list
			$Itemid = JRequest::getInt( 'Itemid', 1);
			$from = 'editor';
			$attachments = AttachmentsHelper::attachmentsListHTML($parent_id, $parent_type, $parent_entity,
																  $user_can_add, $Itemid, $from, false, true);

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
			$body = $parent->insertAttachmentsListInEditor($parent_id, $parent_entity,
														   $attachments, JResponse::getBody());
			JResponse::setBody($body);
		}

		elseif ( $parent_id && ($view == 'category') )
		{
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
