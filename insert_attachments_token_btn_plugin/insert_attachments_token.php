<?php
/**
 * Add Attachments Button plugin
 *
 * @package Attachments
 * @subpackage Insert_Attachments_Token_Button_Plugin
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

jimport('joomla.plugin.plugin');

/**
 * Button that allows you to insert an {attachments} token into the text from the editor
 *
 * @package Attachments
 */
class plgButtonInsert_attachments_token extends JPlugin
{
	/**
	 * Constructor
	 *
	 * @param &object &$subject The object to observe
	 * @param array	 $config	An array that holds the plugin configuration
	 * @since 1.5
	 */
	public function __construct(&$subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}


	/**
	 * Insert attachments token button
	 *
	 * @param string $name The name of the editor form
	 * @param int $asset The asset ID for the entity being edited
	 * @param int $authro The ID of the author of the entity
	 *
	 * @return a button
	 */
	public function onDisplay($name, $asset, $author)
	{
		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// This button should only be displayed in 'custom placement' mode.
		// Check to make sure that is the case
		$placement = $params->get('attachments_placement', 'end');
		if ( $placement != 'custom' ) {
			return false;
			}

		// Avoid displaying the button for anything except for registered parents
		$parent_type = JRequest::getCmd('option');

		// Handle sections and categories specially (since they are really com_content)
		if ($parent_type == 'com_categories') {
			$parent_type = 'com_content';
			}

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			// Exit if there is no Attachments plugin to handle this parent_type
			return new JObject();
			}

		// Get ready for language things
		$lang =	 JFactory::getLanguage();
		if ( !$lang->load('plg_editors-xtd_insert_attachments_token', dirname(__FILE__)) ) {
			// If the desired translation is not available, at least load the English
			$lang->load('plg_editors-xtd_insert_attachments_token', JPATH_ADMINISTRATOR, 'en-GB');
			}

		// Set up the Javascript to insert the tag
		$getContent = $this->_subject->getContent($name);
		$present = JText::_('ATTACH_ATTACHMENTS_TOKEN_ALREADY_PRESENT', true) ;
		$js =  "
			function insertAttachmentsToken(editor) {
				var content = $getContent
				if (content.match(/\{\s*attachments/i)) {
					alert('$present');
					return false;
				} else {
					jInsertEditorText('<span class=\"hide_attachments_token\">{attachments}</span>', editor);
				}
			}
			";

		$app = JFactory::getApplication();
		$doc = JFactory::getDocument();
		$uri = JFactory::getURI();

		$doc->addScriptDeclaration($js);

		// Add the regular css file
		JHtml::stylesheet('com_attachments/attachments_list.css', Array(), true);
		JHtml::stylesheet('com_attachments/insert_attachments_token_button.css', Array(), true);

		// Handle RTL styling (if necessary)
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_list_rtl.css', Array(), true);
			JHtml::stylesheet('com_attachments/insert_attachments_token_button_rtl.css', Array(), true);
			}

		$button = new JObject();
		$button->set('modal', false);
		$button->set('class', 'btn');
		$button->set('onclick', 'insertAttachmentsToken(\''.$name.'\');return false;');
		$button->set('text', JText::_('ATTACH_ATTACHMENTS_TOKEN'));
		$button->set('title', JText::_('ATTACH_ATTACHMENTS_TOKEN_DESCRIPTION'));

		if ( $app->isAdmin() ) {
			$button_name = 'insert_attachments_token';
			if (version_compare(JVERSION, '3.3', 'ge')) {
				$button_name = 'paperclip';
				}
			$button->set('name', $button_name);
			}
		else {
			$button_name = 'insert_attachments_token_frontend';
			if (version_compare(JVERSION, '3.3', 'ge')) {
				$button_name = 'paperclip';
				}
			$button->set('name', $button_name);
			}

		// TODO: The button writer needs to take into account the javascript directive
		// $button->set('link', 'javascript:void(0)');
		$button->set('link', '#');

		return $button;
	}
}
