<?php
/**
 * Attachments component
 *
 * @package Attachments_test
 * @subpackage Attachments_file_types
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

/** Load the PHPUnit test framework */
require_once 'PHPUnit/Framework.php';

/** Load the CSV file iterator class */
require_once JPATH_TESTS.'/utils/CsvFileIterator.php';

/** Load the file types class for testing */
require_once JPATH_BASE.'/components/com_attachments/file_types.php';


/**
 * Tests for file_type conversion functions
 *
 * @package Attachments_test
 * @subpackage Attachments_file_types
 */
class FileTypeConversionsTest extends PHPUnit_Framework_TestCase
{
    /**
	 * Test various file type and mime type conversions to icon filenames
	 *
     * @dataProvider provider
	 *
	 * @param string $filename the filename to test
	 * @param string $icon_filename the expected icon_filename
	 * @param string $mime_type the mime type to test (if the filename is empty)
     */
    public function testConversions($filename, $icon_filename, $mime_type)
    {
		$this->assertEquals($icon_filename, AttachmentsFileTypes::icon_filename($filename, $mime_type));

		if ( $filename ) {
			$this->assertEquals($mime_type, AttachmentsFileTypes::mime_type($filename));
			}
    }

	/**
	 * Get the test data from CSV file
	 */
	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testFileTypesConversionsData.csv');
    }
}
