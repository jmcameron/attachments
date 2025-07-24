<?php

/**
 * Attachments component installation script
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @author Jonathan M. Cameron
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The main attachments installation class
 *
 * @package Attachments
 */
// phpcs:ignore
class pkg_AttachmentsInstallerScript implements InstallerScriptInterface
{
    /**
     * Attachments component install function
     *
     * @param InstallerAdapter $adapter The adapter calling this method
     * @return boolean True on success
     */
    public function install(InstallerAdapter $adapter): bool
    {
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
        $lang->load('pkg_attachments.sys', dirname(__FILE__), 'en-GB');

        if (Folder::exists(JPATH_ROOT . '/plugins/attachments/attachments_plugin_framework')) {
            $msg = Text::_('ATTACH_PACKAGE_REMOVE_OLD_ATTACHMENTS_FRAMEWORK');
            $app->enqueueMessage($msg, 'error');
            return false;
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
        return true;
    }
}
