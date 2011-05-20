<?php
require_once 'PHPUnit/Framework.php';
require_once JPATH_TESTS.'/utils/CsvFileIterator.php';

require_once JPATH_BASE.'/components/com_attachments/file_types.php';


class FileTypeTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider
     */
    public function testConversions($filename, $icon_filename, $mime_type)
    {
		$this->assertEquals($icon_filename, AttachmentsFileTypes::icon_filename($filename, $mime_type));

		if ( $filename ) {
			$this->assertEquals($mime_type, AttachmentsFileTypes::mime_type($filename));
			}
    }

	public function provider()
    {
        return new CsvFileIterator(dirname(__FILE__).'/testFileTypesData.csv');
    }
}


class FileTypeRoundTripTest extends PHPUnit_Framework_TestCase
{
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

?>
