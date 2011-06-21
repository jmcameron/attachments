<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');


/**
 * A class for importing attachments data from files
 *
 * @package Attachments
 */
class AttachmentsImport
{
	static $field_names =
		Array( 'id',
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
			   'created_by_name',
			   'modified',
			   'modified_by',
			   'modified_by_name',
			   'download_count' );

	static $extra_field_names =
		Array( 'parent_title',
			   'created_by_name',
			   'modified_by_name' );

	
	/**
	 * Import attachment data from a CSV file
	 *
	 * The CSV file must have the field names in the first row
	 * 
	 * @param string $filename the filename of the CSV file
	 * @param bool $verify_parent if true, each attachments parent must exist
	 * @param bool $dry_run do everything except actually add entries to attachment table in the database
	 *
	 * @return array of IDs of the imported attachemnts (if $dry_run, number that would have been imported), or error message
	 */
	public static function importAttachmentsFromCSVFile($filename, $verify_parent=true, $dry_run=false)
	{
		$db = JFactory::getDBO();

		// Open the CSV file
		$f = @fopen($filename, 'r');
		if ( !$f ) {
			return JText::sprintf('ERROR_UNABLE_TO_OPEN_CSV_FILE_S', $filename) . ' (ERRN)';
			}

		// Parse the first row to process field names and indeces
		$field = AttachmentsImport::_parseFieldNames($f);
		if ( !is_array($field) ) {
			return $field;
			}

		// Load the attachents parent manager
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();

		// Process the CSV data
		$num_ok = 0;
		while ( !feof($f) ) {

			// Read the next line
			$adata = fgetcsv($f);

			// Skip blank lines
			if ( !$adata ) {
				continue;
				}

			$parent_type = $adata[$field['parent_type']];
			$parent_entity = strtolower($adata[$field['parent_entity']]);
			$parent_id = (int)$adata[$field['parent_id']];

			// Get the attachment parent object
			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				return JText::sprintf('ERROR_UNKNOWN_PARENT_TYPE_S', $parent_type) . ' (ERRN)';
				}
			$parent = $apm->getAttachmentsPlugin($parent_type);

			// Does the parent exist?
			if ( $verify_parent ) {

				// Make sure a parent with the specified ID exists
				if ( !$parent->parentExists($parent_id, $parent_entity) ) {
					return JText::sprintf('ERROR_UNKNOWN_PARENT_ID_N', $parent_id) . ' (ERRN)';
					}

				// Double-check by comparing the title
				$attachment_parent_title = $adata[$field['parent_title']];
				$parent_title = $parent->getTitle($parent_id, $parent_entity);
				if ( strtolower($parent_title) != strtolower($attachment_parent_title) ) {
					return JText::sprintf('ERROR_PARENT_TITLE_MISMATCH_ID_N_TITLE_S_S', $parent_id,
										  $parent_title, $attachment_parent_title) . ' (ERRN)';
					}
				}

			// Check the creator name
			$creator_id = (int)$adata[$field['created_by']];
			$attachment_creator_name = $adata[$field['created_by_name']];
			if ( $attachment_creator_name == 'Super Administrator' ) {
				// Convert to Joomla 1.6+ equivalent
				$attachment_creator_name = 'Super User';
				}
			$query = $db->getQuery(true);
			$query->select('name')->from('#__users')->where('id = ' . $creator_id);
			$db->setQuery($query, 0, 1);
			$creator_name = $db->loadResult();
			if ( empty($creator_name) OR $db->getErrorNum() ) {
				return JText::sprintf('ERROR_UNABLE_TO_FIND_CREATOR_ID_S',
									  $creator_id, $attachment_creator_name) . ' (ERRN)';
				}
			if ( strtolower($creator_name) != strtolower($attachment_creator_name) ) {
				return JText::sprintf('ERROR_CREATOR_NAME_MISMATCH_ID_S_S',
									  $creator_id, $attachment_creator_name, $creator_name) . ' (ERRN)';
				}

			// Check the modifier name
			$modifier_id = (int)$adata[$field['modified_by']];
			$attachment_modifier_name = $adata[$field['modified_by_name']];
			if ( $attachment_modifier_name == 'Super Administrator' ) {
				// Convert to Joomla 1.6+ equivalent
				$attachment_modifier_name = 'Super User';
				}
			$query = $db->getQuery(true);
			$query->select('name')->from('#__users')->where('id = ' . $modifier_id);
			$db->setQuery($query, 0, 1);
			$modifier_name = $db->loadResult();
			if ( empty($modifier_name) OR $db->getErrorNum() ) {
				return JText::sprintf('ERROR_UNABLE_TO_FIND_MODIFIER_ID_S',
									  $modifier_id, $attachment_modifier_name) . ' (ERRN)';
				}
			if ( strtolower($modifier_name) != strtolower($attachment_modifier_name) ) {
				return JText::sprintf('ERROR_MODIFIER_NAME_MISMATCH_ID_S_S',
									  $modifier_id, $attachment_modifier_name, $modifier_name) . ' (ERRN)';
				}

			$num_ok++;

			// Construct an attachments entry
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_attachments/tables');
			$attachment = JTable::getInstance('Attachment', 'AttachmentsTable');
			

			}

		fclose($f);

		return $num_ok;
	}



	/**
	 * Parse the field names from the first(next) line of the CSV file
	 *
	 * @param file $file the handle for the opened file object
	 *
	 * @return the associative array (fieldname => index) or error message
	 */
	protected static function _parseFieldNames($file)
	{
		// Load the field names from the file
		$field = Array();
		$header_line = fgetcsv($file);
		for ($i=0; $i < count($header_line); $i++) {
			$field_name = strtolower($header_line[$i]);
			if ( in_array($field_name, AttachmentsImport::$field_names) ) {
				$field[$field_name] = $i;
				}
			else {
				return JText::sprintf('ERROR_UNRECOGNIZED_FIELD_S', $field_name) . ' (ERRN)';
				}
			}

		// Make sure all field names were found
		if ( count($field) != count(AttachmentsImport::$field_names) ) {
			// Figure out which fields are missing
			$missing = Array();
			foreach (AttachmentsImport::$field_names as $fname) {
				if (!array_key_exists($fname, $field)) {
					$missing[] = $fname;
					}
				}
			return JText::sprintf('ERROR_MISSING_FIELDS_S', implode(',',$missing)) . ' (ERRN)';
			}

		return $field;
	}

}

