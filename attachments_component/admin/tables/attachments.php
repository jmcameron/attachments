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
 * Table for a single attachment
 *
 * @package Attachments
 */
class TableAttachments extends JTable
{
	 var $id = null;
	 var $filename = null;
	 var $filename_sys = null;
	 var $file_type = null;
	 var $file_size = null;
	 var $url = null;
	 var $uri_type = null;
	 var $url_valid = null;
	 var $display_name = null;
	 var $description = null;
	 var $icon_filename = null;
	 var $uploader_id = null;
	 var $published = null;
	 var $user_field_1 = null;
	 var $user_field_2 = null;
	 var $user_field_3 = null;
	 var $parent_type = null;
	 var $parent_entity = null;
	 var $parent_id = null;
	 var $create_date = null;
	 var $modification_date = null;
	 var $download_count = null;

	 /**
	  * Constructor
	  */
	 function __construct(&$db)
	 {
			 parent::__construct('#__attachments', 'id', $db);
	 }
}
?>
