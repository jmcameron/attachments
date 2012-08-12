<?php
/**
 * Manager for plugins for Attachments
 *
 * @package Attachments
 * @subpackage Attachments_Plugin_Framework
 *
 * @copyright Copyright (C) 2009-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Get the singleton plugin manager for attachments
 *
 * @return the plugin manager singleton object
 */
function &getAttachmentsPluginManager()
{
	static $instance;

	if ( !is_object($instance) ) {
		require_once(dirname(__FILE__).'/attachments_plugin_manager.php');
		$instance = new AttachmentsPluginManager();
		}

	return $instance;
}


/** Make sure the plugin class is loaded for derived classes */
require_once(dirname(__FILE__).'/attachments_plugin.php');
