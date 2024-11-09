<?php
/**
 * Attachments component installation script
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @author Jonathan M. Cameron
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;

// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * The main attachments installation class
 *
 * @package Attachments
 */
class com_AttachmentsInstallerScript implements InstallerScriptInterface
{
	/**
	 * An array of supported database types
	 *
	 * @var	   array
	 */
	protected array $dbKnown = array('mysql' => 'MySQL',
							   'mysqli' => 'MySQLi',
							   'pdomysql' => 'PDO MySql',
							   'postgresql' => 'PostgreSQL',
							   'sqlsrv' => 'MS SQL Server',
							   'sqlazure' => 'MS SQL Azure');

	/**
	 * An array of supported database types
	 *
	 * @var	   array
	 */
	protected array $dbSupported = array('mysql', 'mysqli', 'pdomysql');

	/**
	 * Minimum Joomla release supported
	 * 
	 * @var string
	 */
	protected string $minimum_joomla_release = "4.2.0";

	/**
	 * name of moved attachments directory (if present)
	 */
	var $moved_attachments_dir = null;

	/**
	 * List of the plugins
	 */
	var array $plugins = array('plg_content_attachments',
						 'plg_search_attachments',
						 'plg_attachments_plugin_framework',
						 'plg_attachments_for_content',
						 'plg_editors-xtd_add_attachment_btn',
						 'plg_editors-xtd_insert_attachments_token_btn',
						 'plg_system_show_attachments_in_editor',
						 'plg_quickicon_attachments'
						 );


	/**
	 * Attachments component install function
	 *
	 * @param InstallerAdapter $adapter The adapter calling this method
	 * @return boolean True on success
	 */
	public function install(InstallerAdapter $adapter): bool
	{
		$app = Factory::getApplication();
		$app->enqueueMessage(Text::sprintf('ATTACH_ATTACHMENTS_COMPONENT_SUCCESSFULLY_INSTALLED'), 'message');

		com_AttachmentsInstallerScript::installPermissions();

		return true;
	}


	/**
	 * Attachments component update function
	 *
	 * @param InstallerAdapter $adapter The adapter calling this method
	 * @return boolean True on success
	 */
	public function update(InstallerAdapter $adapter): bool
	{
		global $attachments_install_verbose, $attachments_install_last_method;
		$attachments_install_last_method = 'update';

		if ( $attachments_install_verbose ) {
			$app = Factory::getApplication();
			$app->enqueueMessage(Text::sprintf('ATTACH_ATTACHMENTS_COMPONENT_SUCCESSFULLY_UPGRADED'), 'message');
			}

		com_AttachmentsInstallerScript::installPermissions();

		return true;
	}


	/**
	 * Attachments component uninstall function
	 *
	 * @param InstallerAdapter $adapter The adapter calling this method
	 * @return boolean True on success
	 */
	public function uninstall(InstallerAdapter $adapter): bool
	{
		// disable all the plugins
		foreach ($this->plugins as $plugin_name)
		{
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

		return true;
	}


	/**
	 * Attachments component preflight function
	 *
	 * @param string $type The type of change (install or discover_install, update, uninstall)
	 * @param InstallerAdapter $adapter The adapter calling this method
	 * @return boolean True on success
	 */
	public function preflight(string $type, InstallerAdapter $adapter): bool
	{
		$app = Factory::getApplication();

		// Load the installation language
		$lang = $app->getLanguage();

		// First load the English version
		$lang->load('com_attachments.sys', dirname(__FILE__), 'en-GB');
		$lang->load('pkg_attachments.sys', dirname(__FILE__), 'en-GB');

		// Now load the current language (if not English)
		if ( $lang->getTag() != 'en-GB' ) {
			// (Double-loading to fall back to Engish if a new term is missing)
			$lang->load('com_attachments.sys', dirname(__FILE__), null, true);
			$lang->load('pkg_attachments.sys', dirname(__FILE__), null, true);
			}

		// Check to see if the database type is supported
		$db_driver_name = Factory::getContainer()->get('DatabaseDriver')->name;
		if (!in_array($db_driver_name, $this->dbSupported))
		{
			$db_name = $this->dbKnown[$db_driver_name];
			if (empty($db_name)) {
				$db_name = $db_driver_name;
				}
			$errmsg = Text::sprintf('ATTACH_ATTACHMENTS_ERROR_UNSUPPORTED_DB_S', $db_name);
			$app->enqueueMessage($errmsg, 'error');
			return false;
		}

		// Verify that the Joomla version is adequate for this version of the Attachments extension
		$this->minimum_joomla_release = $adapter->getManifest()->attributes()->version;		  
		if ( version_compare(JVERSION, $this->minimum_joomla_release, 'lt') ) {
			$msg = Text::sprintf('ATTACH_ATTACHMENTS_ONLY_WORKS_FOR_VERSION_S_UP', $this->minimum_joomla_release);
			if ( $msg == 'ATTACH_ATTACHMENTS_ONLY_WORKS_FOR_VERSION_S_UP' ) {
				// Handle unupdated languages
				$msg = Text::_('ATTACH_ATTACHMENTS_ONLY_WORKS_FOR_VERSION_16UP');
				$msg = str_replace('1.6', $this->minimum_joomla_release, $msg);
				}
			$app->enqueueMessage($msg, 'error');
			return false;
			}

		// If there is debris from a previous failed attempt to install Attachments, delete it
		// NOTE: Creating custom query because using JComponentHelper::isEnabled insists on
		//		 printing a warning if the component is not installed
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('extension_id AS id, enabled');
		$query->from('#__extensions');
		$query->where($query->qn('type') . ' = ' . $db->quote('component'));
		$query->where($query->qn('element') . ' = ' . $db->quote('com_attachments'));
		$db->setQuery($query);
		if ( $db->loadResult() == 0 )
		{
			if (Folder::exists(JPATH_ROOT . '/components/com_attachments') OR
				Folder::exists(JPATH_ROOT . '/administrator/components/com_attachments'))
			{
				$msg = Text::_('ATTACH_ERROR_UINSTALL_OLD_VERSION');
				$app->enqueueMessage($msg, 'error');
				return false;
			}
		}

		// Temporarily move the attachments directory out of the way to avoid conflicts
		$attachdir = JPATH_ROOT.'/attachments';
		if ( Folder::exists($attachdir) ) {

			// Move the attachments directory out of the way temporarily
			$this->moved_attachments_dir = JPATH_ROOT.'/temporarily_renamed_attachments_folder';
			if ( Folder::move($attachdir, $this->moved_attachments_dir) !== true ) {
				$msg = Text::sprintf('ATTACH_ERROR_MOVING_ATTACHMENTS_DIR');
				$app->enqueueMessage($msg, 'error');
				return false;
				}

			$msg = Text::sprintf('ATTACH_TEMPORARILY_RENAMED_ATTACHMENTS_DIR_TO_S', $this->moved_attachments_dir);
			$app->enqueueMessage($msg, 'message');
			}

		// ??? Joomla! 2.5x+ bugfix for "Can not build admin menus"
		if(in_array($type, array('install','discover_install'))) {
			$this->_bugfixDBFunctionReturnedNoError('com_attachments');
			}
		else {
			$this->_bugfixCantBuildAdminMenus('com_attachments');
			}

		// Joomla 3.x to 4.x remove old mysql upgrade scripts
		if (File::exists(JPATH_ROOT . '/administrator/components/com_attachments/sql/updates/mysql/3.1-2012-11-17.sql')) {
			File::delete(JPATH_ROOT . '/administrator/components/com_attachments/sql/updates/mysql/3.1-2012-11-17.sql');
		}
		if (File::exists(JPATH_ROOT . '/administrator/components/com_attachments/sql/updates/mysql/3.1-2013-04-29.sql')) {
			File::delete(JPATH_ROOT . '/administrator/components/com_attachments/sql/updates/mysql/3.1-2013-04-29.sql');
		}

		return true;
	}


	/**
	 * Attachments component postflight function
	 *
	 * @param string $type The type of change (install or discover_install, update, uninstall)
	 * @param InstallerAdapter $adapter The adapter calling this method
	 * @return boolean True on success
	 */
	public function postflight(string $type, InstallerAdapter $adapter): bool
	{
		$app = Factory::getApplication();
		/** @var \Joomla\Database\DatabaseDriver $db */
		$db = Factory::getContainer()->get('DatabaseDriver');

		// Make sure the translations are available
		$lang = $app->getLanguage();
		$lang->load('com_attachments', JPATH_ADMINISTRATOR);

		// Enable all the plugins
		foreach ($this->plugins as $plugin_name)
		{
			// Make the query to enable the plugin
			$plugin_title = Text::_($plugin_name);
			$query = $db->getQuery(true);
			$query->update('#__extensions');
			$query->set("enabled = 1");
			$query->where('type=' . $db->quote('plugin') . ' AND name=' . $db->quote($plugin_name));
			try {
				$db->setQuery($query);
				$db->execute();
			} catch (\RuntimeException $e) {
				// Complain if there was an error
				$errmsg = Text::sprintf('ATTACH_WARNING_FAILED_ENABLING_PLUGIN_S', $plugin_title);
				$errmsg .= $db->errorMsg;
				$app->enqueueMessage($errmsg, 'error');
				return false;
			}

			$app->enqueueMessage(Text::sprintf('ATTACH_ENABLED_ATTACHMENTS_PLUGIN_S', $plugin_title), 'message');
		}

		$app->enqueueMessage(Text::_('ATTACH_ALL_ATTACHMENTS_PLUGINS_ENABLED'), 'message');

		// Restore the attachments directory (if renamed)
		$attachdir = JPATH_ROOT.'/attachments';
		if ( $this->moved_attachments_dir && Folder::exists($this->moved_attachments_dir) ) {
			Folder::move($this->moved_attachments_dir, $attachdir);
			$app->enqueueMessage(Text::sprintf('ATTACH_RESTORED_ATTACHMENTS_DIR_TO_S', $attachdir), 'message');
			}

		// If needed, add the 'url_verify' column (may be needed because of SQL update issues)
		$attachments_table = '#__attachments';
		$cols = $db->getTableColumns($attachments_table);
		if ( !array_key_exists('url_verify', $cols))
		{
			$query = "ALTER TABLE " . $db->quoteName($attachments_table);
			$query .= " ADD COLUMN " . $db->quoteName('url_verify');
			$query .= " TINYINT(1) UNSIGNED NOT NULL DEFAULT '1'";
			$query .= " AFTER " . $db->quoteName('url_relative');
			$db->setQuery($query);
			try {
				if ( !$db->execute() ) {
					// Ignore any DB errors (may require manual DB mods)
					// ??? $errmsg = $db->stderr();
					}
			} catch (\RuntimeException $e) {
				
			}
		}

		// Check to see if we should be in secure mode
		$htaccess_file = $attachdir . '/.htaccess';
		if ( File::exists($htaccess_file) ) {
			if ( com_AttachmentsInstallerScript::setSecureMode() ) {
				$app->enqueueMessage(Text::_('ATTACH_RESTORED_SECURE_MODE'), 'message');
				}
			}

		// Add warning message about how to uninstall properly
		$emphasis = 'font-size: 125%; font-weight: bold;';
		$padding = 'padding: 0.5em 0;';
		$pkg_name = Text::_('ATTACH_PACKAGE_ATTACHMENTS_FOR_JOOMLA_40PLUS');
		$pkg_uninstall = Text::sprintf('ATTACH_PACKAGE_NOTICE_UNINSTALL_PACKAGE_S', $pkg_name);
		$app->enqueueMessage("<div style=\"$emphasis $padding\">$pkg_uninstall</div>", 'notice');
		
		// Ask the user for feedback
		$msg = Text::sprintf('ATTACH_PLEASE_REPORT_BUGS_AND_SUGGESTIONS_TO_S',
								'<a href="mailto:jmcameron@jmcameron.net">jmcameron@jmcameron.net</a>');
		$app->enqueueMessage("<div style=\"$emphasis $padding\">$msg</div>", 'message');

		return true;
	}


	/**
	 * Install the default ACL/permissions rules for the new attachments privileges in the root rule
	 */
	protected function installPermissions()
	{
		require_once "admin/src/Helper/AttachmentsUpdate.php";
		require_once "site/src/Helper/AttachmentsDefines.php";
		require_once "site/src/Helper/AttachmentsFileTypes.php";
		require_once "site/src/Helper/AttachmentsHelper.php";

		/** Load the Attachments defines */
		JMCameron\Component\Attachments\Administrator\Helper\AttachmentsUpdate::installAttachmentsPermissions();
	}


	/**
	 * Enforce secure mode if attachments/.htaccess file exists and it is a fresh install
	 *
	 * @return true if the secure mode was updated
	 */
	protected function setSecureMode()
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);
		$query->select('*')->from('#__extensions');
		$query->where('type=' . $db->quote('component') . ' AND name=' . $db->quote('com_attachments'));
		try {
			$db->setQuery($query, 0, 1);
			$component = $db->loadObject();
		} catch (\RuntimeException $e) {
			return false;
		}

		if ( $component->params == '{}' ) {
			// Fresh install, update the DB directly (otherwise, this should not be necessary)
			$query = $db->getQuery(true);
			$query->update('#__extensions');
			$query->set('params=' . $db->quote('{\"secure\":\"1\"}'));
			$query->where('type=' . $db->quote('component') . ' AND name=' . $db->quote('com_attachments'));
			try {
				$db->setQuery($query);
				$db->execute();
			} catch (\RuntimeException $e) {
				return false;
			}

			return true;
		}
	}


	/**
	 * Joomla! 1.6+ bugfix for "DB function returned no error"
	 *
	 * Adapted from Akeeba Backup install script (https://www.akeebabackup.com/)
	 * with permission of Nicholas Dionysopoulos (Thanks Nick!)
	 *
	 * @param $extension_name string The name of the extension
	 */
	private function _bugfixDBFunctionReturnedNoError($extension_name)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
			
		// Fix broken #__assets records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where('name = '.$db->quote($extension_name));
			// ??? Removed unneeded db->quote('name') since it failed in Joomla 3.0 Beta
		$db->setQuery($query);
		$ids = $db->loadRow();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__assets')
				  ->where($db->quoteName('id').' = '.$db->quote($id));
			$db->setQuery($query);
			$db->execute();
		}

		// Fix broken #__extensions records
		$query = $db->getQuery(true);
		$query->select('extension_id')
			  ->from('#__extensions')
			  ->where($db->quoteName('element').' = '.$db->quote($extension_name));
		$db->setQuery($query);
		$ids = $db->loadRow();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__extensions')
				  ->where($db->quoteName('extension_id').' = '.$db->quote($id));
			$db->setQuery($query);
			$db->execute();
		}

		// Fix broken #__menu records
		$query = $db->getQuery(true);
		$query->select('id')
			  ->from('#__menu')
			  ->where($db->quoteName('type').' = '.$db->quote('component'))
			  ->where($db->quoteName('menutype').' = '.$db->quote('main'))
			  ->where($db->quoteName('link').' LIKE '.$db->quote('index.php?option='.$extension_name.'%'));
		$db->setQuery($query);
		$ids = $db->loadRow();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__menu')
				  ->where($db->quoteName('id').' = '.$db->quote($id));
			$db->setQuery($query);
			$db->execute();
		}
	}
	
	/**
	 * Joomla! 1.6+ bugfix for "Can not build admin menus"
	 *
	 * Adapted from Akeeba Backup install script (https://www.akeebabackup.com/)
	 * with permission of Nicholas Dionysopoulos (Thanks Nick!)
	 * 
	 */
	private function _bugfixCantBuildAdminMenus($extension_name)
	{
		$db = Factory::getContainer()->get('DatabaseDriver');
		
		// If there are multiple #__extensions record, keep one of them
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->quoteName('element').' = '.$db->quote($extension_name));
		$db->setQuery($query);
		$ids = $db->loadRow();
		if(!is_null($ids) && count($ids) > 1) {
			asort($ids);
			$extension_id = array_shift($ids); // Keep the oldest id
			
			foreach($ids as $id) {
				$query = $db->getQuery(true);
				$query->delete('#__extensions')
					  ->where($db->quoteName('extension_id').' = '.$db->quote($id));
				$db->setQuery($query);
				$db->execute();
			}
		}
		
		// If there are multiple assets records, delete all except the oldest one
		$query = $db->getQuery(true);
		$query->select('id')
			  ->from('#__assets')
			  ->where('name = '.$db->quote($extension_name));
			  // ??? Removed unneeded db->quote('name') since it failed in Joomla 3.0 Beta
		$db->setQuery($query);
		$ids = $db->loadObjectList();
		if(!is_null($ids) && count($ids) > 1) {
			asort($ids);
			$asset_id = array_shift($ids); // Keep the oldest id
			
			foreach($ids as $id) {
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					  ->where($db->quoteName('id').' = '.$db->quote($id));
				$db->setQuery($query);
				$db->execute();
			}
		}

		// Remove #__menu records for good measure!
		$query = $db->getQuery(true);
		$query->select('id')
			  ->from('#__menu')
			  ->where($db->quoteName('type').' = '.$db->quote('component'))
			  ->where($db->quoteName('menutype').' = '.$db->quote('main'))
			  ->where($db->quoteName('link').' LIKE '.$db->quote('index.php?option='.$extension_name.'%'));
		$db->setQuery($query);
		$ids = $db->loadRow();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__menu')
				  ->where($db->quoteName('id').' = '.$db->quote($id));
			$db->setQuery($query);
			$db->execute();
		}
	}
		

}
