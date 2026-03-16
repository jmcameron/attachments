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

        // Set up database driver mock that actually uses the test database
        $this->mockDatabaseDriver = $this->getDatabaseManager()->getConnection();

        $this->mockCacheController = $this->getMockBuilder('Joomla\CMS\Cache\CacheController')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheControllerFactory = $this->getMockBuilder('Joomla\CMS\Cache\CacheControllerFactoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->cacheControllerFactory->method('createCacheController')
            ->willReturn($this->mockCacheController);

        // Create a custom user factory mock that loads users from the database when needed
        $customUserFactory = $this->getMockBuilder('Joomla\CMS\User\UserFactoryInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $customUserFactory->method('loadUserById')
            ->willReturnCallback(function ($userId) {
                // Create a real User object, which will load from the database
                return new \Joomla\CMS\User\User($userId);
            });

        // Set up container to return user factory
        $this->mockContainer->method('get')
            ->willReturnMap([
                [\Joomla\CMS\User\UserFactoryInterface::class, $customUserFactory],
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
                ['id' => 1, 'parent_id' => 0, 'lft' => 1, 'rgt' => 26, 'title' => 'Public'],
                ['id' => 2, 'parent_id' => 1, 'lft' => 6, 'rgt' => 13, 'title' => 'Registered'],
                ['id' => 3, 'parent_id' => 2, 'lft' => 7, 'rgt' => 12, 'title' => 'Author'],
                ['id' => 4, 'parent_id' => 3, 'lft' => 8, 'rgt' => 11, 'title' => 'Editor'],
                ['id' => 5, 'parent_id' => 4, 'lft' => 9, 'rgt' => 10, 'title' => 'Publisher'],
                ['id' => 6, 'parent_id' => 1, 'lft' => 2, 'rgt' => 5, 'title' => 'Manager'],
                ['id' => 7, 'parent_id' => 6, 'lft' => 3, 'rgt' => 4, 'title' => 'Administrator'],
                ['id' => 8, 'parent_id' => 1, 'lft' => 24, 'rgt' => 25, 'title' => 'Super Users'],
                ['id' => 9, 'parent_id' => 1, 'lft' => 14, 'rgt' => 23, 'title' => 'Special'],
                ['id' => 10, 'parent_id' => 9, 'lft' => 15, 'rgt' => 20, 'title' => 'Attachments Author'],
                ['id' => 11, 'parent_id' => 10, 'lft' => 16, 'rgt' => 17, 'title' => 'Attachments Editor'],
                ['id' => 12, 'parent_id' => 10, 'lft' => 18, 'rgt' => 19, 'title' => 'Attachments Publisher'],
                ['id' => 13, 'parent_id' => 9, 'lft' => 21, 'rgt' => 22, 'title' => 'Attachments Manager']
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
            // Based on testActionsData.csv expectations:
            // joe (ID 50) -> Public (ID 1) -> all permissions 0
            // art (ID 51) -> Attachments Author (ID 10) -> core.create=1, core.edit.own=1, attachments.delete.own=1
            // ed (ID 52) -> Attachments Editor (ID 11) -> core.create=1, core.edit=1, core.edit.own=1, attachments.delete.own=1
            // pub (ID 53) -> Attachments Publisher (ID 12) -> core.create=1, core.edit=1, core.edit.state=1, core.edit.own=1, attachments.delete.own=1
            // manny (ID 54) -> Attachments Manager (ID 13) -> core.create=1, core.delete=1, core.edit=1, core.edit.state=1, core.edit.own=1, attachments.delete.own=1
            // adam (ID 55) -> Administrator (ID 7) -> core.manage=1, core.create=1, core.delete=1, core.edit=1, core.edit.state=1, core.edit.own=1, attachments.delete.own=1
            // admin (ID 42) -> Super Users (ID 8) -> all permissions 1
            // jmc (ID 43) -> Attachments Author (ID 10) -> core.create=1, core.edit.own=1, attachments.delete.own=1 (same as art)
            $users = [
                ['user_id' => 42, 'group_id' => 8],  // admin -> Super Users
                ['user_id' => 43, 'group_id' => 10], // jmc -> Attachments Author
                ['user_id' => 50, 'group_id' => 1],  // joe -> Public
                ['user_id' => 51, 'group_id' => 10], // art -> Attachments Author
                ['user_id' => 52, 'group_id' => 11], // ed -> Attachments Editor
                ['user_id' => 53, 'group_id' => 12], // pub -> Attachments Publisher
                ['user_id' => 54, 'group_id' => 13], // manny -> Attachments Manager
                ['user_id' => 55, 'group_id' => 7],  // adam -> Administrator
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

        // Create extensions table if it doesn't exist, as it's required for ACL system
        $this->populateExtensions();

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

            // Insert assets one at a time
            $assets = [
                ['id' => 1, 'parent_id' => 0, 'lft' => 1, 'rgt' => 20, 'level' => 0, 'name' => 'root.1', 'title' => 'Root Asset', 'rules' => '{"core.login.site":{"6":1,"2":1},"core.login.admin":{"6":1},"core.login.offline":{"6":1},"core.admin":{"8":1},"core.manage":{"7":1},"core.create":{"6":1,"3":1},"core.delete":{"6":1},"core.edit":{"6":1,"4":1},"core.edit.state":{"6":1,"5":1},"core.edit.own":{"6":1,"3":1},"attachments.delete.own":{"6":1,"3":1},"attachments.edit.state.own":{"6":1,"4":1},"attachments.edit.state.ownparent":{"6":1,"4":1},"attachments.edit.ownparent":{"6":1,"3":1},"attachments.delete.ownparent":{"6":1,"3":1}}'],
                ['id' => 2, 'parent_id' => 1, 'lft' => 2, 'rgt' => 3, 'level' => 1, 'name' => 'com_attachments', 'title' => 'com_attachments', 'rules' => '{"core.create":{"12":1,"13":1,"11":1,"10":1},"core.delete":{"13":1},"core.edit":{"11":1,"12":1,"13":1},"core.edit.state":{"12":1,"13":1},"core.edit.own":{"12":1,"13":1,"11":1,"10":1},"attachments.edit.state.own":{"12":1,"13":1,"11":1,"10":1},"attachments.delete.own":{"12":1,"13":1,"11":1,"10":1},"attachments.edit.ownparent":{"12":1,"13":1,"11":1,"10":1},"attachments.edit.state.ownparent":{"12":1,"13":1,"11":1,"10":1},"attachments.delete.ownparent":{"12":1,"13":1,"11":1,"10":1}}'],
                ['id' => 8, 'parent_id' => 1, 'lft' => 4, 'rgt' => 19, 'level' => 1, 'name' => 'com_content', 'title' => 'com_content', 'rules' => '{"core.admin":{"7":1},"core.manage":{"6":1},"core.create":{"3":1},"core.edit":{"4":1,"11":1,"12":1,"13":1},"core.edit.state":{"5":1,"12":1,"13":1},"core.edit.own":{"12":1,"11":1,"10":1}}'],
                // Category-specific assets under com_content
                ['id' => 9, 'parent_id' => 8, 'lft' => 5, 'rgt' => 6, 'level' => 2, 'name' => 'com_content.category.2', 'title' => 'Category 2', 'rules' => '{"core.edit":{"11":1,"12":1,"13":1},"core.edit.own":{"12":1,"11":1,"10":1}}'],
                ['id' => 10, 'parent_id' => 8, 'lft' => 7, 'rgt' => 8, 'level' => 2, 'name' => 'com_content.category.7', 'title' => 'Category 7', 'rules' => '{"core.edit":{"11":1,"12":1,"13":1},"core.edit.own":{"12":1,"11":1,"10":1}}'],
                ['id' => 11, 'parent_id' => 8, 'lft' => 9, 'rgt' => 10, 'level' => 2, 'name' => 'com_content.category.8', 'title' => 'Category 8', 'rules' => '{"core.edit":{"11":1,"12":1,"13":1},"core.edit.own":{"12":1,"11":1,"10":1}}'],
                // Article-specific assets under com_content
                ['id' => 12, 'parent_id' => 8, 'lft' => 11, 'rgt' => 12, 'level' => 2, 'name' => 'com_content.article.1', 'title' => 'Article 1', 'rules' => '{"core.edit":{"11":1,"12":1,"13":1},"core.edit.own":{"12":1,"11":1,"10":1}}'],
                ['id' => 13, 'parent_id' => 8, 'lft' => 13, 'rgt' => 14, 'level' => 2, 'name' => 'com_content.article.2', 'title' => 'Article 2', 'rules' => '{"core.edit":{"11":1,"12":1,"13":1},"core.edit.own":{"12":1,"11":1,"10":1}}']
            ];

            $count = 0;
            foreach ($assets as $asset) {
                $query = $db->getQuery(true);
                $query->insert('#__assets')
                    ->columns(['id', 'parent_id', 'lft', 'rgt', 'level', 'name', 'title', 'rules'])
                    ->values($db->quote($asset['id']) . ', ' . $db->quote($asset['parent_id']) . ', ' .
                            $db->quote($asset['lft']) . ', ' . $db->quote($asset['rgt']) . ', ' .
                            $db->quote($asset['level']) . ', ' . $db->quote($asset['name']) . ', ' .
                            $db->quote($asset['title']) . ', ' . $db->quote($asset['rules']));

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

    protected function populateExtensions()
    {
        $db = $this->getDatabaseManager()->getConnection();

        try {
            // Create extensions table if it doesn't exist
            $createTableSQL = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__extensions') . " (
                " . $db->quoteName('extension_id') . " INTEGER PRIMARY KEY NOT NULL,
                " . $db->quoteName('name') . " TEXT NOT NULL,
                " . $db->quoteName('type') . " TEXT NOT NULL,
                " . $db->quoteName('element') . " TEXT NOT NULL,
                " . $db->quoteName('folder') . " TEXT NOT NULL DEFAULT '',
                " . $db->quoteName('client_id') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('enabled') . " INTEGER NOT NULL DEFAULT 1,
                " . $db->quoteName('access') . " INTEGER NOT NULL DEFAULT 1,
                " . $db->quoteName('protected') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('manifest_cache') . " TEXT NOT NULL,
                " . $db->quoteName('params') . " TEXT NOT NULL,
                " . $db->quoteName('custom_data') . " TEXT NOT NULL,
                " . $db->quoteName('system_data') . " TEXT NOT NULL,
                " . $db->quoteName('checked_out') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('checked_out_time') . " TEXT NOT NULL,
                " . $db->quoteName('ordering') . " INTEGER DEFAULT 0,
                " . $db->quoteName('state') . " INTEGER DEFAULT 0
            )";

            $db->setQuery($createTableSQL);
            $db->execute();

            // Insert required extensions
            $extensions = [
                ['extension_id' => 7, 'name' => 'files_joomla', 'type' => 'file', 'element' => 'joomla', 'folder' => '', 'client_id' => 0, 'enabled' => 1, 'access' => 1, 'protected' => 1, 'manifest_cache' => '', 'params' => '', 'custom_data' => '', 'system_data' => '', 'checked_out' => 0, 'checked_out_time' => '0000-00-00 00:00:00', 'ordering' => 0, 'state' => 0],
                ['extension_id' => 19, 'name' => 'com_content', 'type' => 'component', 'element' => 'com_content', 'folder' => '', 'client_id' => 1, 'enabled' => 1, 'access' => 1, 'protected' => 1, 'manifest_cache' => '', 'params' => '', 'custom_data' => '', 'system_data' => '', 'checked_out' => 0, 'checked_out_time' => '0000-00-00 00:00:00', 'ordering' => 0, 'state' => 0],
                ['extension_id' => 21, 'name' => 'com_attachments', 'type' => 'component', 'element' => 'com_attachments', 'folder' => '', 'client_id' => 1, 'enabled' => 1, 'access' => 1, 'protected' => 0, 'manifest_cache' => '', 'params' => '', 'custom_data' => '', 'system_data' => '', 'checked_out' => 0, 'checked_out_time' => '0000-00-00 00:00:00', 'ordering' => 0, 'state' => 0],
                ['extension_id' => 23, 'name' => 'com_admin', 'type' => 'component', 'element' => 'com_admin', 'folder' => '', 'client_id' => 1, 'enabled' => 1, 'access' => 1, 'protected' => 1, 'manifest_cache' => '', 'params' => '', 'custom_data' => '', 'system_data' => '', 'checked_out' => 0, 'checked_out_time' => '0000-00-00 00:00:00', 'ordering' => 0, 'state' => 0]
            ];

            $count = 0;
            foreach ($extensions as $ext) {
                $query = $db->getQuery(true);
                $columns = array_keys($ext);
                $values = array_values($ext);
                $query->insert('#__extensions')
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', array_map([$db, 'quote'], $values)));

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

    protected function populateCategories()
    {
        $db = $this->getDatabaseManager()->getConnection();

        try {
            // Create categories table if it doesn't exist using raw SQL
            $createTableSQL = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__categories') . " (
                " . $db->quoteName('id') . " INTEGER PRIMARY KEY NOT NULL,
                " . $db->quoteName('asset_id') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('parent_id') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('lft') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('rgt') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('level') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('path') . " TEXT NOT NULL DEFAULT '',
                " . $db->quoteName('extension') . " TEXT NOT NULL,
                " . $db->quoteName('title') . " TEXT NOT NULL,
                " . $db->quoteName('alias') . " TEXT NOT NULL,
                " . $db->quoteName('note') . " TEXT NOT NULL DEFAULT '',
                " . $db->quoteName('description') . " TEXT NOT NULL DEFAULT '',
                " . $db->quoteName('published') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('checked_out') . " INTEGER DEFAULT NULL,
                " . $db->quoteName('checked_out_time') . " TEXT DEFAULT NULL,
                " . $db->quoteName('access') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('params') . " TEXT NOT NULL DEFAULT '',
                " . $db->quoteName('metadesc') . " TEXT NOT NULL DEFAULT '',
                " . $db->quoteName('metakey') . " TEXT NOT NULL DEFAULT '',
                " . $db->quoteName('metadata') . " TEXT NOT NULL DEFAULT '',
                " . $db->quoteName('created_user_id') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('created_time') . " TEXT NOT NULL,
                " . $db->quoteName('modified_user_id') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('modified_time') . " TEXT NOT NULL,
                " . $db->quoteName('hits') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('language') . " CHAR(7) NOT NULL,
                " . $db->quoteName('version') . " INTEGER NOT NULL DEFAULT 1
            )";

            $db->setQuery($createTableSQL);
            $db->execute();

            // Insert categories one at a time
            $categories = [
                ['id' => 2, 'asset_id' => 0, 'title' => 'Category 2', 'alias' => 'category-2', 'created_user_id' => 52, 'created_time' => '2024-01-01 00:00:00', 'modified_user_id' => 0, 'modified_time' => '2024-01-01 00:00:00', 'language' => '*', 'parent_id' => 1, 'level' => 1, 'lft' => 1, 'rgt' => 2, 'extension' => 'com_content', 'published' => 1],
                ['id' => 7, 'asset_id' => 0, 'title' => 'Category 7', 'alias' => 'category-7', 'created_user_id' => 53, 'created_time' => '2024-01-01 00:00:00', 'modified_user_id' => 0, 'modified_time' => '2024-01-01 00:00:00', 'language' => '*', 'parent_id' => 1, 'level' => 1, 'lft' => 3, 'rgt' => 4, 'extension' => 'com_content', 'published' => 1],
                ['id' => 8, 'asset_id' => 0, 'title' => 'Category 8', 'alias' => 'category-8', 'created_user_id' => 54, 'created_time' => '2024-01-01 00:00:00', 'modified_user_id' => 0, 'modified_time' => '2024-01-01 00:00:00', 'language' => '*', 'parent_id' => 1, 'level' => 1, 'lft' => 5, 'rgt' => 6, 'extension' => 'com_content', 'published' => 1]
            ];

            $count = 0;
            foreach ($categories as $category) {
                $query = $db->getQuery(true);
                $query->insert('#__categories')
                    ->columns(array_keys($category))
                    ->values(implode(',', array_map([$db, 'quote'], $category)));

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

    protected function populateContent()
    {
        $db = $this->getDatabaseManager()->getConnection();

        try {
            // Create content table if it doesn't exist using raw SQL
            $createTableSQL = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__content') . " (
                " . $db->quoteName('id') . " INTEGER PRIMARY KEY NOT NULL,
                " . $db->quoteName('title') . " TEXT NOT NULL,
                " . $db->quoteName('alias') . " TEXT NOT NULL,
                " . $db->quoteName('introtext') . " TEXT NOT NULL,
                " . $db->quoteName('fulltext') . " TEXT NOT NULL,
                " . $db->quoteName('state') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('catid') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('created') . " TEXT NOT NULL,
                " . $db->quoteName('created_by') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('modified') . " TEXT NOT NULL,
                " . $db->quoteName('modified_by') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('checked_out') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('checked_out_time') . " TEXT NOT NULL,
                " . $db->quoteName('publish_up') . " TEXT NOT NULL,
                " . $db->quoteName('publish_down') . " TEXT NOT NULL,
                " . $db->quoteName('version') . " INTEGER NOT NULL DEFAULT 1,
                " . $db->quoteName('ordering') . " INTEGER NOT NULL DEFAULT 0
            )";

            $db->setQuery($createTableSQL);
            $db->execute();

            // Insert content (articles) one at a time
            $articles = [
                ['id' => 1, 'title' => 'Article 1', 'alias' => 'article-1', 'introtext' => '', 'fulltext' => '', 'state' => 1, 'catid' => 2, 'created' => '2024-01-01 00:00:00', 'created_by' => 52, 'modified' => '2024-01-01 00:00:00', 'modified_by' => 0, 'checked_out' => 0, 'checked_out_time' => '0000-00-00 00:00:00', 'publish_up' => '2024-01-01 00:00:00', 'publish_down' => '0000-00-00 00:00:00', 'version' => 1, 'ordering' => 0],
                ['id' => 2, 'title' => 'Article 2', 'alias' => 'article-2', 'introtext' => '', 'fulltext' => '', 'state' => 1, 'catid' => 7, 'created' => '2024-01-01 00:00:00', 'created_by' => 43, 'modified' => '2024-01-01 00:00:00', 'modified_by' => 0, 'checked_out' => 0, 'checked_out_time' => '0000-00-00 00:00:00', 'publish_up' => '2024-01-01 00:00:00', 'publish_down' => '0000-00-00 00:00:00', 'version' => 1, 'ordering' => 0]
            ];

            $count = 0;
            foreach ($articles as $article) {
                $query = $db->getQuery(true);
                $query->insert('#__content')
                    ->columns(array_keys($article))
                    ->values(implode(',', array_map([$db, 'quote'], $article)));

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