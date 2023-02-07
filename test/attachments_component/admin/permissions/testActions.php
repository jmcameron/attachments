<?php
/**
 * Attachments component
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

/** Load the PHPUnit test framework */
require_once 'PHPUnit/Framework/TestCase.php';

/** Load the CSV file iterator class */
require_once JPATH_TESTS.'/utils/CsvFileIterator.php';

jimport('joomla.log.log');

jimport('joomla.user.user');
jimport('joomla.user.helper');
jimport('joomla.plugin.plugin');
jimport('joomla.plugin.helper');
jimport('joomla.event.dispatcher');
jimport('joomla.filter.filterinput');
jimport('joomla.environment.request');
jimport('joomla.application.component.helper');

require_once JPATH_BASE.'/administrator/components/com_attachments/permissions.php';

/**
 * Tests for ACL action permissions for various users
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 */
class ActionsTest extends JoomlaDatabaseTestCase
{
	/**
	 * Sets up the fixture
	 */
	protected function setUp()
	{
		parent::setUp();
		parent::setUpBeforeClass();
	}


	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return xml dataset
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_TESTS . '/joomla_db.xml');
	}


	/**
	 * Test various ACL action permissions for com_attachments for various users
	 *
	 * @dataProvider provider
	 *
	 * @param string $username The name of ther user (for error outputs)
	 * @param int $admin correct 'core.admin' permission (0/1 interpreted as bool)
	 * @param int $manage correct 'core.manage' permission (0/1 interpreted as bool)
	 * @param int $create correct 'core.create' permission (0/1 interpreted as bool)
	 * @param int $delete correct 'core.delete' permission (0/1 interpreted as bool)
	 * @param int $edit_state correct 'core.edit.state' permission (0/1 interpreted as bool)
	 * @param int $edit correct 'core.edit' permission (0/1 interpreted as bool)
	 * @param int $edit_own correct 'core.edit.own' permission (0/1 interpreted as bool)
	 * @param int $delete_own correct 'attachments.delete.own' permission (0/1 interpreted as bool)
	 */
	public function testActions($username,$admin,$manage,$create,$delete,$edit_state,$edit,$edit_own,$delete_own)
	{
		$user_id = JUserHelper::getUserId($username);

		$errmsg = "ERROR: ========> USERNAME=$username does not exist!";
		$this->assertNotEquals((int)$user_id, 0, $errmsg);

		$canDo = AttachmentsPermissions::getActions((int)$user_id);

		$errmsg = "----> Failed test for $username core.admin for com_attachments, " .
			" expected $admin, got ".$canDo->get('core.admin')." for ".$username;
		$this->assertEquals($canDo->get('core.admin'), (bool)$admin, $errmsg);

		$errmsg = "----> Failed test for $username core.manage for com_attachments, " .
			" expected $manage, got ".$canDo->get('core.manage')." for ".$username;
		$this->assertEquals($canDo->get('core.manage'), (bool)$manage, $errmsg);

		$errmsg = "----> Failed test for $username core.create for com_attachments, " .
			" expected $create, got ".$canDo->get('core.create')." for ".$username;
		$this->assertEquals($canDo->get('core.create'), (bool)$create, $errmsg);

		$errmsg = "----> Failed test for $username core.delete for com_attachments, " .
			" expected $delete, got ".$canDo->get('core.delete')." for ".$username;
		$this->assertEquals($canDo->get('core.delete'), (bool)$delete, $errmsg);

		$errmsg = "----> Failed test for $username core.edit.state for com_attachments, " .
			" expected $edit_state, got ".$canDo->get('core.edit.state')." for ".$username;
		$this->assertEquals($canDo->get('core.edit.state'), (bool)$edit_state, $errmsg);

		$errmsg = "----> Failed test for $username core.edit for com_attachments, " .
			" expected $edit, got ".$canDo->get('core.edit')." for ".$username;
		$this->assertEquals($canDo->get('core.edit'), (bool)$edit, $errmsg);

		$errmsg = "----> Failed test for $username core.edit.own for com_attachments, " .
			" expected $edit_own, got ".$canDo->get('core.edit.own')." for ".$username;
		$this->assertEquals($canDo->get('core.edit.own'), (bool)$edit_own, $errmsg);

		$errmsg = "----> Failed test for $username attachments.delete.own for com_attachments, " .
			" expected $delete_own, got ".$canDo->get('attachments.delete.own')." for ".$username;
		$this->assertEquals($canDo->get('attachments.delete.own'), (bool)$delete_own, $errmsg);
	}
	

	/**
	 * Get the test data from CSV file
	 */
	public function provider()
	{
		return new CsvFileIterator(dirname(__FILE__).'/testActionsData.csv');
	}

}
