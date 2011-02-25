<?php
/**
 * Add Attachments Button plugin
 *
 * @package Attachments
 * @subpackage Add_Attachment_Button_Plugin
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.event.plugin');

/**
 * Class for the button that allows you to add attachments from the editor
 *
 * @package Attachments
 */
class plgButtonAdd_attachment extends JPlugin
{
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param &object &$subject The object to observe
	 * @param array  $config	An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgAdd_attachment(&$subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Add Attachment button
	 *
	 * @return a button
	 */
	function onDisplay($name)
	{
		// Avoid displaying the button for anything except for registered parents
		global $option;
		$parent_type = $option;
		$parent_entity = 'default';
		$editor = 'article';

		// Handle sections and categories specially (since they are really com_content)
		if ($option == 'com_sections') {
			$parent_type = 'com_content';
			$parent_entity = 'section';
			$editor = 'section';
			}
		if ($option == 'com_categories') {
			$parent_type = 'com_content';
			$parent_entity = 'category';
			$editor = 'category';
			}

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments', 'attachments_plugin_framework');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			// Exit if there is no Attachments plugin to handle this parent_type
			return new JObject();
			}

		// Get the parent handler
		$parent =& $apm->getAttachmentsPlugin($parent_type);

		// Get the parent ID (id or first of cid array)
		//	   NOTE: $id=0 means no id (usually means creating a new entity)
		$cid = JRequest::getVar('cid', array(0), '', 'array');
		$id = 0;
		if ( count($cid) > 0 ) {
			$id = (int)$cid[0];
			}
		if ( $id == 0) {
			$nid = JRequest::getInt('id');
			if ( !is_null($nid) ) {
				$id = (int)$nid;
				}
			}

		// Disable adding attachments when creating sections or categories
		if ( $id == 0 and (($parent_entity == 'section') or ($parent_entity == 'category'))) {
			return new JObject();
			}

		// Load the language file from the backend
		$lang =&  JFactory::getLanguage();
		$lang->load('plg_frontend_attachments', JPATH_ADMINISTRATOR);

		// Figure out where we are and construct the right link and set
        $app = JFactory::getApplication();
		$uri = JFactory::getURI();
		$base_url = $uri->root(true) . '/';
		if ( $app->isAdmin() ) {
			$base_url = str_replace('/administrator','', $base_url);
			}

		// up the style sheet (to get the visual for the button working)
		$doc =& JFactory::getDocument();
		$js_path = $base_url . '/plugins/content/attachments/attachments_refresh.js';
		$doc->addScript( $js_path );

		// Add the regular css file
		require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'helper.php');
		AttachmentsHelper::addStyleSheet( $base_url . '/plugins/content/attachments/attachments.css' );
		AttachmentsHelper::addStyleSheet( $base_url . '/plugins/editors-xtd/add_attachment.css' );

		// Handle RTL styling (if necessary)
		if ( $lang->isRTL() ) {
			AttachmentsHelper::addStyleSheet( $base_url . '/plugins/content/attachments/attachments_rtl.css' );
			AttachmentsHelper::addStyleSheet( $base_url . '/plugins/editors-xtd/add_attachment_rtl.css' );
			}

		// Load the language file from the frontend
		$lang->load('com_attachments', JPATH_SITE);

		// Create the [Add Attachment] button object
		$button = new JObject();

		$link = $parent->getEntityAddUrl($id, $parent_entity, 'closeme');
		$link .= '&amp;editor=' . $editor;

		// Finalize the [Add Attachment] button info
		$button->set('modal', true);
		$button->set('class', 'modal');
		$button->set('text', JText::_('ADD_ATTACHMENT'));
		$button->set('name', 'add_attachment');
		$button->set('link', $link);
		$button->set('options', "{handler: 'iframe', size: {x: 900, y: 530}}");

		return $button;
	}
}

?>
