<?php
/**
 * Attachments component
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

/** Load the PHPUnit test framework */
require_once 'PHPUnit/Framework.php';

/** Load the CSV file iterator class */
require_once JPATH_TESTS.'/utils/CsvFileIterator.php';

jimport('joomla.log.log');

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
	 * @param int $user_id The ID of the user to test
	 * @param string $username The name of ther user (for error outputs)
	 * @param int $admin correct 'core.admin' permission (0/1 interpreted as bool)
	 * @param int $manage correct 'core.manage' permission (0/1 interpreted as bool)
	 * @param int $create correct 'core.create' permission (0/1 interpreted as bool)
	 * @param int $delete correct 'core.delete' permission (0/1 interpreted as bool)
	 * @param int $edit_state correct 'core.edit.state' permission (0/1 interpreted as bool)
	 * @param int $edit correct 'core.edit' permission (0/1 interpreted as bool)
	 * @param int $edit_own correct 'core.edit.own' permission (0/1 interpreted as bool)
     */
	public function testActions($user_id,$username,$admin,$manage,$create,$delete,$edit_state,$edit,$edit_own)
	{
		$canDo = AttachmentsPermissions::getActions((int)$user_id);
		$errmsg = "----> Failed test for $username core.admin for com_attachments, " .
			" expected $admin, got ".$canDo->get('core.admin');

		$this->assertEquals($canDo->get('core.admin'), (bool)$admin, $errmsg);

		// @TODO ADD THE REST OF THE TESTS ???
	}
	

	/**
	 * Get the test data from CSV file
	 */
	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testActionsData.csv');
    }

}
