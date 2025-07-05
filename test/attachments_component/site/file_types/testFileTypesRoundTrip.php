<?php

/**
 * Attachments component
 *
 * @package Attachments_test
 * @subpackage Attachments_file_types
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use JMCameron\Component\Attachments\Site\Helper\AttachmentsFileTypes;

/** Load the PHPUnit test framework */
require_once 'PHPUnit/Framework/TestCase.php';

/** Load the CSV file iterator class */
require_once JPATH_TESTS . '/utils/CsvFileIterator.php';

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
        $iconFilenames = AttachmentsFileTypes::uniqueIconFilenames();

        $mime_from_icon = array_flip(AttachmentsFileTypes::$attachments_icon_from_mime_type);

        foreach ($iconFilenames as $icon) {
            if (array_key_exists($icon, $mime_from_icon)) {
                $mime_type = $mime_from_icon[$icon];
                $this->assertEquals($icon, AttachmentsFileTypes::iconFilename('', $mime_type));
            }
        }
    }
}
