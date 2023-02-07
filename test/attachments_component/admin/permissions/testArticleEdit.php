<?php
/**
 * Attachments component
 *
 * @package Attachments_test
 * @subpackage Attachments_helper
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

jimport('joomla.plugin.plugin');
jimport('joomla.plugin.helper');
jimport('joomla.event.dispatcher');
jimport('joomla.filter.filterinput');
jimport('joomla.environment.request');
jimport('joomla.application.component.helper');

require_once JPATH_BASE.'/administrator/components/com_attachments/permissions.php';


/**
 * Tests for permissions to edit articles for variuous users
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 */
class ArticleEditTest extends JoomlaDatabaseTestCase
{

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
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
	 * Test to see whether a user may edit a specified article
	 * 
	 * @dataProvider provider
	 *
	 * @param int $user_id the id of the user to test
	 * @param string $username the username (for error printouts)
	 * @param int $art_id the id of the article to test
	 * @param int $may_edit the expected result of the test
	 */
	public function testArticleEdit($user_id,$username,$art_id,$may_edit)
	{
		$result = AttachmentsPermissions::userMayEditArticle((int)$art_id, (int)$user_id);
		$errmsg = "----> Failed test for $username edit article $art_id, expected $may_edit, got $result";

		$this->assertEquals($result, (bool)$may_edit, $errmsg);
	}
	

	/**
	 * Get the test data from CSV file
	 */
	public function provider()
	{
		return new CsvFileIterator(dirname(__FILE__).'/testArticleEditData.csv');
	}

}
