<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Tests
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace Tests\Integration\Component\Site\FileTypes;

use JMCameron\Component\Attachments\Site\Helper\AttachmentsFileTypes;
use Tests\AttachmentsTestCase;
use Tests\Utils\CsvFileIterator;

/**
 * Tests for file_type conversion functions
 *
 * @package Attachments
 * @subpackage Tests
 */
class FileTypeConversionsTest extends AttachmentsTestCase
{
    /**
     * Test various file type and mime type conversions to icon filenames
     *
     * @dataProvider provider
     *
     * @param string $filename the filename to test
     * @param string $iconFilename the expected iconFilename
     * @param string $mime_type the mime type to test (if the filename is empty)
     */
    public function testConversions($filename, $iconFilename, $mime_type)
    {
        $this->assertEquals($iconFilename, AttachmentsFileTypes::iconFilename($filename, $mime_type));

        if ($filename) {
            $this->assertEquals($mime_type, AttachmentsFileTypes::mimeType($filename));
        }
    }

    /**
     * Get the test data from CSV file
     */
    public static function provider(): CsvFileIterator
    {
        return new CsvFileIterator(__DIR__ . '/testFileTypesConversionsData.csv');
    }
}