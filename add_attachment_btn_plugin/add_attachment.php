<?php
/**
 * Add Attachments Button plugin
 *
 * @package Attachments
 * @subpackage Add_Attachment_Button_Plugin
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

/**
 * Button that allows you to add attachments from the editor
 *
 * @package Attachments
 */
class plgButtonAdd_attachment extends CMSPlugin
{
	/**
	 * Constructor
	 *
	 * @param &object &$subject The object to observe
	 * @param		array	$config	 An array that holds the plugin configuration
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
	 * @param int $author The ID of the author of the entity
	 *
	 * @return a button
	 */
	public function onDisplay($name, $asset, $author)
	{
		$app = Factory::getApplication();
		$input = $app->getInput();

		// Avoid displaying the button for anything except for registered parents
		$parent_type = $input->getCmd('option');
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
		$cid = $input->get('cid', array(0), 'array');
		$parent_id = 0;
		if ( count($cid) > 0 ) {
			$parent_id = (int)$cid[0];
			}
		if ( $parent_id == 0) {
			$a_id = $input->getInt('a_id');
			if ( !is_null($a_id) ) {
				$parent_id = (int)$a_id;
				}
			}
		if ( $parent_id == 0) {
			$nid = $input->getInt('id');
			if ( !is_null($nid) ) {
				$parent_id = (int)$nid;
				}
			}

		// Check for the special case where we are creating an article from a category list
		$item_id = $input->getInt('Itemid');
		$menu = $app->getMenu();
		$menu_item = $menu->getItem($item_id);
		if ( $menu_item AND ($menu_item->query['view'] == 'category') AND empty($a_id) ) {
			$parent_entity = 'article';
			$parent_id = NULL;
			}

		// Get the article/parent handler
		PluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			// Exit if there is no Attachments plugin to handle this parent_type
			return new stdClass();
			}
		// Figure out where we are and construct the right link and set
		$base_url = Uri::root(true);
		if ( $app->isClient('administrator') ) {
			$base_url = str_replace('/administrator','', $base_url);
			}

		// Set up the Javascript framework
		require_once JPATH_SITE . '/components/com_attachments/javascript.php';
		AttachmentsJavascript::setupJavascript();

		// Get the parent handler
		$parent = $apm->getAttachmentsPlugin($parent_type);
		$parent_entity = $parent->getCanonicalEntityId($parent_entity);

		if ( $parent_id == 0 ) {
			# Last chance to get the id in extension editors
			$view = $input->getWord('view');
			$layout = $input->getWord('layout');
			$parent_id = $parent->getParentIdInEditor($parent_entity, $view, $layout);
			}

		// Make sure we have permissions to add attachments to this article or category
		if ( !$parent->userMayAddAttachment($parent_id, $parent_entity, $parent_id == 0) ) {
			return;
			}

		// NOTE: I cannot find anything about AttachmentsRemapper class.
		// Could it be old unnecessary code that needs deletion?
		// ------------------------------------------------------
		// Allow remapping of parent ID (eg, for Joomfish)
		if (jimport('attachments_remapper.remapper'))
		{
			$parent_id = AttachmentsRemapper::remapParentID($parent_id, $parent_type, $parent_entity);
		}

		// Add the regular css file
		HTMLHelper::stylesheet('com_attachments/attachments_list.css');
		HTMLHelper::stylesheet('com_attachments/add_attachment_button.css');

		// Handle RTL styling (if necessary)
		$lang = $app->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('com_attachments/attachments_list_rtl.css');
			HTMLHelper::stylesheet('com_attachments/add_attachment_button_rtl.css');
			}

		// Load the language file from the frontend
		$lang->load('com_attachments', dirname(__FILE__));

		// Create the [Add Attachment] button object
		$button = new Registry();

		$link = $parent->getEntityAddUrl($parent_id, $parent_entity, 'closeme');
		$link .= '&amp;editor=' . $editor;

		// Finalize the [Add Attachment] button info
		$button->set('modal', true);
		$button->set('class', 'btn');
		$button->set('text', Text::_('ATTACH_ADD_ATTACHMENT'));
		$button->set('name', 'paperclip');
		$button->set('link', $link);
		$button->set('options', "{handler: 'iframe', size: {x: 920, y: 530}}");

		return $button;
	}
}
