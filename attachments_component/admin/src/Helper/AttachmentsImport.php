<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\Helper;

use JMCameron\Component\Attachments\Site\Helper\AttachmentsDefines;
use JMCameron\Plugin\AttachmentsPluginFramework\AttachmentsPluginManager;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects



/**
 * A class for importing attachments data from files
 *
 * @package Attachments
 */
class AttachmentsImport
{
    public static $field_names =
        array( 'id',
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
               'icon_filename',
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
               'download_count' );

    public static $extra_field_names =
        array( 'parent_title',
               'created_by_username',
               'modified_by_username' );

    /**
     * Compare two UTF-8 strings
     * (disregards leading and trailing whitespace and case differences)
     *
     * @param string  $ustr1  UTF-8 string 1
     * @param string  $ustr2  UTF-8 string 2
     * @return true if the strings match
     */
    public static function utf8StringsEqual($ustr1, $ustr2)
    {
        return strncasecmp(trim($ustr1), trim($ustr2), 4096) == 0;
    }


    /**
     * Import attachment data from a CSV file
     *
     * The CSV file must have the field names in the first row
     *
     * @param string $filename the filename of the CSV file
     * @param bool $verify_parent if true, each attachments parent must exist
     * @param bool $update if true, if the attachment exists, update it (or create a new one)
     * @param bool $dry_run do everything except actually add entries to attachment table in the database
     *
     * @return array of IDs of the imported attachments (if $dry_run, number that would have been imported),
     * or error message
     */
    public static function importAttachmentsFromCSVFile(
        $filename,
        $verify_parent = true,
        $update = false,
        $dry_run = false
    ) {
        /** @var \Joomla\Database\DatabaseDriver $db */
        $db = Factory::getContainer()->get('DatabaseDriver');

        // Open the CSV file
        $line_num = 0;
        $f = @fopen($filename, 'r');
        if (!$f) {
            return Text::sprintf('ATTACH_ERROR_UNABLE_TO_OPEN_CSV_FILE_S', $filename) . ' (ERR 85)';
        }

        // Parse the first row to process field names and indices
        $field = AttachmentsImport::parseFieldNames($f);
        $line_num += 1;
        if (!is_array($field)) {
            return $field;
        }

        // Get the default access level from the Attachments options
        $params = ComponentHelper::getParams('com_attachments');
        $default_access_level = $params->get('default_access_level', AttachmentsDefines::$DEFAULT_ACCESS_LEVEL_ID);

        // Load the attachments parent manager
        PluginHelper::importPlugin('attachments');
        $apm = AttachmentsPluginManager::getAttachmentsPluginManager();

        // Process the CSV data
        if ($dry_run) {
            $ids_ok = 0;
        } else {
            $ids_ok = array();
        }

        iconv_set_encoding("default_charset", "UTF-8");
        setlocale(LC_ALL, 'en_US.UTF-8');

        while (!feof($f)) {
            // Read the next line
            $adata = fgetcsv($f, escape: '\\');
            $line_num += 1;
            $line_str = '  [LINE: ' . $line_num . '] ';

            // Skip blank lines
            if (!$adata) {
                continue;
            }

            // get the attachment ID
            $attachment_id = $adata[$field['id']];
            if (!is_numeric($attachment_id)) {
                return Text::sprintf('ATTACH_ERROR_BAD_ATTACHMENT_ID_S', $attachment_id) . $line_str . ' (ERR 86)';
            }
            $attachment_id = (int)$attachment_id;

            $parent_type = $adata[$field['parent_type']];
            $parent_entity = strtolower($adata[$field['parent_entity']]);
            $parent_id = (int)$adata[$field['parent_id']];

            // Make sure it is not a 'section' attachment
            if ($parent_entity == 'section') {
                return Text::sprintf('ATTACH_ERROR_SECTION_ATTACHMENT_NOT_ALLOWED_ID', $attachment_id) .
                                    $line_str . ' (ERR 86B)';
            }

            // Get the attachment parent object
            if (!$apm->attachmentsPluginInstalled($parent_type)) {
                return Text::sprintf('ATTACH_ERROR_UNKNOWN_PARENT_TYPE_S', $parent_type) . $line_str . ' (ERR 87)';
            }
            
            // Does the parent exist?
            if ($verify_parent) {
                $parent = $apm->getAttachmentsPlugin($parent_type);

                // Make sure a parent with the specified ID exists
                if (!$parent->parentExists($parent_id, $parent_entity)) {
                    return Text::sprintf('ATTACH_ERROR_UNKNOWN_PARENT_ID_N', $parent_id) . $line_str . ' (ERR 88)';
                }

                // Double-check by comparing the title
                $attachment_parent_title = $adata[$field['parent_title']];
                $parent_title = $parent->getTitle($parent_id, $parent_entity);
                if (!AttachmentsImport::utf8StringsEqual($parent_title, $attachment_parent_title)) {
                    return Text::sprintf(
                        'ATTACH_ERROR_PARENT_TITLE_MISMATCH_ID_N_TITLE_S_S',
                        $parent_id,
                        $parent_title,
                        $attachment_parent_title
                    ) . $line_str . ' (ERR 89)';
                }
            }

            // Check the creator username
            $creator_id = (int)$adata[$field['created_by']];
            $attachment_creator_username = $adata[$field['created_by_username']];
            $query = $db->getQuery(true);
            $query->select('username')->from('#__users')->where('id = ' . (int)$creator_id);
            try {
                $db->setQuery($query, 0, 1);
                $creator_username = $db->loadResult();
            } catch (\Exception $e) {
                return Text::sprintf(
                    'ATTACH_ERROR_UNABLE_TO_FIND_CREATOR_ID_S',
                    $creator_id,
                    $attachment_creator_username
                ) . $line_str . ' (ERR 90)';
            }
            if (empty($creator_username)) {
                return Text::sprintf(
                    'ATTACH_ERROR_UNABLE_TO_FIND_CREATOR_ID_S',
                    $creator_id,
                    $attachment_creator_username
                ) . $line_str . ' (ERR 90)';
            }
            if (!AttachmentsImport::utf8StringsEqual($creator_username, $attachment_creator_username)) {
                return Text::sprintf(
                    'ATTACH_ERROR_CREATOR_USERNAME_MISMATCH_ID_S_S',
                    $creator_id,
                    $attachment_creator_username,
                    $creator_username
                ) . $line_str . ' (ERR 91)';
            }

            // Check the modifier name
            $modifier_id = (int)$adata[$field['modified_by']];
            $attachment_modifier_username = $adata[$field['modified_by_username']];
            $query = $db->getQuery(true);
            $query->select('username')->from('#__users')->where('id = ' . (int)$modifier_id);
            try {
                $db->setQuery($query, 0, 1);
                $modifier_username = $db->loadResult();
            } catch (\Exception $e) {
                return Text::sprintf(
                    'ATTACH_ERROR_UNABLE_TO_FIND_MODIFIER_ID_S',
                    $modifier_id,
                    $attachment_modifier_username
                ) . $line_str . ' (ERR 92)';
            }
            if (empty($modifier_username)) {
                return Text::sprintf(
                    'ATTACH_ERROR_UNABLE_TO_FIND_MODIFIER_ID_S',
                    $modifier_id,
                    $attachment_modifier_username
                ) . $line_str . ' (ERR 92)';
            }
            if (!AttachmentsImport::utf8StringsEqual($modifier_username, $attachment_modifier_username)) {
                return Text::sprintf(
                    'ATTACH_ERROR_MODIFIER_USERNAME_MISMATCH_ID_S_S',
                    $modifier_id,
                    $attachment_modifier_username,
                    $modifier_username
                ) . $line_str . ' (ERR 93)';
            }

            // Construct an attachments entry
            /** @var \Joomla\CMS\MVC\Factory\MVCFactory $mvc */
            $mvc = Factory::getApplication()
                ->bootComponent("com_attachments")
                ->getMVCFactory();
            $attachment = $mvc->createTable('Attachment', 'Administrator');

            if ($update) {
                // The attachment ID cannot be 0 for updating!
                if ($attachment_id == 0) {
                    return Text::_('ATTACH_ERROR_CANNOT_MODIFY_ATTACHMENT_ZERO_ID') . $line_str . ' (ERR 94)';
                }

                // Load the data from the attachment to be updated (or create new one)
                if (!$attachment->load($attachment_id)) {
                    $attachment->reset();
                }
            } else {
                // Create new attachment
                $attachment->reset();
            }

            // Copy in the data from the CSV file
            foreach (AttachmentsImport::$field_names as $fname) {
                if (($fname != 'id') && !in_array($fname, AttachmentsImport::$extra_field_names)) {
                    $attachment->$fname = $adata[$field[$fname]];
                }
            }

            // Do any necessary overrides
            $attachment->parent_entity = $parent_entity;  // ??? what about parent_entity_name?
            $attachment->access = $default_access_level;
            $attachment->file_size = (int)$adata[$field['file_size']];

            if ($dry_run) {
                $ids_ok++;
            } else {
                // Store the new/updated attachment
                if ($attachment->store()) {
                    $ids_ok[] = $attachment->getDbo()->insertid();
                } else {
                    return Text::sprintf('ATTACH_ERROR_STORING_ATTACHMENT_S', $attachment->getError()) . ' (ERR 95)';
                }
            }
        }

        fclose($f);

        return $ids_ok;
    }



    /**
     * Parse the field names from the first(next) line of the CSV file
     *
     * @param file $file the handle for the opened file object
     *
     * @return the associative array (fieldname => index) or error message
     */
    protected static function parseFieldNames($file)
    {
        // Load the field names from the file
        $field = array();
        $header_line = fgetcsv($file, escape: '\\');
        // Strip of the leading BOM, if present
        $header_line = filter_var_array($header_line, FILTER_DEFAULT , FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
        for ($i = 0; $i < count($header_line); $i++) {
            $field_name = trim(strtolower($header_line[$i]));
            if (in_array($field_name, AttachmentsImport::$field_names)) {
                $field[$field_name] = $i;
            } else {
                return Text::sprintf('ATTACH_ERROR_UNRECOGNIZED_FIELD_S', $field_name) . ' (ERR 96)';
            }
        }

        // Make sure all field names were found
        if (count($field) != count(AttachmentsImport::$field_names)) {
            // Figure out which fields are missing
            $missing = array();
            foreach (AttachmentsImport::$field_names as $fname) {
                if (!array_key_exists($fname, $field)) {
                    $missing[] = $fname;
                }
            }
            return Text::sprintf('ATTACH_ERROR_MISSING_FIELDS_S', implode(',', $missing)) . ' (ERR 97)';
        }

        return $field;
    }
}
