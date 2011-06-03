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

// Access check.
if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
	}


/**
 * The controller for special requests
 * (adapted from administrator/components/com_config/controllers/component.php)
 *
 * @package Attachments
 */
class AttachmentsControllerSpecial extends JController
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
		echo "<h1>" . JText::_('ERROR_NO_SPECIAL_FUNCTION_SPECIFIED') . "</h1>";
		exit();
	}


	/**
	 * Show the current SEF mode
	 *
	 * This is for system testing purposes only
	 */
	public function showSEF()
	{
		$app = JFactory::getApplication();
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
		$db =& JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('att.id,parent_id,parent_type,parent_entity,art.catid');
		$query->from('#__attachments as att');
		$query->leftJoin('#__content as art ON att.parent_id = art.id');
		$query->where("att.parent_entity='article'");
		$query->order('art.id');
		$db->setQuery($query);
		$attachments = $db->loadObjectList();

		// Get the category IDs
		$query = $db->getQuery(true);
		$query->select('att.id,att.parent_id,parent_type,parent_entity');
		$query->from('#__attachments as att');
		$query->leftJoin('#__categories as c ON att.parent_id = c.id');
		$query->where("att.parent_entity='category'");
		$query->order('c.id');
		$db->setQuery($query);
		$crows = $db->loadObjectList();

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
			$parent_entity = JString::strtolower($attachment->parent_entity);
			echo ' ' . $attachment->id . '/' . $attachment->parent_id . '/' .
				$attachment->parent_type . '/' . $parent_entity . '/' . $attachment->catid . '<br/>';
			}
		foreach ($crows as $attachment) {
			if ( empty($attachment->id) ) {
				$attachment->id = '0';
				}
			$parent_entity = JString::strtolower($attachment->parent_entity);
			echo ' ' . $attachment->id . '/' . $attachment->parent_id . '/' .
					$attachment->parent_type . '/' . $parent_entity . '/' . $attachment->parent_id . '<br/>';
			}
		echo '</body></html>';
		exit();
	}

}
