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
 * The controller for special requests
 * (adapted from administrator/components/com_config/controllers/component.php)
 *
 * @package Attachments
 */
class AttachmentsControllerSpecial extends JController
{
	/**
	 * Custom Constructor
	 */
	function __construct( $default = array())
	{
		$default['default_task'] = 'noop';
		parent::__construct( $default );
	}

	/** A noop function so this controller does not have a usable default */
	function noop()
	{
		echo "<h1>" . JText::_('ERROR_NO_SPECIAL_FUNCTION_SPECIFIED') . "</h1>";
		exit();
	}


	/**
	 * Show the current SEF mode
	 *
	 * This is for system testing purposes only
	 */
	function showSEF()
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
	function listAttachmentIDs()
	{
		$db =& JFactory::getDBO();

		$query = "SELECT att.id,parent_id,parent_type,parent_entity,art.catid FROM #__attachments as att ";
		$query .= "LEFT JOIN #__content as art ON att.parent_id = art.id WHERE att.parent_entity='ARTICLE' ORDER BY art.id";
		$db->setQuery($query);
		$arows = $db->loadObjectList();

		$query = "SELECT att.id,att.parent_id,parent_type,parent_entity FROM #__attachments as att ";
		$query .= "LEFT JOIN #__categories as c ON att.parent_id = c.id WHERE att.parent_entity='CATEGORY' ORDER BY c.id";
		$db->setQuery($query);
		$crows = $db->loadObjectList();

		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo '<html><head><title>Attachment IDs</title></head><body>';
		echo 'Attachment IDs:<br/>';

		// Do the article attachments
		foreach ($arows as $row) {
			if ( empty($row->id) ) {
				$row->id = '0';
				}
			if ( empty($row->catid) ) {
				$row->catid = '0';
				}
			$parent_entity = JString::strtolower($row->parent_entity);
			echo ' ' . $row->id . '/' . $row->parent_id . '/' .
				$row->parent_type . '/' . $parent_entity . '/' . $row->catid . '<br/>';
			}
		foreach ($crows as $row) {
			if ( empty($row->id) ) {
				$row->id = '0';
				}
			$parent_entity = JString::strtolower($row->parent_entity);
			echo ' ' . $row->id . '/' . $row->parent_id . '/' .
					$row->parent_type . '/' . $parent_entity . '/' . $row->parent_id . '<br/>';
			}
		echo '</body></html>';
		exit();
	}

}

?>
