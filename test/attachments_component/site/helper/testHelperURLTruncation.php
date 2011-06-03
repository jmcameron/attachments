<?php
/**
 * Attachments component
 *
 * @package Attachments_test
 * @subpackage Attachments_helper
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

/** Load the helper class for testing */
require_once JPATH_BASE.'/components/com_attachments/helper.php';


/**
 * Work-around class to expose protected method for testing
 *
 * @package Attachments_test
 * @subpackage Attachments_helper
 */
class AttachmentsHelper3 extends AttachmentsHelper
{
	/**
	 * Truncate the URL if it is longer than the maxlen
	 * Do this by deleting necessary characters from the middle of the URL
	 *
	 * Always preserve the 'http://' part on the left.
	 *
	 * NOTE: The 'maxlen' applies only to the part after the 'http://'
	 *
	 * @param string $raw_url the input URL
	 * @param int $maxlen the maximum allowed length (0 means no limit)
	 *
	 * @return the truncated URL
	 */
	public function truncate_url($raw_url, $maxlen)
	{
		return parent::truncate_url($raw_url, $maxlen);
	}
}


/**
 * Tests URL trunction
 *
 * @package Attachments_test
 * @subpackage Attachments_helper
 */
class HelperURLTruncationTest extends PHPUnit_Framework_TestCase
{

    /**
	 * Test truncating a url
	 * 
     * @dataProvider provider
	 *
	 * @param string $truncated_url the expected truncated URL
	 * @param string $full_url the URL before truncating
	 * @param int $maxlen the maximum length for truncation
     */
    public function testURLTruncation($truncated_url, $full_url, $maxlen)
    {
		$maxlen = (int)$maxlen;

		$this->assertEquals($truncated_url,
							AttachmentsHelper3::truncate_url($full_url, $maxlen));
    }


	/**
	 * Get the test data from CSV file
	 */
	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testHelperURLTruncationData.csv');
    }
}
