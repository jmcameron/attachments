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

global $option;

// Add the plugins stylesheet to style the list of attachments
$document =&  JFactory::getDocument();
$uri = JFactory::getURI();

$document->addStyleSheet( $uri->root(true) . '/plugins/content/attachments.css',
						  'text/css', null, array() );
$document->addStyleSheet( $uri->base(true) . '/components/com_attachments/attachments.css',
						  'text/css', null, array() );

$lang =& JFactory::getLanguage();
if ( $lang->isRTL() ) {
	$document->addStyleSheet( $uri->root(true) . '/plugins/content/attachments_rtl.css',
							  'text/css', null, array() );
	}

$upload_id = 'upload';

if ( $this->parent_title ) {
	echo "<h1>" . JText::sprintf('PARENT_S_COLON_S', $this->parent_entity_name, $this->parent_title) . "</h1>";
	}

?>
<form class="attachmentsBackend" enctype="multipart/form-data"
	  name="adminForm" id="adminForm"
	  action="<?php echo $this->save_url; ?>" method="post">
	<fieldset class="adminform">
		<legend><?php echo JText::_('ADD_ATTACHMENT'); ?></legend>
<?php if ( $this->new_parent ): ?>
		<input type="hidden" name="new_parent" value="1" />
<?php elseif ( $this->parent_id ): ?>
		<input type="hidden" name="parent_id" value="<?php echo $this->parent_id; ?>" />
<?php else: ?>
	  <label for="parent_title"><b><?php echo $this->selpar_label ?></b></label>
	  <input id="parent_title" value="" disabled="disabled" type="text" size="60" />&nbsp;
	  <a class="modal-button" type="button"
		 href="<?php echo $this->selpar_btn_url ?>" title="<?php echo $this->selpar_btn_tooltip ?>"
		 rel="{handler: 'iframe', size: {x: 700, y: 375}}"><?php echo $this->selpar_btn_text ?></a>
	  <input id="parent_id" name="parent_id" value="-1" type="hidden" />
	  <br />&nbsp;<br />
<?php endif; ?>
<?php if ( $this->uri_type == 'file' ): ?>
	<p><label for="upload"><b><?php echo JText::_('ATTACH_FILE_COLON') ?></b></label>
	  <a class="changeButton" href="<?php echo $this->upload_toggle_url ?>"
		 title="<?php echo $this->upload_toggle_tooltip; ?>"><?php
		 echo $this->upload_toggle_button_text;?></a><br />
	  <input type="file" name="upload" id="<?php echo $upload_id; ?>"
			 size="74" maxlength="512" /></p>
	<p><label for="display_name"
			  title="<?php echo JText::_('DISPLAY_FILENAME_TOOLTIP'); ?>"
			  ><b><?php echo JText::_('DISPLAY_FILENAME_COLON'); ?></b></label>
	   <input type="text" name="display_name" id="display_name" size="70" maxlength="80"
			  title="<?php echo JText::_('DISPLAY_FILENAME_TOOLTIP'); ?>"
			  value="" />&nbsp;<?php echo JText::_('OPTIONAL'); ?></p>
<?php else: ?>
	  <label for="upload"><b><?php echo JText::_('ENTER_URL_COLON') ?></b></label>
	  &nbsp;&nbsp;&nbsp;&nbsp;
	  <label for="verify_url"><?php echo JText::_('VERIFY_URL_EXISTENCE') ?></label>
	  <input type="checkbox" name="verify_url" value="verify" checked
			 title="<?php echo JText::_('VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
	  &nbsp;&nbsp;&nbsp;&nbsp;
	  <label for="relative_url"><?php echo JText::_('RELATIVE_URL') ?></label>
	  <input type="checkbox" name="relative_url" value="relative"
			 title="<?php echo JText::_('RELATIVE_URL_TOOLTIP'); ?>" />
	  <a class="changeButton" href="<?php echo $this->upload_toggle_url ?>"
		 title="<?php echo $this->upload_toggle_tooltip; ?>"><?php
	  echo $this->upload_toggle_button_text;?></a><br />
	  <input type="text" name="url" id="<?php echo $upload_id; ?>"
		 size="86" title="<?php echo JText::_('ENTER_URL_TOOLTIP'); ?>"
		 value="<?php echo $this->url; ?>" /><br /><?php
	  echo JText::_('NOTE_ENTER_URL_WITH_HTTP'); ?>
	<p><label for="display_name"
			  title="<?php echo JText::_('DISPLAY_URL_TOOLTIP'); ?>"
			  ><b><?php echo JText::_('DISPLAY_URL_COLON'); ?></b></label>
	   <input type="text" name="display_name" id="display_name" size="70" maxlength="80"
			  title="<?php echo JText::_('DISPLAY_URL_TOOLTIP'); ?>"
			  value="" />&nbsp;<?php echo JText::_('OPTIONAL'); ?></p>
<?php endif; ?>
	<p><label for="description"
			  title="<?php echo JText::_('DESCRIPTION_DESCRIPTION'); ?>"
		  ><b><?php echo JText::_('DESCRIPTION_COLON'); ?></b></label>
	   <input type="text" name="description" id="description"
		  title="<?php echo JText::_('DESCRIPTION_DESCRIPTION'); ?>"
		  size="75" maxlength="255" value="" /></p>
<?php if ( $this->show_user_field_1 ): ?>
	<p><label for="user_field_1"><b><?php echo $this->user_field_1_name; ?>:</b></label>
	   <input type="text" name="user_field_1" id="user_field_1" size="70" maxlength="100" value="" /></p>
<?php endif; ?>
<?php if ( $this->show_user_field_2 ): ?>
	<p><label for="user_field_2"><b><?php echo $this->user_field_2_name; ?>:</b></label>
	   <input type="text" name="user_field_2" id="user_field_2" size="70" maxlength="100" value="" /></p>
<?php endif; ?>
<?php if ( $this->show_user_field_3 ): ?>
	<p><label for="user_field_3"><b><?php echo $this->user_field_3_name; ?>:</b></label>
	   <input type="text" name="user_field_3" id="user_field_3" size="70" maxlength="100" value="" /></p>
<?php endif; ?>
	</fieldset>
	<input type="hidden" name="MAX_FILE_SIZE" value="524288" />
	<input type="hidden" name="save_type" value="upload" />
	<input type="hidden" name="parent_type" value="<?php echo $this->parent_type; ?>" />
	<input type="hidden" name="parent_entity" value="<?php echo $this->parent_entity; ?>" />
	<input type="hidden" name="uri_type" value="<?php echo $this->uri_type; ?>" />
	<input type="hidden" name="option" value="<?php echo $option;?>" />
	<input type="hidden" name="task" value="new" />
	<input type="hidden" name="from" value="<?php echo $this->from; ?>" />
	<?php if ( $this->from == 'closeme' ): ?>
	<div align="center">
	   <input type="submit" name="Submit" class="button"
			  onclick="javascript: submitbutton('saveNew')"
			  value="<?php echo JText::_('UPLOAD_VERB'); ?>" />
	   <span class="right">
		  <input type="button" name="cancel" value="<?php echo JText::_('CANCEL'); ?>"
				 onClick="window.parent.document.getElementById('sbox-window').close();" />
	   </span>
	</div>
	<?php endif; ?>
	<?php echo JHTML::_( 'form.token' ); ?>
</form>
<?php

// Show the existing attachments
if ( $this->uri_type == 'file' AND $this->parent_id ) {
	/** Get the Attachments controller class */
	require_once(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'controllers'.DS.'attachments.php');
	$controller = new AttachmentsControllerAttachments();
	$controller->display($this->parent_id, $this->parent_type, $this->parent_entity,
			 'EXISTING_ATTACHMENTS', false, false, true, $this->from);
}


// Show buttons for adding the attachments to other entitites (if appropriate)
$editor = JRequest::getWord('editor');
$exceptions = Array('article', 'section', 'category', 'add_to_parent');
if ( !in_array($editor, $exceptions) ) {

	$base_url = $uri->base(true) . "/index.php?option=com_attachments&amp;task=add";

	// Add a footer section with buttons to attach files to the supported content types/entities
	echo "<br />&nbsp;<br />";

	// For normal LTR, put the label on the left
	if ( !$lang->isRTL() ) {
		echo '<b>' . JText::_('ADD_ATTACHMENT_TO') . '</b>';
		}

	// Create all the buttons
	foreach ($this->entity_info as $einfo) {
		$parent_type = $einfo['parent_type'];
		$centity = $einfo['id_canonical'];
		$cename = $einfo['name'];
		if ( ($parent_type != $this->parent_type) OR ($centity != $this->parent_entity) ) {
			$url = $base_url . "&amp;parent_type=" . $parent_type;
			$tooltip = JText::sprintf('ADD_ATTACHMENT_TO_S_INSTEAD_OF_S_TOOLTIP',
									  $cename, $this->parent_entity_name);
			if ( $centity != 'default' ) {
				$url .= ':' . $centity;
				}
			if ( $this->uri_type == 'url' ) {
				$url .= '&amp;uri=url';
				}
			echo "<a class=\"changeButton\" href=\"$url\" title=\"$tooltip\">$cename</a>";
			}
		}

	// For normal RTL, put the label on the right
	if ( $lang->isRTL() ) {
		echo '<b>' . JText::_('ADD_ATTACHMENT_TO') . '</b>';
		}
	}

?>
