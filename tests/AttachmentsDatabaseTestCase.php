<?php

/**
 * Base test case class for Attachments component tests
 *
 * @package Attachments
 * @subpackage Tests
 */

namespace Tests;

use PHPUnit\Framework\MockObject\MockObject;
use Joomla\Test\DatabaseTestCase;

/**
 * Base class for Attachments tests
 */
abstract class AttachmentsDatabaseTestCase extends DatabaseTestCase
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
     * @var MockObject The mock cache controller factory
     */

    protected $cacheControllerFactory;

    /**
     * @var MockObject The mock cache controller
     */
    protected $mockCacheController;

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
        if (!defined('JPATH_CACHE')) {
            define('JPATH_CACHE', "temp");
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
            ->onlyMethods(['authorise', 'getAuthorisedViewLevels'])
            ->getMock();

        // Create mock application
        $this->mockApp = $this->getMockBuilder('Joomla\CMS\Application\CMSApplication')
            // ->disableOriginalConstructor()
            ->onlyMethods(['getIdentity', 'getDispatcher', 'getSession', 'bootComponent', 'getInput', 'getConfig'])
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
                return new \Joomla\Database\Sqlite\SqliteQuery();
            });
            
        $this->mockDatabaseDriver->method('loadObject')
            ->willReturnCallback(function() {
                return null;
            });

        $this->mockCacheController = $this->getMockBuilder('Joomla\CMS\Cache\CacheController')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheControllerFactory = $this->getMockBuilder('Joomla\CMS\Cache\CacheControllerFactoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheControllerFactory->method('createCacheController')
            ->willReturn($this->mockCacheController);

        // Set up container to return user factory
        $this->mockContainer->method('get')
            ->willReturnMap([
                [\Joomla\CMS\User\UserFactoryInterface::class, $this->mockUserFactory],
                // [\Joomla\Database\DatabaseDriver::class, $this->mockDatabaseDriver],
                [\Joomla\Database\DatabaseDriver::class, $this->getDatabaseManager()->getConnection()],
                ['DatabaseDriver', $this->getDatabaseManager()->getConnection()],
                ['DatabaseInterface', $this->getDatabaseManager()->getConnection()],
                [\Joomla\Database\DatabaseInterface::class, $this->getDatabaseManager()->getConnection()],
                [\Joomla\CMS\Cache\CacheControllerFactoryInterface::class, $this->cacheControllerFactory],
                [\Joomla\CMS\Language\LanguageFactoryInterface::class, new \Joomla\CMS\Language\LanguageFactory()],
            ]);

        // Default app to return our mock user
        $this->mockApp->method('getIdentity')
            ->willReturn($this->mockUser);

        $this->mockApp->method('getDispatcher')
            ->willReturn(new \Joomla\Event\Dispatcher);

        $session = $this->getMockBuilder('Joomla\CMS\Session\Session')
            ->onlyMethods(['get'])
            ->disableOriginalConstructor()
            ->getMock();

        $session->method('get')
            ->willReturnMap([
                ['lang', null, 'en-GB'],
                ['user', null, $this->mockUser],
            ]);

        $this->mockApp->method('getSession')
            ->willReturn($session);

        // Register the mock container and application with the Joomla Factory so production code uses them
        \Joomla\CMS\Factory::$container   = $this->mockContainer;
        \Joomla\CMS\Factory::$application = $this->mockApp;
        \Joomla\CMS\Factory::$database    = $this->getDatabaseManager()->getConnection();
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

    protected function populateViewLevels()
    {
        $db = $this->getDatabaseManager()->getConnection();
        
        try {
            // Create the viewlevels table if it doesn't exist using raw SQL
            $createTableSQL = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__viewlevels') . " (
                " . $db->quoteName('id') . " INTEGER PRIMARY KEY,
                " . $db->quoteName('title') . " VARCHAR(255) NOT NULL,
                " . $db->quoteName('ordering') . " INTEGER DEFAULT 0,
                " . $db->quoteName('rules') . " TEXT
            )";
            
            $db->setQuery($createTableSQL);
            $db->execute();
            
            // Insert viewlevels one at a time
            $viewlevels = [
                ['id' => 1, 'title' => 'Public', 'ordering' => 0, 'rules' => '[1]'],
                ['id' => 2, 'title' => 'Registered', 'ordering' => 1, 'rules' => '[6,2,8]'],
                ['id' => 3, 'title' => 'Special', 'ordering' => 2, 'rules' => '[6,3,8]'],
            ];
            
            $count = 0;
            foreach ($viewlevels as $level) {
                $query = $db->getQuery(true);
                $query->insert('#__viewlevels')
                    ->columns(['id', 'title', 'ordering', 'rules'])
                    ->values($db->quote($level['id']) . ', ' . $db->quote($level['title']) . ', ' . 
                            $db->quote($level['ordering']) . ', ' . $db->quote($level['rules']));
                
                $db->setQuery($query);
                if ($db->execute()) {
                    $count++;
                }
            }
            return $count;
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}