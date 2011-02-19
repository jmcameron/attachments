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
 * Install the component
 *
 * Note that this also installs all related components and plugins and enables
 * the plugins.
 */
function com_install()
{
	// Make sure the translations are available
	$lang =&  JFactory::getLanguage();
	$lang->load('com_attachments', JPATH_ADMINISTRATOR);

	// First make sure that this version of Joomla is 1.5 or greater
	$version = new JVersion();
	if ( (real)$version->RELEASE < 1.5 ) {
		echo '<h1 style="color: red;">' .
			JText::_('ATTACHMENTS_ONLY_WORKS_FOR_VERSION_15') . '</h1>';
		return false;
		}

	// Update the attachments table
	echo "<h3>" . JText::_('UPDATING_ATTACHMENTS_TABLE') . "</h3>\n";
	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'update.php');
	AttachmentsUpdate::update_attachments_table();

	// Check to see if the main plugin has been installed yet
	$plugin_installed = false;
	$plugin_published = false;
	$db =& JFactory::getDBO();
	$query = "SELECT published FROM #__plugins "
		. "WHERE element='attachments' AND folder='content' LIMIT 1";
	$db->setQuery($query);
	$rows = $db->loadObjectList();
	if ( count($rows) > 0 ) {
		$plugin_installed = true;
		$plugin_published = ((int)$rows[0]->published == 1);
		}

	echo "<br />";

	echo '<div class="header">' .
		JText::_('ATTACHMENTS_COMPONENT_SUCCESFULLY_INSTALLED') . ' </div>';

	if ( !$plugin_published ) {
		echo "<i>Don&rsquo;t forget to install the plugins too!</i>";
		}
	echo "<br />&nbsp;<br />";

	?>
	<br />
	<hr />
	<h2><?php echo JText::_('NOTES_COLON'); ?></h2>
	<p style="font-size: 1.3em"><?php echo JText::_('INSTALL_NOTES_1'); ?></p>
	<p style="font-size: 1.3em"><?php echo JText::_('INSTALL_NOTES_2'); ?></p>
	<p style="font-size: 1.3em"><?php echo JText::_('INSTALL_NOTES_3'); ?></p>
	<p style="font-size: 1.3em"><?php echo JText::_('INSTALL_NOTES_4'); ?></p>

	<p><?php echo JText::sprintf('PLEASE_REPORT_BUGS_AND_SUGGESTIONS_TO_S',
	   '<a href="mailto:jmcameron@jmcameron.net">jmcameron@jmcameron.net</a>'); ?>.
	</p>
	<?php

	return true;
}

?>
