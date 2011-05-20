<?php
require_once 'PHPUnit/Framework.php';
require_once JPATH_TESTS.'/utils/CsvFileIterator.php';

require_once JPATH_BASE.'/components/com_attachments/helper.php';


class AttachmentsHelper3 extends AttachmentsHelper
{
	public function truncate_url($raw_url, $maxlen)
	{
		return parent::truncate_url($raw_url, $maxlen);
	}
}


class HelperURLTruncationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testURLTruncation($truncated_url, $full_url, $maxlen)
    {
		$maxlen = (int)$maxlen;

		$this->assertEquals($truncated_url,
							AttachmentsHelper3::truncate_url($full_url, $maxlen));
    }

	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testHelperURLTruncationData.csv');
    }
}

?>
