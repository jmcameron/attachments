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
class AttachmentsHelper3 extends AttachmentsHelper
{
    /**
     * Truncate the URL if it is longer than the maxlen
     * Do this by deleting necessary characters from the middle of the URL
     *
     * Always preserve the 'https://' or 'http://' part on the left.
     *
     * NOTE: The 'maxlen' applies only to the part after the 'https://' or 'https://'
     *
     * @param string $raw_url the input URL
     * @param int $maxlen the maximum allowed length (0 means no limit)
     *
     * @return the truncated URL
     */
    public static function truncateUrl($raw_url, $maxlen)
    {
        return parent::truncateUrl($raw_url, $maxlen);
    }
}

/**
 * Tests URL truncation
 *
 * @package Attachments
 * @subpackage Tests
 */
class HelperURLTruncationTest extends AttachmentsTestCase
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

        $this->assertEquals(
            $truncated_url,
            AttachmentsHelper3::truncateUrl($full_url, $maxlen)
        );
    }


    /**
     * Get the test data from CSV file
     */
    public static function provider(): CsvFileIterator
    {
        return new CsvFileIterator(__DIR__ . '/testHelperURLTruncationData.csv');
    }
}