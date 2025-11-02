<?php
namespace Tests\Unit\Helper;

use JMCameron\Component\Attachments\Administrator\Helper\AttachmentsPermissions;
use Joomla\Registry\Registry;
use PHPUnit\Framework\TestCase;

// Define required Joomla constants
defined('JPATH_ROOT') or define('JPATH_ROOT', realpath(__DIR__ . '/../../..'));
defined('JPATH_SITE') or define('JPATH_SITE', JPATH_ROOT);
defined('JPATH_ADMINISTRATOR') or define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
defined('_JEXEC') or define('_JEXEC', 1);

class AttachmentsPermissionsTest extends TestCase
{
    /** @var mixed Mock user for testing */
    public static $user = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Define required Joomla Registry
        if (!class_exists('Joomla\Registry\Registry')) {
            eval('
                namespace Joomla\Registry {
                    class Registry {
                        private $data = [];
                        public function set($key, $value) {
                            $this->data[$key] = $value;
                        }
                        public function get($key) {
                            return $this->data[$key] ?? null;
                        }
                    }
                }
            ');
        }

        // Define required Joomla Database classes
        if (!class_exists('Joomla\Database\DatabaseQuery')) {
            eval('
                namespace Joomla\Database {
                    class DatabaseQuery {
                        private $parts = [];
                        
                        public function __toString() {
                            return implode(" ", $this->parts);
                        }

                        public function select($columns) {
                            $this->parts[] = "SELECT " . $columns;
                            return $this;
                        }

                        public function from($table) {
                            $this->parts[] = "FROM " . $table;
                            return $this;
                        }

                        public function join($type, $table, $condition) {
                            $this->parts[] = $type . " JOIN " . $table . " ON " . $condition;
                            return $this;
                        }

                        public function where($conditions) {
                            $this->parts[] = "WHERE " . $conditions;
                            return $this;
                        }
                    }
                }
            ');
        }

        if (!class_exists('Joomla\Database\DatabaseDriver')) {
            eval('
                namespace Joomla\Database {
                    class DatabaseDriver {
                        private $query;
                        
                        public function getQuery() {
                            return new DatabaseQuery();
                        }
                        
                        public function setQuery($query) {
                            $this->query = $query;
                            return $this;
                        }
                        
                        public function loadObject() {
                            $obj = new \stdClass();
                            $obj->id = 1;
                            $obj->created_by = 42;
                            $obj->created_user_id = 42;
                            return $obj;
                        }
                        
                        public function loadResult() {
                            return 1;
                        }
                    }
                }
            ');
        }

        // Define required Joomla Factory
        if (!class_exists('Joomla\CMS\Factory')) {
            eval('
                namespace Joomla\CMS {
                    class Factory {
                        public static function getApplication() {
                            static $app;
                            if ($app === null) {
                                $app = new class {
                                    public function getIdentity() {
                                        return \Tests\Unit\Helper\AttachmentsPermissionsTest::$user;
                                    }

                                    public function getDatabase() {
                                        static $db;
                                        if ($db === null) {
                                            $db = new class {
                                                public function getQuery() {
                                                    return new \Joomla\Database\DatabaseQuery();
                                                }
                                                public function setQuery($query) {
                                                    return $this;
                                                }
                                                public function loadObject() {
                                                    $obj = new \stdClass();
                                                    $obj->id = 1;
                                                    $obj->created_by = 42;
                                                    $obj->created_user_id = 42;
                                                    return $obj;
                                                }
                                                public function loadResult() {
                                                    return 1;
                                                }
                                            };
                                        }
                                        return $db;
                                    }
                                };
                            }
                            return $app;
                        }

                        public static function getContainer() {
                            return new class {
                                public function get($class) {
                                    if ($class === "Joomla\CMS\User\UserFactoryInterface") {
                                        return new class {
                                            public function loadUserById($id) {
                                                return \Tests\Unit\Helper\AttachmentsPermissionsTest::$user;
                                            }
                                        };
                                    } elseif ($class === "DatabaseDriver") {
                                        return new \Joomla\Database\DatabaseDriver();
                                    }
                                    return null;
                                }
                            };
                        }
                    }
                }
            ');
        }
    }

    /**
     * Set up a user with specified permissions
     */
    private function setUpUserWithPermissions(array $permissions, array $userProps = []): void
    {
        // Create user object with authorization method
        self::$user = new class($permissions) {
            private $permissions;
            private $props;

            public function __construct($perms) {
                $this->permissions = $perms;
                $this->props = [
                    'id' => 42,
                    'username' => 'testuser',
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'groups' => [2]
                ];
            }

            public function __get($name) {
                return $this->props[$name] ?? null;
            }

            public function __set($name, $value) {
                $this->props[$name] = $value;
            }

            public function authorise($action, $assetName = null) {
                return $this->permissions[$action] ?? false;
            }
        };

        // Set any custom properties
        foreach ($userProps as $prop => $value) {
            self::$user->$prop = $value;
        }
    }

    /**
     * Test the getActions method returns the correct permissions for a super admin
     */
    public function testGetActionsSuperAdmin()
    {
        // Set up a super admin user
        $this->setUpUserWithPermissions([
            'core.admin' => true,
            'core.manage' => true,
            'core.create' => true,
            'core.delete' => true,
            'core.edit' => true,
            'core.edit.state' => true,
            'core.edit.own' => true,
            'attachments.edit.state.own' => true,
            'attachments.delete.own' => true
        ], ['id' => 42, 'groups' => [8]]);  // Group 8 is Super Users in Joomla

        // Test that the method returns permissions for super admin
        $result = AttachmentsPermissions::getActions(42);
        
        $this->assertInstanceOf(Registry::class, $result);
        
        // Super admin should have all permissions
        $this->assertTrue($result->get('core.admin'), 'Super admin should have core.admin');
        $this->assertTrue($result->get('core.manage'), 'Super admin should have core.manage');
        $this->assertTrue($result->get('core.create'), 'Super admin should have core.create');
        $this->assertTrue($result->get('core.delete'), 'Super admin should have core.delete');
        $this->assertTrue($result->get('core.edit'), 'Super admin should have core.edit');
        $this->assertTrue($result->get('core.edit.state'), 'Super admin should have core.edit.state');
    }

    /**
     * Test permissions for a regular editor
     */
    public function testGetActionsEditor()
    {
        // Set up an editor user with typical permissions
        $this->setUpUserWithPermissions([
            'core.admin' => false,
            'core.manage' => false,
            'core.create' => true,
            'core.edit.own' => true,
            'attachments.edit.state.own' => true,
            'attachments.delete.own' => true
        ], ['id' => 43, 'groups' => [4]]); // Group 4 is Author in Joomla

        $result = AttachmentsPermissions::getActions(43);

        // Editor should have limited permissions
        $this->assertFalse($result->get('core.admin'), 'Editor should not have core.admin');
        $this->assertFalse($result->get('core.manage'), 'Editor should not have core.manage');
        $this->assertTrue($result->get('core.create'), 'Editor should have core.create');
        $this->assertTrue($result->get('core.edit.own'), 'Editor should have core.edit.own');
        $this->assertTrue($result->get('attachments.edit.state.own'), 'Editor should have attachments.edit.state.own');
        $this->assertTrue($result->get('attachments.delete.own'), 'Editor should have attachments.delete.own');
        $this->assertFalse($result->get('core.edit'), 'Editor should not have core.edit');
        $this->assertFalse($result->get('core.edit.state'), 'Editor should not have core.edit.state');
    }

    /**
     * Test userMayEditCategory method for admin user
     */
    public function testUserMayEditCategoryAsAdmin()
    {
        // Set up an admin user with full permissions
        $this->setUpUserWithPermissions([
            'core.edit' => true,
            'core.edit.own' => true
        ], ['id' => 42, 'groups' => [8]]);  // Group 8 is Super Users

        $result = AttachmentsPermissions::userMayEditCategory(1, 42);
        $this->assertTrue($result, 'Admin should be able to edit any category');
    }

    /**
     * Test userMayEditCategory method for regular user
     */
    public function testUserMayEditCategoryAsRegularUser()
    {
        // Set up a regular user with limited permissions
        $this->setUpUserWithPermissions([
            'core.edit' => false,
            'core.edit.own' => true
        ], ['id' => 43, 'groups' => [2]]); // Group 2 is Registered Users

        $result = AttachmentsPermissions::userMayEditCategory(1, 43);
        $this->assertFalse($result, 'Regular user should not be able to edit categories');
    }

    /**
     * Test userMayEditArticle method for admin user
     */
    public function testUserMayEditArticleAsAdmin()
    {
        // Set up an admin user with full permissions
        $this->setUpUserWithPermissions([
            'core.edit' => true,
            'core.edit.own' => true
        ], ['id' => 42, 'groups' => [8]]);  // Group 8 is Super Users

        $result = AttachmentsPermissions::userMayEditArticle(1, 42);
        $this->assertTrue($result, 'Admin should be able to edit any article');
    }

    /**
     * Test userMayEditArticle method for regular user
     */
    public function testUserMayEditArticleAsRegularUser()
    {
        // Set up a regular user with limited permissions
        $this->setUpUserWithPermissions([
            'core.edit' => false,
            'core.edit.own' => true
        ], ['id' => 43, 'groups' => [2]]); // Group 2 is Registered Users

        $result = AttachmentsPermissions::userMayEditArticle(1, 43);
        $this->assertFalse($result, 'Regular user should not be able to edit articles without permission');
    }
}