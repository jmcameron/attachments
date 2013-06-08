<?php
/**
 * Attachments component
 *
 * @package     Attachments
 * @subpackage  Attachments_Component
 *
 * @author      Jonathan M. Cameron <jmcameron@jmcameron.net>
 * @copyright   Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        http://joomlacode.org/gf/project/attachments/frs/
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Define the legacy classes, if necessary
require_once 'helpview.php';

// Load the Attachments defines
require_once JPATH_SITE . '/components/com_attachments/defines.php';


/**
 * View for the help page
 *
 * @package  Attachments
 * @since    3.1
 */
class AttachmentsViewHelp extends HelpView
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 * @return nothing
	 */
	public function display($tpl = null)
	{
		$this->logo_img = JHtml::image('com_attachments/attachments_help_logo32.png', '', null, true);
		$this->version = AttachmentsDefines::$ATTACHMENTS_VERSION;
		$rdate = new JDate(AttachmentsDefines::$ATTACHMENTS_VERSION_DATE);
		$this->date = JHtml::_('date', $rdate, JText::_('DATE_FORMAT_LC1'));

		parent::display($tpl);
	}

	/**
	 * Add the start of the permissions table including the header
	 *
	 * @param   string  $col1_code  Language token for column 1 (permission name)
	 * @param   string  $col2_code  Language token for column 2 (permission note)
	 * @param   string  $col3_code  Language token for column 3 (permission action)
	 *
	 * @return nothing
	 */
	protected function startPermissionsTable($col1_code, $col2_code, $col3_code)
	{
		echo "<table id=\"permissions\"class=\"permissions docutils\">\n";
		echo "<colgroup>\n";
		echo "  <col class=\"col_perm_name\"/>\n";
		echo "  <col class=\"col_perm_note\"/>\n";
		echo "  <col class=\"col_perm_action\"/>\n";
		echo "</colgroup>\n";
		echo "<thead>\n";
		echo "  <tr>\n";
		echo "     <th class=\"head\">" . JText::_($col1_code) . "</th>\n";
		echo "     <th class=\"head\">" . JText::_($col2_code) . "</th>\n";
		echo "     <th class=\"head\">" . JText::_($col3_code) . "</th>\n";
		echo "  </tr>\n";
		echo "</thead>\n";
		echo "<tbody>\n";
	}

	/**
	 * Add the a row of the permissions table
	 *
	 * @param   string  $col1_code  Language token for column 1 (permission name)
	 * @param   string  $col2_code  Language token for column 2 (permission note)
	 * @param   string  $col3_code  Language token for column 3 (permission action)
	 *
	 * @return nothing
	 */
	protected function addPermissionsTableRow($col1_code, $col2_code, $col3_code)
	{
		echo "  <tr>\n";
		echo "     <td>" . JText::_($col1_code) . "</td>\n";
		echo "     <td>" . JText::_($col2_code) . "</td>\n";
		echo "     <td>" . JText::_($col3_code) . "</td>\n";
		echo "  </tr>\n";
	}

	/**
	 * Add the end of the permissions table
	 *
	 * @return nothing
	 */
	protected function endPermissionsTable()
	{
		echo "</table>\n";
	}
}
