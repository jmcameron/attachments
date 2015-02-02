<?php
/**
 * Attachments component
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
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

require_once JPATH_BASE.'/administrator/components/com_attachments/import.php';


/**
 * Work-around class to expose protected method for testing
 *
 * @package Attachments_test
 * @subpackage Attachments_helper
 */
class AttachmentsImport2 extends AttachmentsImport
{
	/**
	 * Parse the field names from the first(next) line of the CSV file
	 * @param file $file the opened file object
	 * @return the associative array (fieldname => index) or error message
	 */
	public static function parseFieldNames($file)
	{
		return AttachmentsImport::_parseFieldNames($file);
	}
}


/**
 * Tests for ACL action permissions for various users
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 */
class ImportParseFieldnamesTest extends JoomlaDatabaseTestCase
{
	/**
	 * Sets up the fixture
	 */
	protected function setUp()
	{
		parent::setUp();
		parent::setUpBeforeClass();

		// Force loading the component language
		$lang =	 JFactory::getLanguage();
		$lang->load('com_attachments', JPATH_BASE.'/administrator/components/com_attachments');
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
	 * 
	 *
	 * @dataProvider provider
	 *
	 */
	public function testParseFieldnames($test_filename, $result)
	{
		// Open the CSV file
		$path = dirname(__FILE__).'/'.$test_filename;
		$this->assertFileExists($path);
		$f = fopen($path, 'r');
		$this->assertNotEquals(false, $f);

		// parse the first line
		$field = AttachmentsImport2::parseFieldNames($f);
		if ( is_array($field) ) {
			// verify that all fieldnames were found
			$this->assertEquals(array_diff(array_keys($field), AttachmentsImport::$field_names), Array());
			}
		else {
			// Cut off the error number for comparison
			$errmsg = substr($field, 0, strpos($field, ' (ERR'));
			$this->assertEquals($errmsg, $result);
			}
	}
	

	/**
	 * Get the test data from CSV file
	 */
	public function provider()
	{
		return new CsvFileIterator(dirname(__FILE__).'/testParseFieldnamesData.csv');
	}

}
