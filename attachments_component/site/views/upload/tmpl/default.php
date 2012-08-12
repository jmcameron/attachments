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

// No direct access
defined('_JEXEC') or die('Restricted access');

// Add the plugins stylesheet to style the list of attachments
$document = JFactory::getDocument();
$app = JFactory::getApplication();
$uri = JFactory::getURI();

// Add javascript
JHTML::_('behavior.mootools');
$document->addScript( $uri->root(true) . '/plugins/content/attachments/attachments_refresh.js' );

$parent_id = $this->parent_id;
if ( $parent_id === null ) {
	$parent_id = 0;
	}
$parent_type = $this->parent_type;
$parent_entity = $this->parent_entity;

$uri_type = $this->uri_type;

$params = $this->params;

// // Use a component template for the iframe view
// $from = JRequest::getWord('from');
// if ( $from == 'closeme' ) {
//	JRequest::setVar('tmpl', 'component');
//	}

// Set up to toggle between uploading file/urls
if ( $uri_type == 'file' ) {
	$upload_toggle_button_text = JText::_('ATTACH_ENTER_URL_INSTEAD');
	$upload_toggle_url = $this->upload_url_url;
	$upload_button_text = JText::_('ATTACH_UPLOAD_VERB');
	}
else {
	$upload_toggle_button_text = JText::_('ATTACH_SELECT_FILE_TO_UPLOAD_INSTEAD');
	$upload_toggle_url = $this->upload_file_url;
	$upload_button_text = JText::_('ATTACH_ADD_URL');
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

// Prepare for error displays
$upload_id = 'upload';
switch ( $this->error ) {
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
$echo_css = $this->error;

JHTML::_('behavior.mootools');

/** Load the Attachments helper */
require_once(JPATH_COMPONENT_SITE.'/helper.php');

// Add the stylesheets
AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css', $echo_css );
AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments2.css', $echo_css );

// Handle RTL styling
$lang = JFactory::getLanguage();
if ( $lang->isRTL() ) {
	AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments_rtl.css', $echo_css );
	}

// Display the form
?>
<div id="uploadAttachmentsPage">
<h1><?php echo JText::sprintf('ATTACH_FOR_PARENT_S_COLON_S', $this->parent_entity_name, $this->parent_title) ?></h1>
	<form class="attachments" enctype="multipart/form-data" name="upload_form"
		  action="<?php echo $this->save_url; ?>" method="post">
		<fieldset>
			<legend><?php echo JText::_('ATTACH_UPLOAD_ATTACHMENT'); ?></legend>
			<?php if ( $this->error_msg ): ?>
			<div class="formWarning" id="formWarning"><?php echo $this->error_msg; ?></div>
			<?php endif; ?>
<?php if ( $uri_type == 'file' ): ?>
			<p><label for="<?php echo $upload_id ?>"><?php
		  echo JText::_('ATTACH_ATTACH_FILE_COLON') ?></label>
		   <a class="changeButton" href="<?php echo $upload_toggle_url ?>"><?php
			  echo $upload_toggle_button_text;?></a></p>
			<p><input type="file" name="upload" id="<?php echo $upload_id; ?>"
				  size="80" maxlength="512" /></p>
			<p class="display_name"><label for="display_name"
				  title="<?php echo JText::_('ATTACH_DISPLAY_FILENAME_TOOLTIP'); ?>"
				  ><?php echo JText::_('ATTACH_DISPLAY_FILENAME_OPTIONAL_COLON'); ?></label>
			   <input type="text" name="display_name" id="display_name"
				  size="70" maxlength="80"
				  title="<?php echo JText::_('ATTACH_DISPLAY_FILENAME_TOOLTIP'); ?>"
				  value="<?php echo $this->display_name; ?>" /></p>
<?php else: ?>
			<p><label for="<?php echo $upload_id ?>"><?php
		  echo JText::_('ATTACH_ENTER_URL') ?></label>
		   &nbsp;&nbsp;&nbsp;&nbsp;
			   <label for="verify_url"><?php echo JText::_('ATTACH_VERIFY_URL_EXISTENCE') ?></label>
	   <input type="checkbox" name="verify_url" value="verify" checked
					  title="<?php echo JText::_('ATTACH_VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
	   &nbsp;&nbsp;&nbsp;&nbsp;
			   <label for="relative_url"><?php echo JText::_('ATTACH_RELATIVE_URL') ?></label>
	   <input type="checkbox" name="relative_url" value="relative"
		  title="<?php echo JText::_('ATTACH_RELATIVE_URL_TOOLTIP'); ?>" />
		   <a class="changeButton" href="<?php echo $upload_toggle_url ?>"><?php
			  echo $upload_toggle_button_text;?></a><br />
			   <input type="text" name="url" id="<?php echo $upload_id; ?>"
				  size="80" maxlength="255" title="<?php echo JText::_('ATTACH_ENTER_URL_TOOLTIP'); ?>"
				  value="<?php echo $this->url; ?>" /><br /><?php
				  echo JText::_('ATTACH_NOTE_ENTER_URL_WITH_HTTP'); ?></p>
			<p class="display_name"><label for="display_name"
				  title="<?php echo JText::_('ATTACH_DISPLAY_URL_TOOLTIP'); ?>"
				  ><?php echo JText::_('ATTACH_DISPLAY_URL_COLON'); ?></label>
			   <input type="text" name="display_name" id="display_name"
				  size="70" maxlength="80"
				  title="<?php echo JText::_('ATTACH_DISPLAY_URL_TOOLTIP'); ?>"
				  value="<?php echo $this->display_name; ?>" /></p>
<?php endif; ?>
			<p><label for="description"><?php echo JText::_('ATTACH_DESCRIPTION_COLON'); ?></label>
			   <input type="text" name="description" id="description"
						  size="70" maxlength="255"
				  value="<?php echo $this->description; ?>" /></p>
<?php if ( $this->may_publish ): ?>
			<p><label><?php echo JText::_('ATTACH_PUBLISHED'); ?></label><?php echo $this->publish; ?></p>
<?php endif; ?>
<?php if ( $params->get('allow_frontend_access_editing', false) ): ?>
			<p><label for="access" title="<?php echo $this->access_level_tooltip; ?>"><? echo JText::_('ATTACH_ACCESS_COLON'); ?></label><?php echo $this->access_level; ?></p>
<?php endif; ?>
			<?php if ( $params->get('user_field_1_name', false) ): ?>
			<p><label for="user_field_1"><?php echo $params->get('user_field_1_name'); ?>:</label>
			   <input type="text" name="user_field_1" id="user_field_1" size="70" maxlength="100"
				  value="<?php echo $this->user_field_1; ?>" /></p>
			<?php endif; ?>
			<?php if ( $params->get('user_field_2_name', false) ): ?>
			<p><label for="user_field_2"><?php echo $params->get('user_field_2_name'); ?>:</label>
			   <input type="text" name="user_field_2" id="user_field_2" size="70" maxlength="100"
					  value="<?php echo $this->user_field_2; ?>" /></p>
			<?php endif; ?>
			<?php if ( $params->get('user_field_3_name', false) ): ?>
			<p><label for="user_field_3"><?php echo $params->get('user_field_3_name'); ?>:</label>
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

		<br/><div class="form_buttons">
			<input type="submit" name="submit" value="<?php echo $upload_button_text ?>" />
			<span class="right">
			  <input type="button" name="cancel" value="<?php echo JText::_('ATTACH_CANCEL'); ?>"
					 onClick="window.parent.SqueezeBox.close();" />
			</span>
		</div>
	</form>
	<?php

	// Display the auto-publish warning, if appropriate
	if ( !$params->get('publish_default', false) && !$this->may_publish ) {
		$msg = $params->get('auto_publish_warning', '');
		if ( JString::strlen($msg) == 0 ) {
			$msg = 'ATTACH_WARNING_ADMIN_MUST_PUBLISH';
			}
		$msg = JText::_($msg);
		echo "<h2>$msg</h2>";
		}

	// Show the existing attachments (if any)
	if ( $parent_id || ($parent_id === 0) ) {
		require_once(JPATH_SITE.'/components/com_attachments/controllers/attachments.php');
		$controller = new AttachmentsControllerAttachments();
		$controller->displayString($parent_id, $parent_type, $parent_entity,
								   'ATTACH_EXISTING_ATTACHMENTS',
								   false, false, true, $this->from);
		}

	?>
</div>
