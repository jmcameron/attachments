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

/** Load the Attachments defines */
require_once(JPATH_SITE.'/components/com_attachments/defines.php');


/**
 * A class for attachments helper functions
 *
 * @package Attachments
 */
class AttachmentsHelper
{
	/**
	 * Install the specified stylesheet (handles caching)
	 *
	 * @param string $css_path the URL/path to the CSS file to add to the document
	 */
	public static function addStyleSheet($css_path, $echo=false)
	{
		static $added_js = false;

		$document = JFactory::getDocument();
		$config	 = JFactory::getConfig();

		// See if we are caching
		$cache = JFactory::getCache();
		// ??? Do we need to handle caching?
		// ??? $caching = $config->get('cache') || $cache->_options['caching'];
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
	protected static function truncate_filename($raw_filename, $maxlen)
	{
		// Do not truncate if $maxlen is 0 or no truncation is needed
		if ( ($maxlen == 0) || (strlen($raw_filename) <= $maxlen) ) {
			return $raw_filename;
			}

		$filename_info = pathinfo($raw_filename);
		$basename = $filename_info['basename'];
		$filename = $filename_info['filename'];

		$extension = '';
		if ($basename != $filename) {
			$extension = $filename_info['extension'];
			}

		if ( JString::strlen($extension) > 0 ) {
			$maxlen = max( $maxlen - (JString::strlen($extension) + 2), 1);
			return JString::substr($filename, 0, $maxlen) . '~.' . $extension;
			}
		else {
			$maxlen = max( $maxlen - 1, 1);
			return JString::substr($filename, 0, $maxlen) . '~';
			}
	}


	/**
	 * Truncate the URL if it is longer than the maxlen
	 * Do this by deleting necessary characters from the middle of the URL
	 *
	 * Always preserve the 'http://' part on the left.
	 *
	 * NOTE: The 'maxlen' applies only to the part after the 'http://'
	 *
	 * @param string $raw_url the input URL
	 * @param int $maxlen the maximum allowed length (0 means no limit)
	 *
	 * @return the truncated URL
	 */
	protected static function truncate_url($raw_url, $maxlen)
	{
		// Do not truncate if $maxlen is 0 or no truncation is needed
		if ( ($maxlen == 0) || (strlen($raw_url) <= $maxlen) ) {
			return $raw_url;
			}

		// Get the part after the protocol ('http://')
		$parts = explode('//', $raw_url, 2);
		$protocol = $parts[0];

		// Let the 'address' be the part of the URL after the '//'
		$address = $parts[1];
		$address_len = strlen($address);

		// Return if only the address part is okay
		if ( $address_len <= $maxlen ) {
			return $raw_url;
			}

		// Work out length of left part to insert ellipses in the middle
		$left = (int)(($maxlen-2)/2);
		if ( 2*$left + 2 < $maxlen ) {
			$left++;
			}
		$right = $maxlen - $left - 2;

		// Truncate the address part of the URL
		$truncated_address = substr($address, 0, $left) . '&#183;&#183;' .
			substr($address, $address_len - $right);

		return $protocol . '//' . $truncated_address;
	}



	/**
	 * Write an empty 'index.html' file in the specified directory to prevent snooping
	 *
	 * @param string $dir full path of the directory needing an 'index.html' file
	 *
	 * @return true if the file was successfully written
	 */
	public static function write_empty_index_html($dir)
	{
		jimport('joomla.filesystem.file');

		$index_fname = $dir.'/index.html';
		if ( JFile::exists($index_fname) ) {
			return true;
			}
		$contents = "<html><body><br /><h2 align=\"center\">Access denied.</h2></body></html>";
		JFile::write($index_fname, $contents);

		return JFile::exists($index_fname);
	}


	/**
	 * Check the directory corresponding to this path.	If it is empty, delete it.
	 * (Assume anything with a trailing DS or '/' is a directory)
	 *
	 * @param string $filename path of the file to have its containing directory cleaned.
	 */
	public static function clean_directory($filename)
	{
		jimport('joomla.filesystem.folder');

		// Assume anything with a trailing DS or '/' is a directory
		if ( ($filename[strlen($filename)-1] == DS) || ($filename[strlen($filename)-1] == '/') ) {

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
				$dirname = $filename_info['dirname'] . '/';
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
		$upload_dir = JPATH_SITE.'/'.AttachmentsDefines::$ATTACHMENTS_SUBDIR;
		$dirend_chars = DS.'/';
		if ( realpath(rtrim($upload_dir,$dirend_chars)) == realpath(rtrim($dirname,$dirend_chars)) ) {
			return;
			}

		// See how many files exist in the directory
		$files = JFolder::files($dirname);

		// If there are no files left (or only the index.html file is left), delete the directory
		if ( (count($files) == 0) || ( (count($files) == 1) && ($files[0] == 'index.html') ) ) {
			JFolder::delete($dirname);
			}
	}


	/**
	 * Set up the upload directory
	 *
	 * @param string $upload_dir the directory to be set up
	 * @param bool $secure true if the directory should be set up for secure mode (with the necessary .htaccess file)
	 *
	 * @return true if successful
	 */
	public static function setup_upload_directory($upload_dir, $secure)
	{
		$subdir_ok = false;

		// Do not allow the main site directory to be set up as the upload directory
		$dirend_chars = DS.'/';
		if ( ( realpath(rtrim($upload_dir,$dirend_chars)) == realpath(JPATH_SITE) ) ||
			 ( realpath(rtrim($upload_dir,$dirend_chars)) == realpath(JPATH_ADMINISTRATOR) ) ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 101)';
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
				// ??? (Should the permission be an option?)
				chmod($upload_dir, 0775);
				$subdir_ok = true;
				}
			}

		if ( !$subdir_ok || !JFolder::exists($upload_dir) ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 102)';
			JError::raiseError(500, $errmsg);
			}

		// Add a simple index.html file to the upload directory to prevent browsing
		$index_ok = false;
		$index_fname = $upload_dir.'/index.html';
		if ( !AttachmentsHelper::write_empty_index_html($upload_dir) ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_ADDING_INDEX_HTML_IN_S', $upload_dir) . ' (ERR 103)';
			JError::raiseError(500, $errmsg);
			}

		// If this is secure, create the .htindex file, if necessary
		$hta_fname = $upload_dir.'/.htaccess';
		jimport('joomla.filesystem.file');
		if ( $secure ) {
			$hta_ok = false;

			$line = "order deny,allow\ndeny from all\n";
			JFile::write($hta_fname, $line);
			if ( JFile::exists($hta_fname) ) {
				$hta_ok = true;
				}
			if ( ! $hta_ok ) {
				$errmsg = JText::sprintf('ATTACH_ERROR_ADDING_HTACCESS_S', $upload_dir) . ' (ERR 104)';
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
	public static function add_view_urls(&$view, $save_type, $parent_id, $parent_type, $attachment_id, $from)
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
		$save_url .= "&from=$from";
		$view->save_url = JRoute::_($save_url);

		// Construct the URL to upload a URL instead of a file
		if ( $save_type == 'upload' ) {
			$upload_file_url = $url_base . "&task=$upload_task&uri=file" . $parentinfo . $template;
			$upload_url_url	 = $url_base . "&task=$upload_task&uri=url" . $parentinfo . $template;

			$upload_file_url .= "&from=$from";
			$upload_url_url .= "&from=$from";

			// Add the URL
			$view->upload_file_url = JRoute::_($upload_file_url);
			$view->upload_url_url = JRoute::_($upload_url_url);
			}

		elseif ( $save_type == 'update' ) {
			$change_url = $url_base . "&task=$update_task" . $idinfo;
			$change_file_url =	$change_url . "&amp;update=file" . $template;
			$change_url_url =  $change_url . "&amp;update=url" . $template;
			$normal_update_url =  $change_url . $template;

			$change_file_url .= "&from=$from";
			$change_url_url .= "&from=$from";
			$normal_update_url .= "&from=$from";

			// Add the URLs
			$view->change_file_url = JRoute::_($change_file_url);
			$view->change_url_url =		   JRoute::_($change_url_url);
			$view->normal_update_url = JRoute::_($normal_update_url);
			}
	}


	/**
	 * Upload the file
	 *
	 * @param &object &$attachment the partially constructed attachment object
	 * @param &object &$parent An Attachments plugin parent object with partial parent info including:
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
	public static function upload_file(&$attachment, &$parent, $attachment_id=false, $save_type='update')
	{
		$user = JFactory::getUser();
		$db = JFactory::getDBO();

		// Figure out if the user may publish this attachment
		$may_publish = $parent->userMayChangeAttachmentState($attachment->parent_id,
															 $attachment->parent_entity,
															 $attachment->created_by);

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Make sure the attachments directory exists
		$upload_dir = JPATH_SITE.'/'.AttachmentsDefines::$ATTACHMENTS_SUBDIR;
		$secure = $params->get('secure', false);
		if ( !AttachmentsHelper::setup_upload_directory( $upload_dir, $secure ) ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 105)';
			JError::raiseError(500, $errmsg);
			}

		// If we are updating, note the name of the old filename
		$old_filename = null;
		$old_filename_sys = null;
		$old_uri_type = $attachment->uri_type;
		if ( $old_uri_type ) {
			$old_filename = $attachment->filename;
			$old_filename_sys = $attachment->filename_sys;
			}

		// Get the new filename
		//	 (Note: The following replacement is necessary to allow
		//			single quotes in filenames to work correctly.)
		$filename = JString::str_ireplace("\'", "'", $_FILES['upload']['name']);
		$ftype = $_FILES['upload']['type'];

		// Truncate the filename, if necessary and alert the user
		if (JString::strlen($filename) > AttachmentsDefines::$MAXIMUM_FILENAME_LENGTH) {
			$filename = AttachmentsHelper::truncate_filename($filename, AttachmentsDefines::$MAXIMUM_FILENAME_LENGTH);
			$msg = JText::_('ATTACH_WARNING_FILENAME_TRUNCATED');
			$app = JFactory::getApplication();
			if ( $app->isAdmin() ) {
				$lang = JFactory::getLanguage();
				if ( $lang->isRTL() ) {
					$msg = "'$filename' " . $msg;
					}
				else {
					$msg = $msg . " '$filename'";
					}
				$app->enqueueMessage($msg, 'warning');
				}
			else {
				$msg .= "\\n \'$filename\'";
				echo "<script type=\"text/javascript\">alert('$msg')</script>";
				}
			}

		$from = JRequest::getWord('from');

		// Check the filename for bad characters
		$bad_chars = false;
		$forbidden_chars = $params->get('forbidden_filename_characters', '#=?%&');
		for ($i=0; $i < strlen($forbidden_chars); $i++) {
			$char = $forbidden_chars[$i];
			if ( strpos($filename, $char) !== false ) {
				$bad_chars = true;
				break;
				}
			}

		// Set up the entity name for display
		$parent_entity = $parent->getCanonicalEntityId($attachment->parent_entity);
		$parent_entity_name = JText::_('ATTACH_' . $parent_entity);

		// A little formatting
		$msgbreak = '<br />';
		$app = JFactory::getApplication();
		if ( $app->isAdmin() ) {
			$msgbreak = '';
			}

		// Make sure a file was successfully uploaded
		if ( (($_FILES['upload']['size'] == 0) &&
			  ($_FILES['upload']['tmp_name'] == '')) || $bad_chars ) {

			// Guess the type of error
			if ( $bad_chars ) {
				$error = 'bad_chars';
				$error_msg = JText::sprintf('ATTACH_ERROR_BAD_CHARACTER_S_IN_FILENAME_S', $char, $filename);
				if ( $app->isAdmin() ) {
					$result = new JObject();
					$result->error = true;
					$result->error_msg = $error_msg;
					return $result;
					}
				}
			elseif ( $filename == '' ) {
				$error = 'no_file';
				$error_msg = JText::sprintf('ATTACH_ERROR_UPLOADING_FILE_S', $filename);
				$error_msg .= $msgbreak . ' (' . JText::_('ATTACH_YOU_MUST_SELECT_A_FILE_TO_UPLOAD') . ')';
				if ( $app->isAdmin() ) {
					$result = new JObject();
					$result->error = true;
					$result->error_msg = $error_msg;
					return $result;
					}
				}
			else {
				$error = 'file_too_big';
				$error_msg = JText::sprintf('ATTACH_ERROR_UPLOADING_FILE_S', $filename);
				$error_msg .= $msgbreak . '(' . JText::_('ATTACH_ERROR_MAY_BE_LARGER_THAN_LIMIT') . ' ';
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
				require_once(JPATH_COMPONENT_SITE.'/views/update/view.html.php');
				$view = new AttachmentsViewUpdate();
				AttachmentsHelper::add_view_urls($view, 'update', $attachment->parent_id,
												 $attachment->parent_type, $attachment_id, $from);

				$view->update = JRequest::getWord('update');

				// Set up the selection lists
				$lists = array();
				$lists['url_valid'] = JHTML::_('select.booleanlist', 'url_valid',
											   'class="inputbox" title="' . JText::_('ATTACH_URL_IS_VALID_TOOLTIP') . '"',
											   $attachment->url_valid);

				// Set up publishing info
				$view->may_publish = $may_publish;
				if ( $may_publish ) {
					$default_state = $params->get('publish_default', false);
					$view->publish = JHTML::_('select.booleanlist', 'state', 'class="inputbox"', $attachment->state);
					}

				// Set up for editing the access level
				if ( $params->get('allow_frontend_access_editing', false) ) {
					require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
					$view->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', $attachment->access);
					$view->access_level_tooltip = JText::_('ATTACH_ACCESS_LEVEL_TOOLTIP');
					}

				$view->lists = $lists;
				$view->attachment = $attachment;
				}
			else {
				require_once(JPATH_COMPONENT_SITE.'/views/upload/view.html.php');
				$view = new AttachmentsViewUpload();
				AttachmentsHelper::add_view_urls($view, 'upload', $attachment->parent_id,
												 $attachment->parent_type, $attachment_id, null, $from);

				// Set up publishing info
				$view->may_publish = $may_publish;
				if ( $may_publish ) {
					$default_state = $params->get('publish_default', false);
					$view->publish = JHTML::_('select.booleanlist', 'state', 'class="inputbox"', $default_state);
					}

				// Set up for editing the access level
				if ( $params->get('allow_frontend_access_editing', false) ) {
					require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
					$view->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', null);
					$view->access_level_tooltip = JText::_('ATTACH_ACCESS_LEVEL_TOOLTIP');
					}

				$view->uri_type		= $attachment->uri_type;
				$view->url			= $attachment->url;
				$view->parent_id	= $attachment->parent_id;
				$view->parent_type	= $attachment->parent_type;
				$view->description	= $attachment->description;
				$view->user_field_1 = $attachment->user_field_1;
				$view->user_field_2 = $attachment->user_field_2;
				$view->user_field_3 = $attachment->user_field_3;
				}

			// Suppress the display filename if we are changing from file to url
			$display_name = $attachment->display_name;
			if ( $save_type == 'update' ) {
				$new_uri_type = JRequest::getWord('update');
				if ( $new_uri_type && (($new_uri_type == 'file') || ($new_uri_type != $attachment->uri_type)) ) {
					$display_name = '';
					}
				}

			// Set up the view
			$view->parent_entity = $attachment->parent_entity;
			$view->parent_entity_name = $parent_entity_name;
			$view->parent_title = $parent->title;
			$view->new_parent = $parent->new;

			$view->display_name = $display_name;

			$view->params = $params;

			$view->from = $from;
			$view->Itemid = JRequest::getInt('Itemid', 1);

			$view->error = $error;
			$view->error_msg = $error_msg;

			// Display the view
			$view->display();
			exit();
			}

		// Make sure the file type is okay (respect restrictions imposed by media manager)
		$cmparams = JComponentHelper::getParams('com_media');

		// First check to make sure the extension is allowed
		jimport('joomla.filesystem.file');
		$allowable = explode( ',', $cmparams->get( 'upload_extensions' ));
		$ignored = explode(',', $cmparams->get( 'ignore_extensions' ));
		$format = JString::strtolower(JFile::getExt($filename));
		$error = false;
		$error_msg = false;
		if (!in_array($format, $allowable) && !in_array($format,$ignored)) {
			$error = 'illegal_file_extension';
			$error_msg = JText::sprintf('ATTACH_ERROR_UPLOADING_FILE_S', $filename);
			$error_msg .= "<br />" . JText::_('ATTACH_ERROR_ILLEGAL_FILE_EXTENSION') . " $format";
			$error_msg .= "<br />" . JText::_('ATTACH_ERROR_CHANGE_IN_MEDIA_MANAGER');
			}

		// Check to make sure the mime type is okay
		if ( $cmparams->get('restrict_uploads',true) ) {
			if ( $cmparams->get('check_mime', true) ) {
				$allowed_mime = explode(',', $cmparams->get('upload_mime'));
				$illegal_mime = explode(',', $cmparams->get('upload_mime_illegal'));
				if ( JString::strlen($ftype) && !in_array($ftype, $allowed_mime) &&
					 in_array($ftype, $illegal_mime) ) {
					$error = 'illegal_mime_type';
					$error_msg = JText::sprintf('ATTACH_ERROR_UPLOADING_FILE_S', $filename);
					$error_msg .= ', ' . JText::_('ATTACH_ERROR_ILLEGAL_FILE_MIME_TYPE') . " $ftype";
					$error_msg .= "	 <br />" . JText::_('ATTACH_ERROR_CHANGE_IN_MEDIA_MANAGER');
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
				require_once(JPATH_COMPONENT_SITE.'/views/update/view.html.php');
				$view = new AttachmentsViewUpdate();
				AttachmentsHelper::add_view_urls($view, 'update', $attachment->parent_id,
												 $attachment->parent_type, $attachment_id, $from);

				$view->update = JRequest::getWord('update');

				// Set up the selection lists
				$lists = array();
				$lists['url_valid'] = JHTML::_('select.booleanlist', 'url_valid',
											   'class="inputbox" title="' . JText::_('ATTACH_URL_IS_VALID_TOOLTIP') . '"',
											   $attachment->url_valid);

				// Set up publishing info
				$view->may_publish = $may_publish;
				if ( $may_publish ) {
					$default_state = $params->get('publish_default', false);
					$view->publish = JHTML::_('select.booleanlist', 'state', 'class="inputbox"', $attachment->state);
					}

				// Set up for editing the access level
				if ( $params->get('allow_frontend_access_editing', false) ) {
					require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
					$view->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', $attachment->access);
					$view->access_level_tooltip = JText::_('ATTACH_ACCESS_LEVEL_TOOLTIP');
					}

				$view->lists = $lists;
				$view->attachment = $attachment;
				}
			else {
				require_once(JPATH_COMPONENT_SITE.'/views/upload/view.html.php');
				$view = new AttachmentsViewUpload();
				AttachmentsHelper::add_view_urls($view, 'upload', $attachment->parent_id,
												 $attachment->parent_type, null, $from);

				// Set up publishing info
				$view->may_publish = $may_publish;
				if ( $may_publish ) {
					$default_state = $params->get('publish_default', false);
					$view->publish = JHTML::_('select.booleanlist', 'state', 'class="inputbox"', $default_state);
					}

				// Set up for editing the access level
				if ( $params->get('allow_frontend_access_editing', false) ) {
					require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
					$view->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', null);
					$view->access_level_tooltip = JText::_('ATTACH_ACCESS_LEVEL_TOOLTIP');
					}

				$view->uri_type =		 $attachment->uri_type;
				$view->url =				 $attachment->url;
				$view->parent_id = $attachment->parent_id;
				$view->parent_type =		 $attachment->parent_type;
				$view->description =		 $attachment->description;
				$view->user_field_1 =	 $attachment->user_field_1;
				$view->user_field_2 =	 $attachment->user_field_2;
				$view->user_field_3 =	 $attachment->user_field_3;
				}

			// Suppress the display filename if we are changing from file to url
			$display_name = $attachment->display_name;
			if ( $save_type == 'update' ) {
				$new_uri_type = JRequest::getWord('update');
				if ( $new_uri_type && (($new_uri_type == 'file') || ($new_uri_type != $attachment->uri_type)) ) {
					$display_name = '';
					}
				}

			// Set up the view
			$view->parent_entity =	 $attachment->parent_entity;
			$view->parent_entity_name = $parent_entity_name;
			$view->parent_title =	 $parent->title;
			$view->new_parent = $parent->new;

			$view->display_name = $display_name;

			$view->params = $params;

			// Set up publishing info
			$view->may_publish = $may_publish;
			if ( $may_publish ) {
				$default_state = $params->get('publish_default', false);
				$view->publish = JHTML::_('select.booleanlist', 'state', 'class="inputbox"', $default_state);
				}

			// Set up the access levels
			if ( $params->get('allow_frontend_access_editing', false) ) {
				require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
				$view->access_level_tooltip = JText::_('JFIELD_ACCESS_LABEL') . '::' . JText::_('JFIELD_ACCESS_DESC');
				$view->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', $attachment->access);
				}

			$view->from = $from;
			$view->Itemid = JRequest::getInt('Itemid', 1);

			$view->error = $error;
			$view->error_msg = $error_msg;

			// Display the view
			$view->display();
			exit();
			}

		// Define where the attachments go
		$upload_url = AttachmentsDefines::$ATTACHMENTS_SUBDIR;
		$upload_dir = JPATH_SITE.'/'.$upload_url;

		// Figure out the system filename
		$path = $parent->getAttachmentPath($attachment->parent_entity,
										   $attachment->parent_id, null);
		$fullpath = $upload_dir.'/'.$path;

		// Make sure the directory exists
		if ( !JFile::exists($fullpath) ) {
			jimport( 'joomla.filesystem.folder' );
			if ( !JFolder::create($fullpath) ) {
				$errmsg = JText::sprintf('ATTACH_ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 106)';
				JError::raiseError(500, $errmsg);
				}
			AttachmentsHelper::write_empty_index_html($fullpath);
			}

		// Get ready to save the file
		$filename_sys = $fullpath . $filename;

		$url = $upload_url . '/' . $path . $filename;

		// If we are on windows, fix the filename and URL
		if ( DS != '/' ) {
			$filename_sys = str_replace('/', DS, $filename_sys);
			$url = str_replace(DS, '/', $url);
			}

		// Make sure the system filename doesn't already exist
		$error = false;
		$duplicate_filename = false;
		if ( ($save_type == 'upload') && JFile::exists($filename_sys) ) {
			// Cannot overwrite an existing file when creating a new attachment!
			$duplicate_filename = true;
			}
		if ( ($save_type == 'update') && JFile::exists($filename_sys) ) {
			// If updating, we may replace the existing file but may not overwrite any other existing file
			$query = $db->getQuery(true);
			$query->select('id')->from('#__attachments');
			$query->where('filename_sys=' . $db->quote($filename_sys) . ' AND id != ' . (int)$attachment->id);
			$db->setQuery($query, 0, 1);
			if ( $db->loadResult() > 0 ) {
				$duplicate_filename = true;
				}
			}

		// Handle duplicate filename error
		if ( $duplicate_filename ) {
			$error = 'file_already_on_server';
			$error_msg = JText::sprintf('ATTACH_ERROR_FILE_S_ALREADY_ON_SERVER', $filename);

			if ( $app->isAdmin() ) {
				$result = new JObject();
				$result->error = true;
				$result->error_msg = $error_msg;
				return $result;
				}

			$save_url = JRoute::_("index.php?option=com_attachments&task=save&tmpl=component");

			// Set up the view to redisplay the form with warnings
			require_once(JPATH_COMPONENT_SITE.'/views/upload/view.html.php');
			$view = new AttachmentsViewUpload();
			AttachmentsHelper::add_view_urls($view, 'upload', $attachment->parent_id,
											 $attachment->parent_type, null, $from);

			// Set up the view
			$view->uri_type =		 $attachment->uri_type;
			$view->url =				 $attachment->url;
			$view->parent_id = $attachment->parent_id;
			$view->parent_type =		 $attachment->parent_type;
			$view->parent_entity =	 $attachment->parent_entity;
			$view->parent_entity_name = $parent_entity_name;
			$view->parent_title =	 $parent->title;
			$view->new_parent = $parent->new;
			$view->description =		 $attachment->description;
			$view->display_name =	 $attachment->display_name;
			$view->user_field_1 =	 $attachment->user_field_1;
			$view->user_field_2 =	 $attachment->user_field_2;
			$view->user_field_3 =	 $attachment->user_field_3;
			$view->from =			 $from;
			$view->Itemid = JRequest::getInt('Itemid', 1);
			$view->params =				 $params;

			// Set up publishing info
			$view->may_publish = $may_publish;
			if ( $may_publish ) {
				$default_state = $params->get('publish_default', false);
				$view->publish = JHTML::_('select.booleanlist', 'state', 'class="inputbox"', $default_state);
				}

			// Set up the access levels
			if ( $params->get('allow_frontend_access_editing', false) ) {
				require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
				$view->access_level_tooltip = JText::_('JFIELD_ACCESS_LABEL') . '::' . JText::_('JFIELD_ACCESS_DESC');
				$view->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', $attachment->access);
				}

			$view->error = $error;
			$view->error_msg = $error_msg;

			// Display the view
			$view->display();
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
		if ( $attachment->display_name && ($save_type == 'update') && ( $filename != $old_filename ) ) {
			$attachment->display_name = '';
			}

		// Create a display filename, if needed (for long filenames)
		if ( ($max_filename_length > 0) &&
			 ( JString::strlen($attachment->display_name) == 0 ) &&
			 ( JString::strlen($filename) > $max_filename_length ) ) {
			$attachment->display_name = AttachmentsHelper::truncate_filename($filename, $max_filename_length);
			}

		// Copy the info about the uploaded file into the new record
		$attachment->uri_type = 'file';
		$attachment->filename = $filename;
		$attachment->filename_sys = $filename_sys;
		$attachment->url = $url;
		$attachment->file_type = $ftype;
		$attachment->file_size = $_FILES['upload']['size'];

		// If the user is not authorised to change the state (eg, publish/unpublish),
		// ignore the form data and make sure the publish state is is set correctly.
		if ( !$may_publish ) {
			if ( $save_type == 'upload' ) {
				// Use the default publish state (ignore form info)
				jimport('joomla.application.component.helper');
				$params = JComponentHelper::getParams('com_attachments');
				$attachment->state = $params->get('publish_default', false);
				}
			else {
				// Restore the old state (ignore form info)
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select('state')->from('#__attachments')->where('id = '.(int)$attachment->id);
				$db->setQuery($query, 0, 1);
				$old_state = $db->loadResult();
				if ( $db->getErrorNum() ) {
					$errmsg = $db->stderr() . ' (ERR 107)';
					JError::raiseError(500, $errmsg);
					}
				$attachment->state = $old_state;
				}
			}

		// Set the create/modify dates
		$now = JFactory::getDate();
		$now = $now->toMySQL();

		// Update the create/modify info
		if ( $save_type == 'upload' ) {
			$attachment->created = $now;
			}
		$attachment->modified = $now;

		// Add the icon file type
		require_once(JPATH_COMPONENT_SITE.'/file_types.php');
		$attachment->icon_filename = AttachmentsFileTypes::icon_filename($filename, $ftype);

		// Save the updated attachment
		if (!$attachment->store()) {
			$errmsg = JText::_('ATTACH_ERROR_SAVING_FILE_ATTACHMENT_RECORD') . $attachment->getError() . ' (ERR 108)';
			JError::raiseError(500, $errmsg);
			}

		// Get the attachment id
		// If we're updating we may not get an insertid, so don't blindly overwrite the old
		// attachment_id just in case (Thanks to Franz-Xaver Geiger for a bug fix on this)
		$new_attachment_id = $db->insertid();
		if ( !empty($new_attachment_id) ) {
			$attachment_id = (int)$new_attachment_id;
			}

		// Move the file
		$msg = "";
		if (JFile::upload($_FILES['upload']['tmp_name'], $filename_sys)) {
			$size = (int)( $attachment->file_size / 1024.0 );
			chmod($filename_sys, 0644);
			if ( $save_type == 'update' )
				$msg = JText::_('ATTACH_UPDATED_ATTACHMENT') . ' ' . $filename . " (" . $size . " Kb)!";
			else
				$msg = JText::_('ATTACH_UPLOADED_ATTACHMENT') . ' ' . $filename . " (" . $size . " Kb)!";
			}
		else {
			$query = $db->getQuery(true);
			$query->delete('#__attachments')->where('id = '.(int)$attachment_id);
			$db->setQuery($query);
			$result = $db->query();
			if ( $db->getErrorNum() ) {
				$errmsg = $db->stderr() . ' (ERR 109)';
				JError::raiseError(500, $errmsg);
				}
			$msg = JText::_('ATTACH_ERROR_MOVING_FILE')
				. " {$_FILES['upload']['tmp_name']} -> {$filename_sys})";
			}

		// If we are updating, we may need to delete the old file
		if ( $old_uri_type ) {
			if ( ($filename_sys != $old_filename_sys) && JFile::exists($old_filename_sys) ) {
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
	private static function parse_url(&$raw_url, $relative_url)
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
			if ( isset($match['protocol']) && $match['protocol'] ) {
				$protocol = JString::rtrim($match['protocol'], '/:');
				}

			// Get the domain (if any)
			$domain = '';
			if ( isset($match['domain']) && $match['domain'] ) {
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
					JText::sprintf('ATTACH_ERROR_UNKNOWN_PROTCOL_S_IN_URL_S', $protocol, $raw_url);
				return $result;
				}
			// Override the port if specified
			if ( isset($match['port']) && $match['port'] ) {
				$port = (int)$match['port'];
				}
			// Default to HTTP if protocol/port is missing
			if ( !$port ) {
				$port = 80;
				}

			// Get the path and reconstruct the full path
			if ( isset($match['path']) && $match['path'] ) {
				$path = $match['path'];
				}
			else {
				$path = '/';
				}

			// Get the parameters (if any)
			if ( isset($match['parameters']) && $match['parameters'] ) {
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
					$result->error_msg = JText::sprintf('ATTACH_ERROR_IN_URL_SYNTAX_S', $raw_url);
					}
				}

			// Save the information
			$result->protocol = $protocol;
			$result->domain = $domain;
			$result->port = $port;
			$result->path = str_replace('//', '/', $path);
			$result->params = $parameters;
			$result->url = str_replace('//', '/', $path . $result->params);
			}
		else {
			// Reject bad url syntax
			$result->error = true;
			$result->error_code = 'url_bad_syntax';
			$result->error_msg = JText::sprintf('ATTACH_ERROR_IN_URL_SYNTAX_S', $raw_url);
			}

		return $result;
	}


	/**
	 * Get the info about this URL
	 *
	 * @param string $raw_url the raw url to parse
	 * @param &object &$attachment the attachment object
	 * @param bool $verify whether the existance of the URL should be checked
	 * @param bool $relative_url allow relative URLs
	 *
	 * @return true if the URL is okay, or an error object if not
	 */
	public static function get_url_info($raw_url, &$attachment, $verify, $relative_url)
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
		$attachment->filename = JString::trim($filename);
		$attachment->file_size = $file_size;
		$attachment->url_valid = false;

		// Get parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');
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

			// Set up error handler in case it times out or some other error occurs
			set_error_handler(create_function('$a, $b, $c, $d',
				'throw new Exception("fsockopen error");'), E_ALL);
			try {
				$fp = fsockopen($u->domain, $u->port, $errno, $errstr, $timeout);
				restore_error_handler();
				}
			catch (Exception $e) {
				restore_error_handler();
				if ( $verify ) {
					$u->error = true;
					$u->error_code = 'url_check_exception';
					$u->error_msg = $e->getMessage();
					}
				}

			if ( $u->error ) {
				$error_msg = JText::sprintf('ATTACH_ERROR_CHECKING_URL_S', $raw_url);
				$error_msg .= ' <br />(' . $u->error_msg . ': ' . $errstr . ')';
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
			if ( !$found && $verify ) {
				$u->error = true;
				$u->error_code = 'url_not_found';
				$u->error_msg = JText::sprintf('ATTACH_ERROR_COULD_NOT_ACCESS_URL_S', $raw_url);
				return $u;
				}
			}
		else {
			if ( $verify && $timeout > 0 ) {
				// Error connecting
				$u->error = true;
				$u->error_code = 'url_error_connecting';
				$error_msg = JText::sprintf('ATTACH_ERROR_CONNECTING_TO_URL_S', $raw_url)
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
		$attachment->filename = JString::trim($filename);
		$attachment->file_size = $file_size;
		$attachment->url_valid = $found;

		// Deal with the file type
		if ( !$mime_type ) {
			require_once(JPATH_COMPONENT_SITE.'/file_types.php');
			$mime_type = AttachmentsFileTypes::mime_type($filename);
			}
		if ( $mime_type ) {
			$attachment->file_type = JString::trim($mime_type);
			}
		else {
			if ( $overlay ) {
				$mime_type = 'link/generic';
				$attachment->file_type = 'link/generic';
				}
			else {
				$mime_type = 'link/unknown';
				$attachment->file_type = 'link/unknown';
				}
			}

		// See if we can figure out the icon
		require_once(JPATH_COMPONENT_SITE.'/file_types.php');
		$icon_filename = AttachmentsFileTypes::icon_filename($filename, $mime_type);
		if ( $icon_filename ) {
			$attachment->icon_filename = AttachmentsFileTypes::icon_filename($filename, $mime_type);
			}
		else {
			if ( $mime_type == 'link/unknown' ) {
				$attachment->icon_filename = 'link.gif';
				}
			elseif ( $mime_type == 'link/broken' ) {
				$attachment->icon_filename = 'link_broken.gif';
				}
			else {
				$attachment->icon_filename = 'link.gif';
				}
			}

		return true;
	}


	/**
	 * Add the infomation about the URL to the attaachment record and then save it
	 *
	 * @param &object &$attachment the attachment object
	 * @param &object &$parent the attachments parent object
	 * @param bool $verify whether the existance of the URL should be checked
	 * @param bool $relative_url allow relative URLs
	 * @param string $update the type of update (or false if it is not an update)
	 * @param int $attachment_id the attachment ID, false if this is a new attachment
	 *
	 * @return an error message if there is a problem
	 */
	public static function add_url(&$attachment, &$parent, $verify, $relative_url=false,
								   $update=false, $attachment_id=false)
	{
		$user = JFactory::getUser();

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Get the auto-publish setting
		$auto_publish = $params->get('publish_default', false);

		// Figure out if the user has permissions to publish
		$may_publish = $parent->userMayChangeAttachmentState($attachment->parent_id,
															 $attachment->parent_entity,
															 $attachment->created_by);

		// If we are updating, note the name of the old filename (if there was one)
		// (Needed for switching from a file to a URL)
		$old_filename = null;
		$old_filename_sys = null;
		if ( $update ) {
			if ( $attachment->filename_sys ) {
				$old_filename = $attachment->filename;
				$old_filename_sys = $attachment->filename_sys;
				}
			}

		// Set up the entity name for display
		$parent_entity = $attachment->parent_entity;
		$parent_entity_name = JText::_('ATTACH_' . $parent_entity);

		// Check to make sure the URL is valid
		$from = JRequest::getWord('from');

		// Get the info from the url
		$result = AttachmentsHelper::get_url_info($attachment->url, $attachment, $verify, $relative_url);

		// If there was an error, bow out
		if ( $result !== true ) {

			$app = JFactory::getApplication();
			if ( $app->isAdmin() ) {
				return $result;
				}

			$update_form = JRequest::getWord('update');

			// Redisplay the upload/update form with complaints
			if ( $update ) {
				require_once(JPATH_COMPONENT_SITE.'/views/update/view.html.php');
				$view = new AttachmentsViewUpdate();
				AttachmentsHelper::add_view_urls($view, 'update',
												 $attachment->parent_id, $attachment->parent_type,
												 $attachment_id, $from);

				$view->update = $update_form;

				// Set up the selection lists
				$lists = array();
				$lists['url_valid'] = JHTML::_('select.booleanlist', 'url_valid',
											   'class="inputbox" title="' . JText::_('ATTACH_URL_IS_VALID_TOOLTIP') . '"',
											   $attachment->url_valid);

				// Set up publishing info
				$view->may_publish = $may_publish;
				if ( $may_publish ) {
					$default_state = $params->get('publish_default', false);
					$view->publish = JHTML::_('select.booleanlist', 'state', 'class="inputbox"', $attachment->state);
					}

				// Set up for editing the access level
				if ( $params->get('allow_frontend_access_editing', false) ) {
					require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
					$view->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', $attachment->access);
					$view->access_level_tooltip = JText::_('ATTACH_ACCESS_LEVEL_TOOLTIP');
					}

				$view->lists = $lists;
				$view->attachment = $attachment;
				}
			else {
				require_once(JPATH_COMPONENT_SITE.'/views/upload/view.html.php');
				$view = new AttachmentsViewUpload();
				AttachmentsHelper::add_view_urls($view, 'upload', $attachment->parent_id, $attachment->parent_type, null, $from);

				// Set up publishing info
				$view->may_publish = $may_publish;
				if ( $may_publish ) {
					$default_state = $params->get('publish_default', false);
					$view->publish = JHTML::_('select.booleanlist', 'state', 'class="inputbox"', $default_state);
					}

				// Set up for editing the access level
				if ( $params->get('allow_frontend_access_editing', false) ) {
					require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
					$view->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', null);
					$view->access_level_tooltip = JText::_('ATTACH_ACCESS_LEVEL_TOOLTIP');
					}

				$view->uri_type =		 $attachment->uri_type;
				$view->url =				 $attachment->url;
				$view->parent_id = $attachment->parent_id;
				$view->parent_type =		 $attachment->parent_type;
				$view->description =		 $attachment->description;
				$view->user_field_1 =	 $attachment->user_field_1;
				$view->user_field_2 =	 $attachment->user_field_2;
				$view->user_field_3 =	 $attachment->user_field_3;
				}

			// Suppress the display filename if we are changing from file to url
			$display_name = $attachment->display_name;
			if ( $update && (($update == 'file') || ($update != $attachment->uri_type)) ) {
				$display_name = '';
				}

			// Set up the view
			$view->parent_entity =	   $attachment->parent_entity;
			$view->parent_entity_name = $parent_entity_name;
			$view->parent_title =	   $parent->title;
			$view->new_parent = $parent->new;

			$view->display_name =	   $display_name;

			$view->params =		$params;

			$view->from =	$from;
			$view->Itemid = JRequest::getInt('Itemid', 1);

			$view->error = $result->error;
			$view->error_msg = $result->error_msg;

			// Display the view
			$view->display();
			exit();
			}

		// Clear out the display_name if the URL has changed
		$old_url = JRequest::getString('old_url');
		if ( $attachment->display_name && ( $attachment->url != $old_url ) ) {
			$old_display_name = JRequest::getString('old_display_name');
			if ( $old_display_name == $attachment->display_name ) {
				$attachment->display_name = '';
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
		if ( ($max_filename_length > 0) && (strlen($attachment->display_name) == 0) ) {
			if ( $attachment->filename ) {
				$attachment->display_name =
					AttachmentsHelper::truncate_filename($attachment->filename,
														 $max_filename_length);
				}
			else {
				$attachment->display_name =
					AttachmentsHelper::truncate_url($attachment->url,
													$max_filename_length);
				}
			}

		// Assume relative URLs are valid
		if ( $relative_url ) {
			$attachment->url_valid = true;
			}

		// If there is no filename, do something about it
		if ( !$attachment->filename && !$attachment->display_name ) {
			$attachment->display_name = $attachment->url;
			}

		// If the user is not authorised to change the state (eg, publish/unpublish),
		// ignore the form data and make sure the publish state is set correctly.
		if ( !$may_publish ) {
			$save_type = JString::strtolower(JRequest::getWord('save_type', 'update'));
			if ( $save_type == 'upload' ) {
				// Use the default publish state
				jimport('joomla.application.component.helper');
				$params = JComponentHelper::getParams('com_attachments');
				$attachment->state = $params->get('publish_default', false);
				}
			else {
				// Restore the old state
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->select('state')->from('#__attachments')->where('id = '.(int)$attachment->id);
				$db->setQuery($query, 0, 1);
				$old_state = $db->loadResult();
				if ( $db->getErrorNum() ) {
					$errmsg = $db->stderr() . ' (ERR 110)';
					JError::raiseError(500, $errmsg);
					}
				$attachment->state = $old_state;
				}
			}

		// Set the create/modify dates
		$now = JFactory::getDate();
		$attachment->created = $now->toMySQL();
		$attachment->modified = $attachment->created;
		$attachment->uri_type = 'url';

		// Save the updated attachment
		if (!$attachment->store()) {
			$errmsg = JText::_('ATTACH_ERROR_SAVING_URL_ATTACHMENT_RECORD') . $attachment->getError() . ' (ERR 111)';
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
			$msg = JText::_('ATTACH_ATTACHMENT_UPDATED');
			}
		else {
			$msg = JText::_('ATTACH_ATTACHMENT_SAVED');
			}

		return $msg;
	}


	/**
	 * Download an attachment (in secure mode)
	 *
	 * @param int $id the attachment id
	 */
	public static function download_attachment($id)
	{
		$user	= JFactory::getUser();
		$user_levels = implode(',', array_unique($user->authorisedLevels()));

		// Get the info about the attachment
		require_once(JPATH_COMPONENT_SITE.'/models/attachment.php');
		$model = new AttachmentsModelAttachment();
		$model->setId($id);
		$attachment = $model->getAttachment();
		if ( !$attachment ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 112)';
			JError::raiseError(500, $errmsg);
			}
		$parent_id = $attachment->parent_id;
		$parent_type = $attachment->parent_type;
		$parent_entity = $attachment->parent_entity;

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_UNKNOWN_PARENT_TYPE_S', $parent_type) . ' (ERR 113)';
			JError::raiseError(500, $errmsg);
			}
		$parent = $apm->getAttachmentsPlugin($parent_type);

		// Make sure that the user can access the attachment
		if ( !$parent->userMayAccessAttachment( $attachment ) ) {
			$errmsg = JText::_('ATTACH_ERROR_NO_PERMISSION_TO_DOWNLOAD') . ' (ERR 114)';
			JError::raiseError(500, $errmsg);
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Get the other info about the attachment
		$download_mode = $params->get('download_mode', 'attachment');
		$content_type = $attachment->file_type;
		$filename = $attachment->filename;
		$filename_sys = $attachment->filename_sys;

		// Make sure the file exists
		jimport('joomla.filesystem.file');
		if ( !JFile::exists($filename_sys) ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_FILE_S_NOT_FOUND_ON_SERVER', $filename) . ' (ERR 115)';
			JError::raiseError(500, $errmsg);
			}
		$len = filesize($filename_sys);

		// Update the download count
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->update('#__attachments')->set('download_count = (download_count + 1)');
		$query->where('id = ' .(int)$id);
		$db->setQuery($query);
		if ( !$db->query() ) {
			$errmsg = $db->stderr() . ' (ERR 116)';
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

		// Ensure UTF8 characters in filename are encoded correctly in IE
		$ISIE = preg_match( "/MSIE/", $_SERVER["HTTP_USER_AGENT"] );
		if ( $ISIE ) {
			$mod_filename = rawurlencode($mod_filename);
			}

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
	 * @param &object &$attachment the attachment object
	 * @param int $old_parent_id the id for the old parent
	 * @param int $new_parent_id the id for the new parent
	 * @param string $new_parent_type the new parent type (eg, 'com_content')
	 * @param string $new_parent_entity the new parent entity (eg, 'category')
	 *
	 * @return '' if successful, else an error message
	 */
	public static function switch_parent(&$attachment, $old_parent_id, $new_parent_id,
								  $new_parent_type=null, $new_parent_entity=null)
	{
		// Switch the parent as specified, renaming the file as necessary
		// Return success status

		if ( $attachment->uri_type == 'url' ) {
			// Do not need to do any file operations if this is a URL
			return '';
			}

		// Get the article/parent handler
		if ( $new_parent_type ) {
			$parent_type = $new_parent_type;
			$parent_entity = $new_parent_entity;
			}
		else {
			$parent_type = $attachment->parent_type;
			$parent_entity = $attachment->parent_entity;
			}
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_UNKNOWN_PARENT_TYPE_S', $parent_type) . ' (ERR 117)';
			JError::raiseError(500, $errmsg);
			}
		$parent = $apm->getAttachmentsPlugin($parent_type);

		// Set up the entity name for display
		$parent_entity = $parent->getCanonicalEntityId($parent_entity);
		$parent_entity_name = JText::_('ATTACH_' . $parent_entity);

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Define where the attachments move to
		$upload_url = AttachmentsDefines::$ATTACHMENTS_SUBDIR;
		$upload_dir = JPATH_SITE.'/'.$upload_url;

		// Figure out the new system filename
		$new_path = $parent->getAttachmentPath($parent_entity, $new_parent_id, null);
		$new_fullpath = $upload_dir.'/'.$new_path;

		// Make sure the new directory exists
		jimport('joomla.filesystem.folder');
		if ( !JFolder::create($new_fullpath) ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_UNABLE_TO_CREATE_DIR_S', $new_fullpath) . ' (ERR 118)';
			JError::raiseError(500, $errmsg);
			}

		// Construct the new filename and URL
		$old_filename_sys = $attachment->filename_sys;
		$new_filename_sys = $new_fullpath . $attachment->filename;
		$new_url = JString::str_ireplace(DS, '/', $upload_url . '/' . $new_path . $attachment->filename);

		// Rename the file
		jimport('joomla.filesystem.file');
		if ( JFile::exists($new_filename_sys) ) {
			return JText::sprintf('ATTACH_ERROR_CANNOT_SWITCH_PARENT_S_NEW_FILE_S_ALREADY_EXISTS',
								  $parent_entity_name, $attachment->filename);
			}
		if ( !JFile::move($old_filename_sys, $new_filename_sys) ) {
			$new_filename = $new_path . $attachment->filename;
			return JText::sprintf('ATTACH_ERROR_CANNOT_SWITCH_PARENT_S_RENAMING_FILE_S_FAILED',
								  $parent_entity_name, $new_filename);
			}
		AttachmentsHelper::write_empty_index_html($new_fullpath);

		// Save the changes to the attachment record immediately
		$attachment->parent_id = $new_parent_id;
		$attachment->filename_sys = $new_filename_sys;
		$attachment->url = $new_url;

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
	public static function attachmentsListHTML($parent_id, $parent_type, $parent_entity,
											   $user_can_add, $Itemid, $from,
											   $show_file_links=true, $allow_edit=true)
	{
		$app = JFactory::getApplication();

		$user	= JFactory::getUser();
		$user_levels = implode(',', array_unique($user->authorisedLevels()));

		// Generate the HTML for the attachments for the specified parent
		$alist = '';
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('count(*)')->from('#__attachments');
		$query->where('((parent_id='.(int)$parent_id . ') OR (parent_id is NULL))'.
					  ' AND parent_type=' . $db->quote($parent_type) .
					  ' AND parent_entity=' . $db->quote($parent_entity));
		$query->where('access in ('.$user_levels.')');
		$db->setQuery($query);
		$total = $db->loadResult();
		if ( $db->getErrorNum() ) {
			$errmsg = $db->stderr() . ' (ERR 119)';
			JError::raiseError(500, $errmsg);
			}

		if ( $total > 0 ) {

			// Get the component parameters
			jimport('joomla.application.component.helper');
			$params = JComponentHelper::getParams('com_attachments');

			// Check the security status
			$attach_dir = JPATH_SITE.'/'.AttachmentsDefines::$ATTACHMENTS_SUBDIR;
			$secure = $params->get('secure', false);
			$hta_filename = $attach_dir.'/.htaccess';
			if ( ($secure && !file_exists($hta_filename)) ||
				 (!$secure && file_exists($hta_filename)) ) {
				require_once(JPATH_SITE.'/components/com_attachments/helper.php');
				AttachmentsHelper::setup_upload_directory($attach_dir, $secure);
				}

			if ( $app->isAdmin() ) {
				// Get the html for the attachments list
				require_once(JPATH_ADMINISTRATOR.'/components/com_attachments/controllers/list.php');

				$controller = new AttachmentsControllerList();

				$alist = $controller->displayString($parent_id, $parent_type, $parent_entity,
													null, $show_file_links, $allow_edit, false, $from);
				}
			else {
				// Get the html for the attachments list
				require_once(JPATH_SITE.'/components/com_attachments/controllers/attachments.php');

				$controller = new AttachmentsControllerAttachments();

				$alist = $controller->displayString($parent_id, $parent_type, $parent_entity,
													null, $show_file_links, $allow_edit, false, $from);
				}

			}

		return $alist;
	}

}
