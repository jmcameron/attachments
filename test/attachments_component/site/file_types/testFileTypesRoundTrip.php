<?php
/**
 * Attachments component
 *
 * @package Attachments_test
 * @subpackage Attachments_file_types
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

/** Load the file types class for testing */
require_once JPATH_BASE.'/components/com_attachments/file_types.php';


/**
 * Tests for file_type functions
 *
 * @package Attachments_test
 * @subpackage Attachments_file_types
 */
class FileTypeRoundTripTest extends PHPUnit_Framework_TestCase
{
	/**
	 * Test the round-trip conversions form icon-filename to mime-type and back
	 */
	public function testRoundTrip()
	{
		$icon_filenames = AttachmentsFileTypes::unique_icon_filenames();

		$mime_from_icon = array_flip(AttachmentsFileTypes::$attachments_icon_from_mime_type);

		foreach ($icon_filenames as $icon) {
			if ( array_key_exists($icon, $mime_from_icon) ) {
				$mime_type = $mime_from_icon[$icon];
				$this->assertEquals($icon, AttachmentsFileTypes::icon_filename('', $mime_type));
				}
			}
	}
}
