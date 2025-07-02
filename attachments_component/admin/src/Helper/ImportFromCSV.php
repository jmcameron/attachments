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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects



/**
 * A class for importing table records from a CSV file
 *
 * NOTES:
 *   - The Comma Separated Value (CSV) file must contain the data
 *     one line per record
 *   - The first line of the CSV file must contain the field names
 *     (the case of the field names is ignored).
 *
 * @package Attachments
 */

class ImportFromCSV
{
    /**
     * The CSV filename
     */
    protected $filename = null;

    /**
     * The CSV file object
     */
    protected $file = null;

    /**
     * The current line number
     */
    protected $line_number = null;

    /**
     * The data for the current CSV record/line
     */
    protected $data = null;

    /**
     * The required field names
     *
     * @var array
     */
    protected $fields = null;

    /**
     * Optional field names
     *
     * @var array
     */
    protected $optional_fields = null;

    /**
     * Field defaults (associative array)
     *
     * The value to be used for the field if it is empty or not specified in
     * the CSV file (typically for optional fields).
     *
     * Defaults can be defined for fields that are not in
     * in the required or optional fields but are in the Table.
     *
     * eg,   Array('hits' => 0)
     *
     * @var array
     */
    protected $field_default = null;


    /**
     * Extra fields
     *
     * These fields will be used by the derived class to construct
     * values for the required or optional fields.  The extra field
     * names may not duplicate any of the required or optional field
     * names or duplicate any field name in the actual table.
     *
     * 'Extra' fields must be present in every record in the CSV file
     *
     * @var array
     */
    protected $extra_fields = null;


    /**
     * The field map (between the field name and the column number)
     *
     * eg,   Array('fname1' => 0, 'fname2' => 1)
     *
     * @var array
     */
    protected $field_map = null;


    /**
     * Constructor
     *
     * @param  array $required_fields the list of required field names in the CSV file
     * @param  array $optional_fields the list of optional field names in the CSV file
     * @param  array $field_default an associative array of the default values for each field (if empty)
     * @param  array $extra_fields a list of extra user-defined field names in the CSV file
     */
    public function __construct(
        $required_fields,
        $optional_fields = array(),
        $field_default = array(),
        $extra_fields = array()
    ) {
        $this->filename = null;
        $this->file = null;
        $this->line_number = null;
        $this->data = null;
        $this->fields = $required_fields;
        $this->optional_fields = $optional_fields;
        $this->field_default = $field_default;
        $this->extra_fields = $extra_fields;
        $this->field_map = array();
    }


    /**
     * Open the CSV file
     *
     * @param string $csv_filename The filename/path of the CSV input file
     *
     * @return true if successful or error message on failure
     */
    public function open($csv_filename)
    {
        $this->filename = null;
        $this->file = null;
        $this->line_number = 0;

        // Open the CSV file
        $this->file = @fopen($csv_filename, 'r');
        if (! $this->file) {
            return Text::sprintf('ATTACH_ERROR_UNABLE_TO_OPEN_CSV_FILE_S', $csv_filename) . ' (ERR 98)';
        }

        // Parse the first row to process field names and indices
        $result = $this->parseFieldNames($this->file);
        if ($result !== true) {
            fclose($this->file);
            return $result;
        }

        return true;
    }


    /**
     * Close the CSV file
     */
    public function close()
    {
        if ($this->file) {
            fclose($this->file);
        }
        $this->file = null;
        $this->filename = null;
        $this->line_number = 0;
    }


    /**
     * Read the next record, skipping over blank lines
     *
     * Also checks to make sure the extra fields are not empty
     *
     * @return true of read is okay, false if at the end of the file, or error message
     */
    public function readNextRecord()
    {
        if (feof($this->file)) {
            // Extra check just to be safe
            return false;
        }

        // Keep reading until we get a non-blank line
        while (!feof($this->file)) {
            // Read the line
            $this->data = fgetcsv($this->file);
            $this->line_number += 1;

            // Do we have data?
            if ($this->data) {
                break;
            } else {
                // Fail if the blank line is at the end of the file
                if (feof($this->file)) {
                    return false;
                }
            }
        }

        // Make sure the extra fields are present
        $missing_fields = array();
        foreach ($this->extra_fields as $efield) {
            if (!isset($this->data[$efield])) {
                $missing_fields[] = $efield;
            }
        }
        if (count($missing_fields) > 0) {
            return Text::sprintf('ATTACH_ERROR_MISSING_EXTRA_FIELDS_S', implode(',', $missing_fields)) . ' (ERR 99)';
        }

        return true;
    }


    /**
     * Get the data for the specified field
     *
     * @return the field data if available (otherwise null)
     */
    protected function getData($fieldName)
    {
        if (isset($this->data[$fieldName])) {
            return $this->data[$fieldName];
        } else {
            return null;
        }
    }


    /**
     * Bind the data from the next line of the CSV file with the provided object
     *
     * @param object $record the record object that will be updated
     */
    public function bind(&$record)
    {
        // Bind the required fields
        foreach ($this->fields as $field) {
            $fdata = $this->getData($field);
            if ($fdata) {
                $record->$field = $fdata;
            }
        }

        // Bind the optional fields, if present
        foreach ($this->optional_fields as $field) {
            $fdata = $this->getData($field);
            if ($fdata) {
                $record->$field = $fdata;
            }
        }

        // Bind any defaults (not already set in required or optional fields)
        foreach ($this->field_default as $field => $val) {
            if (
                (isset($record->$field) &&  ( $record->$field == '' )) ||
                 !isset($record->$field)
            ) {
                    $record->$field = $val;
            }
        }
    }


    /**
     * Parse the field names from the first(next) line of the CSV file
     *
     * @param file $file the handle for the already opened file object
     *
     * @return various true if successful and a translated error message if not
     */
    protected function parseFieldNames($file)
    {
        $bad_fields = array();

        // Load the field names from the file
        $header_line = fgetcsv($file);
        $this->line_number += 1;
        for ($i = 0; $i < count($header_line); $i++) {
            $field_name = strtolower(trim($header_line[$i]));
            if (
                in_array($field_name, $this->fields) ||
                 in_array($field_name, $this->optional_fields) ||
                 in_array($field_name, $this->extra_fields)
            ) {
                $field[$field_name] = $i;
            } else {
                $bad_fields[] = $field_name;
            }
        }
        if (count($bad_fields) > 0) {
            // Warn if there were unrecognized field names
            return Text::sprintf('ATTACH_ERROR_UNRECOGNIZED_FIELD_S', implode(', ', $bad_fields)) . ' (ERR 100)';
        }

        // Make sure all required field names were found
        $missing = array();
        foreach ($this->fields as $fname) {
            if (!array_key_exists($fname, $this->field_map)) {
                $missing[] = $fname;
            }
        }
        if (count($missing) > 0) {
            // Warn if there were missing required field names
            throw new \Exception(Text::sprintf(
                'ATTACH_ERROR_MISSING_FIELDS_S',
                implode(', ', $missing)
            ) . ' (ERR 101)', 500);
            return;
        }

        return true;
    }


    /**
     * Verify a user
     *
     * @param int $user_id the ID of the user
     * @param string $expected_username the expected username
     *
     * @return true if ok, error message if not
     */
    protected function verifyUser($user_id, $expected_username)
    {
        /** @var \Joomla\Database\DatabaseDriver $db */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);
        $query->select('username')->from('#__users')->where('id = ' . (int)$user_id);
        try {
            $db->setQuery($query, 0, 1);
            $actual_username = $db->loadResult();
        } catch (\RuntimeException $e) {
            return Text::sprintf(
                'ATTACH_ERROR_UNABLE_TO_FIND_USER_S_ID_N',
                $user_id,
                $expected_username
            ) . ' (ERR 102)';
        }
        if (empty($actual_username)) {
            return Text::sprintf(
                'ATTACH_ERROR_UNABLE_TO_FIND_USER_S_ID_N',
                $user_id,
                $expected_username
            ) . ' (ERR 102)';
        }
        if (strtolower($expected_username) != strtolower($actual_username)) {
            return Text::sprintf(
                'ATTACH_ERROR_USERNAME_MISMATCH_ID_N_S_S',
                $user_id,
                $expected_username,
                $actual_username
            ) . ' (ERR 103)';
        }

        return true;
    }


    /**
     * Verify a category
     *
     * @param int $category_id the ID of the category
     * @param string $expected_category_title the expected category title
     *
     * @return true if ok, error message if not
     */
    protected function verifyCategory($category_id, $expected_category_title)
    {
        /** @var \Joomla\Database\DatabaseDriver $db */
        $db = Factory::getContainer()->get('DatabaseDriver');

        $query = $db->getQuery(true);
        $query->select('title')->from('#__categories')->where('id = ' . (int)$category_id);
        try {
            $db->setQuery($query, 0, 1);
            $actual_category_title = $db->loadResult();
        } catch (\RuntimeException $e) {
            return Text::sprintf(
                'ATTACH_ERROR_UNABLE_TO_FIND_CATEGORY_ID_S',
                $category_id,
                $expected_category_title
            ) . ' (ERR 104)';
        }
        if (empty($actual_category_title)) {
            return Text::sprintf(
                'ATTACH_ERROR_UNABLE_TO_FIND_CATEGORY_ID_S',
                $category_id,
                $expected_category_title
            ) . ' (ERR 104)';
        }
        if (strtolower($expected_category_title) != strtolower($actual_category_title)) {
            return Text::sprintf(
                'ATTACH_ERROR_CATEGORY_TITLE_MISMATCH_ID_S_S',
                $category_id,
                $expected_category_title,
                $actual_category_title
            ) . ' (ERR 105)';
        }

        return true;
    }
}
