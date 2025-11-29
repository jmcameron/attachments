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
        // $this->mockUser->method('authorise')
        //     ->willReturn(false);

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

    protected function populateUsers()
    {
        $db = $this->getDatabaseManager()->getConnection();
        
        try {
            // Create the viewlevels table if it doesn't exist using raw SQL
            $createTableSQL = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__users') . " (
                " . $db->quoteName('id') . " INTEGER PRIMARY KEY NOT NULL,
                " . $db->quoteName('name') . " TEXT NOT NULL,
                " . $db->quoteName('username') . " TEXT NOT NULL,
                " . $db->quoteName('email') . " TEXT NOT NULL,
                " . $db->quoteName('password') . " TEXT NOT NULL,
                " . $db->quoteName('block') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('sendEmail') . " INTEGER DEFAULT 0,
                " . $db->quoteName('registerDate') . " NUMERIC NOT NULL DEFAULT NULL,
                " . $db->quoteName('lastvisitDate') . " NUMERIC DEFAULT NULL,
                " . $db->quoteName('activation') . " TEXT NOT NULL,
                " . $db->quoteName('params') . " MEDIUMTEXT NOT NULL DEFAULT NULL,
                " . $db->quoteName('lastResetTime') . " NUMERIC DEFAULT NULL,
                " . $db->quoteName('resetCount') . " INT(11) NOT NULL DEFAULT 0,
                " . $db->quoteName('otpKey') . " TEXT NOT NULL,
                " . $db->quoteName('otep') . " TEXT NOT NULL,
                " . $db->quoteName('requireReset') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('authProvider') . " TEXT NOT NULL
            )";
            
            $db->setQuery($createTableSQL);
            $db->execute();
            
            // Insert users one at a time
            $users = [
                ['id' => 42, 'name' => 'admin', 'username' => 'admin', 'email' => 'admin@example.com', 'password' => 'hashed_password', 'block' => 0,
                 'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
                 'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
                 'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
                ['id' => 43, 'name' => 'jmc', 'username' => 'jmc', 'email' => 'jmc@example.com', 'password' => 'hashed_password', 'block' => 0,
                 'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
                 'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
                 'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
                ['id' => 50, 'name' => 'joe', 'username' => 'joe', 'email' => 'joe@example.com', 'password' => 'hashed_password', 'block' => 0,
                 'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
                 'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
                 'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
                ['id' => 51, 'name' => 'art', 'username' => 'art', 'email' => 'art@example.com', 'password' => 'hashed_password', 'block' => 0,
                 'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
                 'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
                 'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
                ['id' => 52, 'name' => 'ed', 'username' => 'ed', 'email' => 'ed@example.com', 'password' => 'hashed_password', 'block' => 0,
                 'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
                 'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
                 'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
                ['id' => 53, 'name' => 'pub', 'username' => 'pub', 'email' => 'pub@example.com', 'password' => 'hashed_password', 'block' => 0,
                 'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
                 'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
                 'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
                ['id' => 54, 'name' => 'manny', 'username' => 'manny', 'email' => 'manny@example.com', 'password' => 'hashed_password', 'block' => 0,
                 'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
                 'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
                 'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
                ['id' => 55, 'name' => 'adam', 'username' => 'adam', 'email' => 'adam@example.com', 'password' => 'hashed_password', 'block' => 0,
                 'sendEmail' => 0, 'registerDate' => '2024-01-01 00:00:00', 'lastvisitDate' => null,
                 'activation' => '', 'params' => '', 'lastResetTime' => null, 'resetCount' => 0,
                 'otpKey' => '', 'otep' => '', 'requireReset' => 0, 'authProvider' => ''],
            ];
            
            $count = 0;
            foreach ($users as $level) {
                $query = $db->getQuery(true);
                $query->insert('#__users')
                    ->columns(['id', 'name', 'username', 'email', 'password', 'block', 'sendEmail', 'registerDate',
                               'lastvisitDate', 'activation', 'params', 'lastResetTime', 'resetCount',
                               'otpKey', 'otep', 'requireReset', 'authProvider'])
                    ->values($db->quote($level['id']) . ', ' . $db->quote($level['name']) . ', ' . 
                            $db->quote($level['username']) . ', ' . $db->quote($level['email']) . ', ' . 
                            $db->quote($level['password']) . ', ' . $db->quote($level['block']) . ', ' . 
                            $db->quote($level['sendEmail']) . ', ' . $db->quote($level['registerDate']) . ', ' . 
                            $db->quote($level['lastvisitDate']) . ', ' . $db->quote($level['activation']) . ', ' . 
                            $db->quote($level['params']) . ', ' . $db->quote($level['lastResetTime']) . ', ' . 
                            $db->quote($level['resetCount']) . ', ' . $db->quote($level['otpKey']) . ', ' . 
                            $db->quote($level['otep']) . ', ' . $db->quote($level['requireReset']) . ', ' . $db->quote($level['authProvider']));
                
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

    protected function populateUserGroups()
    {
        $db = $this->getDatabaseManager()->getConnection();
        
        try {
            // Create the viewlevels table if it doesn't exist using raw SQL
            $createTableSQL = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__usergroups') . " (
                " . $db->quoteName('id') . " INTEGER PRIMARY KEY NOT NULL,
                " . $db->quoteName('parent_id') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('lft') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('rgt') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('title') . " TEXT NOT NULL
            )";
            
            $db->setQuery($createTableSQL);
            $db->execute();
            
            // Insert users one at a time
            $users = [
                ['id' => 1, 'parent_id' => 0, 'lft' => 1, 'rgt' => 16, 'title' => 'Public'],
                ['id' => 2, 'parent_id' => 1, 'lft' => 6, 'rgt' => 13, 'title' => 'Registered'],
                ['id' => 3, 'parent_id' => 2, 'lft' => 7, 'rgt' => 12, 'title' => 'Author'],
                ['id' => 4, 'parent_id' => 3, 'lft' => 8, 'rgt' => 11, 'title' => 'Editor'],
                ['id' => 5, 'parent_id' => 4, 'lft' => 9, 'rgt' => 10, 'title' => 'Publisher'],
                ['id' => 6, 'parent_id' => 1, 'lft' => 2, 'rgt' => 5, 'title' => 'Manager'],
                ['id' => 7, 'parent_id' => 6, 'lft' => 3, 'rgt' => 4, 'title' => 'Administrator'],
                ['id' => 8, 'parent_id' => 1, 'lft' => 14, 'rgt' => 15, 'title' => 'Super Users'],
            ];
            
            $count = 0;
            foreach ($users as $level) {
                $query = $db->getQuery(true);
                $query->insert('#__usergroups')
                    ->columns(['id', 'parent_id', 'lft', 'rgt', 'title'])
                    ->values($db->quote($level['id']) . ', ' . $db->quote($level['parent_id']) . ', ' . 
                            $db->quote($level['lft']) . ', ' . $db->quote($level['rgt']) . ', ' . 
                            $db->quote($level['title']));
                
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

    protected function populateUserGroupMap()
    {
        $db = $this->getDatabaseManager()->getConnection();
        
        try {
            // Create the viewlevels table if it doesn't exist using raw SQL
            $createTableSQL = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__user_usergroup_map') . " (
                " . $db->quoteName('user_id') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('group_id') . " INTEGER NOT NULL DEFAULT 0,
                PRIMARY KEY (" . $db->quoteName('user_id') . "," . $db->quoteName('group_id') . ")
            )";
            
            $db->setQuery($createTableSQL);
            $db->execute();
            
            // Insert users one at a time
            $users = [
                ['user_id' => 42, 'group_id' => 8],
                ['user_id' => 50, 'group_id' => 1],
            ];
            
            $count = 0;
            foreach ($users as $level) {
                $query = $db->getQuery(true);
                $query->insert('#__user_usergroup_map')
                    ->columns(['user_id', 'group_id'])
                    ->values($db->quote($level['user_id']) . ', ' . $db->quote($level['group_id']));
                
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

    protected function populateAssets()
    {
        $db = $this->getDatabaseManager()->getConnection();
        
        try {
            // Create the viewlevels table if it doesn't exist using raw SQL
            $createTableSQL = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__assets') . " (
                " . $db->quoteName('id') . " INTEGER PRIMARY KEY NOT NULL DEFAULT NULL,
                " . $db->quoteName('parent_id') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('lft') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('rgt') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('level') . " INTEGER NOT NULL DEFAULT NULL,
                " . $db->quoteName('name') . " TEXT NOT NULL DEFAULT NULL,
                " . $db->quoteName('title') . " TEXT NOT NULL DEFAULT NULL,
                " . $db->quoteName('rules') . " TEXT NOT NULL DEFAULT NULL
            )";
            
            $db->setQuery($createTableSQL);
            $db->execute();
            
            // Insert users one at a time
            $users = [
                ['id' => 1, 'parent_id' => 0, 'lft' => 1, 'rgt' => 6, 'level' => 0, 'name' => 'root.1', 'title' => 'Root Asset', 'rules' => '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1},"attachments.delete.own":{"6":1,"3":1},"attachments.edit.state.own":{"6":1,"4":1},"attachments.edit.state.ownparent":{"6":1,"4":1},"attachments.edit.ownparent":{"6":1,"3":1},"attachments.delete.ownparent":{"6":1,"3":1}}'],
                ['id' => 2, 'parent_id' => 1, 'lft' => 2, 'rgt' => 5, 'level' => 1, 'name' => 'com_attachments', 'title' => 'com_attachments', 'rules' => '{"core.create":{"12":1,"13":1,"11":1,"10":1},"core.edit.own":{"12":1,"13":1,"11":1,"10":1},"attachments.edit.state.own":{"12":1,"13":1,"11":1,"10":1},"attachments.delete.own":{"12":1,"13":1,"11":1,"10":1},"attachments.edit.ownparent":{"12":1,"13":1,"11":1,"10":1},"attachments.edit.state.ownparent":{"12":1,"13":1,"11":1,"10":1},"attachments.delete.ownparent":{"12":1,"13":1,"11":1,"10":1}}'],
                ['id' => 8, 'parent_id' => 1, 'lft' => 3, 'rgt' => 4, 'level' => 1, 'name' => "com_content", 'title' => "com_content", 'rules' => "{\"core.admin\":{\"7\":1},\"core.manage\":{\"6\":1},\"core.create\":{\"3\":1},\"core.edit\":{\"4\":1},\"core.edit.state\":{\"5\":1},\"core.edit.own\":{\"12\":1,\"11\":1,\"10\":1}}"]
            ];
            
            $count = 0;
            foreach ($users as $level) {
                $query = $db->getQuery(true);
                $query->insert('#__assets')
                    ->columns(['id', 'parent_id', 'lft', 'rgt', 'level', 'name', 'title', 'rules'])
                    ->values($db->quote($level['id']) . ', ' . $db->quote($level['parent_id']) . ', ' . 
                            $db->quote($level['lft']) . ', ' . $db->quote($level['rgt']) . ', ' . 
                            $db->quote($level['level']) . ', ' . $db->quote($level['name']) . ', ' . 
                            $db->quote($level['title']) . ', ' . $db->quote($level['rules']));
                
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