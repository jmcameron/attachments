<?php

/**
 * Base test case class for Attachments component tests
 *
 * @package Attachments
 * @subpackage Tests
 */

namespace Tests;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Joomla\CMS\Factory;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserFactory;
use Joomla\CMS\Application\CMSApplication;
use Joomla\DI\Container;

/**
 * Base class for Attachments tests
 */
abstract class AttachmentsTestCase extends TestCase
{
    public static $instance;
    /**
     * @var MockObject The mock application
     */
    public $mockApp;

    /**
     * @var MockObject The mock container
     */
    public $mockContainer;

    /**
     * @var MockObject The mock user factory
     */
    protected $mockUserFactory;

    /**
     * @var MockObject The mock user
     */
    protected $mockUser;

    /**
     * @var MockObject The mock database driver
     */
    protected $mockDatabaseDriver;

    /**
     * @var array Permission configuration for current test
     */
    protected $permissions = [];

    /**
     * @var MockObject The mock language object
     */
    protected $mockLang;

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::$instance = $this;

        // Set up required Joomla constants if not already defined
        if (!defined('JPATH_ROOT')) {
            define('JPATH_ROOT', realpath(__DIR__ . '/..'));
        }
        if (!defined('JPATH_SITE')) {
            define('JPATH_SITE', JPATH_ROOT);
        }
        if (!defined('JPATH_ADMINISTRATOR')) {
            define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
        }
        if (!defined('_JEXEC')) {
            define('_JEXEC', 1);
        }
        if (!defined('JPATH_CONFIGURATION')) {
            define('JPATH_CONFIGURATION', JPATH_ROOT);
        }
        if (!defined('JDEBUG')) {
            define('JDEBUG', 0);
        }


        $this->setUpJoomlaMocks();
    }

    /**
     * Sets up common Joomla CMS mocks including the Factory class
     */
    protected function setUpJoomlaMocks(): void
    {
        // Create mock container
        $this->mockContainer = $this->getMockBuilder('Joomla\DI\Container')
            ->disableOriginalConstructor()
            ->getMock();

        // Create mock user factory
        $this->mockUserFactory = $this->getMockBuilder('Joomla\CMS\User\UserFactory')
            ->disableOriginalConstructor()
            ->getMock();

        // Create mock user
        $this->mockUser = $this->getMockBuilder('Joomla\CMS\User\User')
            ->disableOriginalConstructor()
            ->onlyMethods(['authorise'])
            ->getMock();

        // Create mock application
        $this->mockApp = $this->getMockBuilder('Joomla\CMS\Application\CMSApplication')
            ->disableOriginalConstructor()
            ->onlyMethods(['getIdentity'])
            ->getMockForAbstractClass();

        // Set up basic user properties
        $this->mockUser->id = 42;
        $this->mockUser->name = 'Test User';
        $this->mockUser->username = 'testuser';
        $this->mockUser->email = 'test@example.com';

        // Default authorise to false
        $this->mockUser->method('authorise')
            ->willReturn(false);

        // Set up database driver mock
        $this->mockDatabaseDriver = $this->getMockBuilder('Joomla\Database\DatabaseDriver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mockDatabaseDriver->method('getQuery')
            ->willReturnCallback(function() {
                return new \Joomla\Database\Mysqli\MysqliQuery();
            });
            
        $this->mockDatabaseDriver->method('loadObject')
            ->willReturnCallback(function() {
                return null;
            });

        // Set up container to return user factory
        $this->mockContainer->method('get')
            ->willReturnMap([
                ['Joomla\CMS\User\UserFactoryInterface', $this->mockUserFactory],
                ['DatabaseDriver', $this->mockDatabaseDriver],
            ]);

        // Default app to return our mock user
        $this->mockApp->method('getIdentity')
            ->willReturn($this->mockUser);

        // Register the mock container and application with the Joomla Factory so production code uses them
        \Joomla\CMS\Factory::$container   = $this->mockContainer;
        \Joomla\CMS\Factory::$application = $this->mockApp;
    }

    /**
     * Set up a user with specified permissions
     *
     * @param array $permissions The permissions to set
     * @param array $userProps Optional user properties to override defaults
     */
    protected function setUpUserWithPermissions(array $permissions, array $userProps = []): void
    {
        $this->permissions = $permissions;

        // Create a new mock user with the specified permissions
        $this->mockUser = $this->getMockBuilder('Joomla\CMS\User\User')
            ->disableOriginalConstructor()
            ->onlyMethods(['authorise'])
            ->getMock();

        // Set up user properties
        $defaultProps = [
            'id' => 42,
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'groups' => [2] // Default to Registered Users group
        ];

        foreach ($defaultProps as $prop => $value) {
            $this->mockUser->$prop = $userProps[$prop] ?? $value;
        }

        // Configure authorise to return values from permissions array
        $this->mockUser->method('authorise')
            ->willReturnCallback(function($action) {
                return $this->permissions[$action] ?? false;
            });

        // Update app and user factory to use new mock user
        $this->mockApp->method('getIdentity')
            ->willReturn($this->mockUser);

        $this->mockUserFactory
            ->method('loadUserById')
            ->willReturn($this->mockUser);
    }

    /**
     * Tear down the test environment
     */
    protected function tearDown(): void
    {
        // Reset Joomla Factory application
        if (class_exists('Joomla\CMS\Factory')) {
            \Joomla\CMS\Factory::$application = null;
        }
        
        $this->mockApp = null;
        $this->mockLang = null;
        $this->mockUser = null;

        parent::tearDown();
    }
}