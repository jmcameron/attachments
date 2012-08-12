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
$user = JFactory::getUser();
$document = JFactory::getDocument();
$app = JFactory::getApplication();
$uri = JFactory::getURI();

$lang = JFactory::getLanguage();

$attachment = $this->attachment;
$params = $this->params;

$update = $this->update;
$uri_type = $attachment->uri_type;

$parent_id = $attachment->parent_id;
if ( $parent_id === null ) {
	$parent_id = 0;
	}

// set up URL redisplay in case of errors
$old_url = '';
if ( $this->error_msg && ($update == 'url') ) {
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

// Format modified date
jimport( 'joomla.utilities.date' );
$tz = new DateTimeZone( $user->getParam('timezone', $app->getCfg('offset')) );
$mdate = JFactory::getDate($attachment->modified);
$mdate->setTimezone($tz);
$mod_date_format = $params->get('mod_date_format', '%Y-%m-%d %I:%M%P');
$last_modified = $mdate->toFormat($mod_date_format, true);

// If this is an error re-display, display the CSS links directly
$echo_css = $this->error;

JHTML::_('behavior.mootools');

/** Load the Attachments helper */
require_once(JPATH_COMPONENT_SITE.'/helper.php');

// Add the stylesheets
$uri = JFactory::getURI();
AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css', $echo_css );
AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments2.css', $echo_css );

// Add javascript
JHTML::_('behavior.mootools');
$document->addScript( $uri->root(true) . '/plugins/content/attachments/attachments_refresh.js' );

// Handle RTL styling
if ( $lang->isRTL() ) {
	AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments_rtl.css', $echo_css );
	}

if ( $uri_type == 'file' ) {
	$header_msg = JText::sprintf('ATTACH_UPDATE_ATTACHMENT_FILE_S', $filename);
	}
else {
	$header_msg = JText::sprintf('ATTACH_UPDATE_ATTACHMENT_URL_S', $attachment->url);
	}
?>
<div id="uploadAttachmentsPage">
<h1><?php echo $header_msg; ?></h1>
<form class="attachments" <?php echo $enctype ?> name="upload_form"
	  action="<?php echo $this->save_url; ?>" method="post">
	<fieldset>
		<legend><?php echo JText::sprintf('ATTACH_UPDATE_ATTACHMENT_FOR_PARENT_S_COLON_S',
										  $this->parent_entity_name, $this->parent_title); ?></legend>
		<?php if ( $this->error_msg ): ?>
		<div class="formWarning" id="formWarning"><?php echo $this->error_msg; ?></div>
		<?php endif; ?>
<?php if ( $update == 'file' ): ?>
<p><label for="<?php echo $update_id; ?>"><?php
   echo JText::_('ATTACH_SELECT_NEW_FILE_IF_YOU_WANT_TO_UPDATE_ATTACHMENT_FILE') ?></label>
		<a class="changeButton" href="<?php echo $this->normal_update_url ?>"
		   title="<?php echo JText::_('ATTACH_NORMAL_UPDATE_TOOLTIP'); ?>"
		   ><?php echo JText::_('ATTACH_NORMAL_UPDATE') ?></a> <br />
		<input type="file" name="upload" id="<?php echo $update_id; ?>"
			   size="80" maxlength="512" />
		</p>
<?php elseif ( $update == 'url' ): ?>
		<p><label for="<?php echo $update_id; ?>"><?php echo JText::_('ATTACH_ENTER_URL') ?></label>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="verify_url"><?php echo JText::_('ATTACH_VERIFY_URL_EXISTENCE') ?></label>
		<input type="checkbox" name="verify_url" value="verify" checked
			   title="<?php echo JText::_('ATTACH_VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
		&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="relative_url"><?php echo JText::_('ATTACH_RELATIVE_URL') ?></label>
		<input type="checkbox" name="relative_url" value="relative"
			   title="<?php echo JText::_('ATTACH_RELATIVE_URL_TOOLTIP'); ?>" />
		<a class="changeButton" href="<?php echo $this->normal_update_url ?>"
		   title="<?php echo JText::_('ATTACH_NORMAL_UPDATE_TOOLTIP'); ?>"
		   ><?php echo JText::_('ATTACH_NORMAL_UPDATE') ?></a> <br />
		<input type="text" name="url" id="<?php echo $update_id; ?>"
			   size="80" maxlength="255" title="<?php echo JText::_('ATTACH_ENTER_URL_TOOLTIP'); ?>"
			   value="<?php echo $old_url; ?>" /><br /><?php
		echo JText::_('ATTACH_NOTE_ENTER_URL_WITH_HTTP'); ?>
		</p>
<?php else: ?>
<?php if ( $uri_type == 'file' ): ?>
		<p><label><?php echo JText::_('ATTACH_FILENAME_COLON'); ?></label> <?php echo $filename; ?>
		<a class="changeButton" href="<?php echo $this->change_file_url ?>"
		   title="<?php echo JText::_('ATTACH_CHANGE_FILE_TOOLTIP'); ?>"
		   ><?php echo JText::_('ATTACH_CHANGE_FILE') ?></a>
		<a class="changeButton" href="<?php echo $this->change_url_url ?>"
		   title="<?php echo JText::_('ATTACH_CHANGE_TO_URL_TOOLTIP'); ?>"
		   ><?php echo JText::_('ATTACH_CHANGE_TO_URL') ?></a>
		</p>
<?php elseif ( $uri_type == 'url' ): ?>
<p><label for="<?php echo $update_id; ?>"><?php echo JText::_('ATTACH_ENTER_NEW_URL_COLON') ?></label>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="verify_url"><?php echo JText::_('ATTACH_VERIFY_URL_EXISTENCE') ?></label>
		<input type="checkbox" name="verify_url" value="verify" checked
					   title="<?php echo JText::_('ATTACH_VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
		&nbsp;&nbsp;&nbsp;&nbsp;
		<label for="relative_url"><?php echo JText::_('ATTACH_RELATIVE_URL') ?></label>
		<input type="checkbox" name="relative_url" value="relative"
					   title="<?php echo JText::_('ATTACH_RELATIVE_URL_TOOLTIP'); ?>" />
		<a class="changeButton" href="<?php echo $this->change_file_url ?>"
		   title="<?php echo JText::_('ATTACH_CHANGE_TO_FILE_TOOLTIP'); ?>"
		   ><?php echo JText::_('ATTACH_CHANGE_TO_FILE') ?></a> </p>
<p><label for="url_valid"><?php echo JText::_('ATTACH_URL_IS_VALID') ?></label>
		<?php echo $this->lists['url_valid']; ?>
</p>
<p>
		<input type="text" name="url" id="<?php echo $update_id ?>"
			   size="80" maxlength="255" title="<?php echo JText::_('ATTACH_ENTER_URL_TOOLTIP'); ?>"
			   value="<?php echo $attachment->url; ?>" /><br /><?php
		echo JText::_('ATTACH_NOTE_ENTER_URL_WITH_HTTP'); ?>
		<input type="hidden" name="old_url" value="<?php echo $old_url; ?>" />
</p>
<?php endif; ?>
<?php endif; ?>
<?php if ( ($update == 'file') || ($uri_type == 'file') ): ?>
<p class="display_name"><label for="display_name"
		  title="<?php echo JText::_('ATTACH_DISPLAY_FILENAME_TOOLTIP'); ?>"
		  ><?php echo JText::_('ATTACH_DISPLAY_FILENAME_OPTIONAL_COLON'); ?></label>
   <input type="text" name="display_name" id="display_name"
		  size="70" maxlength="80"
		  title="<?php echo JText::_('ATTACH_DISPLAY_FILENAME_TOOLTIP'); ?>"
		  value="<?php echo $this->display_name; ?>" />
   <input type="hidden" name="old_display_name" value="<?php echo $this->display_name; ?>" />
</p>
<?php elseif ( ($update == 'url') || ($uri_type == 'url') ): ?>
<p class="display_name"><label for="display_name"
		  title="<?php echo JText::_('ATTACH_DISPLAY_URL_TOOLTIP'); ?>"
		  ><?php echo JText::_('ATTACH_DISPLAY_URL_COLON'); ?></label>
   <input type="text" name="display_name" id="display_name"
		  size="70" maxlength="80"
		  title="<?php echo JText::_('ATTACH_DISPLAY_URL_TOOLTIP'); ?>"
		  value="<?php echo $this->display_name; ?>" />
   <input type="hidden" name="old_display_name" value="<?php echo $this->display_name; ?>" />
</p>
<?php endif; ?>
		<p><label for="description"><?php echo JText::_('ATTACH_DESCRIPTION_COLON'); ?></label>
		   <input type="text" name="description" id="description"
				  size="70" maxlength="255" value="<?php echo $attachment->description; ?>" /></p>
<?php if ( $this->may_publish ): ?>
		<p><label><?php echo JText::_('ATTACH_PUBLISHED'); ?></label><?php echo $this->lists['published']; ?></p>
<?php endif; ?>
<?php if ( $params->get('allow_frontend_access_editing', false) ): ?>
		<p><label for="access" title="<?php echo $this->access_level_tooltip; ?>"><? echo JText::_('ATTACH_ACCESS_COLON'); ?></label><?php echo $this->access_level; ?> </p>
<?php endif; ?>
		<?php if ( $params->get('user_field_1_name') ): ?>
		<p><label for="user_field_1"><?php echo $params->get('user_field_1_name'); ?>:</label>
		   <input type="text" name="user_field_1" id="user_field_1" size="70" maxlength="100"
				  value="<?php echo $attachment->user_field_1; ?>" /></p>
		<?php endif; ?>
		<?php if ( $params->get('user_field_2_name') ): ?>
		<p><label for="user_field_2"><?php echo $params->get('user_field_2_name'); ?>:</label>
		   <input type="text" name="user_field_2" id="user_field_2" size="70" maxlength="100"
				  value="<?php echo $attachment->user_field_2; ?>" /></p>
		<?php endif; ?>
		<?php if ( $params->get('user_field_3_name') ): ?>
		<p><label for="user_field_3"><?php echo $params->get('user_field_3_name'); ?>:</label>
		   <input type="text" name="user_field_3" id="user_field_3" size="70" maxlength="100"
				  value="<?php echo $attachment->user_field_3; ?>" /></p>
		<?php endif; ?>
		<p><?php echo JText::sprintf('ATTACH_LAST_MODIFIED_ON_D_BY_S',
									 $last_modified, $attachment->modifier_name); ?></p>
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
		<input type="submit" name="submit" value="<?php echo JText::_('ATTACH_UPDATE'); ?>" />
		<span class="right">
		  <input type="button" name="cancel" value="<?php echo JText::_('ATTACH_CANCEL'); ?>"
				 onClick="window.parent.SqueezeBox.close();" />
		</span>
	</div>
</form>

<?php

// Generate the list of existing attachments
if ( ($update == 'file') || ($uri_type == 'file') ) {
	require_once(JPATH_SITE.'/components/com_attachments/controllers/attachments.php');
	$controller = new AttachmentsControllerAttachments();
	$controller->display($parent_id, $attachment->parent_type, $this->parent_entity,
						 'ATTACH_EXISTING_ATTACHMENTS',
						 false, false, true, $this->from);
	}

echo '</div>';
