<?php
/**
 * Attachments component installation script
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * The main attachments installation class
 *
 * @package Attachments
 */
class Com_AttachmentsInstallerScript {

	/**
	 * name of moved attachments directory (if present)
	 */
	var $moved_attachments_dir = null;

	/**
	 * List of the plugins
	 */
	var $plugins = Array('plg_content_attachments',
						 'plg_search_attachments',
						 'plg_attachments_plugin_framework',
						 'plg_attachments_for_content',
						 'plg_editors-xtd_add_attachment_btn',
						 'plg_editors-xtd_insert_attachments_token_btn',
						 'plg_system_show_attachments_in_editor');


	/**
	 * Attachments component install function
	 *
	 * @param $parent : the name of the installer parent
	 */
	function install($parent)
	{
		$app = JFactory::getApplication('administrator');
		$app->enqueueMessage(JText::sprintf('ATTACHMENTS_COMPONENT_SUCCESFULLY_INSTALLED'), 'message');
		$app->enqueueMessage('<br/>', 'message');
	}

	/**
	 * Attachments component uninstall function
	 *
	 * @param $parent : the name of the installer parent
	 */
	function uninstall($parent)
	{
	}

	/**
	 * Attachments component update function
	 *
	 * @param $parent : the name of the installer parent
	 */
	function update($parent)
	{
	}


	/**
	 * Attachments component preflight function
	 *
	 * @param $type : type of installation
	 * @param $parent : the name of the installer parent
	 */
	function preflight($type, $parent)
	{
		// Load the installation language
		$lang =&  JFactory::getLanguage();
		$lang->load('com_attachments.sys', dirname(__FILE__));

		// Temporarily move the attachments directory out of the way to avoid conflicts
		jimport('joomla.filesystem.folder');
		$attachdir = JPATH_ROOT . DS . 'attachments';
		if ( JFolder::exists($attachdir) ) {

			// Move the attachments directory out of the way temporarily
			$this->moved_attachments_dir = JPATH_ROOT. DS . 'temporarily_renamed_attachments_folder';
			// ?? SHOULD Catch errors here
			JFolder::move($attachdir, $this->moved_attachments_dir);

			$app = JFactory::getApplication('administrator');
			$msg = JText::sprintf('TEMPORARILY_RENAMED_ATTACHMENTS_DIR_TO_S', $this->moved_attachments_dir);
			$app->enqueueMessage($msg, 'message');
			$app->enqueueMessage('<br/>', 'message');
			}
	}


	/**
	 * Attachments component postflight function
	 *
	 * @param $type : type of installation
	 * @param $parent : the name of the installer parent
	 */
	function postflight($type, $parent) 
	{
		$app = JFactory::getApplication('administrator');
		$db =& JFactory::getDBO();

		// Make sure the translations are available
		$lang =&  JFactory::getLanguage();
		$lang->load('com_attachments', JPATH_ADMINISTRATOR);

		// Enable all the plugins
		foreach ($this->plugins as $plugin_name)
		{
			// Make the query to enable the plugin
			$plugin_title = JText::_($plugin_name);
			$query = $db->getQuery(true);
			$query->update('#__extensions');
			$query->set("enabled = 1");
			$query->where("type = 'plugin' AND name = '" . $plugin_name . "'");
			$db->setQuery($query);
			$db->query();

			// Complain if there was an error
			if ( $db->getErrorNum() ) {
				$errmsg = JText::sprintf('WARNING_FAILED_ENABLING_PLUGIN_S', $plugin_title);
				$errmsg .= $db->getErrorMsg();
				$app->enqueueMessage($errmsg, 'error');
				return false;
				}
			$app->enqueueMessage(JText::sprintf('ENABLED_ATTACHMENTS_PLUGIN_S', $plugin_title), 'message');
		}
		$app->enqueueMessage('<br/>', 'message');
		$app->enqueueMessage(JText::_('ALL_ATTACHMENTS_PLUGINS_ENABLED'), 'message');
		$app->enqueueMessage('<br/>', 'message');

		// Restore the attachments directory (if renamed)
		if ( $this->moved_attachments_dir AND JFolder::exists($this->moved_attachments_dir) ) {
			$attachdir = JPATH_ROOT . DS . 'attachments';
			JFolder::move($this->moved_attachments_dir, $attachdir);
			$app->enqueueMessage(JText::sprintf('RESTORED_ATTACHMENTS_DIR_TO_S', $attachdir), 'message');
			$app->enqueueMessage('<br/>', 'message');
			}
	}
}
