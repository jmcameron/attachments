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

	/**
	 * @param   integer  $id  The user to load - Can be an integer or string - If string, it is converted to ID automatically.
	 */
	protected function userMayEditCategory($category_id, $id = null)
	{
		$user =& JFactory::getUser($id);

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_content')) {
			return true;
		}

		// Check specific edit permission.
		if ($user->authorise('core.edit', 'com_content.category.'.$category_id)) {
			return true;
		}

		// No general permissions, see if 'edit own' is permitted for this category
		if ( $user->authorise('core.edit.own', 'com_content.category.'.$category_id) ||
			 $user->authorise('core.edit.own', 'com_content')) {

			// Yes, Find out if the user created the category
			$db =& JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('id')->from('#__categories');
			$query->where('id = '.(int)$parent_id.' AND created_user_id = '.(int)$user->id());
			$db->setQuery($query, 0, 1);
			$results = $db->loadObject();
			if ($db->getErrorNum()) {
				$errmsg = JText::_('ERROR_CHECKING_CATEGORY_OWNERSHIP');
				JError::raiseError(500, $errmsg);
				}

			if ( !empty($results) ) {
				// The user did actually create the category
				return true;
				}
			}

		return false;
	}

}	