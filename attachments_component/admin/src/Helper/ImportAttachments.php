<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\Helper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * A class for importing attachments data from files
 *
 * @package Attachments
 */
class ImportAttachments extends ImportFromCSV
{
    public function __construct()
    {
        $required_fields = array( 'id',
                                  'filename',
                                  'filename_sys',
                                  'file_type',
                                  'file_size',
                                  'url',
                                  'uri_type',
                                  'url_valid',
                                  'url_relative',
                                  'display_name',
                                  'description',
                                  'iconFilename',
                                  'access',
                                  'state',
                                  'user_field_1',
                                  'user_field_2',
                                  'user_field_3',
                                  'parent_type',
                                  'parent_entity',
                                  'parent_id',
                                  'parent_title',
                                  'created',
                                  'created_by',
                                  'created_by_username',
                                  'modified',
                                  'modified_by',
                                  'modified_by_username',
                                  'download_count'
                                  );

        $optional_fields = array( );

        $field_default = array( /* ??? */
                                );

        $extra_fields = array( 'parent_title',
                               'created_by_username',
                               'modified_by_username'
                               );

        parent::__construct($required_fields, $optional_fields, $field_default, $extra_fields);
    }


    public function importAttachments($filename, $dry_run = false)
    {
        // Open the file
        $open_ok = $this->open($filename);
        if ($open_ok !== true) {
            throw new \Exception($open_ok, 500);
            return;
        }

        // Read the data and import the attachments
        $num_records = 0;
        while ($this->readNextRecord()) {
// ???          // Create the raw record (???)
// ???          $record = object();
// ???
// ???          // Copy in the fields from the CSV data
// ???          $this->bind($record);
// ???
// ???          // Verify the category
// ???          $cat_ok = $this->_verifyCategory((int)$record->catid,
// ???                                           $record->category_title);
// ???          if ( $cat_ok !== true ) {
// ???              return JError::raiseError(500, $cat_ok);
// ???              }
// ???
// ???          // Verify the creator
// ???          $creator_ok = $this->_verifyUser((int)$record->created_by,
// ???                                           $record->created_by_name);
// ???          if ( $creator_ok !== true ) {
// ???              return JError::raiseError(500, $creator_ok);
// ???              }

            // Save the record
            if (!$dry_run) {
                // ???
            }
            $num_records += 1;
        }

        $this->close();
    }
}
