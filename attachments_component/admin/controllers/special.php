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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\String\StringHelper;

defined('_JEXEC') or die('Restricted access');

// Access check.
$app = Factory::getApplication();
$user = $app->getIdentity();
if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 147)', 404);
	}

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/controller.php');


/**
 * The controller for special requests
 * (adapted from administrator/components/com_config/controllers/component.php)
 *
 * @package Attachments
 */
class AttachmentsControllerSpecial extends BaseController
{
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 */
	public function __construct( $default = array())
	{
		$default['default_task'] = 'noop';
		parent::__construct( $default );
	}


	/**
	 * A noop function so this controller does not have a usable default
	 */
	public function noop()
	{
		echo "<h1>" . Text::_('ATTACH_ERROR_NO_SPECIAL_FUNCTION_SPECIFIED') . "</h1>";
		exit();
	}


	/**
	 * Show the current SEF mode
	 *
	 * This is for system testing purposes only
	 */
	public function showSEF()
	{
		$app = Factory::getApplication();
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo "<html><head><title>SEF Status</title></head><body>";
		echo "SEF: " . $app->getCfg('sef') . "<br />";
		echo "</body></html>";
		exit();
	}


	/**
	 * Show a list of all attachment IDs
	 *
	 * This is for system testing purposes only
	 */
	public function listAttachmentIDs()
	{
		// Get the article IDs
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('att.id,parent_id,parent_type,parent_entity,art.catid');
		$query->from('#__attachments as att');
		$query->leftJoin('#__content as art ON att.parent_id = art.id');
		$query->where('att.parent_entity=' . $db->quote('article'));
		$query->order('art.id');
		try {
			$db->setQuery($query);
			$attachments = $db->loadObjectList();
		} catch (RuntimeException $e) {
			$errmsg = $e->getMessage() . ' (ERR 148)';
			throw new Exception($errmsg, 500);
		}

		// Get the category IDs
		$query = $db->getQuery(true);
		$query->select('att.id,att.parent_id,parent_type,parent_entity');
		$query->from('#__attachments as att');
		$query->leftJoin('#__categories as c ON att.parent_id = c.id');
		$query->where('att.parent_entity=' . $db->quote('category'));
		$query->order('c.id');
		try {
			$db->setQuery($query);
			$crows = $db->loadObjectList();
		} catch (RuntimeException $e) {
			$errmsg = $e->getMessage() . ' (ERR 149)';
			throw new Exception($errmsg, 500);
		}

		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo '<html><head><title>Attachment IDs</title></head><body>';
		echo 'Attachment IDs:<br/>';

		// Do the article attachments
		foreach ($attachments as $attachment) {
			if ( empty($attachment->id) ) {
				$attachment->id = '0';
				}
			if ( empty($attachment->catid) ) {
				$attachment->catid = '0';
				}
			$parent_entity = StringHelper::strtolower($attachment->parent_entity);
			echo ' ' . $attachment->id . '/' . $attachment->parent_id . '/' .
				$attachment->parent_type . '/' . $parent_entity . '/' . $attachment->catid . '<br/>';
			}
		foreach ($crows as $attachment) {
			if ( empty($attachment->id) ) {
				$attachment->id = '0';
				}
			$parent_entity = StringHelper::strtolower($attachment->parent_entity);
			echo ' ' . $attachment->id . '/' . $attachment->parent_id . '/' .
					$attachment->parent_type . '/' . $parent_entity . '/' . $attachment->parent_id . '<br/>';
			}
		echo '</body></html>';
		exit();
	}

	
	/**
	 * Show a list of all attachment IDs
	 *
	 * This is for system testing purposes only
	 */
	public function listKnownParentTypes()
	{
		// Get the article/parent handler
		PluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();

		$ptypes = $apm->getInstalledParentTypes();
		echo implode('<br/>', $ptypes);
	}
}
