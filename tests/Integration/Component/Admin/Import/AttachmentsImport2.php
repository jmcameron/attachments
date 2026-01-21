<?php

namespace Tests\Integration\Component\Admin\Import;

use JMCameron\Component\Attachments\Administrator\Helper\AttachmentsImport;

/**
 * Work-around class to expose protected method for testing
 *
 * @package Attachments_test
 * @subpackage Attachments_helper
 */
class AttachmentsImport2 extends AttachmentsImport
{
    /**
     * Parse the field names from the first(next) line of the CSV file
     * @param file $file the opened file object
     * @return the associative array (fieldname => index) or error message
     */
    public static function parseFieldNames($file)
    {
        return parent::parseFieldNames($file);
    }
}