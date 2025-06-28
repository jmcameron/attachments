<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\Helper;

defined('_JEXEC') or die('Restricted access');

/**
 * A class for importing attachments data from files
 *
 * @package Attachments
 */
class ImportArticles extends ImportFromCSV
{
    public function __construct()
    {
        $required_fields = array( 'title',
                                  'alias',
                                  'introtext',
                                  'fulltext',
                                  'state',
                                  'catid',
                                  'created',
                                  'created_by',
                                  'created_by_alias',
                                  'modified',
                                  'modified_by',
                                  'publish_up',
                                  'publish_down',
                                  'version',
                                  'access',
                                  );

        $optional_fields = array( 'attribs',
                                  'mask',
                                  'hits',
                                  'metadata',
                                  'metadesc',
                                  'metakey',
                                  'title_alias',
                                  'version',
                                  );

        $field_default = array( 'language' => '*',
                                'state' => 1, /* Published */
                                'access' => 1 /* Public */
                                );

        $extra_fields = array( 'category_title',
                               'created_by_name'
                               );

        parent::__construct($required_fields, $optional_fields, $field_default, $extra_fields);
    }


    public function importArticles($filename, $dry_run = false)
    {
        // Open the file
        $open_ok = $this->open($filename);
        if ($open_ok !== true) {
            throw new \Exception($open_ok, 500);
            return;
        }

        // Read the data and import the articles
        $num_records = 0;
        while ($this->readNextRecord()) {
            // Create the raw record
            $record = new \stdClass();

            // Copy in the fields from the CSV data
            $this->bind($record);

            // Verify the category
            $cat_ok = $this->_verifyCategory(
                (int)$record->catid,
                $record->category_title
            );
            if ($cat_ok !== true) {
                throw new \Exception($cat_ok, 500);
                return;
            }

            // Verify the creator
            $creator_ok = $this->_verifyUser(
                (int)$record->created_by,
                $record->created_by_name
            );
            if ($creator_ok !== true) {
                throw new \Exception($creator_ok, 500);
                return;
            }

            // Save the record
            if (!$dry_run) {
                // ???
            }
            $num_records += 1;
        }

        $this->close();
    }
}
