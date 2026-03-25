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

use JMCameron\Component\Attachments\Site\Helper\AttachmentsFileTypes;
use Tests\AttachmentsTestCase;

/**
 * Tests for file_type functions
 *
 * @package Attachments
 * @subpackage Tests
 */
class FileTypeRoundTripTest extends AttachmentsTestCase
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