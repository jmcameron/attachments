<?php
require_once 'PHPUnit/Framework.php';
require_once JPATH_TESTS.'/utils/CsvFileIterator.php';

require_once JPATH_BASE.'/components/com_attachments/helper.php';


class AttachmentsHelper2 extends AttachmentsHelper
{
	public function truncate_filename($raw_filename, $maxlen)
	{
		return parent::truncate_filename($raw_filename, $maxlen);
	}
}


class HelperFilenameTruncationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testFilenameTruncation($truncated_filename, $full_filename, $maxlen)
    {
		$maxlen = (int)$maxlen;

		$this->assertEquals($truncated_filename,
							AttachmentsHelper2::truncate_filename($full_filename, $maxlen));
    }

	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testHelperFilenameTruncationData.csv');
    }
}

?>
