<?php
/**
 * System plugin to display the existing attachments in the editor
 *
 * @package Attachments
 * @subpackage Show_Attachments_in_Editor_Plugin
 *
 * @copyright Copyright (C) 2009-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');


/**
 * Show Attachments in Editor system plugin
 *
 * @package Attachments
 */
class plgSystemShow_attachments extends JPlugin
{
	/**
	 * Inserts the attachments list above the row of xtd-buttons
	 *
	 * @access	public
	 * @since	1.5
	 */
	public function onAfterRender()
	{
		// Make sure this we should handle this
		$parent_type = JRequest::getCMD('option');
		if (!$parent_type) {
			return;
			}
		$parent_entity = 'default';
		$editor = 'article';

		// Handle sections and categories specially (since they are really com_content)
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
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			// Exit if there is no Attachments plugin to handle this parent_type
			return false;
			}

		// Get the article ID (strip off any SEF name appended with a colon)
		$parent_id = JRequest::getVar('id');
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

		$task = JRequest::getCmd('task');
		$view = JRequest::getCmd('view');
		$layout = JRequest::getWord('layout');

		// If we cannot determine the article ID
		if (!$parent_id) {
			if ( $task == 'add' OR ( ($view == 'article') AND ( $layout=='form') ) ) {
				// If we are creating an article, note that
				$parent_id = 0;
				}
			else {
				// Otherwise do not show attachments
				return;
				}
			}

		if ( ($layout =='edit') OR ( ($view == 'article') AND ( $layout=='form') ) ) {

			// Load the code from the attachments plugin to create the list
			require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'helper.php');

			// Add the refresh Javascript
			$app = JFactory::getApplication();
	        $uri = JFactory::getURI();
			$base_url = $uri->root(true);
			if ( $app->isAdmin() ) {
				// ??? This should not be necessary
				$base_url = str_replace('/administrator','', $base_url);
				}
			$doc =& JFactory::getDocument();
			$js_path = $base_url . '/plugins/content/attachments/attachments_refresh.js';
			// ??? $doc->addScript( $js_path );

			// Get the article/parent handler
			$parent =& $apm->getAttachmentsPlugin($parent_type);
			$user_can_add = $parent->userMayAddAttachment($parent_id, $parent_entity);

			// Construct the attachment list
			$Itemid = JRequest::getInt( 'Itemid', 1);
			$from = 'editor';
			$attachments = AttachmentsHelper::attachmentListHTML($parent_id, $parent_type, $parent_entity,
																 $user_can_add, $Itemid, $from, true, true);

			// Embed the username in liu of the parent_id (if the parent_id is missing)
			// (eg, when articles are being created)
			if ( !$parent_id ) {
				$parent_id = 0;
				}

			// If the attachments list is empty, insert an empty div for it
			if ( $attachments == '' ) {
				jimport('joomla.application.component.helper');
				$params =& JComponentHelper::getParams('com_attachments');
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
		else {
			return;
			}
	}
}

?>
