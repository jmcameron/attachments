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
 * Tests for permissions to edit categories for various users
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 */
class CategoryEditTest extends JoomlaDatabaseTestCase
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
	 * Test to see whether a user may edit a specified category
	 * 
     * @dataProvider provider
	 *
	 * @param int $user_id the id of the user to test
	 * @param string $username the username (for error printouts)
	 * @param int $cat_id the id of the category to test
	 * @param int $may_edit the expected result of the test
     */
	public function testCategoryEdit($user_id, $username, $cat_id, $may_edit)
	{
		$result = AttachmentsPermissions::userMayEditCategory((int)$cat_id, (int)$user_id);
		$errmsg = "----> Failed test for $username edit category $cat_id, expected $may_edit, got $result";

		$this->assertEquals($result, (bool)$may_edit, $errmsg);
	}
	
	/**
	 * Get the test data from CSV file
	 */
	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testCategoryEditData.csv');
    }

}

