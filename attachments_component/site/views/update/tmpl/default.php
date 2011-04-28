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
defined('_JEXEC') or die('Restricted access');

// Add the plugins stylesheet to style the list of attachments
$document =& JFactory::getDocument();
$app = JFactory::getApplication();
$uri = JFactory::getURI();

$lang =& JFactory::getLanguage();

$attachment =& $this->attachment;
$params =& $this->params;

$update = $this->update;
$uri_type = $attachment->uri_type;

$parent_id = $attachment->parent_id;
if ( $parent_id === null ) {
	$parent_id = 0;
	}

// set up URL redisplay in case of errors
$old_url = '';
if ( $this->error_msg AND ( $update == 'url' ) ) {
	$old_url = $attachment->url;
	}

// Decide what type of update to do
if ( $update == 'file' ) {
	$enctype = "enctype=\"multipart/form-data\"";
	}
else {
	$enctype = '';
	}

// Prepare for error displays
$update_id = 'upload';
$filename = $attachment->filename;
if ( $this->error ) {
	switch ( $this->error ) {

	case 'no_file':
		$update_id = 'upload_warning';
		$filename = '';
		break;

	case 'file_too_big':
		$upload_id = 'upload_warning';
		break;

	case 'file_already_on_server':
		$upload_id = 'upload_warning';
		break;
		}
	}

// If this is an error re-display, display the CSS links directly
$echo_css = $this->error;

JHTML::_('behavior.mootools');

// Add the stylesheets
require_once(JPATH_COMPONENT_SITE.DS.'helper.php');
$uri = JFactory::getURI();
AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css', $echo_css );
AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments2.css', $echo_css );

// Add javascript
$document->addScript( $uri->root(true) . '/plugins/content/attachments/attachments_refresh.js' );

// Handle RTL styling
if ( $lang->isRTL() ) {
	AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments_rtl.css', $echo_css );
	}

if ( $uri_type == 'file' ) {
	$header_msg = JText::sprintf('UPDATE_ATTACHMENT_FILE_S', $filename);
	}
else {
	$header_msg = JText::sprintf('UPDATE_ATTACHMENT_URL_S', $attachment->url);
	}
?>
<div id="uploadAttachmentsPage">
<h1><?php echo $header_msg; ?></h1>
<form class="attachments" <?php echo $enctype ?> name="upload_form"
	  action="<?php echo $this->save_url; ?>" method="post">
	<fieldset>
		<legend><?php echo JText::sprintf('UPDATE_ATTACHMENT_FOR_PARENT_S_COLON_S',
										  $this->parent_entity_name, $this->parent_title); ?></legend>
		<?php if ( $this->error_msg ): ?>
		<div class="formWarning" id="formWarning"><?php echo $this->error_msg; ?></div>
		<?php endif; ?>
<?php if ( $update == 'file' ): ?>
<p><label for="<?php echo $update_id; ?>"><b><?php
   echo JText::_('SELECT_NEW_FILE_IF_YOU_WANT_TO_UPDATE_ATTACHMENT_FILE') ?></b></label>
		<a class="changeButton" href="<?php echo $this->normal_update_url ?>"
		   title="<?php echo JText::_('NORMAL_UPDATE_TOOLTIP'); ?>"
		   ><?php echo JText::_('NORMAL_UPDATE') ?></a> <br />
		<input type="file" name="upload" id="<?php echo $update_id; ?>"
			   size="60" maxlength="512" />
		</p>
<?php elseif ( $update == 'url' ): ?>
		<p><label for="<?php echo $update_id; ?>"><b><?php echo JText::_('ENTER_URL_COLON') ?></b></label>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="verify_url"><b><?php echo JText::_('VERIFY_URL_EXISTENCE') ?></b></label>
		<input type="checkbox" name="verify_url" value="verify" checked
			   title="<?php echo JText::_('VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
		&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="relative_url"><b><?php echo JText::_('RELATIVE_URL') ?></b></label>
		<input type="checkbox" name="relative_url" value="relative"
			   title="<?php echo JText::_('RELATIVE_URL_TOOLTIP'); ?>" />
		<a class="changeButton" href="<?php echo $this->normal_update_url ?>"
		   title="<?php echo JText::_('NORMAL_UPDATE_TOOLTIP'); ?>"
		   ><?php echo JText::_('NORMAL_UPDATE') ?></a> <br />
		<input type="text" name="url" id="<?php echo $update_id; ?>"
			   size="80" maxlength="255" title="<?php echo JText::_('ENTER_URL_TOOLTIP'); ?>"
			   value="<?php echo $old_url; ?>" /><br /><?php
		echo JText::_('NOTE_ENTER_URL_WITH_HTTP'); ?>
		</p>
<?php else: ?>
<?php if ( $uri_type == 'file' ): ?>
		<p><label><b><?php echo JText::_('FILENAME_COLON'); ?></b></label> <?php echo $filename; ?>
		<a class="changeButton" href="<?php echo $this->change_file_url ?>"
		   title="<?php echo JText::_('CHANGE_FILE_TOOLTIP'); ?>"
		   ><?php echo JText::_('CHANGE_FILE') ?></a>
		<a class="changeButton" href="<?php echo $this->change_url_url ?>"
		   title="<?php echo JText::_('CHANGE_TO_URL_TOOLTIP'); ?>"
		   ><?php echo JText::_('CHANGE_TO_URL') ?></a>
		</p>
<?php elseif ( $uri_type == 'url' ): ?>
<p><label for="<?php echo $update_id; ?>"><b><?php echo JText::_('ENTER_NEW_URL_COLON') ?></b></label>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="verify_url"><b><?php echo JText::_('VERIFY_URL_EXISTENCE') ?></b></label>
		<input type="checkbox" name="verify_url" value="verify" checked
					   title="<?php echo JText::_('VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
		&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="relative_url"><b><?php echo JText::_('RELATIVE_URL') ?></b></label>
		<input type="checkbox" name="relative_url" value="relative"
					   title="<?php echo JText::_('RELATIVE_URL_TOOLTIP'); ?>" />
		<a class="changeButton" href="<?php echo $this->change_file_url ?>"
		   title="<?php echo JText::_('CHANGE_TO_FILE_TOOLTIP'); ?>"
		   ><?php echo JText::_('CHANGE_TO_FILE') ?></a> </p>
<p><label for="url_valid"><b><?php echo JText::_('URL_IS_VALID') ?></b></label>
		<?php echo $this->lists['url_valid']; ?>
</p>
<p>
		<input type="text" name="url" id="<?php echo $update_id ?>"
			   size="80" maxlength="255" title="<?php echo JText::_('ENTER_URL_TOOLTIP'); ?>"
			   value="<?php echo $attachment->url; ?>" /><br /><?php
		echo JText::_('NOTE_ENTER_URL_WITH_HTTP'); ?>
		<input type="hidden" name="old_url" value="<?php echo $old_url; ?>" />
</p>
<?php endif; ?>
<?php endif; ?>
<?php if ( $update == 'file' OR $uri_type == 'file' ): ?>
<p class="display_name"><label for="display_name"
		  title="<?php echo JText::_('DISPLAY_FILENAME_TOOLTIP'); ?>"
		  ><b><?php echo JText::_('DISPLAY_FILENAME_OPTIONAL_COLON'); ?></b></label>
   <input type="text" name="display_name" id="display_name"
		  size="70" maxlength="80"
		  title="<?php echo JText::_('DISPLAY_FILENAME_TOOLTIP'); ?>"
		  value="<?php echo $this->display_name; ?>" />
   <input type="hidden" name="old_display_name" value="<?php echo $this->display_name; ?>" />
</p>
<?php elseif ( $update == 'url' OR $uri_type == 'url' ): ?>
<p class="display_name"><label for="display_name"
		  title="<?php echo JText::_('DISPLAY_URL_TOOLTIP'); ?>"
		  ><b><?php echo JText::_('DISPLAY_URL_COLON'); ?></b></label>
   <input type="text" name="display_name" id="display_name"
		  size="70" maxlength="80"
		  title="<?php echo JText::_('DISPLAY_URL_TOOLTIP'); ?>"
		  value="<?php echo $this->display_name; ?>" />
   <input type="hidden" name="old_display_name" value="<?php echo $this->display_name; ?>" />
</p>
<?php endif; ?>
		<p><label for="description"><b><?php echo JText::_('DESCRIPTION_COLON'); ?></b></label>
		   <input type="text" name="description" id="description"
				  size="70" maxlength="255" value="<?php echo $attachment->description; ?>" /></p>
		<?php if ( $params->get('user_field_1_name', false) ): ?>
		<p><label for="user_field_1"><b><?php echo $params->get('user_field_1_name'); ?>:</b></label>
		   <input type="text" name="user_field_1" id="user_field_1" size="70" maxlength="100"
				  value="<?php echo $attachment->user_field_1; ?>" /></p>
		<?php endif; ?>
		<?php if ( $params->get('user_field_2_name', false) ): ?>
		<p><label for="user_field_2"><b><?php echo $params->get('user_field_2_name'); ?>:</b></label>
		   <input type="text" name="user_field_2" id="user_field_2" size="70" maxlength="100"
				  value="<?php echo $attachment->user_field_2; ?>" /></p>
		<?php endif; ?>
		<?php if ( $params->get('user_field_3_name', false) ): ?>
		<p><label for="user_field_3"><b><?php echo $params->get('user_field_3_name'); ?>:</b></label>
		   <input type="text" name="user_field_3" id="user_field_3" size="70" maxlength="100"
				  value="<?php echo $attachment->user_field_3; ?>" /></p>
		<?php endif; ?>
		<p><?php echo JText::sprintf('LAST_MODIFIED_ON_D_BY_S',
									 $attachment->modified, $attachment->modifier_name); ?></p>
	</fieldset>
	<input type="hidden" name="MAX_FILE_SIZE" value="524288" />
	<input type="hidden" name="submitted" value="TRUE" />
	<input type="hidden" name="id" value="<?php echo $attachment->id; ?>" />
	<input type="hidden" name="save_type" value="update" />
	<input type="hidden" name="update" value="<?php echo $update; ?>" />
	<input type="hidden" name="uri_type" value="<?php echo $uri_type; ?>" />
	<input type="hidden" name="new_parent" value="<?php echo $this->new_parent; ?>" />
	<input type="hidden" name="parent_id" value="<?php echo $parent_id; ?>" />
	<input type="hidden" name="parent_type" value="<?php echo $attachment->parent_type; ?>" />
	<input type="hidden" name="parent_entity" value="<?php echo $this->parent_entity; ?>" />
	<input type="hidden" name="from" value="<?php echo $this->from; ?>" />
	<input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<div class="form_buttons">
		<input type="submit" name="submit" value="<?php echo JText::_('UPDATE'); ?>" />
		<span class="right">
		  <input type="button" name="cancel" value="<?php echo JText::_('CANCEL'); ?>"
				 onClick="window.parent.SqueezeBox.close();" />
		</span>
	</div>
</form>

<?php

// Generate the list of existing attachments
if ( $update == 'file' OR $uri_type == 'file' ) {
	require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.
				 'controllers'.DS.'attachments.php');
	$controller = new AttachmentsControllerAttachments();
	$controller->display($parent_id, $attachment->parent_type, $this->parent_entity,
						 'EXISTING_ATTACHMENTS',
						 false, false, true, $this->from);
	}

echo '</div>';
