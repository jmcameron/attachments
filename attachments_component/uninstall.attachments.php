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
 * Helper function to uninstall a plugin
 *
 * @param string $type type of plugin
 * @param int $id id for the plugin
 * @param string $group the group of the plugin
 * @param string $description a description of the plugin to be used in error messages
 */
function _uninstallPlugin($type, $id, $group, $description)
{
	$indent = "&nbsp;&nbsp;&nbsp;&nbsp;";

	$db =& JFactory::getDBO();
	$result = $id;
	switch($type) {

	case 'plugin':
	$db->setQuery("SELECT id FROM #__plugins WHERE folder =	'$group' AND element = '$id'");
	$result = $db->loadResult();
	break;

	case 'module':
	$db->setQuery("SELECT id FROM #__modules WHERE module = '$id'");
	$result = $db->loadResult();
	break;
	}

	if ($result){

	$tmpinstaller = new JInstaller();
	$installer_result = $tmpinstaller->uninstall($type, $result, 0 );

	if(!$result) {
		echo $indent .
		JText::sprintf('UNINSTALL_OF_S_FAILED', $description) . '<br />';
		}
	else {
		echo $indent .
		JText::sprintf('UNINSTALLATION_OF_S_SUCCEEDED', $description) . '<br />';
		}
	}
}


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


	//////////////////////////////////////////////////////////////////////
	//
	// Uninstall the parts/plugins

	$installdir = dirname(__FILE__) . DS . 'install' . DS;

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

	echo '<h2>'. JText::_('UNINSTALLING_PLUGINS') . '</h2>';

	$indent = "&nbsp;&nbsp;&nbsp;&nbsp;";

	// Uninstall each part/plugin
	foreach ( array_keys(get_object_vars($ini)) as $part ) {
	$part = JString::strtolower(JString::trim($part));

	$type = JString::trim($ini->$part->type);
	$element = JString::trim($ini->$part->element);
	$folder = JString::trim($ini->$part->folder);
	$file = JString::trim($ini->$part->file);
	$description = JString::trim($ini->$part->description);

	_uninstallPlugin($type, $element, $folder, $description);
	}
	echo '<h2>' .JText::_('ALL_ATTACHMENTS_PLUGINS_UNINSTALLED') . '</h2>&nbsp;<br />';

	return true;
}

?>
