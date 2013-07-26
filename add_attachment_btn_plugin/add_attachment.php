<?php
/**
 * Add Attachments Button plugin
 *
 * @package Attachments
 * @subpackage Add_Attachment_Button_Plugin
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Button that allows you to add attachments from the editor
 *
 * @package Attachments
 */
class plgButtonAdd_attachment extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param &object &$subject The object to observe
	 * @param		array	$config  An array that holds the plugin configuration
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}


	/**
	 * Add Attachment button
	 *
	 * @param string $name The name of the editor form
	 * @param int $asset The asset ID for the entity being edited
	 * @param int $authro The ID of the author of the entity
	 *
	 * @return a button
	 */
	public function onDisplay($name, $asset, $author)
	{
		$app = JFactory::getApplication();

		// Avoid displaying the button for anything except for registered parents
		$parent_type = JRequest::getCmd('option');
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

		// Get the parent ID (id or first of cid array)
		//	   NOTE: $parent_id=0 means no id (usually means creating a new entity)
		$cid = JRequest::getVar('cid', array(0), '', 'array');
		$parent_id = 0;
		if ( count($cid) > 0 ) {
			$parent_id = (int)$cid[0];
			}
		if ( $parent_id == 0) {
			$a_id = JRequest::getInt('a_id');
			if ( !is_null($a_id) ) {
				$parent_id = (int)$a_id;
				}
			}
		if ( $parent_id == 0) {
			$nid = JRequest::getInt('id');
			if ( !is_null($nid) ) {
				$parent_id = (int)$nid;
				}
			}

		// Check for the special case where we are creating an article from a category list
		$item_id = JRequest::getInt('Itemid');
		$menu = $app->getMenu();
		$menu_item = $menu->getItem($item_id);
		if ( $menu_item AND ($menu_item->query['view'] == 'category') AND empty($a_id) ) {
			$parent_entity = 'article';
			$parent_id = NULL;
			}

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			// Exit if there is no Attachments plugin to handle this parent_type
			return new JObject();
			}
		// Figure out where we are and construct the right link and set
		$uri = JFactory::getURI();
		$base_url = $uri->root(true);
		if ( $app->isAdmin() ) {
			$base_url = str_replace('/administrator','', $base_url);
			}

		// Set up the Javascript framework
		require_once JPATH_SITE . '/components/com_attachments/javascript.php';
		AttachmentsJavascript::setupJavascript();

		// Get the parent handler
		$parent = $apm->getAttachmentsPlugin($parent_type);
		$parent_entity = $parent->getCanonicalEntityId($parent_entity);

		// Make sure we have permissions to add attachments to this article or category
		if ( !$parent->userMayAddAttachment($parent_id, $parent_entity, $parent_id == 0) ) {
			return;
			}

		// Add the regular css file
		JHtml::stylesheet('com_attachments/attachments_list.css', Array(), true);
		JHtml::stylesheet('com_attachments/add_attachment_button.css', Array(), true);

		// Handle RTL styling (if necessary)
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_list_rtl.css', Array(), true);
			JHtml::stylesheet('com_attachments/add_attachment_button_rtl.css', Array(), true);
			}

		// Load the language file from the frontend
		$lang->load('com_attachments', dirname(__FILE__));

		// Create the [Add Attachment] button object
		$button = new JObject();

		$link = $parent->getEntityAddUrl($parent_id, $parent_entity, 'closeme');
		$link .= '&amp;editor=' . $editor;

		// Finalize the [Add Attachment] button info
		$button->set('modal', true);
		$button->set('class', 'modal btn');
		$button->set('text', JText::_('ATTACH_ADD_ATTACHMENT'));

		if ( $app->isAdmin() ) {
			$button->set('name', 'add_attachment');
			}
		else {
			$button->set('name', 'add_attachment_frontend');
			}
		$button->set('link', $link);
		$button->set('options', "{handler: 'iframe', size: {x: 920, y: 530}}");

		return $button;
	}
}
