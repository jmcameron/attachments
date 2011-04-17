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


	/** Show the current SEF mode */
	function showSEF()
	{
		$app = JFactory::getApplication();
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo "<html><head><title>SEF Status</title></head><body>";
		echo "SEF: " . $app->getCfg('sef') . "<br />";
		echo "</body></html>";
		exit();
	}


	/** Show a list of all attachment IDs */
	function listAttachmentIDs()
	{
		$db =& JFactory::getDBO();
		$query = "SELECT attach.id,parent_id,parent_type,art.catid FROM #__attachments as attach ";
		$query .= "LEFT JOIN #__content as art ON attach.parent_id = art.id ORDER BY art.id";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
		echo "\n<html><head><title>Attachment IDs</title></head><body>\n";
		echo "Attachment IDS:";
		foreach ($rows as $row) {
			echo " " . $row->id . "/" . $row->parent_id . "/" . $row->parent_type . "/" . $row->catid;
			}
		echo "\n</body></html>";
		exit();
	}

}

?>
