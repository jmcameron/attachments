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

define('AttachmentsComponentVersion', '3.0');


/**
 * A class for attachments helper functions
 *
 * @package Attachments
 */
class AttachmentsHelper
{
	/**
	 * Install the specified stylesheet (handles caching)
	 * @param string $css_path the URL/path to the CSS file to add to the document
	 */
	function addStyleSheet($css_path, $echo=false)
	{
		static $added_js = false;

		$document =&  JFactory::getDocument();
		$config	  =&  JFactory::getConfig();

		// See if we are caching
		$cache =& JFactory::getCache();
		// ??? $caching = $config->get('cache') OR $cache->_options['caching'];
		$caching = false;

		// Add the style sheet
		if ( $echo ) {
			echo "<link rel=\"stylesheet\" href=\"$css_path\" type=\"text/css\" />\n";
			}
		elseif ( $caching ) {
			// If caching, load the Javascript function that allows dynamic insertion of stylesheets
			if ( !$added_js ) {
	            $uri = JFactory::getURI();
				$js_path = $uri->root(true) . '/plugins/content/attachments/attachments_caching.js';
				echo "<script type=\"text/javascript\" src=\"$js_path\"></script>\n";
				$added_js = true;
				}
			echo "<script type=\"text/javascript\">includeCSS(\"$css_path\");</script>\n";
			}
		else {
			$document->addStyleSheet( $css_path, 'text/css', null, array() );
			}
	}


	/**
	 * Truncate the filename if it is longer than the maxlen
	 * Do this by deleting necessary at the end of the base filename (before the extensions)
	 *
	 * @param string $raw_filename the input filename
	 * @param int $maxlen the maximum allowed length (0 means no limit)
	 *
	 * @return the truncated filename
	 */
	function truncate_filename($raw_filename, $maxlen)
	{
		// Do not truncate if $maxlen is 0 or no truncation is needed
		if ( $maxlen == 0 OR strlen($raw_filename) <= $maxlen ) {
			return $raw_filename;
			}

		$filename_info = pathinfo($raw_filename);
		$basename = $filename_info['basename'];
		$extension = $filename_info['extension'];

		// Construct the filename without extension (since pathinfo doesn't
		// support directly this for PHP pre 5.2.0)
		$filename = JString::substr($basename, 0, strlen($basename) - strlen($extension) - 1);

		if ( JString::strlen($extension) > 0 ) {
			$maxlen = max( $maxlen - (JString::strlen($extension) + 2), 1);
			return JString::substr($filename, 0, $maxlen) . '~.' . $extension;
			}
		else {
			return JString::substr($filename, 0, $maxlen) . '~';
			}
	}


	/**
	 * Truncate the URL if it is longer than the maxlen
	 * Do this by deleting necessary characters from the middle of the URL
	 *
	 * @param string $raw_url the input URL
	 * @param int $maxlen the maximum allowed length (0 means no limit)
	 *
	 * @return the truncated URL
	 */
	function truncate_url($raw_url, $maxlen)
	{
		$url_len = strlen($raw_url);

		// Do not truncate if $maxlen is 0 or no truncation is needed
		if ( $maxlen == 0 OR $url_len <= $maxlen ) {
			return $raw_url;
			}

		$left = (int)(($maxlen-2)/2);
		if ( 2*$left + 1 < $maxlen ) {
			$left++;
			}

		return substr($raw_url, 0, $left) . '&#183;&#183;' .
			substr($raw_url, $url_len - $left + 1);
	}


	/**
	 * Check to see if the given parent ID is valid
	 *
	 * @param int $parent_id The ID to be checked (may be a string)
	 *
	 * @return the parent ID if valid, never returns if not
	 */
	function valid_parent_id($parent_id)
	{
		if ( is_numeric($parent_id) ) {
			$parent_id = (int)$parent_id;
			}
		else {
			$errmsg = JText::_('ERROR_BAD_ARTICLE_ID') . ' (ERR 36)';
			JError::raiseError(500, $errmsg);
			}
		return $parent_id;
	}

	/**
	 * Write an empty 'index.html' file in the specified directory to prevent snooping
	 *
	 * @param string $dir full path of the directory needing an 'index.html' file
	 *
	 * @return true if the file was succesfully written
	 */
	function write_empty_index_html($dir)
	{
		jimport('joomla.filesystem.file');

		$index_fname = $dir.DS.'index.html';
		if ( JFile::exists($index_fname) ) {
			return true;
			}
		$contents = "<html><body><br /><h2 align=\"center\">Access denied.</h2></body></html>";
		JFile::write($index_fname, $contents);

		return JFile::exists($index_fname);
	}


	/**
	 * Check the directory corresponding to this path.	If it is empty, delete it.
	 * (Assume anything with a trailing DS is a directory)
	 *
	 * @param string $filename path of the file to have its containing directory cleaned.
	 */
	function clean_directory($filename)
	{
		jimport('joomla.filesystem.folder');

		if ( $filename[strlen($filename)-1] == DS ) {

			// Assume anything with a trailing DS is a directory
			if ( !JFolder::exists($filename) ) {
				return;
				}

			$dirname = $filename;
			}

		else {
			// This might be a file or directory

			if ( JFolder::exists($filename) ) {
				$dirname = $filename;
				}
			else {
				// Get the directory name
				$filename_info = pathinfo($filename);
				$dirname = $filename_info['dirname'] . DS;
				}
			}

		// If the directory does not exist, quitely ignore the request
		if ( !JFolder::exists($dirname) ) {
			return;
			}

		// If the directory is the top-level attachments directory, ignore the request
		// (This can occur when upgrading pre-2.0 attachments (with prefixes) since
		// they were all saved in the top-level directory.)
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');
		$upload_subdir = $params->get('attachments_subdir', 'attachments');
		$upload_dir = JPATH_SITE.DS.$upload_subdir;
		if ( realpath(rtrim($upload_dir,DS)) == realpath(rtrim($dirname,DS)) ) {
			return;
			}

		// See how many files exist in the directory
		$files = JFolder::files($dirname);

		// If there are no files left (or only the index.html file is left), delete the directory
		if ( (count($files) == 0) OR ( (count($files) == 1) AND ($files[0] == 'index.html') ) ) {
			JFolder::delete($dirname);
			}
	}


	/**
	 * Return a list of the currently active attachments upload directories
	 *
	 * If attachments exist when someone switches the upload directory, they
	 * old attachments still work and live in thier own directory.	When
	 * secure mode changes, all of these directories must be updated.
	 * Therefore it is necessary to scan all attachments and find all upload
	 * directories.	 For most users, this will only return the current upload
	 * directory.
	 *
	 * @return an array of known upload directories
	 */
	function get_upload_directories()
	{
		$dirs = Array();
		$dirs[] = 'attachments';   // Always check the canonical directory, if it exists

		// First get the currently configured upload directory
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');
		$upload_dir = $params->get('attachments_subdir', 'attachments');
		if ( !in_array($upload_dir, $dirs) ) {
			$dirs[] = $upload_dir;
			}

		// Get the known content entities (for filename paths)
		$entities = Array();
		JPluginHelper::importPlugin('attachments');
		$apm =& getAttachmentsPluginManager();
		$parent_types = $apm->getInstalledParentTypes();
		foreach ($parent_types as $parent_type) {
			$parent = $apm->getAttachmentsPlugin($parent_type);
			foreach ( $parent->getEntities() as $raw_entity ) {
				$entity = $parent->getCanonicalEntity($raw_entity);
				if ( $entity == 'default' ) {
					$entity = $parent->getDefaultEntity();
					}
				$entities[] = $entity;
				}
			}

		// Get the full filenames
		$db =& JFactory::getDBO();
		$query = "SELECT filename_sys FROM #__attachments WHERE uri_type='file'";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		foreach ($rows as $row) {
			$file = str_replace(JPATH_SITE.DS, '', $row->filename_sys);
			if ( $file == '' ) {
				// Skip empty filenames
				continue;
				}
			$parts = explode(DS, $file);

			// Git rid of the filename part
			array_pop($parts);

			// Pop off the id numer, if present
			if ( is_numeric(end($parts)) ) {
				array_pop($parts);
				}

			// Pop off the entity part, if present
			if ( in_array(end($parts), $entities) ) {
				array_pop($parts);
				}

			// Add the directory, if not already added
			$dir = implode(DS, $parts);
			if ( !in_array($dir, $dirs) ) {
				$dirs[] = $dir;
				}
			}

		return $dirs;
	}

	/**
	 * Set up the upload directory
	 *
	 * @param string $upload_dir the directory to be set up
	 * @param bool $secure true if the directory should be set up for secure mode (with the necessary .htaccess file)
	 *
	 * @return true if succesful
	 */
	function setup_upload_directory($upload_dir, $secure)
	{
		$subdir_ok = false;

		// Do not allow the main site directory to be set up as the upload directory
		if ( ( realpath(rtrim($upload_dir,DS)) == realpath(JPATH_SITE) ) OR
			 ( realpath(rtrim($upload_dir,DS)) == realpath(JPATH_ADMINISTRATOR) ) ) {
			$errmsg = JText::sprintf('ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 92)';
			JError::raiseError(500, $errmsg);
			}

		// Create the subdirectory (if necessary)
		jimport( 'joomla.filesystem.folder' );
		if ( JFolder::exists( $upload_dir ) ) {
			$subdir_ok = true;
			}
		else {
			if ( JFolder::create( $upload_dir )) {
				// ??? Change to 2775 if files are owned by you but webserver runs as group
				// ??? SHOULD THE PERMISSION BE AN OPTION?
				chmod($upload_dir, 0775);
				$subdir_ok = true;
				}
			}

		if ( !$subdir_ok OR !JFolder::exists($upload_dir) ) {
			$errmsg = JText::sprintf('ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 37)';
			JError::raiseError(500, $errmsg);
			}

		// Add a simple index.html file to the upload directory to prevent browsing
		$index_ok = false;
		$index_fname = $upload_dir.DS.'index.html';
		if ( !AttachmentsHelper::write_empty_index_html($upload_dir) ) {
			$errmsg = JText::sprintf('ERROR_ADDING_INDEX_HTML_IN_S', $upload_dir) . ' (ERR 38)';
			JError::raiseError(500, $errmsg);
			}

		// If this is secure, create the .htindex file, if necessary
		$hta_fname = $upload_dir.DS.'.htaccess';
		jimport('joomla.filesystem.file');
		if ( $secure ) {
			$hta_ok = false;

			JFile::write($hta_fname, "order deny,allow\ndeny from all\n");
			if ( JFile::exists($hta_fname) ) {
				$hta_ok = true;
				}
			if ( ! $hta_ok ) {
				$errmsg = JText::sprintf('ERROR_ADDING_HTACCESS_S', $upload_dir) . ' (ERR 39)';
				JError::raiseError(500, $errmsg);
				}
			}
		else {
			if ( JFile::exists( $hta_fname ) ) {
				// If the htaccess file exists, delete it so normal access can occur
				JFile::delete($hta_fname);
				}
			}

		return true;
	}


	/**
	 * Add the save/upload/update urls to the view
	 *
	 * @param &object &$view the view to add the urls to
	 * @param string $save_type type of save ('file' or 'url')
	 * @param int $parent_id id for the parent
	 $ @param string $parent_type type of parent (eg, com_content)
	 * @param int $attachment_id id for the attachment
	 * @param string $from the from ($option) value
	 */
	function add_view_urls(&$view, $save_type, $parent_id, $parent_type, $attachment_id, $from)
	{
		// Construct the url to save the form
		$url_base = "index.php?option=com_attachments";

		$template = '&tmpl=component';
		$save_task = 'save';
		$upload_task = 'upload';
		$update_task = 'update';
		$idinfo = "&id=$attachment_id";
		$parentinfo = '';
		if ( $parent_id ) {
			$parentinfo = "&parent_id=$parent_id&parent_type=$parent_type";
			}
		
		$app = JFactory::getApplication();
		if ( $app->isAdmin() ) {
			$upload_task = 'add';
			$update_task = 'edit';
			if ( $save_type == 'upload' ) {
				$save_task = 'saveNew';
				}
			$idinfo = "&cid[]=$attachment_id";
			$template = '';
			}

		// Handle the main save URL
		$save_url = $url_base . "&task=" . $save_task . $template;
		if ( $from == 'closeme') {
			// Keep track of what are supposed to do after saving
			$save_url .= "&from=closeme";
			}
		$view->assign('save_url', JRoute::_($save_url));

		// Construct the URL to upload a URL instead of a file
		if ( $save_type == 'upload' ) {
			$upload_file_url = $url_base . "&task=$upload_task&uri=file" . $parentinfo . $template;
			$upload_url_url	 = $url_base . "&task=$upload_task&uri=url" . $parentinfo . $template;

			// Keep track of what are supposed to do after saving
			if ( $from == 'closeme') {
				$upload_file_url .= "&from=closeme";
				$upload_url_url .= "&from=closeme";
				}

			// Add the URL
			$view->assign('upload_file_url', JRoute::_($upload_file_url));
			$view->assign('upload_url_url', JRoute::_($upload_url_url));
			}

		elseif ( $save_type == 'update' ) {
			$change_url = $url_base . "&task=$update_task" . $idinfo;
			$change_file_url =	$change_url . "&amp;update=file" . $template;
			$change_url_url =  $change_url . "&amp;update=url" . $template;
			$normal_update_url =  $change_url . $template;

			// Keep track of what are supposed to do after saving
			if ( $from == 'closeme') {
				$change_file_url .= "&from=closeme";
				$change_url_url .= "&from=closeme";
				$normal_update_url .= "&from=closeme";
				}

			// Add the URLs
			$view->assign('change_file_url',   JRoute::_($change_file_url));
			$view->assign('change_url_url',	   JRoute::_($change_url_url));
			$view->assign('normal_update_url', JRoute::_($normal_update_url));
			}
	}


	/**
	 * Upload the file
	 *
	 * @param &object &$row the partially constructed attachment object
	 * @param &object &$parent An object with partial parent info including:
	 *		$parent->new : True if the parent has not been created yet
	 *						(like adding attachments to an article before it has been saved)
	 *		$parent->title : Title/name of the parent object
	 * @param int $attachment_id false if this is a new attachment
	 * @param string $save_type 'update' or 'update'
	 *
	 * @return a message indicating succes or failure
	 *
	 * NOTE: The caller should set up all the parent info in the record before calling this
	 *		 (see $parent->* below for necessary items)
	 */
	function upload_file(&$row, &$parent, $attachment_id=false, $save_type='update')
	{
		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Get the auto-publish setting
		$auto_publish = $params->get('publish_default', false);

		// Make sure the attachments directory exists
		$upload_subdir = $params->get('attachments_subdir', 'attachments');
		if ( $upload_subdir == '' ) {
			$upload_subdir = 'attachments';
			}
		$upload_dir = JPATH_SITE.DS.$upload_subdir;
		$secure = $params->get('secure', false);
		if ( !AttachmentsHelper::setup_upload_directory( $upload_dir, $secure ) ) {
			$errmsg = JText::sprintf('ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 40)';
			JError::raiseError(500, $errmsg);
			}

		// If we are updating, note the name of the old filename
		$old_filename = null;
		$old_filename_sys = null;
		$old_uri_type = $row->uri_type;
		if ( $old_uri_type ) {
			$old_filename = $row->filename;
			$old_filename_sys = $row->filename_sys;
			}

		// Get the new filename
		//	 (Note: The following replacement is necessary to allow
		//			single quotes in filenames to work correctly.)
		$filename = JString::str_ireplace("\'", "'", $_FILES['upload']['name']);
		$ftype = $_FILES['upload']['type'];

		$from = JRequest::getWord('from');

		// Set up the entity name for display
		$parent->loadLanguage();
		$parent_entity = $parent->getCanonicalEntity($row->parent_entity);
		$parent_entity_name = JText::_($parent->getEntityName($parent_entity));

		// A little formatting
		$msgbreak = '<br />';
		$app = JFactory::getApplication();
		if ( $app->isAdmin() ) {
			$msgbreak = '';
			}

		// Make sure a file was successfully uploaded
		if ( ($_FILES['upload']['size'] == 0) AND
			 ($_FILES['upload']['tmp_name'] == '') ) {

			// Guess the type of error
			if ( $filename == '' ) {
				$error = 'no_file';
				$error_msg = JText::sprintf('ERROR_UPLOADING_FILE_S', $filename);
				$error_msg .= $msgbreak . ' (' . JText::_('YOU_MUST_SELECT_A_FILE_TO_UPLOAD') . ')';
				if ( $app->isAdmin() ) {
					$result = new JObject();
					$result->error = true;
					$result->error_msg = $error_msg;
					return $result;
					}
				}
			else {
				$error = 'file_too_big';
				$error_msg = JText::sprintf('ERROR_UPLOADING_FILE_S', $filename);
				$error_msg .= $msgbreak . '(' . JText::_('ERROR_MAY_BE_LARGER_THAN_LIMIT') . ' ';
				$error_msg .= get_cfg_var('upload_max_filesize') . ')';
				if ( $app->isAdmin() ) {
					$result = new JObject();
					$result->error = true;
					$result->error_msg = $error_msg;
					return $result;
					}
				}

			// Set up the view to redisplay the form with warnings
			if ( $save_type == 'update' ) {
				require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'update'.DS.'view.php');
				$view = new AttachmentsViewUpdate();
				$view->assign('update', JRequest::getWord('update'));

				// Set up the selection lists
				$lists = array();
				$lists['url_valid'] = JHTML::_('select.booleanlist', 'url_valid',
											   'class="inputbox" title="' . JText::_('URL_IS_VALID_TOOLTIP') . '"',
											   $row->url_valid);
				$view->assignRef('lists', $lists);
				$view->assignRef('attachment', $row);

				AttachmentsHelper::add_view_urls($view, 'update', $row->parent_id, $row->parent_type,
												 $attachment_id, $from);
				}
			else {
				require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'upload'.DS.'view.php');
				$view = new AttachmentsViewUpload();
				AttachmentsHelper::add_view_urls($view, 'upload', $row->parent_id, $row->parent_type,
												 $attachment_id, null, $from);

				$view->assignRef('uri_type',		 $row->uri_type);
				$view->assignRef('url',				 $row->url);
				$view->assign(	 'parent_id',		 $row->parent_id);
				$view->assignRef('parent_type',		 $row->parent_type);
				$view->assignRef('description',		 $row->description);
				$view->assignRef('user_field_1',	 $row->user_field_1);
				$view->assignRef('user_field_2',	 $row->user_field_2);
				$view->assignRef('user_field_3',	 $row->user_field_3);
				}

			// Suppress the display filename if we are changing from file to url
			$display_name = $row->display_name;
			if ( $save_type == 'update' ) {
				$new_uri_type = JRequest::getWord('update');
				if ( $new_uri_type AND (($new_uri_type == 'file') OR ($new_uri_type != $row->uri_type)) ) {
					$display_name = '';
					}
				}

			// Set up the view
			$view->assignRef('parent_entity',	 $row->parent_entity);
			$view->assignref('parent_entity_name', $parent_entity_name);
			$view->assignRef('parent_title',	 $parent->title);
			$view->assign(	 'new_parent',		 $parent->new);

			$view->assignRef('display_name',	 $display_name);

			$view->assignRef('params', $params);

			$view->assign(	 'from',			 $from);
			$view->assign(	 'Itemid', JRequest::getInt('Itemid', 1));

			// Display the view
			$view->display(null, $error, $error_msg);
			exit();
			}

		// Make sure the file type is okay (respect restrictions imposed by media manager)
		$cmparams =& JComponentHelper::getParams( 'com_media' );

		// First check to make sure the extension is allowed
		jimport('joomla.filesystem.file');
		$allowable = explode( ',', $cmparams->get( 'upload_extensions' ));
		$ignored = explode(',', $cmparams->get( 'ignore_extensions' ));
		$format = JString::strtolower(JFile::getExt($filename));
		$error = false;
		$error_msg = false;
		if (!in_array($format, $allowable) AND !in_array($format,$ignored)) {
			$error = 'illegal_file_extension';
			$error_msg = JText::sprintf('ERROR_UPLOADING_FILE_S', $filename);
			$error_msg .= "<br />" . JText::_('ERROR_ILLEGAL_FILE_EXTENSION') . " $format";
			$error_msg .= "<br />" . JText::_('ERROR_CHANGE_IN_MEDIA_MANAGER');
			}

		// Check to make sure the mime type is okay
		if ( $cmparams->get('restrict_uploads',true) ) {
			if ( $cmparams->get('check_mime', true) ) {
				$allowed_mime = explode(',', $cmparams->get('upload_mime'));
				$illegal_mime = explode(',', $cmparams->get('upload_mime_illegal'));
				if( JString::strlen($ftype) AND !in_array($ftype, $allowed_mime) AND
					in_array($ftype, $illegal_mime)) {
					$error = 'illegal_mime_type';
					$error_msg = JText::sprintf('ERROR_UPLOADING_FILE_S', $filename);
					$error_msg .= ', ' . JText::_('ERROR_ILLEGAL_FILE_MIME_TYPE') . " $ftype";
					$error_msg .= "	 <br />" . JText::_('ERROR_CHANGE_IN_MEDIA_MANAGER');
					}
				}
			}

		// If there was an error, refresh the form with a warning
		if ( $error ) {

			if ( $app->isAdmin() ) {
				$result = new JObject();
				$result->error = true;
				$result->error_msg = $error_msg;
				return $result;
				}

			// Set up the view to redisplay the form with warnings
			if ( $save_type == 'update' ) {
				require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'update'.DS.'view.php');
				$view = new AttachmentsViewUpdate();
				$view->assign('update', JRequest::getWord('update'));

				// Set up the selection lists
				$lists = array();
				$lists['url_valid'] = JHTML::_('select.booleanlist', 'url_valid',
											   'class="inputbox" title="' . JText::_('URL_IS_VALID_TOOLTIP') . '"',
											   $row->url_valid);
				$view->assignRef('lists', $lists);
				$view->assignRef('attachment', $row);

				AttachmentsHelper::add_view_urls($view, 'update', $row->parent_id, $row->parent_type,
												 $attachment_id, $from);
				}
			else {
				require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'upload'.DS.'view.php');
				$view = new AttachmentsViewUpload();
				AttachmentsHelper::add_view_urls($view, 'upload', $row->parent_id, $row->parent_type, null, $from);

				$view->assignRef('uri_type',		 $row->uri_type);
				$view->assignRef('url',				 $row->url);
				$view->assign(	 'parent_id',		 $row->parent_id);
				$view->assignRef('parent_type',		 $row->parent_type);
				$view->assignRef('description',		 $row->description);
				$view->assignRef('user_field_1',	 $row->user_field_1);
				$view->assignRef('user_field_2',	 $row->user_field_2);
				$view->assignRef('user_field_3',	 $row->user_field_3);
				}

			// Suppress the display filename if we are changing from file to url
			$display_name = $row->display_name;
			if ( $save_type == 'update' ) {
				$new_uri_type = JRequest::getWord('update');
				if ( $new_uri_type AND (($new_uri_type == 'file') OR ($new_uri_type != $row->uri_type)) ) {
					$display_name = '';
					}
				}

			// Set up the view
			$view->assignRef('parent_entity',	 $row->parent_entity);
			$view->assignref('parent_entity_name', $parent_entity_name);
			$view->assignRef('parent_title',	 $parent->title);
			$view->assign(	 'new_parent',		 $parent->new);

			$view->assignRef('display_name',	 $display_name);

			$view->assignRef('params',			 $params);

			$view->assign('from',			 $from);
			$view->assign('Itemid',			 JRequest::getInt('Itemid', 1));

			// Display the view
			$view->display(null, $error, $error_msg);
			exit();
			}

		// Define where the attachments go
		$upload_url = $params->get('attachments_subdir', 'attachments');
		$upload_dir = JPATH_SITE . DS . $upload_url;

		// Figure out the system filename
		$path = $parent->getAttachmentPath($row->parent_entity,
										   $row->parent_id, null);
		$fullpath = $upload_dir . DS . $path;

		// Make sure the directory exists
		if ( !JFile::exists($fullpath) ) {
			jimport( 'joomla.filesystem.folder' );
			if ( !JFolder::create($fullpath) ) {
				$errmsg = JText::sprintf('ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 41)';
				JError::raiseError(500, $errmsg);
				}
			AttachmentsHelper::write_empty_index_html($fullpath);
			}

		// Get ready to save the file
		$filename_sys = $fullpath . $filename;
		
		// ??? $url = JString::str_ireplace(DS, '/', $upload_url . '/' . $path . $filename);
		// BROKEN: $url = $upload_url . '/' . $path . JString::str_ireplace(DS, '/', $filename);
		$url = $upload_url . '/' . $path . $filename;

		// If not updating, make sure the system filename doesn't already exist
		$error = false;
		if ( $save_type == 'upload' AND JFile::exists($filename_sys) ) {
			$error = 'file_already_on_server';
			$error_msg = JText::sprintf('ERROR_FILE_S_ALREADY_ON_SERVER', $filename_sys);

			if ( $app->isAdmin() ) {
				$result = new JObject();
				$result->error = true;
				$result->error_msg = $error_msg;
				return $result;
				}

			$save_url = JRoute::_("index.php?option=com_attachments&task=save&tmpl=component");

			// Set up the view to redisplay the form with warnings
			require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'upload'.DS.'view.php');
			$view = new AttachmentsViewUpload();
			AttachmentsHelper::add_view_urls($view, 'upload', $row->parent_id, $row->parent_type, null, $from);

			// Set up the view
			$view->assignRef('uri_type',		 $row->uri_type);
			$view->assignRef('url',				 $row->url);
			$view->assign(	 'parent_id',		 $row->parent_id);
			$view->assignRef('parent_type',		 $row->parent_type);
			$view->assignRef('parent_entity',	 $row->parent_entity);
			$view->assignref('parent_entity_name', $parent_entity_name);
			$view->assignRef('parent_title',	 $parent->title);
			$view->assign(	 'new_parent',		 $parent->new);
			$view->assignRef('description',		 $row->description);
			$view->assignRef('display_name',	 $row->display_name);
			$view->assignRef('user_field_1',	 $row->user_field_1);
			$view->assignRef('user_field_2',	 $row->user_field_2);
			$view->assignRef('user_field_3',	 $row->user_field_3);
			$view->assignRef('from',			 $from);
			$view->assign(	 'Itemid',			 JRequest::getInt('Itemid', 1));
			$view->assignRef('params',			 $params);

			// Display the view
			$view->display(null, $error, $error_msg);
			exit();
			}

		// Get the maximum allowed filename length (for the filename display)
		$max_filename_length =$params->get('max_filename_length', 0);
		if ( is_numeric($max_filename_length) ) {
			$max_filename_length = (int)$max_filename_length;
			}
		else {
			$max_filename_length = 0;
			}

		// See of the display name needs to change
		if ( $row->display_name AND ($save_type == 'update') AND ( $filename != $old_filename ) ) {
			$row->display_name = '';
			}

		// Create a display filename, if needed (for long filenames)
		if ( ($max_filename_length > 0) and
			 ( JString::strlen($row->display_name) == 0 ) and
			 ( JString::strlen($filename) > $max_filename_length ) ) {
			$row->display_name = AttachmentsHelper::truncate_filename($filename,
																	  $max_filename_length);
			}

		// Copy the info about the uploaded file into the new record
		$row->uri_type = 'file';
		$row->filename = $filename;
		$row->filename_sys = $filename_sys;
		$row->url = $url;
		$row->file_type = $ftype;
		$row->file_size = $_FILES['upload']['size'];
		$row->state = $auto_publish;

		// Set the create/modify dates
		jimport('joomla.utilities.date');
		$now = new JDate();
		$row->create_date = $now->toMySQL();
		$row->modification_date = $row->create_date;

		// Add the icon file type
		require_once(JPATH_COMPONENT_SITE.DS.'file_types.php');
		$row->icon_filename = AttachmentsFileTypes::icon_filename($filename, $ftype);

		// Set up the parent entity to save
		$row->parent_entity = $parent->getEntityname( $row->parent_entity );

		// Save the updated attachment
		if (!$row->store()) {
			$errmsg = JText::_('ERROR_SAVING_FILE_ATTACHMENT_RECORD') . $row->getError() . ' (ERR 42)';
			JError::raiseError(500, $errmsg);
			}

		// Get the attachment id
		$db =& JFactory::getDBO();
		// If we're updating we may not get an insertid, so don't blindly overwrite the old
		// attachment_id just in case (Thanks to Franz-Xaver Geiger for a bug fix on this)
		$new_attachment_id = $db->insertid();
		if ( !empty($new_attachment_id) ) {
			$attachment_id = (int)$new_attachment_id;
			}

		// Move the file
		$msg = "";
		if (JFile::upload($_FILES['upload']['tmp_name'], $filename_sys)) {
			$size = (int)( $row->file_size / 1024.0 );
			chmod($filename_sys, 0644);
			if ( $save_type == 'update' )
				$msg = JText::_('UPDATED_ATTACHMENT') . ' ' . $filename . " (" . $size . " Kb)!";
			else
				$msg = JText::_('UPLOADED_ATTACHMENT') . ' ' . $filename . " (" . $size . " Kb)!";
			}
		else {
			$query ="DELETE FROM #__attachments WHERE id='".(int)$attachment_id."'";
			$db->setQuery($query);
			$result = $db->query();
			$msg = JText::_('ERROR_MOVING_FILE')
				. " {$_FILES['upload']['tmp_name']} -> {$filename_sys})";
			}

		// If we are updating, we may need to delete the old file
		if ( $old_uri_type ) {
			if ( $filename_sys != $old_filename_sys AND	 JFile::exists($old_filename_sys) ) {
				JFile::delete($old_filename_sys);
				AttachmentsHelper::clean_directory($old_filename_sys);
				}
			}

		return $msg;
	}



	/**
	 * Parse the url into parts
	 *
	 * @param &string &$raw_url the raw url to parse
	 * @param bool $relative_url allow relative URLs
	 *
	 * @return an object (if successful) with the parts as attributes (or a error string in case of error)
	 */
	function parse_url(&$raw_url, $relative_url)
	{
		// Set up the return object
		$result = new JObject();
		$result->error = false;
		$result->relative = $relative_url;

		// Handle relative URLs
		$url = $raw_url;
		if ( $relative_url ) {
	        $uri = JFactory::getURI();
			$url = $uri->base(true) . "/" . $raw_url;
			}

		// Thanks to http://www.roscripts.com/PHP_regular_expressions_examples-136.html
		// For parts of the URL regular expression here

		if ( preg_match('^(?P<protocol>\b[A-Z]+\b://)?'
						. '(?P<domain>[-A-Z0-9\.]+)?'
						. ':?(?P<port>[0-9]*)'
						. '(?P<path>/[-A-Z0-9+&@#/%=~_|!:,.;]*)'
						. '?(?P<parameters>\?[-A-Z0-9+&@#/%=~_|!:,.;]*)?^i',
						$url, $match) ) {

			// Get the protocol (if any)
			$protocol = '';
			if ( isset($match['protocol']) AND $match['protocol'] ) {
				$protocol = JString::rtrim($match['protocol'], '/:');
				}

			// Get the domain (if any)
			$domain = '';
			if ( isset($match['domain']) AND $match['domain'] ) {
				$domain = $match['domain'];
				}

			// Figure out the port
			$port = null;
			if ( $protocol == 'http' ) {
				$port = 80;
				}
			elseif ( $protocol == 'https' ) {
				$ports = 443;
				}
			elseif ( $protocol == 'ftp' ) {
				$ports = 21;
				}
			elseif ( $protocol == '' ) {
				$port = 80;
				}
			else {
				// Unrecognized protocol
				$result->error = true;
				$result->error_code = 'url_unknown_protocol';
				$result->error_msg =
					JText::sprintf('ERROR_UNKNOWN_PROTCOL_S_IN_URL_S', $protocol, $raw_url);
				return $result;
				}
			// Override the port if specified
			if ( isset($match['port']) AND $match['port'] ) {
				$port = (int)$match['port'];
				}
			// Default to HTTP if protocol/port is missing
			if ( !$port ) {
				$port = 80;
				}

			// Get the path and reconstruct the full path
			if ( isset($match['path']) AND $match['path'] ) {
				$path = $match['path'];
				}
			else {
				$path = '/';
				}

			// Get the parameters (if any)
			if ( isset($match['parameters']) AND $match['parameters'] ) {
				$parameters = $match['parameters'];
				}
			else {
				$parameters = '';
				}

			// Handle relative URLs (or missing info)
			if ( $relative_url ) {
				// Do nothing
				}
			else {
				// If it is not a relative URL, make sure we have a protocl and domain
				if ( $protocol == '' ) {
					$protocol = 'http';
					}
				if ( $domain == '' ) {
					// Reject bad url syntax
					$result->error = true;
					$result->error_code = 'url_no_domain';
					$result->error_msg = JText::sprintf('ERROR_IN_URL_SYNTAX_S', $raw_url);
					}
				}

			// Save the information
			$result->protocol = $protocol;
			$result->domain = $domain;
			$result->port = $port;
			$result->path = JString::str_ireplace('//', '/', $path);
			$result->params = $parameters;
			$result->url = JString::str_ireplace('//', '/', $path . $result->params);
			}
		else {
			// Reject bad url syntax
			$result->error = true;
			$result->error_code = 'url_bad_syntax';
			$result->error_msg = JText::sprintf('ERROR_IN_URL_SYNTAX_S', $raw_url);
			}

		return $result;
	}


	/**
	 * Get the info about this URL
	 *
	 * @param string $raw_url the raw url to parse
	 * @param &object &$row the attachment object
	 * @param bool $verify whether the existance of the URL should be checked
	 * @param bool $relative_url allow relative URLs
	 *
	 * @return true if the URL is okay, or an error object if not
	 */
	function get_url_info($raw_url, &$row, $verify, $relative_url)
	{
		// Check the URL for existence
		// * Get 'size' (null if the there were errors accessing the link,
		//		or 0 if the URL loaded but had None/Null/0 for length
		// * Get 'file_type'
		// * Get 'filename' (for display)
		//
		// * Rename all occurances of 'display_name' to 'display_name'

		$u = AttachmentsHelper::parse_url($raw_url, $relative_url);

		// Deal with parsing errors
		if ( $u->error ) {
			return $u;
			}

		// Set up defaults for what we want to know
		$filename = basename($u->path);
		$file_size = 0;
		$mime_type = '';
		$found = false;

		// Set the defaults
		$row->filename = JString::trim($filename);
		$row->file_size = $file_size;
		$row->url_valid = false;

		// Get parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');
		$overlay = $params->get('superimpose_url_link_icons', true);

		// Get the timeout
		$timeout = $params->get('link_check_timeout', 10);
		if ( is_numeric($timeout) ) {
			$timeout = (int)$timeout;
			}
		else {
			$timeout = 10;
			}

		// Check the URL to see if it is valid
		$errstr = null;
		$fp = false;

		$app = JFactory::getApplication();

		if ( $timeout > 0 ) {
			if (version_compare(PHP_VERSION, '5.0.0') >= 0) {
				require_once(JPATH_COMPONENT_SITE.DS.'fsockopen5.php');
				$fp = fsockopen_protected($u, $errno, $errstr, $timeout, $verify);
				if ( $u->error ) {
					$error_msg = JText::sprintf('ERROR_CHECKING_URL_S', $raw_url);
					$error_msg .= ' <br />(' . $u->err_msg . ' <br />' . $errstr . ')';
					if ( $app->isAdmin() ) {
						$result = new JObject();
						$result->error = true;
						$result->error_msg = $error_msg;
						return $result;
						}
					$u->error_msg = $error_msg;
					return $u;
					}
				}
			else {
				$fp = fsockopen($u->domain, $u->port, $errno, $errstr, $timeout);
				}
			}

		// Check the URL to get the size, etc
		if ($fp) {
			$request = "HEAD {$u->url} HTTP/1.1\nHOST: {$u->domain}\nConnection: close\n\n";
			fputs($fp, $request);
			while ( !feof($fp) ) {

				$http_response = fgets($fp, 128);

				// Check to see if it was found
				if ( preg_match("|^HTTP/1\.\d [0-9]+ ([^$]+)$|m",
								$http_response, $match) ) {
					if ( trim($match[1]) == 'OK' ) {
						$found = true;
						}
					}

				// Check for length
				if( preg_match("/Content\-Length: (\d+)/i",
							   $http_response, $match ) ) {
					$file_size = (int)$match[1];
					}

				// Check for content type
				if( preg_match("/Content\-Type: ([^;$]+)/i",
							   $http_response, $match ) ) {
					$mime_type = trim($match[1]);
					}

				}
			fclose($fp);

			// Return error if it was not found (timed out, etc)
			if ( !$found AND $verify ) {
				$u->error = true;
				$u->error_code = 'url_not_found';
				$u->error_msg = JText::sprintf('ERROR_COULD_NOT_ACCESS_URL_S', $raw_url);
				return $u;
				}
			}
		else {
			if ( $verify AND $timeout > 0 ) {
				// Error connecting
				$u->error = true;
				$u->error_code = 'url_error_connecting';
				$error_msg = JText::sprintf('ERROR_CONNECTING_TO_URL_S', $raw_url)
					. "<br /> (" . $errstr . ")";
				$u->error_msg = $error_msg;
				return $u;
				}
			if ( $timeout == 0 ) {
				// Pretend it was found
				$found = true;
				if ( $overlay ) {
					$mime_type = 'link/generic';
					}
				else {
					$mime_type = 'link/unknown';
					}
				}
			}


		// Update the record
		$row->filename = JString::trim($filename);
		$row->file_size = $file_size;
		$row->url_valid = $found;

		// Deal with the file type
		if ( !$mime_type ) {
			require_once(JPATH_COMPONENT_SITE.DS.'file_types.php');
			$mime_type = AttachmentsFileTypes::mime_type($filename);
			}
		if ( $mime_type ) {
			$row->file_type = JString::trim($mime_type);
			}
		else {
			if ( $overlay ) {
				$mime_type = 'link/generic';
				$row->file_type = 'link/generic';
				}
			else {
				$mime_type = 'link/unknown';
				$row->file_type = 'link/unknown';
				}
			}

		// See if we can figure out the icon
		require_once(JPATH_COMPONENT_SITE.DS.'file_types.php');
		$icon_filename = AttachmentsFileTypes::icon_filename($filename, $mime_type);
		if ( $icon_filename ) {
			$row->icon_filename = AttachmentsFileTypes::icon_filename($filename, $mime_type);
			}
		else {
			if ( $mime_type == 'link/unknown' ) {
				$row->icon_filename = 'link.gif';
				}
			elseif ( $mime_type == 'link/broken' ) {
				$row->icon_filename = 'link_broken.gif';
				}
			else {
				$row->icon_filename = 'link.gif';
				}
			}

		return true;
	}


	/**
	 * Add the infomation about the URL to the attaachment record and then save it
	 *
	 * @param &object &$row the attachment object
	 * @param &object &$parent the attachments parent object
	 * @param bool $verify whether the existance of the URL should be checked
	 * @param bool $relative_url allow relative URLs
	 * @param string $update the type of update (or false if it is not an update)
	 * @param int $attachment_id the attachment ID, false if this is a new attachment
	 *
	 * @return an error message if there is a problem
	 */
	function add_url(&$row, &$parent, $verify, $relative_url=false,
					 $update=false, $attachment_id=false)
	{
		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Get the auto-publish setting
		$auto_publish = $params->get('publish_default', false);

		// If we are updating, note the name of the old filename (if there was one)
		// (Needed for switching from a file to a URL)
		$old_filename = null;
		$old_filename_sys = null;
		if ( $update ) {
			if ( $row->filename_sys ) {
				$old_filename = $row->filename;
				$old_filename_sys = $row->filename_sys;
				}
			}

		// Set up the entity name for display
		$parent->loadLanguage();
		$parent_entity = $parent->getCanonicalEntity($row->parent_entity);
		$parent_entity_name = JText::_($parent->getEntityName($parent_entity));

		// Check to make sure the URL is valid
		$from = JRequest::getWord('from');

		// Get the info from the url
		$result = AttachmentsHelper::get_url_info($row->url, $row, $verify, $relative_url);

		// If there was an error, bow out
		$app = JFactory::getApplication();
		if ( $result !== true ) {

			if ( $app->isAdmin() ) {
				return $result;
				}

			$update_form = JRequest::getWord('update');

			// Redisplay the upload/update form with complaints
			if ( $update ) {
				require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'update'.DS.'view.php');
				$view = new AttachmentsViewUpdate();
				$view->assign('update', $update_form);

				// Set up the selection lists
				$lists = array();
				$lists['url_valid'] = JHTML::_('select.booleanlist', 'url_valid',
											   'class="inputbox" title="' . JText::_('URL_IS_VALID_TOOLTIP') . '"',
											   $row->url_valid);
				$view->assignRef('lists', $lists);
				$view->assignRef('attachment', $row);

				AttachmentsHelper::add_view_urls($view, 'update', $row->parent_id, $row->parent_type, $attachment_id, $from);
				}
			else {
				require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'upload'.DS.'view.php');
				$view = new AttachmentsViewUpload();
				AttachmentsHelper::add_view_urls($view, 'upload', $row->parent_id, $row->parent_type, null, $from);

				$view->assignRef('uri_type',		 $row->uri_type);
				$view->assignRef('url',				 $row->url);
				$view->assign(	 'parent_id',		 $row->parent_id);
				$view->assignRef('parent_type',		 $row->parent_type);
				$view->assignRef('description',		 $row->description);
				$view->assignRef('user_field_1',	 $row->user_field_1);
				$view->assignRef('user_field_2',	 $row->user_field_2);
				$view->assignRef('user_field_3',	 $row->user_field_3);
				}

			// Suppress the display filename if we are changing from file to url
			$display_name = $row->display_name;
			if ( $update AND (($update == 'file') OR ($update != $row->uri_type)) ) {
				$display_name = '';
				}

			// Set up the view
			$view->assignRef('parent_entity',	   $row->parent_entity);
			$view->assignref('parent_entity_name', $parent_entity_name);
			$view->assignRef('parent_title',	   $parent->title);
			$view->assign(	 'new_parent',		 $parent->new);

			$view->assignRef('display_name',	   $display_name);

			$view->assignRef('params',	$params);

			$view->assignRef('from',	$from);
			$view->assign(	 'Itemid',	JRequest::getInt('Itemid', 1));

			// Display the view
			$view->display(null, $result->error, $result->error_msg);
			exit();
			}

		

		// Clear out the display_name if the URL has changed
		$old_url = JRequest::getString('old_url');
		if ( $row->display_name AND ( $row->url != $old_url ) ) {
			$old_display_name = JRequest::getString('old_display_name');
			if ( $old_display_name == $row->display_name ) {
				$row->display_name = '';
				}
			}

		// Get the maximum allowed filename length (for the filename display)
		$max_filename_length =$params->get('max_filename_length', 0);
		if ( is_numeric($max_filename_length) ) {
			$max_filename_length = (int)$max_filename_length;
			}
		else {
			$max_filename_length = 0;
			}

		// Create a display filename, if needed (for long URLs)
		if ( $max_filename_length > 0 AND strlen($row->display_name) == 0 ) {
			if ( $row->filename ) {
				$row->display_name =
					AttachmentsHelper::truncate_filename($row->filename,
														 $max_filename_length);
				}
			else {
				$row->display_name =
					AttachmentsHelper::truncate_url($row->url,
													$max_filename_length);
				}
			}

		// Assume relative URLs are valid
		if ( $relative_url ) {
			$row->url_valid = true;
			}

		// If there is no filename, do something about it
		if ( !$row->filename AND !$row->display_name ) {
			$row->display_name = $row->url;
			}

		// Set the create/modify dates
		jimport('joomla.utilities.date');
		$now = new JDate();
		$row->create_date = $now->toMySQL();
		$row->modification_date = $row->create_date;
		$row->state = $auto_publish;
		$row->uri_type = 'url';

		// Set up the parent entity to save
		$row->parent_entity = $parent->getEntityname( $row->parent_entity );

		// Save the updated attachment
		if (!$row->store()) {
			$errmsg = JText::_('ERROR_SAVING_URL_ATTACHMENT_RECORD') . $row->getError() . ' (ERR 43)';
			JError::raiseError(500, $errmsg);
			}

		// Delete any old attachment file
		if ( $old_filename_sys ) {
			jimport('joomla.filesystem.file');
			if ( JFile::exists($old_filename_sys) ) {
				JFile::delete($old_filename_sys);
				AttachmentsHelper::clean_directory($old_filename_sys);
				}
			}

		if ( $update ) {
			$msg = JText::_('ATTACHMENT_UPDATED');
			}
		else {
			$msg = JText::_('ATTACHMENT_SAVED');
			}

		return $msg;
	}


	/**
	 * Download an attachment (in secure mode)
	 *
	 * @param int $id the attachment id
	 */
	function download_attachment($id)
	{
		// Get the info about the attachment
		$db =& JFactory::getDBO();
		$query = "SELECT * FROM #__attachments WHERE id='".(int)$id."' LIMIT 1";
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ( count($rows) != 1 ) {
			$errmsg = JText::sprintf('ERROR_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 44)';
			JError::raiseError(500, $errmsg);
			}
		$attachment =& $rows[0];
		$parent_id = $attachment->parent_id;
		$parent_type = $attachment->parent_type;
		$parent_entity = $attachment->parent_entity;

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			$errmsg = JText::sprintf('ERROR_UNKNOWN_PARENT_TYPE_S', $parent_type) . ' (ERR 45)';
			JError::raiseError(500, $errmsg);
			}
		$parent =& $apm->getAttachmentsPlugin($parent_type);

		// Make sure that the user can access the attachment
		if ( !$parent->userMayAccessAttachment( $attachment ) ) {

			// If not logged in, encourage them to log in
			$user =& JFactory::getUser();
			if ( $user->get('username') == '' ) {
				$redirect_to = JRoute::_('index.php?option=com_attachments&task=request_login');
				$this->setRedirect( $redirect_to );
				$this->redirect();
				}

			$errmsg = JText::_('ERROR_NO_PERMISSION_TO_DOWNLOAD') . ' (ERR 46)';
			JError::raiseError(500, $errmsg);
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');
		$who_can_see = $params->get('who_can_see', 'logged_in');

		// Get the other info about the attachment
		$download_mode = $params->get('download_mode', 'attachment');
		$content_type = $attachment->file_type;
		$filename = $attachment->filename;
		$filename_sys = $attachment->filename_sys;

		// Make sure the file exists
		jimport('joomla.filesystem.file');
		if ( !JFile::exists($filename_sys) ) {
			$errmsg = JText::sprintf('ERROR_FILE_S_NOT_FOUND_ON_SERVER', $filename) . ' (ERR 47)';
			JError::raiseError(500, $errmsg);
			}
		$len = filesize($filename_sys);

		// Update the download count
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'tables');
		$attachment =& JTable::getInstance('Attachment', 'AttachmentsTable');
		$attachment->load($id);
		$dl_count = (int)$attachment->download_count;
		$attachment->download_count = $dl_count + 1;
		if ( !$attachment->store() ) {
			$errmsg = $row->getError() . ' (ERR 48)';
			JError::raiseError(500, $errmsg);
			}

		// Begin writing headers
		ob_clean(); // Clear any previously written headers in the output buffer
		header('Cache-Control: private, max-age=0, must-revalidate, no-store');

		// Use the desired Content-Type
		header("Content-Type: $content_type");

		// Construct the downloaded filename
		$filename_info = pathinfo($filename);
		$extension = "." . $filename_info['extension'];
		$basename = basename($filename, $extension);
		// Modify the following line insert a string into
		// the filename of the downloaded file, for example:
		//	  $mod_filename = $basename . "(yoursite)" . $extension;
		$mod_filename = $basename . $extension;

		// Force the download
		header("Content-Disposition: $download_mode; filename=\"$mod_filename\"");
		header("Content-Transfer-Encoding: binary");
		header("Content-Length: ".$len);
		@readfile($filename_sys);
		exit;
	}

	/**
	 * Switch attachment from one parent to another
	 *
	 * @param &object &$row the attachment object
	 * @param int $old_parent_id the id for the old parent
	 * @param int $new_parent_id the id for the new parent
	 * @param string $new_parent_type the new parent type (eg, 'com_content')
	 * @param string $new_parent_entity the new parent entity (eg, 'category')
	 *
	 * @return '' if succesful, else an error message
	 */
	function switch_parent(&$row, $old_parent_id, $new_parent_id, $new_parent_type=null, $new_parent_entity=null)
	{
		// Switch the parent as specified, renaming the file as necessary
		// Return success status

		if ( $row->uri_type == 'url' ) {
			// Do not need to do any file operations if this is a URL
			return '';
			}

		// Get the article/parent handler
		if ( $new_parent_type ) {
			$parent_type = $new_parent_type;
			$parent_entity = $new_parent_entity;
			}
		else {
			$parent_type = $row->parent_type;
			$parent_entity = $row->parent_entity;
			}
		JPluginHelper::importPlugin('attachments');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			$errmsg = JText::sprintf('ERROR_UNKNOWN_PARENT_TYPE_S', $parent_type) . ' (ERR 49)';
			JError::raiseError(500, $errmsg);
			}
		$parent =& $apm->getAttachmentsPlugin($parent_type);

		// Set up the entity name for display
		$parent->loadLanguage();
		$parent_entity = $parent->getCanonicalEntity($parent_entity);
		$parent_entity_name = JText::_($parent->getEntityName($parent_entity));

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Define where the attachments move to
		$upload_url = $params->get('attachments_subdir', 'attachments');
		$upload_dir = JPATH_SITE . DS . $upload_url;

		// Figure out the new system filename
		$new_path = $parent->getAttachmentPath($parent_entity, $new_parent_id, null);
		$new_fullpath = $upload_dir . DS . $new_path;

		// Make sure the new directory exists
		jimport('joomla.filesystem.folder');
		if ( !JFolder::create($new_fullpath) ) {
			$errmsg = JText::sprintf('ERROR_UNABLE_TO_CREATE_DIR_S', $new_fullpath) . ' (ERR 50)';
			JError::raiseError(500, $errmsg);
			}

		// Construct the new filename and URL
		$old_filename_sys = $row->filename_sys;
		$new_filename_sys = $new_fullpath . $row->filename;
		$new_url = JString::str_ireplace(DS, '/', $upload_url . '/' . $new_path . $row->filename);

		// Rename the file
		jimport('joomla.filesystem.file');
		if ( JFile::exists($new_filename_sys) ) {
			return JText::sprintf('ERROR_CANNOT_SWITCH_PARENT_S_NEW_FILE_S_ALREADY_EXISTS',
								  $parent_entity_name, $row->filename);
			}
		if ( !JFile::move($old_filename_sys, $new_filename_sys) ) {
			$new_filename = $new_path . $row->filename;
			return JText::sprintf('ERROR_CANNOT_SWITCH_PARENT_S_RENAMING_FILE_S_FAILED',
								  $parent_entity_name, $new_filename);
			}
		AttachmentsHelper::write_empty_index_html($new_fullpath);

		// Save the changes to the attachment record immediately
		$row->parent_id = $new_parent_id;
		$row->filename_sys = $new_filename_sys;
		$row->url = $new_url;

		// Clean up after ourselves
		AttachmentsHelper::clean_directory($old_filename_sys);

		return '';
	}


	/**
	 * Construct and return the attachments list (as HTML)
	 *
	 * @param int $parent_id the id of the parent
	 * @param string $parent_type the type of the parent (usually $option)
	 * @param string $parent_entity the parent entity
	 * @param bool $user_can_add true if the user can add attachments to this parent
	 * @param int $Itemid the system item id (for menus)
	 * @param string $from a token indicating where to return to
	 * @param bool $show_file_links true if the files should be shown as links
	 * @param bool $allow_edit true if the user can edit/delete attachments for this parent
	 *
	 * @return the html as a string
	 */
	function attachmentListHTML($parent_id, $parent_type, $parent_entity, $user_can_add, $Itemid, $from,
								$show_file_links=true, $allow_edit=true)
	{
		$app = JFactory::getApplication();

		// Generate the HTML for the attachments for the specified parent
		$alist = '';
		$db =& JFactory::getDBO();
		$query = "SELECT count(*) FROM #__attachments "
			. "WHERE parent_id='".(int)$parent_id."' AND status='1' AND parent_type='$parent_type'";
		$db->setQuery($query);
		$total = $db->loadResult();

		if ( $total > 0 ) {

			// Get the component parameters
			jimport('joomla.application.component.helper');
			$params =& JComponentHelper::getParams('com_attachments');

			// Check the security status
			$attach_dir = JPATH_SITE.DS.$params->get('attachments_subdir', 'attachments');
			$secure = $params->get('secure', false);
			$hta_filename = $attach_dir.DS.'.htaccess';
			if ( ($secure AND !file_exists($hta_filename)) OR
				 (!$secure AND file_exists($hta_filename)) ) {
				require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'helper.php');
				AttachmentsHelper::setup_upload_directory($attach_dir, $secure);
				}

			if ( $app->isAdmin() ) {
				// Get the html for the attachments list
				require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.
							 'controllers'.DS.'attachments.php');
				}
			else {
				// Get the html for the attachments list
				require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.
							 'controllers'.DS.'attachments.php');
				}
			$controller = new AttachmentsControllerAttachments();
			$alist = $controller->display($parent_id, $parent_type, $parent_entity,
										  null, $show_file_links, $allow_edit, false, $from);
			}

		return $alist;
	}


	/**
	 * Save a warning message for display later by the main attachment warning function.
	 *
	 * Note that the message is saved in the dom so it will be lost after any refreshes/redirects
	 *
	 * @param string $msg the message to be saved
	 */
	function save_warning_message($msg)
	{
		$doc =& JFactory::getDocument();
		$doc->addScriptDeclaration(
			"function save_warning_msg(str) { document.warning_msg = str; };");
		echo "<script>save_warning_msg('$msg');</script>";
	}

	/**
	 * Save a system message in the session to be displayed in the next redirect
	 *
	 * @param string $msg the message to be saved
	 * @param string $msgType the type of message
	 */
	function enqueueSystemMessage($msg, $msgType='message')
	{
		$app = JFactory::getApplication();
		$app->enqueueMessage($msg, $msgType);

		// Persist the message, borrowed from redirect() function in:
		//	 libraries/joomla/application/application.php
		$session =& JFactory::getSession();
		$session->set('application.queue', $app->getMessageQueue());
	}

}

?>
