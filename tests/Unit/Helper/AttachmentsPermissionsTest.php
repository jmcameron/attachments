<?php
namespace Tests\Unit\Helper;

use JMCameron\Component\Attachments\Administrator\Helper\AttachmentsPermissions;
use JMCameron\Plugin\Content\Attachments\Extension\Attachments;
use Joomla\CMS\Factory;
use Joomla\CMS\User\UserFactoryInterface;
use Joomla\Registry\Registry;
use Tests\AttachmentsTestCase;

// Define required Joomla constants
defined('JPATH_ROOT') or define('JPATH_ROOT', realpath(__DIR__ . '/../../..'));
defined('JPATH_SITE') or define('JPATH_SITE', JPATH_ROOT);
defined('JPATH_ADMINISTRATOR') or define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
defined('_JEXEC') or define('_JEXEC', 1);

class AttachmentsPermissionsTest extends AttachmentsTestCase
{
    /** @var mixed Mock user for testing */
    public static $user = null;

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