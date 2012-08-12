<?php
/**
 * Attachments component installation script
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @author Jonathan M. Cameron
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Define some global variables to help figure out whether log messages.
//
//	Note: Apparently with Joomala 2.5, the installer behavior has changed.
//		  If the extension is being installed the first time, it first does the
//		  install() method and the the update() method of this install script class.
//		  Similarly when upgrading a previously installed component, it does the
//		  update() method twice.  Not sure if this is a bug in Joomla or a config
//		  error in this extension.	In any case, these flags are used to eliminate
//		  the duplicate user information messages (about enabled plugins, etc).
//		  The second time through the postFlight() function does not hurt anything,
//		  so there is no point in repeating the inforamtional messages to the user.

/** Flag whether the informational messages should be emitted (warnings always go).
 */
$attachments_install_verbose = true;

/** Name of the last executed install method (install or upgrade)
 */
$attachments_install_last_method = null;


/**
 * The main attachments installation class
 *
 * @package Attachments
 */
class com_AttachmentsInstallerScript {

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
	 * @param $parent : the installer parent
	 */
	public function install($parent)
	{
		global $attachments_install_verbose, $attachments_install_last_method;
		$attachments_install_verbose = true;
		$attachments_install_last_method = 'install';

		$app = JFactory::getApplication();
		$app->enqueueMessage(JText::sprintf('ATTACH_ATTACHMENTS_COMPONENT_SUCCESSFULLY_INSTALLED'), 'message');
		$app->enqueueMessage('<br/>', 'message');

		com_AttachmentsInstallerScript::installPermissions();
		$app->enqueueMessage('<br/>', 'message');
	}


	/**
	 * Attachments component update function
	 *
	 * @param $parent : the installer parent
	 */
	public function update($parent)
	{
		global $attachments_install_verbose, $attachments_install_last_method;
		$attachments_install_last_method = 'update';

		$app = JFactory::getApplication();
		if ( $attachments_install_verbose ) {
			$app->enqueueMessage(JText::sprintf('ATTACH_ATTACHMENTS_COMPONENT_SUCCESSFULLY_UPGRADED'), 'message');
			$app->enqueueMessage('<br/>', 'message');
			}

		com_AttachmentsInstallerScript::installPermissions();

		if ( $attachments_install_verbose ) {
			$app->enqueueMessage('<br/>', 'message');
			}
	}


	/**
	 * Attachments component uninstall function
	 *
	 * @param $parent : the installer parent
	 */
	public function uninstall($parent)
	{
	}


	/**
	 * Attachments component preflight function
	 *
	 * @param $type : type of installation
	 * @param $parent : the installer parent
	 */
	public function preflight($type, $parent)
	{
		global $attachments_install_verbose, $attachments_install_last_method;

		if ( $attachments_install_last_method == 'update' ) {
			$attachments_install_verbose = false;
			}
		if ( $attachments_install_last_method == null ) {
			$attachments_install_verbose = true;
			}

		// Load the installation language
		$lang =  JFactory::getLanguage();
		$lang->load('com_attachments.sys', dirname(__FILE__));
 
		// Verify that the Joomla version is adequate for this version of the Attachments extension
		$this->minimum_joomla_release = $parent->get( 'manifest' )->attributes()->version;		  
		if ( version_compare(JVERSION, $this->minimum_joomla_release, 'lt') ) {
			$msg = JText::sprintf('ATTACH_ATTACHMENTS_ONLY_WORKS_FOR_VERSION_S_UP', $this->minimum_joomla_release);
			if ( $msg == 'ATTACH_ATTACHMENTS_ONLY_WORKS_FOR_VERSION_S_UP' ) {
				// Handle unupdated languages
				$msg = JText::_('ATTACH_ATTACHMENTS_ONLY_WORKS_FOR_VERSION_16UP');
				$msg = str_replace('1.6', $this->minimum_joomla_release, $msg);
				}
			$app = JFactory::getApplication();
			$app->enqueueMessage($msg, 'warning');
			return false;
			}

		// Temporarily move the attachments directory out of the way to avoid conflicts
		jimport('joomla.filesystem.folder');
		$attachdir = JPATH_ROOT.'/attachments';
		if ( JFolder::exists($attachdir) ) {
			$app = JFactory::getApplication();

			// Move the attachments directory out of the way temporarily
			$this->moved_attachments_dir = JPATH_ROOT.'/temporarily_renamed_attachments_folder';
			if ( JFolder::move($attachdir, $this->moved_attachments_dir) !== true ) {
				$msg = JText::sprintf('ATTACH_ERROR_MOVING_ATTACHMENTS_DIR');
				$app->enqueueMessage($msg, 'warning');
				return false;
				}

			if ( $attachments_install_verbose ) {
				$msg = JText::sprintf('ATTACH_TEMPORARILY_RENAMED_ATTACHMENTS_DIR_TO_S', $this->moved_attachments_dir);
				$app->enqueueMessage($msg, 'message');
				$app->enqueueMessage('<br/>', 'message');
				}
			}

		// Joomla! 1.6/1.7 bugfix for "Can not build admin menus"
		if(in_array($type, array('install','discover_install'))) {
			$this->_bugfixDBFunctionReturnedNoError('com_attachments');
			}
		else {
			$this->_bugfixCantBuildAdminMenus('com_attachments');
			}
	}


	/**
	 * Attachments component postflight function
	 *
	 * @param $type : type of installation
	 * @param $parent : the installer parent
	 */
	public function postflight($type, $parent)
	{
		global $attachments_install_verbose, $attachments_install_last_method;

		$app = JFactory::getApplication();
		$db = JFactory::getDBO();

		// Make sure the translations are available
		$lang =  JFactory::getLanguage();
		$lang->load('com_attachments', JPATH_ADMINISTRATOR);

		// Enable all the plugins
		foreach ($this->plugins as $plugin_name)
		{
			// Make the query to enable the plugin
			$plugin_title = JText::_($plugin_name);
			$query = $db->getQuery(true);
			$query->update('#__extensions');
			$query->set("enabled = 1");
			$query->where('type=' . $db->quote('plugin') . ' AND name=' . $db->quote($plugin_name));
			$db->setQuery($query);
			$db->query();

			// Complain if there was an error
			if ( $db->getErrorNum() ) {
				$errmsg = JText::sprintf('ATTACH_WARNING_FAILED_ENABLING_PLUGIN_S', $plugin_title);
				$errmsg .= $db->getErrorMsg();
				$app->enqueueMessage($errmsg, 'error');
				return false;
				}

			if ( $attachments_install_verbose ) {
				$app->enqueueMessage(JText::sprintf('ATTACH_ENABLED_ATTACHMENTS_PLUGIN_S', $plugin_title), 'message');
				}
		}

		if ( $attachments_install_verbose ) {
			$app->enqueueMessage('<br/>', 'message');
			$app->enqueueMessage(JText::_('ATTACH_ALL_ATTACHMENTS_PLUGINS_ENABLED'), 'message');
			$app->enqueueMessage('<br/>', 'message');
			}

		// Restore the attachments directory (if renamed)
		$attachdir = JPATH_ROOT.'/attachments';
		if ( $this->moved_attachments_dir && JFolder::exists($this->moved_attachments_dir) ) {
			JFolder::move($this->moved_attachments_dir, $attachdir);
			if ( $attachments_install_verbose ) {
				$app->enqueueMessage(JText::sprintf('ATTACH_RESTORED_ATTACHMENTS_DIR_TO_S', $attachdir), 'message');
				$app->enqueueMessage('<br/>', 'message');
				}
			}

		// Check to see if we should be in secure mode
		jimport('joomla.filesystem.file');
		$htaccess_file = $attachdir . '/.htaccess';
		if ( JFile::exists($htaccess_file) ) {
			if ( com_AttachmentsInstallerScript::setSecureMode() ) {
				if ( $attachments_install_verbose ) {
					$app->enqueueMessage(JText::_('ATTACH_RESTORED_SECURE_MODE'), 'message');
					$app->enqueueMessage('<br/>', 'message');
					}
				}
			}
		
		// Ask the user for feedback
		if ( $attachments_install_verbose ) {
			$app->enqueueMessage(JText::sprintf('ATTACH_PLEASE_REPORT_BUGS_AND_SUGGESTIONS_TO_S',
												'<a href="mailto:jmcameron@jmcameron.net">jmcameron@jmcameron.net</a>'
												), 'message');
			$app->enqueueMessage('<br/>', 'message');
			}

		// Once postflight has run once, don't repeat the message if it runs again (eg, upgrade after install)
		$attachments_install_verbose = false;
	}


	/**
	 * Install the default ACL/permissions rules for the new attachments privileges in the root rule
	 */
	protected function installPermissions()
	{
		global $attachments_install_verbose;

		/** Load the Attachments defines */
		require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/update.php');
		AttachmentsUpdate::installAttachmentsPermissions($attachments_install_verbose);
	}


	/**
	 * Enforce secure mode if attachments/.htaccess file exists and it is a fresh install
	 *
	 * @return true if the secure mode was updated
	 */
	protected function setSecureMode()
	{
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('*')->from('#__extensions');
		$query->where('type=' . $db->quote('component') . ' AND name=' . $db->quote('com_attachments'));
		$db->setQuery($query, 0, 1);
		$component = $db->loadObject();
		if ( $db->getErrorNum() ) {
			return false;
			}
		if ( $component->params == '{}' ) {
			// Fresh install, update the DB directly (otherwise, this should not be necessary)
			$query = $db->getQuery(true);
			$query->update('#__extensions');
			$query->set('params=' . $db->quote('{\"secure\":\"1\"}'));
			$query->where('type=' . $db->quote('component') . ' AND name=' . $db->quote('com_attachments'));
			$db->setQuery($query);
			$db->query();
			if ( $db->getErrorNum() ) {
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
		$db = JFactory::getDbo();
			
		// Fix broken #__assets records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->nameQuote('name').' = '.$db->quote($extension_name));
		$db->setQuery($query);
		$ids = $db->loadResultArray();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__assets')
				->where($db->nameQuote('id').' = '.$db->quote($id));
			$db->setQuery($query);
			$db->query();
		}

		// Fix broken #__extensions records
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->nameQuote('element').' = '.$db->quote($extension_name));
		$db->setQuery($query);
		$ids = $db->loadResultArray();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__extensions')
				->where($db->nameQuote('extension_id').' = '.$db->quote($id));
			$db->setQuery($query);
			$db->query();
		}

		// Fix broken #__menu records
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->nameQuote('type').' = '.$db->quote('component'))
			->where($db->nameQuote('menutype').' = '.$db->quote('main'))
			->where($db->nameQuote('link').' LIKE '.$db->quote('index.php?option='.$extension_name.'%'));
		$db->setQuery($query);
		$ids = $db->loadResultArray();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__menu')
				->where($db->nameQuote('id').' = '.$db->quote($id));
			$db->setQuery($query);
			$db->query();
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
		$db = JFactory::getDbo();
		
		// If there are multiple #__extensions record, keep one of them
		$query = $db->getQuery(true);
		$query->select('extension_id')
			->from('#__extensions')
			->where($db->nameQuote('element').' = '.$db->quote($extension_name));
		$db->setQuery($query);
		$ids = $db->loadResultArray();
		if(count($ids) > 1) {
			asort($ids);
			$extension_id = array_shift($ids); // Keep the oldest id
			
			foreach($ids as $id) {
				$query = $db->getQuery(true);
				$query->delete('#__extensions')
					->where($db->nameQuote('extension_id').' = '.$db->quote($id));
				$db->setQuery($query);
				$db->query();
			}
		}
		
		// If there are multiple assets records, delete all except the oldest one
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__assets')
			->where($db->nameQuote('name').' = '.$db->quote($extension_name));
		$db->setQuery($query);
		$ids = $db->loadObjectList();
		if(count($ids) > 1) {
			asort($ids);
			$asset_id = array_shift($ids); // Keep the oldest id
			
			foreach($ids as $id) {
				$query = $db->getQuery(true);
				$query->delete('#__assets')
					->where($db->nameQuote('id').' = '.$db->quote($id));
				$db->setQuery($query);
				$db->query();
			}
		}

		// Remove #__menu records for good measure!
		$query = $db->getQuery(true);
		$query->select('id')
			->from('#__menu')
			->where($db->nameQuote('type').' = '.$db->quote('component'))
			->where($db->nameQuote('menutype').' = '.$db->quote('main'))
			->where($db->nameQuote('link').' LIKE '.$db->quote('index.php?option='.$extension_name.'%'));
		$db->setQuery($query);
		$ids = $db->loadResultArray();
		if(!empty($ids)) foreach($ids as $id) {
			$query = $db->getQuery(true);
			$query->delete('#__menu')
				->where($db->nameQuote('id').' = '.$db->quote($id));
			$db->setQuery($query);
			$db->query();
		}
	}
		

}
