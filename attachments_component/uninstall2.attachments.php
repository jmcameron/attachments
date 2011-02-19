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
 * Install the component and all related plugins
 *
 * @return true if successful
 */
function com_uninstall()
{
	// Make sure the translations are available
	$lang =&  JFactory::getLanguage();
	$lang->load('com_attachments', JPATH_ADMINISTRATOR);

	// Get the component parameters
	jimport('joomla.application.component.helper');
	$params =& JComponentHelper::getParams('com_attachments');

	// Determine the upload directory
	$upload_subdir = $params->get('attachments_subdir', 'attachments');
	if ( $upload_subdir == '' ) {
		$upload_subdir = "attachments";
		}
	if ( !JFolder::exists( JPATH_SITE . DS . $upload_subdir ) ) {
		$upload_subdir = null;
		}
	echo "<div class=\"header\">" . JText::_('ATTACHMENTS_COMPONENT_SUCCESFULLY_REMOVED') . "</div>\n";
	if ( $upload_subdir ) {
		echo "<h2>" . JText::sprintf('WARNING_YOU_MUST_MANUALLY_DELETE_ATTACHMENTS_DIRECTORY_S',
									 $upload_subdir) . "</h2>\n";
		}

	return true;
}

?>
