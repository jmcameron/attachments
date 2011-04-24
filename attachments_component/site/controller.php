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

jimport( 'joomla.application.component.controller' );

require_once(JPATH_COMPONENT.DS.'defines.php');


/**
 * The main attachments controller class (for the front end)
 *
 * @package Attachments
 */
class AttachmentsController extends JController
{

	/**
	 * Constructor
	 */
	function __construct( $default = array() )
	{
		parent::__construct( $default );
		// $this->registerTask('apply', 'save');
	}

	/** A noop function so this controller does not have a usable default */
	function noop()
	{
		$errmsg = JText::_('ERROR_NO_FUNCTION_SPECIFIED') . ' (ERR 51)';
		JError::raiseError(500, $errmsg);
	}

	/**
	 * Display a form for uploading a file/url
	 */
	function upload()
	{
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');

		// Get the parent info
		$parent_entity = 'default';
		if ( JRequest::getString('article_id') ) {
			$pid_info = explode(',', JRequest::getString('article_id'));
			$parent_type = 'com_content';
			}
		else {
			$pid_info = explode(',', JRequest::getString('parent_id'));
			$parent_type = AttachmentsController::_getCmd2('parent_type', 'com_content');

			// If the entity is embedded in the parent type, split them
			if ( strpos($parent_type, '.') ) {
				$parts = explode('.', $parent_type);
				$parent_type = $parts[0];
				$parent_entity = $parts[1];
				}
			}

		// Get the parent id
		$parent_id = null;
		if ( is_numeric($pid_info[0]) ) {
			$parent_id = (int)$pid_info[0];
			}

		// See if the parent is new (or already exists)
		$new_parent = false;
		if ( count($pid_info) > 1 ) {
			if ( $pid_info[1] == 'new' ) {
				$new_parent = true;
				}
			}

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 52)';
			JError::raiseError(500, $errmsg);
			}
		$parent =& $apm->getAttachmentsPlugin($parent_type);

		$parent_entity = $parent->getCanonicalEntityId($parent_entity);
		$parent_entity_name = JText::_($parent_entity);

		// Make sure this user can add attachments to this parent
		$user =& JFactory::getUser();
		if ( !$parent->userMayAddAttachment($parent_id, $parent_entity, $new_parent) ) {
			$errmsg = JText::sprintf('ERROR_NO_PERMISSION_TO_UPLOAD_S', $parent_entity_name) . ' (ERR 53)';
			JError::raiseError(500, $errmsg);
			}

		// Get the title of the parent
		$parent_title = '';
		if ( !$new_parent ) {
			$parent_title = $parent->getTitle($parent_id, $parent_entity);
			}

		// Use a different template for the iframe view
		$from = JRequest::getWord('from');
		$Itemid = JRequest::getInt('Itemid', 1);
		if ( $from == 'closeme') {
			JRequest::setVar('tmpl', 'component');
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Make sure the attachments directory exists
		$upload_subdir = $params->get('attachments_subdir', 'attachments');
		if ( $upload_subdir == '' ) {
			$upload_subdir = 'attachments';
			}
		$upload_dir = JPATH_BASE.DS.$upload_subdir;
		$secure = $params->get('secure', false);
		if ( !AttachmentsHelper::setup_upload_directory( $upload_dir, $secure ) ) {
			$errmsg = JText::sprintf('ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 54)';
			JError::raiseError(500, $errmsg);
			}

		// Determine the type of upload
		$default_uri_type = 'file';
		$uri_type = JRequest::getWord('uri', $default_uri_type);
		if ( !in_array( $uri_type, AttachmentsDefines::$LEGAL_URI_TYPES ) ) {
			// Make sure only legal values are entered
			$uri_type = 'file';
			}

		// Set up the view to redisplay the form with warnings
		require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'upload'.DS.'view.php');
		$view = new AttachmentsViewUpload();

		// Set up the view
		AttachmentsHelper::add_view_urls($view, 'upload', $parent_id, $parent_type, null, $from);

		$view->uri_type = 	  $uri_type;
		$view->url = 			  '';
		$view->parent_id = 		 $parent_id;
		$view->parent_type = 		 $parent_type;
		$view->parent_entity = 	 $parent_entity;
		$view->parent_entity_name = $parent_entity_name;
		$view->parent_title = $parent_title;
		$view->new_parent = 		 $new_parent;
		$view->description = 	  '';
		$view->display_name = 	  '';
		$view->user_field_1 = 	  '';
		$view->user_field_2 = 	  '';
		$view->user_field_3 = 	  '';
		$view->Itemid = 		  $Itemid;
		$view->from = 		  $from;
		$view->params = 		  $params;

		// Display the view
		$view->display(null, false, false);
	}


	/**
	 * Save a new or edited attachment
	 */
	function save()
	{
		// Check for request forgeries
		JRequest::checkToken() or die( 'Invalid Token');

		// Make sure that the user is logged in
		$user =& JFactory::getUser();
		if ( $user->get('username') == '' ) {
			$errmsg = JText::_('ERROR_MUST_BE_LOGGED_IN_TO_UPLOAD_ATTACHMENT') . ' (ERR 55)';
			JError::raiseError(500, $errmsg);
			}

		// Get the article/parent handler
		$new_parent = JRequest::getBool('new_parent', false);
		$parent_type = AttachmentsController::_getCmd2('parent_type', 'com_content');
		$parent_entity = JRequest::getCmd('parent_entity', 'default');
		JPluginHelper::importPlugin('attachments');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 56)';
			JError::raiseError(500, $errmsg);
			}
		$parent =& $apm->getAttachmentsPlugin($parent_type);

		$parent_entity = $parent->getCanonicalEntityId($parent_entity);
		$parent_entity_name = JText::_($parent_entity);

		// Make sure we have a valid parent ID
		$parent_id = JRequest::getInt('parent_id', -1);
		if ( !$new_parent and (($parent_id == 0) or
							   ($parent_id == -1) or
							   !$parent->parentExists($parent_id, $parent_entity)) ) {
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_S_ID_N',
									 $parent_entity_name , $parent_id) . ' (ERR 57)';
			JError::raiseError(500, $errmsg);
			}

		// Verify that this user may add attachments to this parent
		if ( !$parent->userMayAddAttachment($parent_id, $parent_entity, $new_parent) ) {
			$errmsg = JText::sprintf('ERROR_NO_PERMISSION_TO_UPLOAD_S', $parent_entity_name) . ' (ERR 58)';
			JError::raiseError(500, $errmsg);
			}

		// Get the Itemid
		$Itemid = JRequest::getInt('Itemid', 1);

		// How to redirect?
		$from = JRequest::getWord('from', 'closeme');
	    $uri = JFactory::getURI();
		if ( $from ) {
			if ( $from == 'frontpage' ) {
				$redirect_to = $uri->root(true);
				}
			elseif ( $from == 'article' ) {
				$redirect_to = JRoute::_("index.php?option=com_content&view=article&id=$parent_id", False);
				}
			else {
				$redirect_to = $uri->root(true);
				}
			}
		else {
			$redirect_to = $uri->root(true);
			}

		// See if we should cancel
		if ( $_POST['submit'] == JText::_('CANCEL') ) {
			$msg = JText::_('UPLOAD_CANCELED');
			$this->setRedirect( $redirect_to, $msg );
			return;
			}

		// Figure out if we are uploading or updating
		$save_type = JString::strtolower(JRequest::getWord('save_type'));
		if ( !in_array($save_type, AttachmentsDefines::$LEGAL_SAVE_TYPES) ) {
			$errmsg = JText::_('ERROR_INVALID_SAVE_PARAMETERS') . ' (ERR 59)';
			JError::raiseError(500, $errmsg);
			}

		// If this is an update, get the attachment id
		$attachment_id = false;
		if ( $save_type == 'update' ) {
			$attachment_id = JRequest::getInt('id');
			}

		// Bind the info from the form
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'tables');
		$row =& JTable::getInstance('Attachment', 'AttachmentsTable');
		if ( $attachment_id AND !$row->load($attachment_id) ) {
			$errmsg = JText::sprintf('ERROR_CANNOT_UPDATE_ATTACHMENT_INVALID_ID_N', $id) . ' (ERR 60)';
			JError::raiseError(500, $errmsg);
			}
		if (!$row->bind(JRequest::get('post'))) {
			$errmsg = $row->getError() . ' (ERR 61)';
			JError::raiseError(500, $errmsg);
			}

		// Note what the old uri type is, if updating
		$old_uri_type = null;
		if ( $save_type	 == 'update' ) {
			$old_uri_type = $row->uri_type;
			}

		// Figure out what the new URI is
		if ( $save_type == 'upload' ) {
			// See if we are uploading a file or URL
			$new_uri_type = JRequest::getWord('uri_type');
			if ( $new_uri_type AND !in_array( $new_uri_type, AttachmentsDefines::$LEGAL_URI_TYPES ) ) {
				// Make sure only legal values are entered
				$new_uri_type = '';
				}
			}

		elseif ( $save_type == 'update' ) {
			// See if we are updating a file or URL
			$new_uri_type = JRequest::getWord('update');
			if ( $new_uri_type AND !in_array( $new_uri_type, AttachmentsDefines::$LEGAL_URI_TYPES ) ) {
				// Make sure only legal values are entered
				$new_uri_type = '';
				}

			// Since URLs can be edited, we always evaluate them from scratch
			if ( ($new_uri_type == '') AND ($old_uri_type == 'url') ) {
				$new_uri_type = 'url';
				}

			// Double-check to see if the URL changed
			$old_url = JRequest::getString('old_url');
			if ( !$new_uri_type AND $old_url AND $old_url != $row->url ) {
				$new_uri_type = 'url';
				}
			}

		// Get more info about the type of upload/update
		$verify_url = false;
		$relative_url = false;
		if ( $new_uri_type == 'url' ) {
			if ( JRequest::getWord('verify_url') == 'verify' ) {
				$verify_url = true;
				}
			if ( JRequest::getWord('relative_url') == 'relative' ) {
				$relative_url = true;
				}
			}

		// Handle the various ways this function might get invoked
		if ( $save_type == 'upload' ) {
			$row->uploader_id = $user->get('id');
			$row->parent_id = $parent_id;
			}

		// Set up a couple of items that the upload function may need
		$parent->new = $new_parent;
		if ( $new_parent ) {
			$row->parent_id = null;
			$parent->title = '';
			}
		else {
			$row->parent_id = $parent_id;
			$parent->title = $parent->getTitle($parent_id, $parent_entity);
			}

		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');

		// Upload new file/url and create/update the attachment
		if ( $new_uri_type == 'file' ) {

			// Upload a new file
			$msg = AttachmentsHelper::upload_file($row, $parent, $attachment_id, $save_type);
			// NOTE: store() is not needed if upload_file() is called since it does it
			}

		elseif ( $new_uri_type == 'url' ) {

			// Upload/add the new URL
			$msg = AttachmentsHelper::add_url($row, $parent, $verify_url, $relative_url,
											  $old_uri_type, $attachment_id);
			// NOTE: store() is not needed if upload_url() is called since it does it
			}

		else {

			// Save the updated attachment info
			if (!$row->store()) {
				$errmsg = $row->getError() . ' (ERR 62)';
				JError::raiseError(500, $errmsg);
				}

			$lang =&  JFactory::getLanguage();
			$lang->load('com_attachments', JPATH_SITE);

			$msg = JText::_('ATTACHMENT_UPDATED');
			}

		// If we are supposed to close this iframe, do it now.
		$known_froms = array('frontpage', 'article', 'editor', 'details', 'closeme', 'featured');
		if ( in_array( $from, $known_froms ) ) {

			// If there is no parent_id, the parent is being created, use the username instead
			if ( $new_parent ) {
				$pid = 0;
				}
			else {
				$pid = (int)$parent_id;
				}

			// Close the iframe and refresh the attachments list in the parent window
			$base_url = $uri->root(true);
			echo "<script type=\"text/javascript\">
               var fn = window.parent.refreshAttachments;
			   window.parent.SqueezeBox.close();
			   fn(\"$base_url\",\"$parent_type\",\"$parent_entity\",$pid,\"$from\");
			</script>";
			exit();
			}

		$this->setRedirect( $redirect_to, $msg );
	}


	/**
	 * Download a file
	 */
	function download()
	{

		// Get the attachment ID
		$id = JRequest::getInt('id');
		if ( !is_numeric($id) ) {
			$errmsg = JText::sprintf('ERROR_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 63)';
			JError::raiseError(500, $errmsg);
			}

		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');

		AttachmentsHelper::download_attachment($id);
	}

	/**
	 * Display a page requesting that the user log in.
	 *
	 * This function is invoked if the user tries to access an attachment in
	 * secure mode and they are not logged in (and 'who can see' is not set to
	 * 'anyone').
	 */
	function request_login()
	{
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
		$uri = JFactory::getURI();

		// Add CSS for styling
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css' );

		// Get the component parameters for the registration URL
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');
		$url = $params->get('register_url', "index.php?option=com_user&task=register");
		$url = JRoute::_($url);

		// Deal with RTL styling
		$lang =& JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments_rtl.css' );
			}

		// Get a phrase from the login module
		$lang->load('mod_login');
		$register = JText::_('CREATE_AN_ACCOUNT');

		// Set up the phrases/refs
		$must_be_logged_in = JText::_('ERROR_MUST_BE_LOGGED_IN_TO_DOWNLOAD_ATTACHMENT');
		$ref = "<a href=\"$url\">$register</a>";

		// Output the HTML
		echo '<div class="requestLogin">';
		echo "<h1>$must_be_logged_in</h1>";
		echo "<h2>".JText::sprintf('REGISTER_HERE', $ref)."</h2>";
		echo '</div>';
	}


	/**
	 * Delete an attachment
	 */
	function delete()
	{
		$db =& JFactory::getDBO();

		// Verify the user is logged in
		$user =& JFactory::getUser();
		if ( $user->get('username') == '' ) {
			$errmsg = JText::_('ERROR_MUST_BE_LOGGED_IN_TO_DELETE_ATTACHMENT') . ' (ERR 64)';
			JError::raiseError(500, $errmsg);
			}

		// Make sure we have a valid attachment ID
		$id = JRequest::getInt( 'id');
		if ( is_numeric($id) ) {
			$id = (int)$id;
			}
		else {
			$errmsg = JText::sprintf('ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 65)';
			JError::raiseError(500, $errmsg);
			}
		$query = "SELECT * FROM #__attachments WHERE id='".(int)$id."'";
		$db->setQuery($query);
		// ??? Convert to use model
		$rows = $db->loadObjectList();
		if (count($rows) != 1) {
			$errmsg = JText::sprintf('ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 66)';
			JError::raiseError(500, $errmsg);
			}
		$filename_sys  = $rows[0]->filename_sys;
		$filename	   = $rows[0]->filename;
		$parent_id	   = $rows[0]->parent_id;
		$parent_type   = $rows[0]->parent_type;
		$parent_entity = $rows[0]->parent_entity;

		// Get the article/parent handler
		JPluginHelper::importPlugin('attachments');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 68)';
			JError::raiseError(500, $errmsg);
			}
		$parent =& $apm->getAttachmentsPlugin($parent_type);

		$parent_entity_name = JText::_($parent_entity);

		// Make sure the parent exists
		// NOTE: $parent_id===null means the parent is being created
		if ( $parent_id !== null AND !$parent->parentExists($parent_id, $parent_entity) ) {
			$errmsg = JText::sprintf('ERROR_CANNOT_DELETE_INVALID_S_ID_N',
									 $parent_entity_name, $parent_id) . ' (ERR 69)';
			JError::raiseError(500, $errmsg);
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// See if this user can edit (or delete) the attachment
		if ( !$parent->userMayEditAttachment($rows[0], $parent_id, $params) ) {
			$errmsg = JText::sprintf('ERROR_NO_PERMISSION_TO_DELETE_S', $parent_entity_name) . ' (ERR 70)';
			JError::raiseError(500, $errmsg);
			}

		// First delete the actual attachment files (if any)
		if ( $filename_sys ) {
			jimport('joomla.filesystem.file');
			if ( JFile::exists( $filename_sys )) {
				JFile::delete($filename_sys);
				}
			}

		// Delete the entries in the attachments table
		$query = "DELETE FROM #__attachments WHERE id='".(int)$id."' LIMIT 1";
		$db->setQuery($query);
		if (!$db->query()) {
			$errmsg = $db->getErrorMsg() . ' (ERR 71)';
			JError::raiseError(500, $errmsg);
			}

		// Clean up after ourselves
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php'); 
		AttachmentsHelper::clean_directory($filename_sys);

		// Get the Itemid
		$Itemid = JRequest::getInt( 'Itemid', 1);

		$msg = JText::_('DELETED_ATTACHMENT') . " '$filename'";
		
		// Figure out how to redirect
		$from = JRequest::getWord('from', 'closeme');
		$known_froms = array('frontpage', 'article', 'details', 'closeme', 'featured');
	    $uri = JFactory::getURI();
		if ( in_array( $from, $known_froms ) ) {

			// If there is no parent_id, the parent is being created, use the username instead
			if ( !$parent_id ) {
				$pid = 0;
				}
			else {
				$pid = (int)$parent_id;
				}

			// Close the iframe and refresh the attachments list in the parent window
			$base_url = $uri->root(true);
			echo "<script type=\"text/javascript\">
               var fn = window.parent.refreshAttachments;
			   window.parent.SqueezeBox.close();
			   fn(\"$base_url\",\"$parent_type\",\"$parent_entity\",$pid,\"$from\");
			</script>";
			exit();
			
			}
		else {
			$redirect_to = $uri->root(true);
			}

		$this->setRedirect( $redirect_to, $msg );
	}


	/**
	 * Show the warning for deleting an attachment
	 */
	function delete_warning()
	{
		global $option;

		// Meant to be shown in the iframe popup
		$document =&  JFactory::getDocument();
		$uri = JFactory::getURI();
		JHTML::_('behavior.mootools');

		// Add the regular css file
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css' );

		// Handle the RTL styling
		$lang =& JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments_rtl.css' );
			}

		// ??? Not sure if this fix is still necessary
		$document->addStyleDeclaration(
			'div.componentheading { display: none; } * { overflow: hidden; };');

		// Make sure we have a valid attachment ID
		$id = JRequest::getInt('id');
		if ( is_numeric($id) ) {
			$id = (int)$id;
			}
		else {
			$errmsg = JText::sprintf('ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 72)';
			JError::raiseError(500, $errmsg);
			}

		// Get the attachment record
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'tables');
		$attachment =& JTable::getInstance('Attachment', 'AttachmentsTable');
		if ( !$attachment->load($id) ) {
			$errmsg = JText::sprintf('ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 73)';
			JError::raiseError(500, $errmsg);
			}

		// Set up the URL
		$from = JRequest::getWord('from', 'closeme');
		$delete_url = "index.php?option=com_attachments&task=delete&id=$id";
		$delete_url .= "&from=$from";
		$delete_url = JRoute::_($delete_url);
?>
		<div class="deleteWarning">
			 <h1><?php echo JText::_('WARNING'); ?></h1>
			 <h2 id="warning_msg"><?php echo JText::_('REALLY_DELETE_ATTACHMENT'); ?><br />
			 (<?php if ( $attachment->uri_type == 'file' ) {
				 echo " " . $attachment->filename . " ";
				 }
			 else {
				 echo $attachment->url;
				 } ?>)</h2>
		  <form action="<?php echo $delete_url; ?>" name="warning_form" method="post">
			<div align="center">
			    <span class="left">&nbsp;</span>
			    <input type="submit" name="submit" value="<?php echo JText::_('DELETE'); ?>" />
				<span class="right">
				  <input type="button" name="cancel" value="<?php echo JText::_('CANCEL'); ?>"
						 onClick="window.parent.SqueezeBox.close();" />
				</span>
			</div>
		  </form>
		 </div>
<?php

	}


	/**
	 * Display a form for updating/editing an attachment
	 */
	function update()
	{
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
		// Call with: index.php?option=com_attachments&task=update&id=1&tmpl=component
		//		  or: component/attachments/update/id/1/tmpl/component

		// Make sure we have a valid attachment ID
		$id = JRequest::getInt( 'id');
		if ( is_numeric($id) ) {
			$id = (int)$id;
			}
		else {
			$errmsg = JText::sprintf('ERROR_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 74)';
			JError::raiseError(500, $errmsg);
			}

		// Get the attachment record (??? convert to model)
		JTable::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'tables');
		$attachment =& JTable::getInstance('Attachment', 'AttachmentsTable');
		if ( !$attachment->load($id) ) {
			$errmsg = JText::sprintf('ERROR_CANNOT_UPDATE_ATTACHMENT_INVALID_ID_N', $id) . ' (ERR 75)';
			JError::raiseError(500, $errmsg);
			}

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Get the article/parent handler
		$parent_type = $attachment->parent_type;
		$parent_entity = $attachment->parent_entity;
		JPluginHelper::importPlugin('attachments');
		$apm =& getAttachmentsPluginManager();
		if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
			$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 76)';
			JError::raiseError(500, $errmsg);
			}
		$parent =& $apm->getAttachmentsPlugin($parent_type);

		// Set up the entity name for display
		$parent_entity_name = JText::_($parent_entity);

		// Verify that this user may add attachments to this parent
		$user =& JFactory::getUser();
		$parent_id = $attachment->parent_id;
		$new_parent = false;
		if ( $parent_id === null ) {
			$parent_id = 0;
			$new_parent = true;
			}
		$parent_title = $parent->getTitle($parent_id, $parent_entity);
		if ( !$parent->userMayEditAttachment($attachment, $parent_id, $params ) ) {
			$errmsg = JText::sprintf('ERROR_NO_PERMISSION_TO_UPLOAD_S', $parent_entity_name) . ' (ERR 77)';
			JError::raiseError(500, $errmsg);
			}

		// Make sure the attachments directory exists
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
		$upload_subdir = $params->get('attachments_subdir', 'attachments');
		if ( $upload_subdir == '' ) {
			$upload_subdir = 'attachments';
			}
		$upload_dir = JPATH_BASE.DS.$upload_subdir;
		$secure = $params->get('secure', false);
		if ( !AttachmentsHelper::setup_upload_directory( $upload_dir, $secure ) ) {
			$errmsg = JText::sprintf('ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 78)';
			JError::raiseError(500, $errmsg);
			}

		// Make sure the update parameter is legal
		$update = JRequest::getWord('update');
		if ( $update AND !in_array($update, AttachmentsDefines::$LEGAL_URI_TYPES) ) {
			$update = false;
			}

		// Set up the selection lists
		$lists = array();
		$lists['url_valid'] = JHTML::_('select.booleanlist', 'url_valid',
									   'class="inputbox" title="' . JText::_('URL_IS_VALID_TOOLTIP') . '"',
									   $attachment->url_valid);

		// Suppress the display filename if we are switching from file to url
		$display_name = $attachment->display_name;
		if ( $update AND (($update == 'file') OR ($update != $attachment->uri_type)) ) {
			$display_name = '';
			}

		// Set up the view
		require_once(JPATH_COMPONENT_SITE.DS.'views'.DS.'update'.DS.'view.php');
		$view = new AttachmentsViewUpdate();
		$from = JRequest::getWord('from', 'closeme');
		AttachmentsHelper::add_view_urls($view, 'update', $parent_id,
										 $attachment->parent_type, $id, $from);

		$view->update = 				$update;
		$view->new_parent = 			$new_parent;
		$view->parent_title = 		$parent_title;
		$view->parent_entity = 		$parent_entity;
		$view->parent_entity_name = $parent_entity_name;
		$view->display_name = 		$display_name;

		$view->lists = 	   $lists;
		$view->params = 	   $params;
		$view->attachment = $attachment;

		$view->from = 		   $from;
		$view->Itemid = 		   JRequest::getInt('Itemid', 1);

		$view->display(null, false, false);
	}


	/**
	 * Return the attachments list as HTML (for use by Ajax)
	 */
	function attachmentsList()
	{
		$parent_id = JRequest::getInt('parent_id', false);
		$parent_type = JRequest::getWord('parent_type', '');
		$parent_entity = JRequest::getWord('parent_entity', 'default');
		$show_links = JRequest::getBool('show_links', true);
		$allow_edit = JRequest::getBool('allow_edit', true);
		$from = JRequest::getWord('from', 'closeme');
		$title = '';

		$response = '';

		if ( ($parent_id === false) OR ($parent_type == '') ) {
			return '';
			}
			
		require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'controllers'.DS.'attachments.php');
		$controller = new AttachmentsControllerAttachments();
		$response = $controller->display($parent_id, $parent_type, $parent_entity,
										 $title, $show_links, $allow_edit, false, $from);
		echo $response;
	}


	/**
	 * Show a warning (that has previously been saved via the
	 * AttachmentsHelper::save_warning_message() function.
	 */
	function warning()
	{
		$document =&  JFactory::getDocument();
	    $uri = JFactory::getURI();

		// Add the stylesheet
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css' );

		// Handle the RTL styling
		$lang =& JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments_rtl.css' );
			}

		// ??? Not sure if this is still necessary
		$document->addStyleDeclaration(
			'div.componentheading { display: none; } * { overflow: hidden; };');

		// Issue the warning
		echo '<div class="warning"><h1>' . JText::_('WARNING') . '</h1>';
		echo '<h2 id="warning_msg">';
		echo '<script>document.write(parent.document.warning_msg);</script>';
		echo '</h2></div>';
	}



	/**
	 * Filter out the request - Like JRequest::getCmd() but allows colons
	 *
	 * @param string $name name of the item to get from the request
	 *
	 * @return the filtered string
	 */
	function _getCmd2($name, $default='')
	{
		$source = JRequest::getString($name, $default);
		return (string) preg_replace( '/[^A-Z0-9_\.:-]/i', '', $source );
	}
}

?>
