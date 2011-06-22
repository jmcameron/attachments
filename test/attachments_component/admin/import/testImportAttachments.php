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

require_once JPATH_BASE.'/administrator/components/com_attachments/import.php';



/**
 * Tests for ACL action permissions for various users
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 */
class ImportAttachmentsTest extends JoomlaDatabaseTestCase
{
	/**
	 * Sets up the fixture
	 */
	protected function setUp()
	{
		parent::setUp();
		parent::setUpBeforeClass();

		// Force loading the component language
		$lang =  JFactory::getLanguage();
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
	public function testImportAttachmentsFromCSVFile($test_filename, $expected_result, $dry_run)
	{
		$path = dirname(__FILE__).'/'.$test_filename;

		// Open the CSV file
		$result = AttachmentsImport::importAttachmentsFromCSVFile($path, $verify_parent=true, $dry_run);
		if ( is_numeric($expected_result) AND is_numeric($result) ) {
			$this->assertEquals((int)$expected_result, (int)$result);
			}
		else {
			// Cut off the error number for comparison
			$errmsg = substr($result, 0, strpos($result, ' (ERR'));

			$this->assertEquals($expected_result, $errmsg);
			}
	}
	

	/**
	 * Get the test data from CSV file
	 */
	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testImportAttachmentsData.csv');
    }

}
