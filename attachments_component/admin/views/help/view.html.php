<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/** Define the legacy classes, if necessary */
require_once('helpview.php');

/** Load the Attachments defines */
require_once(JPATH_SITE.'/components/com_attachments/defines.php');


/**
 * View for the help page
 * 
 * @package Attachments
 */
class AttachmentsViewHelp extends HelpView
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 */
	public function display($tpl = null)
	{
		$this->version = AttachmentsDefines::$ATTACHMENTS_VERSION;
		$this->date = AttachmentsDefines::$ATTACHMENTS_VERSION_DATE;

		parent::display($tpl);
	}


	/**
	 * Add the start of the permissions table including the header
	 *
	 * @param  string  $col1_code  Language token for column 1 (permission name)
	 * @param  string  $col2_code  Language token for column 2 (permission note)
	 * @param  string  $col3_code  Language token for column 3 (permission action)
	 */
	function startPermissionsTable($col1_code, $col2_code, $col3_code)
	{
		echo "<table id=\"permissions\"class=\"permissions docutils\">\n";
		echo "<colgroup>\n";
		echo "  <col class=\"col_perm_name\"/>\n";
		echo "  <col class=\"col_perm_note\"/>\n";
		echo "  <col class=\"col_perm_action\"/>\n";
		echo "</colgroup>\n";
		echo "<thead>\n";
		echo "  <tr>\n";
		echo "     <th class=\"head\">".JText::_($col1_code)."</th>\n";
		echo "     <th class=\"head\">".JText::_($col2_code)."</th>\n";
		echo "     <th class=\"head\">".JText::_($col3_code)."</th>\n";
		echo "  </tr>\n";
		echo "</thead>\n";
		echo "<tbody>\n";
	}

	/**
	 * Add the a row of the permissions table
	 *
	 * @param  string  $col1_code  Language token for column 1 (permission name)
	 * @param  string  $col2_code  Language token for column 2 (permission note)
	 * @param  string  $col3_code  Language token for column 3 (permission action)
	 */
	function addPermissionsTableRow($col1_code, $col2_code, $col3_code)
	{
		echo "  <tr>\n";
		echo "     <td>".JText::_($col1_code)."</td>\n";
		echo "     <td>".JText::_($col2_code)."</td>\n";
		echo "     <td>".JText::_($col3_code)."</td>\n";
		echo "  </tr>\n";
	}


	/**
	 * Add the end of the permissions table
	 */
	function endPermissionsTable()
	{
		echo "</table>\n";
	}

}
