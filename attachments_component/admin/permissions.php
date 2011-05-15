<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');


/**
 * A class for attachments permissions functions
 *
 * @package Attachments
 */
class AttachmentsPermissions
{

	/**
	 * Get the actions
	 *
	 * @return an array of which actions are permitted for this user
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;
 
		$assetName = 'com_attachments';
 
		$actions = array( 'core.admin', 'core.manage',
						  'core.create', 'core.delete', 'core.edit.state',
						  'core.edit', 'core.edit.own'
						 );
 
		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
			}
 
		return $result;
	}

}	