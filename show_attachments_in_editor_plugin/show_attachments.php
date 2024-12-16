<?php
/**
 * System plugin to display the existing attachments in the editor
 *
 * @package Attachments
 * @subpackage Show_Attachments_In_Editor_Plugin
 *
 * @copyright Copyright (C) 2009-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

use JMCameron\Component\Attachments\Site\Helper\AttachmentsHelper;
use JMCameron\Component\Attachments\Site\Helper\AttachmentsJavascript;
use JMCameron\Plugin\AttachmentsPluginFramework\AttachmentsPluginManager;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\SubscriberInterface;

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

/** Load the Attachments defines (if available) */
if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_attachments/attachments.xml'))
{
	// Exit quietly if the attachments component has been uninstalled or deleted
	return;
}


/**
 * Show Attachments in Editor system plugin
 *
 * @package Attachments
 */
class Plgsystemshow_attachments extends CMSPlugin implements SubscriberInterface
{
	/**
	 * $db and $app are loaded on instantiation
	 */
	protected ?DatabaseDriver $db = null;

	/** @var CMSApplication $app */
	protected $app = null;

	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onBeforeRender' => 'onBeforeRender',
			'onAfterRender' => 'onAfterRender',
		];
	}

	public function onBeforeRender()
	{
		AttachmentsJavascript::setupModalJavascript();
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
		$app = $this->app;
		$input = $app->getInput();
		$task = $input->getCmd('task');
		$view = $input->getCmd('view');
		$layout = $input->getWord('layout');

		// Make sure this we should handle this
		$parent_type = $input->getCMD('option');
		if (!$parent_type) {
			return;
			}

		// Handle the special case of Global Config for Attachments 3.x
		if (($parent_type == 'com_config') AND ($task == '') AND ($view == ''))
		{
			// Force use of the Attachments options editor

			// option=com_config&view=component&component=com_attachments
			$body = $app->getBody();
			$body = str_replace('option=com_config&view=component&component=com_attachments',
								'option=com_attachments&task=params.edit', $body);
			$app->setBody($body);
		}

		// Handle attachments
		$parent_entity = 'default';

		// Handle categories specially (since they are really com_content)
		if ($parent_type == 'com_categories') {
			$parent_type = 'com_content';
			$parent_entity = 'category';
			}

		// Get the article/parent handler
		if ( !PluginHelper::importPlugin('attachments') ) {
			// Exit if the framework does not exist (eg, during uninstallaton)
			return false;
			}
		if (!class_exists("JMCameron\\Plugin\\AttachmentsPluginFramework\\AttachmentsPluginManager") ) {
			// Exit if the function does not exist (eg, during uninstallaton)
			return false;
			}

		$apm = AttachmentsPluginManager::getAttachmentsPluginManager();
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

		// See if this type of content supports displaying attachments in its editor
		if ($parent->showAttachmentsInEditor($parent_entity, $view, $layout))
		{
			// Get the article/parent handler
			$user_can_add = $parent->userMayAddAttachment($parent_id, $parent_entity);

			// NOTE: I cannot find anything about AttachmentsRemapper class.
			// Could it be old unnecessary code that needs deletion?
			// ------------------------------------------------------
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
			$Itemid = $input->getInt( 'Itemid', 1);
			$from = 'editor';
			$attachments = AttachmentsHelper::attachmentsListHTML($parent_id, $parent_type, $parent_entity,
																  $user_can_add, $Itemid, $from, true, true);

			// If the attachments list is empty, insert an empty div for it
			if ( $attachments == '' ) {
				$params = ComponentHelper::getParams('com_attachments');
				$class_name = $params->get('attachments_table_style', 'attachmentsList');
				$div_id = 'attachmentsList' . '_' . $parent_type . '_' . $parent_entity	 . '_' . (string)$parent_id;
				$attachments = "\n<div class=\"$class_name\" id=\"$div_id\"></div>\n";
				}

			// Insert the attachments above the editor buttons
			// NOTE: Assume that anyone editing the article can see its attachments
			$body = $parent->insertAttachmentsListInEditor($parent_id, $parent_entity,
														   $attachments, $app->getBody());
			$app->setBody($body);
		}
	}
}
