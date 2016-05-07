<?php
/**
 * Attachments plugin installation script
 *
 * @package Attachments
 * @subpackage Attachments_Plugin
 *
 * @author Jonathan M. Cameron
 * @copyright Copyright (C) 2014-2016 Jonathan M. Cameron
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 */

// No direct access
defined('_JEXEC') or die('Restricted access');


/**
 * The attachments content plugin installation class
 *
 * @package Attachments
 */
class plgContentAttachmentsInstallerScript 
{

	/**
	 * Attachments plugin uninstall function
	 *
	 * @param $parent : the installer parent
	 */
	public function uninstall($parent)
	{
		// List all the Attachments plugins
		$plugins = Array('plg_content_attachments',
						 'plg_search_attachments',
						 'plg_attachments_plugin_framework',
						 'plg_attachments_for_content',
						 'plg_editors-xtd_add_attachment_btn',
						 'plg_editors-xtd_insert_attachments_token_btn',
						 'plg_system_show_attachments_in_editor',
						 'plg_quickicon_attachments'
						 );

		// To be safe, disable ALL attachments plugins first!
		foreach ($plugins as $plugin_name)
		{
			// Make the query to enable the plugin
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->update('#__extensions') 
				  ->set("enabled = 0")
				  ->where('type=' . $db->quote('plugin') . ' AND name=' . $db->quote($plugin_name));
			$db->setQuery($query);
			$db->query();

			// NOTE: Do NOT complain if there was an error
			// (in case any plugin is already uninstalled and this query fails)
		}
	}

}
