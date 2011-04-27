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

$document->addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css',
						  'text/css', null, array() );
$document->addStyleSheet( $uri->base(true) . '/components/com_attachments/media/attachments.css',
						  'text/css', null, array() );

$lang =& JFactory::getLanguage();
if ( $lang->isRTL() ) {
	$document->addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments_rtl.css',
							  'text/css', null, array() );
	$document->addStyleSheet( $uri->root(true) . '/components/com_attachments/media/attachments_rtl.css',
							  'text/css', null, array() );
	}

// Get the component parameters
jimport('joomla.application.component.helper');
$params = JComponentHelper::getParams('com_attachments');
$secure = $params->get('secure',false);

$attachment = $this->attachment;

if ( $this->change_parent ) {
	$parent_id = $this->selpar_parent_id;
	}
else {
	$parent_id = $attachment->parent_id;
	}

// Set up the create/modify dates
jimport( 'joomla.utilities.date' );
$cdate = new JDate($attachment->created, -$app->getCfg('offset'));
$created = $cdate->toFormat("%x %H:%M");
$mdate = new JDate($attachment->modified, -$app->getCfg('offset'));
$modified = $mdate->toFormat("%x %H:%M");

$update = $this->update;
$uri_type = $attachment->uri_type;

$show_download_count = $secure && ($uri_type == 'file');
if ( $show_download_count ) {
	$download_count_tooltip = JText::_('NUMBER_OF_DOWNLOADS_TOOLTIP');
	}

if ( $this->update == 'file' )
	$enctype = "enctype=\"multipart/form-data\"";
else
	$enctype = '';

?>
<?php if ( $this->in_popup ): ?>
<div class="attachmentsBackendTitle">
	<h1><?php echo JText::_('UPDATE_ATTACHMENT_COLON') . " " . $attachment->filename; ?></h1>
	<h2><?php echo JText::sprintf('FOR_PARENT_S_COLON_S', $attachment->parent_entity_name, $attachment->parent_title); ?></h2>
</div>
<?php endif; ?>
<form class="attachmentsBackend" action="<?php echo $this->save_url; ?>" method="post" <?php echo $enctype ?>
	  name="adminForm" id="adminForm">
<fieldset class="adminform">
<legend><?php echo JText::_('UPDATE_ATTACHMENT'); ?></legend>
<table class="admintable">
  <tr>
<?php if ( $this->change_parent ): ?>
	  <td class="key"><label for="parent_id"><b><?php
		  echo $this->selpar_label ?></b></label></td>
	  <td colspan="5"><input id="parent_title" value="<?php echo $this->selpar_parent_title; ?>"
				 disabled="disabled" type="text" size="60" />&nbsp;
		 <a class="modal-button" type="button" title="<?php echo $this->selpar_btn_tooltip ?>"
			href="<?php echo $this->selpar_btn_url ?>"
			rel="{handler: 'iframe', size: {x: 700, y: 375}}"><?php echo $this->selpar_btn_text ?></a>
	  </td>
<?php else: ?>
	  <td class="key"><label><?php echo
	   JText::sprintf('ATTACHED_TO', $attachment->parent_entity_name); ?></label></td>
	   <td class="at_title" colspan="2"><?php
	    if ( $attachment->parent_id == null ) {
			echo '<span class="error">' . $attachment->parent_title . '</span>';
		    }
		else {
			echo $attachment->parent_title;
			} ?>
       </td>
	   <td class="switch">
 	    <a class="changeButton" href="<?php echo $this->change_parent_url; ?>"
	       title="<?php echo JText::sprintf('CHANGE_ENTITY_S_TOOLTIP',$attachment->parent_entity_name); ?>"
		   ><?php echo JText::sprintf('CHANGE_ENTITY_S', $attachment->parent_entity_name) ?></a>
       </td>
	   <td class="switch" colspan="3"> <?php echo JText::_('SWITCH_TO_COLON') ?>
<?php
	// Create all the buttons to switch to other types of parents
	foreach ($this->entity_info as $einfo) {
		$parent_type = $einfo['parent_type'];
		$centity = $einfo['id'];
		$cename = $einfo['name'];
		if ( ($parent_type != $attachment->parent_type) OR ($centity != $attachment->parent_entity) ) {
			$url = $this->change_parent_url . "&amp;new_parent_type=" . $parent_type;
			$tooltip = JText::sprintf('SWITCH_ATTACHMENT_TO_S_TOOLTIP', $cename);
			if ( $centity != 'default' ) {
				$url .= '.' . $centity;
				}
			if ( $update == 'file' ) {
				$url .= '&amp;update=file';
				}
			if ( $update == 'url' ) {
				$url .= '&amp;update=url';
				}
			echo "<a class=\"changeButton\" href=\"$url\" title=\"$tooltip\">$cename</a>";
			}
		}
?>
	  </td>

<?php endif; ?>
  <tr><td class="key"><label><?php echo JText::_('PUBLISHED'); ?></label></td>
	  <td colspan="5"><?php echo $this->lists['published']; ?></td>
  </tr>
  </tr>
  <tr><td class="key"><label><?php echo JText::_('ATTACHMENT_TYPE'); ?></label></td>
  <td colspan="5"><?php echo JText::_(JString::strtoupper($uri_type));?>
  <?php if ( $uri_type == 'file' AND ( $update != 'url' ) ): ?>
	  <a class="changeButton" href="<?php echo $this->change_url_url ?>"
		 title="<?php echo JText::_('CHANGE_TO_URL_TOOLTIP'); ?>"
		 ><?php echo JText::_('CHANGE_TO_URL') ?></a>
  <?php elseif ( $uri_type == 'url' AND $update != 'file' ): ?>
	  <a class="changeButton" href="<?php echo $this->change_file_url ?>"
		 title="<?php echo JText::_('CHANGE_TO_FILE_TOOLTIP'); ?>"
		 ><?php echo JText::_('CHANGE_TO_FILE') ?></a>
  <?php elseif ( ($uri_type == 'file' AND $update != 'file') OR
				 ($uri_type == 'url' AND $update != 'url') ): ?>
	  <a class="changeButton" href="<?php echo $this->normal_update_url ?>"
		 title="<?php echo JText::_('NORMAL_UPDATE_TOOLTIP'); ?>"
		 ><?php echo JText::_('NORMAL_UPDATE') ?></a>
  <?php endif; ?>
  </td>
  </tr>

<?php if ( $update == 'file' ): ?>
  <tr>
	  <td class="key"><label for="upload"><?php echo JText::_('SELECT_FILE_COLON') ?></label></td>
	  <td colspan="5"><b><?php echo JText::_('SELECT_NEW_FILE_IF_YOU_WANT_TO_UPDATE_ATTACHMENT_FILE') ?></b><br />
	  <input type="file" name="upload" id="upload" size="68" maxlength="512" />
	  </td>
  </tr>
<?php elseif ( $update == 'url' ): ?>
  <tr>
	  <td class="key"><label for="upload"><?php echo JText::_('ENTER_URL_COLON') ?></label></td>
	  <td colspan="5">
		 <label for="verify_url"><?php echo JText::_('VERIFY_URL_EXISTENCE') ?></label>
		 <input type="checkbox" name="verify_url" value="verify" checked align="middle"
				title="<?php echo JText::_('VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
	 &nbsp;&nbsp;&nbsp;&nbsp;
	 <label for="url_relative"><?php echo JText::_('RELATIVE_URL') ?></label>
	 <input type="checkbox" name="url_relative" value="relative"
            <?php echo $this->url_relative_checked ?> title="<?php echo JText::_('RELATIVE_URL_TOOLTIP'); ?>" />
		 <br />
		 <input type="text" name="url" id="upload"
			 size="70" title="<?php echo JText::_('ENTER_URL_TOOLTIP'); ?>"
			 value="<?php if ( $uri_type == 'url' ) { echo $attachment->url; } ?>" />
		 <br />
		 <?php echo JText::_('NOTE_ENTER_URL_WITH_HTTP'); ?>
	  </td>
  </tr>
<?php else: ?>
   <?php if ( $uri_type == 'file' ): ?>
   <tr>
	  <td class="key"><label><?php echo JText::_('FILENAME'); ?></label></td>
	  <td colspan="5"><?php echo $attachment->filename; ?>
	  <a class="changeButton" href="<?php echo $this->change_file_url ?>"
		 title="<?php echo JText::_('CHANGE_FILE_TOOLTIP'); ?>"
		 ><?php echo JText::_('CHANGE_FILE') ?></a>
	  </td>
  </tr>
  <tr><td class="key"><label><?php echo JText::_('SYSTEM_FILENAME'); ?></label></td>
	  <td colspan="5"><?php echo $attachment->filename_sys; ?></td>
  </tr>
  <tr><td class="key"><label><?php echo JText::_('URL_COLON'); ?></label></td>
	  <td colspan="5"><?php echo $attachment->url; ?></td>
  </tr>
   <?php elseif ( $uri_type == 'url' ): ?>
  <tr>
	  <td class="key"><label for="upload"><?php
	if ( $uri_type == 'file' ) {
	echo JText::_('ENTER_NEW_URL_COLON');
		}
	else {
	echo JText::_('URL_COLON');
	}
   ?></label></td>
	  <td colspan="5">
		 <label for="verify_url"><?php echo JText::_('VERIFY_URL_EXISTENCE') ?></label>
		 <input type="checkbox" name="verify_url" value="verify" checked
				title="<?php echo JText::_('VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
	 &nbsp;&nbsp;&nbsp;&nbsp;
		 <label for="url_relative"><?php echo JText::_('RELATIVE_URL') ?></label>
	 <input type="checkbox" name="url_relative" value="relative"
            <?php echo $this->url_relative_checked ?> title="<?php echo JText::_('RELATIVE_URL_TOOLTIP'); ?>" />
	  <br />
	  <input type="text" name="url" id="upload" value="<?php echo $attachment->url; ?>"
			 size="70" title="<?php echo JText::_('ENTER_URL_TOOLTIP'); ?>" />
	  <input type="hidden" name="old_url" value="<?php echo $attachment->url; ?>" />
	  </td>
   </tr>
   <tr>
	 <td class="key"><label for="url_valid"><?php echo JText::_('URL_IS_VALID') ?></label></td>
	 <td colspan="5"><?php echo $this->lists['url_valid']; ?></td>
   </tr>
   <?php endif; ?>
<?php endif; ?>

<?php if ( $update == 'file' OR $uri_type == 'file' ): ?>

  <tr><td class="key"><label for="display_name"
							 title="<?php echo JText::_('DISPLAY_FILENAME_TOOLTIP'); ?>"
							 ><?php echo JText::_('DISPLAY_FILENAME'); ?></label></td>
	  <td colspan="5"><input class="text" type="text" name="display_name"
				 id="display_name" size="80" maxlength="80"
				 title="<?php echo JText::_('DISPLAY_FILENAME_TOOLTIP'); ?>"
				 value="<?php echo $attachment->display_name;?>"
				 />&nbsp;&nbsp;<?php echo JText::_('OPTIONAL'); ?>
		  <input type="hidden" name="old_display_name" value="<?php echo $attachment->display_name; ?>" />
	 </td>
  </tr>
<?php elseif ( $update == 'url' OR $uri_type == 'url' ): ?>
  <tr><td class="key"><label for="display_name"
							 title="<?php echo JText::_('DISPLAY_URL_TOOLTIP'); ?>"
							 ><?php echo JText::_('DISPLAY_URL'); ?></label></td>
	  <td colspan="5"><input class="text" type="text" name="display_name"
				 id="display_name" size="80" maxlength="80"
				 title="<?php echo JText::_('DISPLAY_URL_TOOLTIP'); ?>"
				 value="<?php echo $attachment->display_name;?>"
				 />&nbsp;&nbsp;<?php echo JText::_('OPTIONAL'); ?>
		  <input type="hidden" name="old_display_name" value="<?php echo $attachment->display_name; ?>" />
	 </td>
  </tr>
<?php endif; ?>

  <tr><td class="key"><label for="description"
				 title="<?php echo JText::_('DESCRIPTION_DESCRIPTION'); ?>"><?php
				 echo JText::_('DESCRIPTION'); ?></label></td>
	  <td colspan="5"><input class="text" type="text" name="description"
			 title="<?php echo JText::_('DESCRIPTION_DESCRIPTION'); ?>"
				 id="description" size="80" maxlength="255"
				 value="<?php echo $attachment->description;?>" /></td>
  </tr>
  <?php if ( $params->get('user_field_1_name', '') != '' ): ?>
  <tr><td class="key"><label for="user_field_1"><?php echo $params->get('user_field_1_name'); ?></label></td>
	  <td colspan="5"><input class="text" type="text" name="user_field_1"
		 id="user_field_1" size="80" maxlength="100"
		 value="<?php echo $attachment->user_field_1;?>" /></td>
  </tr>
  <?php endif; ?>
  <?php if ( $params->get('user_field_2_name', '') != '' ): ?>
  <tr><td class="key"><label for="user_field_2"><?php echo $params->get('user_field_2_name'); ?></label></td>
	  <td colspan="5"><input class="text" type="text" name="user_field_2"
		 id="user_field_2" size="80" maxlength="100"
		 value="<?php echo $attachment->user_field_2;?>" /></td>
  </tr>
  <?php endif; ?>
  <?php if ( $params->get('user_field_3_name', '') != '' ): ?>
  <tr><td class="key"><label for="user_field_3"><?php echo $params->get('user_field_3_name'); ?></label></td>
	  <td colspan="5"><input class="text" type="text" name="user_field_3"
		 id="user_field_3" size="80" maxlength="100"
		 value="<?php echo $attachment->user_field_3;?>" /></td>
  </tr>
  <?php endif; ?>
  <tr>
      <td class="key"><label for="icon_filename"><?php echo JText::_('ICON_FILENAME'); ?></label></td>
	  <td><?php echo $this->lists['icon_filenames']; ?></td>
	  <td class="key2"><label><?php echo JText::_('FILE_TYPE'); ?></label></td>
	  <td colspan="3"><?php echo $attachment->file_type; ?></td>
  </tr>
  <tr>
	  <td class="key"><label><?php echo JText::_('FILE_SIZE'); ?></label></td?>
	  <td><?php echo $attachment->size_kb; ?> <?php echo JText::_('KB'); ?></td?>
	  <td class="key2"><label><?php echo JText::_('DATE_CREATED'); ?></label></td>
	  <td><?php echo $created; ?></td>
	  <td class="key2"><label><?php echo JText::_('DATE_LAST_MODIFIED'); ?></label></td>
	  <td><?php echo $modified; ?></td>
  </tr>
  <tr>
	  <td class="key"><label><?php echo JText::_('ATTACHMENT_ID'); ?></label></td>
	  <td><?php echo $attachment->id; ?></td>
	  <td class="key2"><label><?php echo JText::_('CREATED_BY'); ?></label></td>
	  <td><?php echo $attachment->creator_name;?></td>
	  <td class="key2"><label><?php echo JText::_('MODIFIED_BY'); ?></label></td>
	  <td><?php echo $attachment->modifier_name;?></td>
  </tr>
<?php if ( $show_download_count ): ?>
  <tr>
	<td class="key" title="<?php echo $download_count_tooltip; ?>"><label><?php echo JText::_('NUMBER_OF_DOWNLOADS'); ?></label></td>
	<td colspan="5" title="<?php echo $download_count_tooltip; ?>"><?php echo $attachment->download_count; ?></td>
  </tr>
<?php endif; ?>

</table>
</fieldset>
<input type="hidden" name="id" value="<?php echo $attachment->id; ?>" />
<input type="hidden" name="update" value="<?php echo $update; ?>" />
<input type="hidden" name="uri_type" value="<?php echo $uri_type; ?>" />
<input type="hidden" name="parent_id" id="parent_id" value="<?php echo $parent_id; ?>" />
<input type="hidden" name="parent_type" id="parent_type" value="<?php echo $attachment->parent_type; ?>" />
<input type="hidden" name="parent_entity" id="parent_entity" value="<?php echo $attachment->parent_entity; ?>" />
<input type="hidden" name="old_parent_id" value="<?php echo $attachment->parent_id ?>" />
<input type="hidden" name="old_parent_type" value="<?php echo $attachment->parent_type ?>" />
<input type="hidden" name="old_parent_entity" value="<?php echo $attachment->parent_entity ?>" />
<input type="hidden" name="new_parent_type" id="new_parent_type" value="<?php echo $this->new_parent_type; ?>" />
<input type="hidden" name="new_parent_entity" id="new_parent_entity" value="<?php echo $this->new_parent_entity; ?>" />
<input type="hidden" name="option" value="<?php echo $this->option;?>" />
<input type="hidden" name="from" value="<?php echo $this->from;?>" />
<input type="hidden" name="task" value="attachment.edit" />
<?php if ( $this->in_popup ): ?>
<div align="center">
	<input type="submit" name="submit" onclick="javascript: submitbutton('attachment.save')" value="<?php echo JText::_('SAVE'); ?>" />
	<span class="right">
	   <input type="button" name="cancel" value="<?php echo JText::_('CANCEL'); ?>"
			  onClick="window.parent.SqueezeBox.close();" />
	</span>
</div>
<?php endif; ?>
<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php

// Show the existing attachments (if any)
if ( $attachment->parent_id ) {
	/** Get the attachments controller class */
	require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_attachments'.DS.'controllers'.DS.'list.php');
	$controller = new AttachmentsControllerList();
	$controller->display($attachment->parent_id, $attachment->parent_type, $attachment->parent_entity,
						 'EXISTING_ATTACHMENTS', false, false, true, $this->from);
}

?>
