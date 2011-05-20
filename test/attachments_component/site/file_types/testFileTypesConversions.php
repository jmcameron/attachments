<?php
require_once 'PHPUnit/Framework.php';
require_once JPATH_TESTS.'/utils/CsvFileIterator.php';

require_once JPATH_BASE.'/components/com_attachments/file_types.php';


class FileTypeConversionsTest extends PHPUnit_Framework_TestCase
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
        return new CsvFileIterator(dirname(__FILE__).'/testFileTypesConversionsData.csv');
    }
}

?>
