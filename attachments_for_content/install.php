<?php

/**
 * Attachments plugins for content installation script
 *
 * @package     Attachments
 * @subpackage  Attachments_Plugin_For_Content
 *
 * @author Jonathan M. Cameron
 * @copyright Copyright (C) 2014-2025 Jonathan M. Cameron
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 */

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * The attachments content plugin installation class
 *
 * @package Attachments
 */
class plgAttachmentsAttachments_for_contentInstallerScript
{
    /**
     * Attachments plugin uninstall function
     *
     * @param $parent : the installer parent
     */
    public function uninstall($parent)
    {
        // List all the Attachments plugins
        $plugins = array('plg_content_attachments',
                         'plg_search_attachments',
                         'plg_attachments_plugin_framework',
                         'plg_attachments_for_content',
                         'plg_editors-xtd_add_attachment_btn',
                         'plg_editors-xtd_insert_attachments_token_btn',
                         'plg_system_show_attachments_in_editor',
                         'plg_quickicon_attachments'
                         );

        // To be safe, disable ALL attachments plugins first!
        foreach ($plugins as $plugin_name) {
            // Make the query to enable the plugin
            $db = Factory::getContainer()->get('DatabaseDriver');
            $query = $db->getQuery(true);
            $query->update('#__extensions')
                  ->set("enabled = 0")
                  ->where('type=' . $db->quote('plugin') . ' AND name=' . $db->quote($plugin_name));
            $db->setQuery($query);
            $db->execute();

            // NOTE: Do NOT complain if there was an error
            // (in case any plugin is already uninstalled and this query fails)
        }
    }
}
