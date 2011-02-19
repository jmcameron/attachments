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

	// Delete obsolete attachments files (from Attachments version 1.3.4)
	$obsolete_files = Array(
		JPATH_SITE . DS . 'components/com_attachments/permissions.php',
		JPATH_SITE . DS . 'components/com_attachments/models/update.php',
		JPATH_SITE . DS . 'components/com_attachments/media/icons/odg.gif',
		JPATH_SITE . DS . 'components/com_attachments/media/icons/ods.gif',
		JPATH_SITE . DS . 'components/com_attachments/media/icons/odp.gif',
		JPATH_SITE . DS . 'components/com_attachments/media/icons/odt.gif',
		JPATH_SITE . DS . 'administrator/components/com_attachments/admin.attachments.html.php',
		JPATH_SITE . DS . 'administrator/components/com_attachments/attachments_admin32.png'
		);
	$printed_head = false;
	jimport('joomla.filesystem.file');
	foreach($obsolete_files as $file) {
		if ( JFile::exists($file) ) {
			if ( ! $printed_head ) {
				echo '<h3>' . JText::_('DELETING_OBSOLETE_FILES_COLON') . '</h3>';
				$printed_head = true;
				}
			if ( JFile::delete($file) ) {
				echo JText::sprintf('DELETED_OBSOLETE_FILE_S', $file) . "<br />";
				}
			else {
				echo JText::sprintf('WARNING_UNABLE_TO_DELETE_OBSOLETE_FILE_S', $file) . "<br />";
				}
			}

		// NOTE: If the file is not found, just skip it quietly
		}

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

	//////////////////////////////////////////////////////////////////////
	// Install all the plugins

	$installdir = dirname(__FILE__) . DS . 'install' . DS;

	// Install Package Manager
	jimport('joomla.installer.helper');
	$tmpInstaller = new JInstaller();

	// Read the plugins/parts to install from the manifest.ini file
	//	(Since the normal registry loader does not process sections,
	//	 we must invoke the INI handler directly)
	$handler =& JRegistryFormat::getInstance('INI');

	// Read the file
	jimport('joomla.filesystem.file');
	$file = $installdir . 'manifest.ini';
	$data = JFile::read($file);

	// Construct an object with the info from the INI file
	$ini = $handler->stringToObject($data, $process_sections = true );

	echo "<h2>". JText::_('INSTALLING_PLUGINS') . "</h2>";

	$indent = "&nbsp;&nbsp;&nbsp;&nbsp;";

	// install each part/plugin
	$plugin_install_error = false;
	foreach ( array_keys(get_object_vars($ini)) as $part ) {
		$part = JString::strtolower(JString::trim($part));

		$type = JString::trim($ini->$part->type);
		$element = JString::trim($ini->$part->element);
		$folder = JString::trim($ini->$part->folder);
		$file = JString::trim($ini->$part->file);
		$description = JString::trim($ini->$part->description);

		// Unzip the part
		$package = JInstallerHelper::unpack($installdir . $file);

		// Install the part
		if ($tmpInstaller->install($package['dir'])) {

			// Enable the plugin
			if ( $type == 'plugin' ) {
				$query = "UPDATE #__plugins SET `published`=1 "
					. "WHERE `element`='$element' AND `folder`='$folder' LIMIT 1";
				$db->setQuery($query);
				if ( !$db->query() ) {
					echo $indent . $indent .
						JText::sprintf('WARNING_FAILED_ENABLING_PLUGIN_S', $description) . '<br />';
					echo $indent . $indent . '(' . $db->getErrorMsg() . ')<br />';
					}
				}
			echo $indent .
				JText::sprintf('ATTACHMENTS_PLUGIN_S_INSTALLED', $description) . '<br />';
			}
		else {
			echo $indent .
				JText::sprintf('ERROR_INSTALLING_ATTCHMENT_PLUGIN_S', $description) . '<br />';
			$plugin_install_error = true;
			}
		}
	if ( !$plugin_install_error ) {
		echo "<h2>" . JText::_('ALL_ATTACHMENTS_PLUGINS_ENABLED') . '</h2>';
		}

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
