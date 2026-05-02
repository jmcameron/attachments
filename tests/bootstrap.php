<?php

/**
 * Bootstrap file for unit tests
 *
 * @package Attachments
 * @subpackage Tests
 *
 * This file sets up the testing environment for the Attachments package
 */

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', '/dev/stderr');

// Define required constants
defined('JPATH_PLATFORM') or define('JPATH_PLATFORM', __DIR__);
defined('JPATH_BASE') or define('JPATH_BASE', realpath(__DIR__ . '/..'));
defined('JPATH_ROOT') or define('JPATH_ROOT', JPATH_BASE);
defined('JPATH_SITE') or define('JPATH_SITE', JPATH_ROOT);
defined('JPATH_ADMINISTRATOR') or define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
defined('JPATH_LIBRARIES') or define('JPATH_LIBRARIES', JPATH_ROOT . '/libraries');
defined('JPATH_TESTS') or define('JPATH_TESTS', __DIR__);
defined('JPATH_CONFIGURATION') or define('JPATH_CONFIGURATION', JPATH_ROOT);
defined('JDEBUG') or define('JDEBUG', 0);
defined('JPATH_CACHE') or define('JPATH_CACHE', "temp");

// Set up Composer autoloader
$composerAutoload = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($composerAutoload)) {
    echo "Composer autoload not found. Please run 'composer install' first.\n";
    exit(1);
}

require_once $composerAutoload;

define('_JEXEC', 1);

// Set up the Joomla testing framework if available
if (class_exists('\\Joomla\\CMS\\Factory')) {
    // If running within a Joomla installation, use the Joomla factory
    // This is for when tests are run from within a Joomla instance
} else {
    // Setup Joomla framework for standalone testing
    // This is where we'd initialize mock services if needed
}
