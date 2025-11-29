<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Tests
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace Tests\Integration\Component\Admin\Permissions;

use JMCameron\Component\Attachments\Administrator\Helper\AttachmentsPermissions;
use Joomla\CMS\User\User;
use Joomla\CMS\User\UserHelper;
use Tests\Utils\CsvFileIterator;
use Joomla\Registry\Registry;
use Tests\AttachmentsDatabaseTestCase;

/**
 * Tests for ACL action permissions for various users
 *
 * @package Attachments
 * @subpackage Tests
 */
class ActionsTest extends AttachmentsDatabaseTestCase
{
    /**
     * Sets up the fixture
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::setUpBeforeClass();

        $this->populateUsers();
        $this->populateUserGroups();
        $this->populateUserGroupMap();
        $this->populateAssets();
    }

    /**
     * Test various ACL action permissions for com_attachments for various users
     *
     * @dataProvider provider
     *
     * @param string $username The name of the user (for error outputs)
     * @param int $admin correct 'core.admin' permission (0/1 interpreted as bool)
     * @param int $manage correct 'core.manage' permission (0/1 interpreted as bool)
     * @param int $create correct 'core.create' permission (0/1 interpreted as bool)
     * @param int $delete correct 'core.delete' permission (0/1 interpreted as bool)
     * @param int $edit_state correct 'core.edit.state' permission (0/1 interpreted as bool)
     * @param int $edit correct 'core.edit' permission (0/1 interpreted as bool)
     * @param int $edit_own correct 'core.edit.own' permission (0/1 interpreted as bool)
     * @param int $delete_own correct 'attachments.delete.own' permission (0/1 interpreted as bool)
     */
    public function testActions($username, $admin, $manage, $create, $delete, $edit_state, $edit, $edit_own, $delete_own)
    {
        $user_id = UserHelper::getUserId($username);
        $errmsg = "ERROR: ========> USERNAME=$username does not exist!";
        $this->assertNotEquals((int)$user_id, 0, $errmsg);

        $this->mockUserFactory
            ->method('loadUserById')
            ->willReturn(new User($user_id));
        // $this->setUpUserWithPermissions([
        //     'core.admin' => (bool)(int)$admin,
        //     'core.manage' => (bool)(int)$manage,
        //     'core.create' => (bool)(int)$create,
        //     'core.delete' => (bool)(int)$delete,
        //     'core.edit' => (bool)(int)$edit,
        //     'core.edit.state' => (bool)(int)$edit_state,
        //     'core.edit.own' => (bool)(int)$edit_own,
        //     'attachments.delete.own' => (bool)(int)$delete_own
        // ], UserHelper::getUserProps($user_id));

        $result = AttachmentsPermissions::getActions((int)$user_id);
        var_dump($result);
        $this->assertInstanceOf(Registry::class, $result);
        
         $errmsg = "----> Failed test for $username core.admin for com_attachments, " .
            "expected ".var_export((bool)(int)$admin,true).", got " . var_export($result->get('core.admin'),true) . " for " . $username;
        $this->assertEquals($result->get('core.admin'), (bool)(int)$admin, $errmsg);

        $errmsg = "----> Failed test for $username core.manage for com_attachments, " .
            "expected ".var_export((bool)(int)$manage, true).", got " . $result->get('core.manage') . " for " . $username;
        $this->assertEquals($result->get('core.manage'), (bool)(int)$manage, $errmsg);

        $errmsg = "----> Failed test for $username core.create for com_attachments, " .
            "expected ".var_export((bool)(int)$create,true).", got " . var_export($result->get('core.create'),true) . " for " . $username;
        $this->assertEquals($result->get('core.create'), (bool)(int)$create, $errmsg);

        $errmsg = "----> Failed test for $username core.delete for com_attachments, " .
            "expected ".var_export((bool)(int)$delete,true).", got " . var_export($result->get('core.delete'),true) . " for " . $username;
        $this->assertEquals($result->get('core.delete'), (bool)(int)$delete, $errmsg);

        $errmsg = "----> Failed test for $username core.edit.state for com_attachments, " .
            "expected ".var_export((bool)(int)$edit_state,true).", got " . var_export($result->get('core.edit.state'),true) . " for " . $username;
        $this->assertEquals($result->get('core.edit.state'), (bool)(int)$edit_state, $errmsg);

        $errmsg = "----> Failed test for $username core.edit for com_attachments, " .
            "expected ".var_export((bool)(int)$edit,true).", got " . var_export($result->get('core.edit'),true) . " for " . $username;
        $this->assertEquals($result->get('core.edit'), (bool)(int)$edit, $errmsg);

        $errmsg = "----> Failed test for $username core.edit.own for com_attachments, " .
            "expected ".var_export((bool)(int)$edit_own,true).", got " . var_export($result->get('core.edit.own'),true) . " for " . $username;
        $this->assertEquals($result->get('core.edit.own'), (bool)(int)$edit_own, $errmsg);

        $errmsg = "----> Failed test for $username attachments.delete.own for com_attachments, " .
            "expected ".var_export((bool)(int)$delete_own,true).", got " . var_export($result->get('attachments.delete.own'),true) . " for " . $username;
        $this->assertEquals($result->get('attachments.delete.own'), (bool)(int)$delete_own, $errmsg);
    }

    /**
     * Get the test data from CSV file
     */
    public static function provider(): CsvFileIterator
    {
        $csvFile = dirname(__FILE__) .'/testActionsData.csv';
        return new CsvFileIterator($csvFile);
    }
}