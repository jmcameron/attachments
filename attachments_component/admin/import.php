<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');


/** Load the Attachements defines */
require_once(JPATH_SITE.'/components/com_attachments/defines.php');

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
			   'created_by_username',
			   'modified',
			   'modified_by',
			   'modified_by_username',
			   'download_count' );

	static $extra_field_names =
		Array( 'parent_title',
			   'created_by_username',
			   'modified_by_username' );


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
	 * @return array of IDs of the imported attachemnts (if $dry_run, number that would have been imported), or error message
	 */
	public static function importAttachmentsFromCSVFile($filename,
														$verify_parent=true,
														$update=false,
														$dry_run=false)
	{
		$db = JFactory::getDBO();

		// Open the CSV file
		$f = @fopen($filename, 'r');
		if ( !$f ) {
			return JText::sprintf('ATTACH_ERROR_UNABLE_TO_OPEN_CSV_FILE_S', $filename) . ' (ERR 29)';
			}

		// Parse the first row to process field names and indeces
		$field = AttachmentsImport::_parseFieldNames($f);
		if ( !is_array($field) ) {
			return $field;
			}

		// Get the default access level from the Attachments options
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');
		$default_access_level = $params->get('default_access_level', AttachmentsDefines::$DEFAULT_ACCESS_LEVEL_ID);

		// Load the attachents parent manager
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();

		// Process the CSV data
		if ( $dry_run ) {
			$ids_ok = 0;
			}
		else {
			$ids_ok = Array();
			}

		while ( !feof($f) ) {

			// Read the next line
			$adata = fgetcsv($f);

			// Skip blank lines
			if ( !$adata ) {
				continue;
				}

			// get the attachment ID
			$attachment_id = $adata[$field['id']];
			if ( !is_numeric($attachment_id) ) {
				return JText::sprintf('ATTACH_ERROR_BAD_ATTACHMENT_ID_S', $attachment_id) . ' (ERR 30)';
				}
			$attachment_id = (int)$attachment_id;

			$parent_type = $adata[$field['parent_type']];
			$parent_entity = strtolower($adata[$field['parent_entity']]);
			$parent_id = (int)$adata[$field['parent_id']];

			// Get the attachment parent object
			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				return JText::sprintf('ATTACH_ERROR_UNKNOWN_PARENT_TYPE_S', $parent_type) . ' (ERR 31)';
				}
			$parent = $apm->getAttachmentsPlugin($parent_type);

			// Does the parent exist?
			if ( $verify_parent ) {

				// Make sure a parent with the specified ID exists
				if ( !$parent->parentExists($parent_id, $parent_entity) ) {
					return JText::sprintf('ATTACH_ERROR_UNKNOWN_PARENT_ID_N', $parent_id) . ' (ERR 32)';
					}

				// Double-check by comparing the title
				$attachment_parent_title = $adata[$field['parent_title']];
				$parent_title = $parent->getTitle($parent_id, $parent_entity);
				if ( strtolower($parent_title) != strtolower($attachment_parent_title) ) {
					return JText::sprintf('ATTACH_ERROR_PARENT_TITLE_MISMATCH_ID_N_TITLE_S_S', $parent_id,
										  $parent_title, $attachment_parent_title) . ' (ERR 33)';
					}
				}

			// Check the creator username
			$creator_id = (int)$adata[$field['created_by']];
			$attachment_creator_username = $adata[$field['created_by_username']];
			$query = $db->getQuery(true);
			$query->select('username')->from('#__users')->where('id = ' . (int)$creator_id);
			$db->setQuery($query, 0, 1);
			$creator_username = $db->loadResult();
			if ( empty($creator_username) || $db->getErrorNum() ) {
				return JText::sprintf('ATTACH_ERROR_UNABLE_TO_FIND_CREATOR_ID_S',
									  $creator_id, $attachment_creator_username) . ' (ERR 34)';
				}
			if ( strtolower($creator_username) != strtolower($attachment_creator_username) ) {
				return JText::sprintf('ATTACH_ERROR_CREATOR_USERNAME_MISMATCH_ID_S_S',
									  $creator_id, $attachment_creator_username, $creator_username) . ' (ERR 35)';
				}

			// Check the modifier name
			$modifier_id = (int)$adata[$field['modified_by']];
			$attachment_modifier_username = $adata[$field['modified_by_username']];
			$query = $db->getQuery(true);
			$query->select('username')->from('#__users')->where('id = ' . (int)$modifier_id);
			$db->setQuery($query, 0, 1);
			$modifier_username = $db->loadResult();
			if ( empty($modifier_username) || $db->getErrorNum() ) {
				return JText::sprintf('ATTACH_ERROR_UNABLE_TO_FIND_MODIFIER_ID_S',
									  $modifier_id, $attachment_modifier_username) . ' (ERR 36)';
				}
			if ( strtolower($modifier_username) != strtolower($attachment_modifier_username) ) {
				return JText::sprintf('ATTACH_ERROR_MODIFIER_USERNAME_MISMATCH_ID_S_S',
									  $modifier_id, $attachment_modifier_username, $modifier_username) . ' (ERR 37)';
				}

			// Construct an attachments entry
			JTable::addIncludePath(JPATH_ADMINISTRATOR.'/components/com_attachments/tables');
			$attachment = JTable::getInstance('Attachment', 'AttachmentsTable');

			if ( $update ) {

				// The attachment ID cannot be 0 for updating!
				if ( $attachment_id == 0 ) {
					return JText::_('ATTACH_ERROR_CANNOT_MODIFY_ATTACHMENT_ZERO_ID') . ' (ERR 38)';
					}

				// Load the data from the attachment to be updated (or create new one)
				if ( !$attachment->load($attachment_id) ) {
					$attachment->reset();
					}
				}
			else {
				// Create new attachment
				$attachment->reset();
				}

			// Copy in the data from the CSV file
			foreach (AttachmentsImport::$field_names as $fname) {
				if ( ($fname != 'id') && !in_array($fname, AttachmentsImport::$extra_field_names) ) {
					$attachment->$fname = $adata[$field[$fname]];
					}
				}

			// Do any necessary overrides
			$attachment->parent_entity = $parent_entity;
			$attachment->access = $default_access_level;
			$attachment->file_size = (int)$adata[$field['file_size']];

			if ( $dry_run ) {
				$ids_ok++;
				}
			else {
				// Store the new/updated attachment
				if ( $attachment->store() ) {
					$ids_ok[] = $attachment->getDbo()->insertid();
					}
				else {
					return JText::sprintf('ATTACH_ERROR_STORING_ATTACHMENT_S', $attachment->getError()) . ' (ERR 39)';
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
	protected static function _parseFieldNames($file)
	{
		// Load the field names from the file
		$field = Array();
		$header_line = fgetcsv($file);
		for ($i=0; $i < count($header_line); $i++) {
			$field_name = trim(strtolower($header_line[$i]));
			if ( in_array($field_name, AttachmentsImport::$field_names) ) {
				$field[$field_name] = $i;
				}
			else {
				return JText::sprintf('ATTACH_ERROR_UNRECOGNIZED_FIELD_S', $field_name) . ' (ERR 40)';
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
			return JText::sprintf('ATTACH_ERROR_MISSING_FIELDS_S', implode(',',$missing)) . ' (ERR 41)';
			}

		return $field;
	}

}

