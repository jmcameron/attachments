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

jimport('joomla.plugin.plugin');

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
	 * @access      protected
	 * @param       object  $subject The object to observe
	 * @param       array   $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Add Attachment button
	 *
	 * @return a button
	 */
	function onDisplay($name)
	{
		// Avoid displaying the button for anything except for registered parents
		$parent_type = JRequest::getCMD('option');
		if (!$parent_type) {
			return;
			}
		$parent_entity = 'default';
		$editor = 'article';

		// Handle categories specially (since they are really com_content)
		// ??? Still true?
		if ($parent_type == 'com_categories') {
			$parent_type = 'com_content';
			$parent_entity = 'category';
			$editor = 'category';
			}

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments');
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

		// Disable adding attachments when creating categories
		if ( $id == 0 and ($parent_entity == 'category')) {
			return new JObject();
			}

		// Figure out where we are and construct the right link and set
        $app = JFactory::getApplication();
		$uri = JFactory::getURI();
		$base_url = $uri->root(true);
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
		AttachmentsHelper::addStyleSheet( $base_url . '/plugins/editors-xtd/add_attachment/add_attachment.css' );

		// Handle RTL styling (if necessary)
		$lang =&  JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			AttachmentsHelper::addStyleSheet( $base_url . '/plugins/content/attachments/attachments_rtl.css' );
			AttachmentsHelper::addStyleSheet( $base_url . '/plugins/editors-xtd/add_attachment/add_attachment_rtl.css' );
			}

		// Load the language file from the frontend
		$lang->load('com_attachments', dirname(__FILE__));

		// Create the [Add Attachment] button object
		$button = new JObject();

		$link = $parent->getEntityAddUrl($id, $parent_entity, 'closeme');
		$link .= '&amp;editor=' . $editor;

		// Finalize the [Add Attachment] button info
		$button->set('modal', true);
		$button->set('class', 'modal');
		$button->set('text', JText::_('ADD_ATTACHMENT'));

		if ( $app->isAdmin() ) {
			$button->set('name', 'add_attachment');
			}
		else {
			$button->set('name', 'add_attachment_frontend');
			}
		$button->set('link', $link);
		$button->set('options', "{handler: 'iframe', size: {x: 900, y: 530}}");

		return $button;
	}
}

?>
