<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */
 
defined('_JEXEC') or die;

use JMCameron\Component\Attachments\Administrator\Helper\AttachmentsPermissions;
use JMCameron\Component\Attachments\Site\Controller\AttachmentsController;
use JMCameron\Component\Attachments\Site\Model\AttachmentsModel;
use JMCameron\Plugin\AttachmentsPluginFramework\AttachmentsPluginManager;
use JMCameron\Plugin\AttachmentsPluginFramework\PlgAttachmentsFramework;
use Joomla\Event\Event;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;


define('PF_REGEX_ATTACHIDI_PATTERN', '/\{attachmentsid\sid=([^\}]+?)\}/');



class PlgContentAttachmentsId extends PlgAttachmentsFramework implements SubscriberInterface {

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onContentPrepare' => 'onContentPrepare'
		];
	}
	
	public function onContentPrepare(Event $event)
	{
		if (version_compare(JVERSION, '5', 'lt')) {
			[$context, $row, $params, $page] = $event->getArguments();
		} 
		 else {
			$context = $event['context'];
			$row = $event['subject'];
			$params = $event['params'];
		}
	// Set the parent info from the context
		if (strpos($context, '.') === false)
		{
			// Assume the context is the parent_type
			$parent_type = $context;
			$parent_entity = '';
		}
		else
		{
			list ($parent_type, $parent_entity) = explode('.', $context, 2);
		}
		return $this->OnPrepareRow($row, $parent_type, $parent_entity, $row->id);
	}

	public function OnPrepareRow(&$row, $parent_type, $parent_entity, $parent_id) {	
		static $load = null;

		$matches = array();
		preg_match_all(PF_REGEX_ATTACHIDI_PATTERN, $row->text, $matches);
		if(count($matches[0])){
			$oldid = $row->id;
			for($i = 0, $total = count($matches[0]); $i < $total; $i++){
				$base = $matches[0][$i];
				$attachment_ids = $matches[1][$i];
				JPluginHelper::importPlugin('attachments');
				$apm = AttachmentsPluginManager::getAttachmentsPluginManager();
				if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
					// Exit quietly if there is no Attachments plugin to handle this parent_type
					$row->text = "no plugins";
					return false;
				}
				$parent = $apm->getAttachmentsPlugin($parent_type);
				$mvc = Factory::getApplication()
					->bootComponent("com_attachments")
					->getMVCFactory();
				/** @var \JMCameron\Component\Attachments\Site\Controller\AttachmentsController $controller */
				$controller		  = $mvc->createController('Attachments', 'Site', [], $this->app, $this->app->getInput());
				// parent_id is set arbitrary to 1
				$attachments_list = $controller->displayString($parent_id, $parent_type, 'article', null, true, true, false, 'article', $attachment_ids);
				$row->text = str_replace($base, $attachments_list, $row->text);
			}
		}
	}
}