<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2016 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');


/** Load the Attachements defines */
require_once(JPATH_SITE.'/components/com_attachments/defines.php');
require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/importFromCSV.php');


/**
 * A class for importing attachments data from files
 *
 * @package Attachments
 */
class ImportArticles extends ImportFromCSV
{
	public function __construct()
	{
		$required_fields = Array( 'title',
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

		$optional_fields = Array( 'attribs',
								  'mask',
								  'hits',
								  'metadata',
								  'metadesc',
								  'metakey',
								  'title_alias',
								  'version',
								  );

		$field_default = Array( 'language' => '*',
								'state' => 1, /* Published */
								'access' => 1 /* Public */
								);

		$extra_fields = Array( 'category_title',
							   'created_by_name'
							   );

		parent::__construct($required_fields, $optional_fields, $field_default, $extra_fields);
	}


	public function importArticles($filename, $dry_run=false)
	{
		// Open the file
		$open_ok = $self->open($filename);
		if ( $open_ok !== true ) {
			return JError::raiseError(500, $open_ok);
			}

		// Read the data and import the articles
		$num_records = 0;
		while ( $self->readNextRecord() ) {

			// Create the raw record
			$record = object();

			// Copy in the fields from the CSV data
			$this->bind($record);

			// Verify the category
			$cat_ok = $this->_verifyCategory((int)$record->catid,
											 $record->category_title);
			if ( $cat_ok !== true ) {
				return JError::raiseError(500, $cat_ok);
				}

			// Verify the creator
			$creator_ok = $this->_verifyUser((int)$record->created_by,
											 $record->created_by_name);
			if ( $creator_ok !== true ) {
				return JError::raiseError(500, $creator_ok);
				}

			// Save the record
			if ( !$dry_run ) {
				// ???
				}
			$num_records += 1;
			}

		$this->close();
	}

}
