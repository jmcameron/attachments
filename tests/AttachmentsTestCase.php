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

        $this->setUpJoomlaMocks();
    }

    /**
     * Sets up common Joomla CMS mocks including the Factory class
     */
    protected function setUpJoomlaMocks(): void
    {
        // Mock the User class first (required for AttachmentsTestCase)
        if (!class_exists('Joomla\CMS\User\User')) {
            eval('
                namespace Joomla\CMS\User {
                    class User {
                        public $id;
                        public $name;
                        public $username;
                        public $email;
                        public $groups = [];
                        
                        public function authorise($action, $assetName = null) {
                            return false;
                        }
                    }
                }
            ');
        }

        // Mock the Container class
        if (!class_exists('Joomla\DI\Container')) {
            eval('
                namespace Joomla\DI {
                    class Container {
                        public function get($key) {
                            return new \stdClass();
                        }
                    }
                }
            ');
        }

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

        // Mock the UserFactoryInterface
        if (!interface_exists('Joomla\CMS\User\UserFactoryInterface')) {
            eval('
                namespace Joomla\CMS\User {
                    interface UserFactoryInterface {
                        public function loadUserById($id);
                    }
                }
            ');
        }

        // Mock the base application classes if they don't exist
        if (!class_exists('Joomla\Application\AbstractApplication')) {
            eval('
                namespace Joomla\Application {
                    abstract class AbstractApplication {
                        protected $config;
                        public function get($name, $default = null) {
                            return $default;
                        }
                    }
                }
            ');
        }

        if (!class_exists('Joomla\CMS\Application\CMSApplication')) {
            eval('
                namespace Joomla\CMS\Application {
                    class CMSApplication extends \Joomla\Application\AbstractApplication {
                        public function getIdentity() {
                            return null;
                        }
                        
                        public function enqueueMessage($msg, $type = "message") {}
                        
                        public function getLanguage() {
                            return null;
                        }
                    }
                }
            ');
        }

        // Create mock application
        $this->mockApp = $this->getMockBuilder('Joomla\CMS\Application\CMSApplication')
            ->disableOriginalConstructor()
            ->onlyMethods(['getIdentity'])
            ->getMock();

        // Set up basic user properties
        $this->mockUser->id = 42;
        $this->mockUser->name = 'Test User';
        $this->mockUser->username = 'testuser';
        $this->mockUser->email = 'test@example.com';

        // Default authorise to false
        $this->mockUser->method('authorise')
            ->willReturn(false);

        // Set up container to return user factory
        $this->mockContainer->method('get')
            ->with('Joomla\CMS\User\UserFactoryInterface')
            ->willReturn($this->mockUserFactory);

        // Default app to return our mock user
        $this->mockApp->method('getIdentity')
            ->willReturn($this->mockUser);

        // Set up the Joomla Factory class if not already defined
        if (!class_exists('Joomla\CMS\Factory')) {
            eval('
                namespace Joomla\CMS {
                    class Factory {
                        public static $application;

                        public static function getApplication() {
                            return \Tests\AttachmentsTestCase::$instance->mockApp;
                        }
                        public static function getContainer() {
                            return \Tests\AttachmentsTestCase::$instance->mockContainer;
                        }
                        public static function getLanguage() {
                            return self::$application ? self::$application->getLanguage() : new \stdClass();
                        }
                        public static function getUser() {
                            return self::$application ? self::$application->getIdentity() : new \stdClass();
                        }
                    }
                }
            ');
        }

        // Mock the User class (required for AttachmentsTestCase)
        if (!class_exists('Joomla\CMS\User\User')) {
            eval('
                namespace Joomla\CMS\User {
                    class User {
                        public $id;
                        public $name;
                        public $username;
                        public $email;
                        public $groups = [];
                        
                        public function authorise($action, $assetName = null) {
                            return false;
                        }
                    }
                }
            ');
        }


		// Mock the UserFactory class
        if (!class_exists('Joomla\CMS\User\UserFactory')) {
            eval('
                namespace Joomla\CMS\User {
                    class UserFactory implements UserFactoryInterface {
                        public function loadUserById($id) {
                            return new User();
                        }
                    }
                }
            ');
        }

        // Mock the CMSApplication class
        if (!class_exists('Joomla\CMS\Application\CMSApplication')) {
            eval('
                namespace Joomla\CMS\Application {
                    abstract class CMSApplication {
                        public function getIdentity() {
                            return new \Joomla\CMS\User\User();
                        }
                        public function getLanguage() {
                            return new \stdClass();
                        }
                    }
                }
            ');
        }

        // Mock the Text class
        if (!class_exists('Joomla\CMS\Language\Text')) {
            eval('
                namespace Joomla\CMS\Language {
                    class Text {
                        public static function _($text) {
                            return $text;
                        }
                    }
                }
            ');
        }
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

        $this->mockUserFactory->method('loadUserById')
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