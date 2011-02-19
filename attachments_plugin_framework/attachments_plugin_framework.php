<?php
/**
 * Manager for plugins for Attachments
 *
 * @package Attachments
 * @subpackage Attachments_Plugin_Framework
 *
 * @copyright Copyright (C) 2009-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Get the plugin manager for attachments
 */
function &getAttachmentsPluginManager()
{
	static $instance;

	if ( !is_object($instance) ) {
		require_once(dirname(__FILE__).DS.'attachments_plugin_manager.php');
		$instance = new AttachmentsPluginManager();
		}

	return $instance;
}

?>
