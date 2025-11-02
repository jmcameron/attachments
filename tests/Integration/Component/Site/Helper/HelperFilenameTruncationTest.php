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

namespace Tests\Integration\Component\Site\Helper;

use JMCameron\Component\Attachments\Site\Helper\AttachmentsHelper;
use Tests\AttachmentsTestCase;
use Tests\Utils\CsvFileIterator;

/**
 * Work-around class to expose protected method for testing
 *
 * @package Attachments
 * @subpackage Tests
 */
class AttachmentsHelper2 extends AttachmentsHelper
{
    /**
     * Truncate the filename if it is longer than the maxlen
     * Do this by deleting necessary at the end of the base filename (before the extensions)
     *
     * @param string $raw_filename the input filename
     * @param int $maxlen the maximum allowed length (0 means no limit)
     *
     * @return the truncated filename
     */
    public static function truncateFilename($raw_filename, $maxlen)
    {
        return parent::truncateFilename($raw_filename, $maxlen);
    }
}

/**
 * Tests filename truncation
 *
 * @package Attachments
 * @subpackage Tests
 */
class HelperFilenameTruncationTest extends AttachmentsTestCase
{
    /**
     * Test filename truncation
     *
     * @dataProvider provider
     *
     * @param string $truncated_filename the expected result of the truncation
     * @param string $full_filename the filename before truncating
     * @param int $maxlen the maximum length for the filename
     */
    public function testFilenameTruncation($truncated_filename, $full_filename, $maxlen)
    {
        $maxlen = (int)$maxlen;

        $this->assertEquals(
            $truncated_filename,
            AttachmentsHelper2::truncateFilename($full_filename, $maxlen)
        );
    }

    /**
     * Get the test data from CSV file
     */
    public static function provider(): CsvFileIterator
    {
        return new CsvFileIterator(__DIR__ . '/testHelperFilenameTruncationData.csv');
    }
}