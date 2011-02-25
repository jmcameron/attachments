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

// No direct access
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * View for the uploads
 *
 * @package Attachments
 */
class AttachmentsViewUpload extends JView
{
	var $_legal_uri_types = array('file', 'url');

	/**
	 * Display the view
	 */
	function display($tpl=null, $error=false, $error_msg=false)
	{
		// Add javascript
		$app = JFactory::getApplication();
		$doc =& JFactory::getDocument();
		$uri = JFactory::getURI();
		$doc->addScript( $uri->root(true) . '/plugins/content/attachments/attachments_refresh.js' );
		
		$parent_id = $this->parent_id;
		if ( $parent_id === null ) {
			$parent_id = 0;
			}
		$parent_type = $this->parent_type;
		$parent_entity = $this->parent_entity;

		$uri_type = $this->uri_type;

		// Use a component template for the iframe view
		$from = JRequest::getWord('from');
		if ( $from == 'closeme' ) {
			JRequest::setVar('tmpl', 'component');
			}

		// Set up to toggle between uploading file/urls
		if ( $uri_type == 'file' ) {
			$upload_toggle_button_text = JText::_('ENTER_URL_INSTEAD');
			$upload_toggle_url = $this->upload_url_url;
			$upload_button_text = JText::_('UPLOAD_VERB');
			}
		else {
			$upload_toggle_button_text = JText::_('SELECT_FILE_TO_UPLOAD_INSTEAD');
			$upload_toggle_url = $this->upload_file_url;
			$upload_button_text = JText::_('ADD_URL');
			}

		// If this is for an existing content item, modify the URL appropriately
		if ( $this->new_parent ) {
			$upload_toggle_url .= "&amp;parent_id=0,new";
			}
		if ( JRequest::getWord('editor') ) {
			$upload_toggle_url .= "&amp;editor=" . JRequest::getWord('editor');
			}

		// Needed URLs
		$save_url = JRoute::_($this->save_url);
		$base_url = $uri->root(true) . '/';
	
		$params = $this->params;

		// Prepare for error displays
		$upload_id = 'upload';
		switch ( $error ) {
		case 'no_file':
			$upload_id = 'upload_warning';
			break;

		case 'file_too_big':
			$upload_id = 'upload_warning';
			break;

		case 'file_already_on_server':
			$upload_id = 'upload_warning';
			break;
			}

		// If this is an error re-display, display the CSS links directly
		$echo_css = $error;

		// Add the stylesheets
		require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/templates/system/css/system.css', $echo_css );
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/templates/system/css/general.css', $echo_css );
		AttachmentsHelper::addStyleSheet(
			$uri->root(true) . '/templates/' . $app->getTemplate() . '/css/template.css', $echo_css );
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css', $echo_css );
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments2.css', $echo_css );
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/media/system/css/modal.css', $echo_css );

		// Handle RTL styling
		$lang =& JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments_rtl.css', $echo_css );
			}

		// Display the form
		?>
		<div class="uploadAttachmentsPage">
	<h1><?php echo JText::sprintf('FOR_PARENT_S_COLON_S', $this->parent_entity_name, $this->parent_title) ?></h1>
		<form class="attachments" enctype="multipart/form-data" name="upload_form"
			  action="<?php echo $this->save_url; ?>" method="post">
			<fieldset>
				<legend><?php echo JText::_('UPLOAD_ATTACHMENT'); ?></legend>
				<?php if ( $error_msg ): ?>
				<div class="formWarning" id="formWarning"><?php echo $error_msg; ?></div>
				<?php endif; ?>
<?php if ( $uri_type == 'file' ): ?>
				<p><label for="<?php echo $upload_id ?>"><b><?php
			  echo JText::_('ATTACH_FILE_COLON') ?></b></label>
			   <a class="changeButton" href="<?php echo $upload_toggle_url ?>"><?php
				  echo $upload_toggle_button_text;?></a></p>
				<p><input type="file" name="upload" id="<?php echo $upload_id; ?>"
					  size="60" maxlength="512" /></p>
				<p class="display_name"><label for="display_name"
					  title="<?php echo JText::_('DISPLAY_FILENAME_TOOLTIP'); ?>"
					  ><b><?php echo JText::_('DISPLAY_FILENAME_OPTIONAL_COLON'); ?></b></label>
				   <input type="text" name="display_name" id="display_name"
					  size="70" maxlength="80"
					  title="<?php echo JText::_('DISPLAY_FILENAME_TOOLTIP'); ?>"
					  value="<?php echo $this->display_name; ?>" /></p>
<?php else: ?>
				<p><label for="<?php echo $upload_id ?>"><b><?php
			  echo JText::_('ENTER_URL_COLON') ?></b></label>
			   &nbsp;&nbsp;&nbsp;&nbsp;
				   <label for="verify_url"><b><?php echo JText::_('VERIFY_URL_EXISTENCE') ?></b></label>
		   <input type="checkbox" name="verify_url" value="verify" checked
						  title="<?php echo JText::_('VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
		   &nbsp;&nbsp;&nbsp;&nbsp;
				   <label for="relative_url"><b><?php echo JText::_('RELATIVE_URL') ?></b></label>
		   <input type="checkbox" name="relative_url" value="relative"
			  title="<?php echo JText::_('RELATIVE_URL_TOOLTIP'); ?>" />
			   <a class="changeButton" href="<?php echo $upload_toggle_url ?>"><?php
				  echo $upload_toggle_button_text;?></a><br />
				   <input type="text" name="url" id="<?php echo $upload_id; ?>"
					  size="80" maxlength="255" title="<?php echo JText::_('ENTER_URL_TOOLTIP'); ?>"
					  value="<?php echo $this->url; ?>" /><br /><?php
					  echo JText::_('NOTE_ENTER_URL_WITH_HTTP'); ?></p>
				<p class="display_name"><label for="display_name"
					  title="<?php echo JText::_('DISPLAY_URL_TOOLTIP'); ?>"
					  ><b><?php echo JText::_('DISPLAY_URL_COLON'); ?></b></label>
				   <input type="text" name="display_name" id="display_name"
					  size="70" maxlength="80"
					  title="<?php echo JText::_('DISPLAY_URL_TOOLTIP'); ?>"
					  value="<?php echo $this->display_name; ?>" /></p>
<?php endif; ?>
				<p><label for="description"><b><?php echo JText::_('DESCRIPTION_COLON'); ?></b></label>
				   <input type="text" name="description" id="description"
							  size="70" maxlength="255"
					  value="<?php echo $this->description; ?>" /></p>
				<?php if ( $params->get('user_field_1_name', false) ): ?>
				<p><label for="user_field_1"><b><?php echo $params->get('user_field_1_name'); ?>:</b></label>
				   <input type="text" name="user_field_1" id="user_field_1" size="70" maxlength="100"
					  value="<?php echo $this->user_field_1; ?>" /></p>
				<?php endif; ?>
				<?php if ( $params->get('user_field_2_name', false) ): ?>
				<p><label for="user_field_2"><b><?php echo $params->get('user_field_2_name'); ?>:</b></label>
				   <input type="text" name="user_field_2" id="user_field_2" size="70" maxlength="100"
						  value="<?php echo $this->user_field_2; ?>" /></p>
				<?php endif; ?>
				<?php if ( $params->get('user_field_3_name', false) ): ?>
				<p><label for="user_field_3"><b><?php echo $params->get('user_field_3_name'); ?>:</b></label>
				   <input type="text" name="user_field_3" id="user_field_3" size="70" maxlength="100"
						  value="<?php echo $this->user_field_3; ?>" /></p>
				<?php endif; ?>

			</fieldset>
			<input type="hidden" name="MAX_FILE_SIZE" value="524288" />
			<input type="hidden" name="submitted" value="TRUE" />
			<input type="hidden" name="save_type" value="upload" />
			<input type="hidden" name="uri_type" value="<?php echo $uri_type; ?>" />
			<input type="hidden" name="update_file" value="TRUE" />
			<input type="hidden" name="parent_id" value="<?php echo $parent_id; ?>" />
			<input type="hidden" name="parent_type" value="<?php echo $parent_type; ?>" />
			<input type="hidden" name="parent_entity" value="<?php echo $parent_entity; ?>" />
			<input type="hidden" name="new_parent" value="<?php echo $this->new_parent; ?>" />
			<input type="hidden" name="from" value="<?php echo $this->from; ?>" />
			<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
			<?php echo JHTML::_( 'form.token' ); ?>

			<div align="center">
				<input type="submit" name="submit" value="<?php echo $upload_button_text ?>" />
				<span class="right">
				  <input type="button" name="cancel" value="<?php echo JText::_('CANCEL'); ?>"
						 onClick="window.parent.document.getElementById('sbox-window').close();" />
				</span>
			</div>
		</form>
		<?php

		// Display the auto-publish warning, if appropriate
		if ( ! $params->get('publish_default', false) ) {
			$msg = $params->get('auto_publish_warning', '');
			if ( JString::strlen($msg) == 0 ) {
				$msg = 'WARNING_ADMIN_MUST_PUBLISH';
				}
			$msg = JText::_($msg);
			echo "<h2>$msg</h2>";
			}

		// Show the existing attachments (if any)
		if ( $parent_id OR ($parent_id === 0) ) {
			require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.
						 'controllers'.DS.'attachments.php');
			$controller = new AttachmentsControllerAttachments();
			$controller->display($parent_id, $parent_type, $parent_entity,
								 'EXISTING_ATTACHMENTS',
								 false, false, true, $this->from);
			}
		
		?>
	</div>
<?php

	}
}

?>
