<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

use JMCameron\Component\Attachments\Administrator\Controller\ListController;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// No direct access
defined('_JEXEC') or die('Restricted access');

// Load the tooltip behavior.
HTMLHelper::_('bootstrap.tooltip');

// Add the plugins stylesheet to style the list of attachments
$app = Factory::getApplication();
$document = $app->getDocument();
$uri = Uri::getInstance();

$attachment = $this->attachment;

$upload_id = 'upload';


// Show buttons for adding the attachments to other entitites (if appropriate)
$alt_parent_html = '';
$editor = $app->getInput()->getWord('editor');
$exceptions = Array('article', 'category', 'add_to_parent');
if ( !in_array($editor, $exceptions) ) {

	$base_url = $uri->base(true) . "/index.php?option=com_attachments&amp;task=attachment.add";

	// Add a footer section with buttons to attach files to the supported content types/entities
	$alt_parent_html .= '<div id="attachmentsPotentialParents">';
	$alt_parent_html .= '<p>';

	// For normal LTR, put the label on the left
	if ( !$lang->isRTL() ) {
		$alt_parent_html .= '<span>' . Text::_('ATTACH_ADD_ATTACHMENT_TO') . '</span> ';
		}

	// Create all the buttons
	foreach ($this->entity_info as $einfo) {
		$parent_type = $einfo['parent_type'];
		$centity = $einfo['id'];
		$cename = $einfo['name'];
		if ( ($parent_type != $attachment->parent_type) || ($centity != $attachment->parent_entity) ) {
			$url = $base_url . "&amp;parent_type=" . $parent_type;
			$tooltip = Text::sprintf('ATTACH_ADD_ATTACHMENT_TO_S_INSTEAD_OF_S_TOOLTIP',
									  $cename, $attachment->parent_entity_name);
			if ( $centity != 'default' ) {
				$url .= '.' . $centity;
				}
			if ( $attachment->uri_type == 'url' ) {
				$url .= '&amp;uri=url';
				}
			$alt_parent_html .= " <a class=\"changeButton\" href=\"$url\" title=\"$tooltip\">$cename</a>";
			}
		}

	// For normal RTL, put the label on the right
	if ( $lang->isRTL() ) {
		$alt_parent_html .= '<span>' . Text::_('ATTACH_ADD_ATTACHMENT_TO') . '</span>';
		}

	$alt_parent_html .= '</p>';
	$alt_parent_html .= '</div>';
	}


if ( $attachment->parent_title ) {
	echo "<h1>" . Text::sprintf('ATTACH_PARENT_S_COLON_S', $attachment->parent_entity_name, $attachment->parent_title) . "</h1>";
	}

?>
<form class="attachmentsBackend" enctype="multipart/form-data"
	  name="adminForm" id="adminForm"
	  action="<?php echo $this->save_url; ?>" method="post">
	<fieldset class="adminform">
		<legend><?php echo Text::_('ATTACH_ADD_ATTACHMENT'); ?></legend>
<table class="admintable">
<?php if ( !$this->new_parent && !$attachment->parent_id ): ?>
  <tr>
	<td class="key"><label for="parent_title"><?php echo $this->selpar_label ?></label></td>
	<td><?php echo $alt_parent_html; ?>
	  <input id="parent_title" value="<?php echo $attachment->parent_title ?>" disabled="disabled" type="text" size="60" />&nbsp;
<?php
	$modalId = 'attachment';
	$modalParams['title']  = $this->escape($this->selpar_btn_tooltip);
	$modalParams['url']    = $this->selpar_btn_url;
	$modalParams['height'] = '100%';
	$modalParams['width']  = '100%';
	$modalParams['bodyHeight'] = 70;
	$modalParams['modalWidth'] = 80;
	echo HTMLHelper::_('bootstrap.renderModal', 'modal-' . $modalId, $modalParams);
	
	echo '<button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#modal-' . $modalId . '">'
		. $this->selpar_btn_text .
	'</button>';

?>
	  <input id="parent_id" name="parent_id" value="-1" type="hidden" />
	</td>
  </tr>
<?php endif; ?>
<?php if ( $attachment->uri_type == 'file' ): ?>
  <tr>
	<td class="key"><label for="upload" id="upload_file_label"><?php echo Text::_('ATTACH_ATTACH_FILE_COLON') ?></label></td>
	<td>
	  <a class="changeButton" href="<?php echo $this->upload_toggle_url ?>"
		 title="<?php echo $this->upload_toggle_tooltip; ?>"><?php
		 echo $this->upload_toggle_button_text;?></a><br />
	  <input type="file" name="upload" id="<?php echo $upload_id; ?>"
			 size="74" maxlength="1024" />
	</td>
  </tr>
  <tr>
	<td class="key"><label for="display_name" class="hasTip" title="<?php echo $this->display_filename_tooltip; ?>"
			  ><?php echo Text::_('ATTACH_DISPLAY_FILENAME'); ?></label></td>
	<td>
	   <input type="text" name="display_name" id="display_name" size="75" maxlength="80"
			  title="<?php echo $this->display_filename_tooltip; ?>" class="hasTip"
			  value="<?php echo $attachment->display_name ?>" /><span class="optional"><?php echo Text::_('ATTACH_OPTIONAL'); ?></span>
   </td>
<?php else: ?>
  <tr>
	<td class="key"><label for="upload" class="hasTip"
		title="<?php echo $this->enter_url_tooltip ?>"><?php echo Text::_('ATTACH_ENTER_URL') ?></label></td>
	<td>
	  <label for="verify_url"><?php echo Text::_('ATTACH_VERIFY_URL_EXISTENCE') ?></label>
	  <input type="checkbox" name="verify_url" value="verify" <?php echo $this->verify_url_checked ?>
			 title="<?php echo Text::_('ATTACH_VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
	  <label for="url_relative"><?php echo Text::_('ATTACH_RELATIVE_URL') ?></label>
	  <input type="checkbox" name="url_relative" value="relative"  <?php echo $this->relative_url_checked ?>
			 title="<?php echo Text::_('ATTACH_RELATIVE_URL_TOOLTIP'); ?>" />
	  <a class="changeButton" href="<?php echo $this->upload_toggle_url ?>"
		 title="<?php echo $this->upload_toggle_tooltip; ?>"><?php
		 echo $this->upload_toggle_button_text;?></a><br />

	  <input type="text" name="url" id="<?php echo $upload_id; ?>"
		 size="86" title="<?php echo Text::_('ATTACH_ENTER_URL_TOOLTIP'); ?>"
		 value="<?php echo $attachment->url; ?>" /><br /><?php
	  echo Text::_('ATTACH_NOTE_ENTER_URL_WITH_HTTP'); ?>
	</td>
 </tr>
 <tr>
	<td class="key"><label for="display_name" title="<?php echo $this->display_url_tooltip; ?>" class="hasTip"
			  ><?php echo Text::_('ATTACH_DISPLAY_URL'); ?></label></td>
	<td>
	   <input type="text" name="display_name" id="display_name" size="75" maxlength="80"
			  title="<?php echo $this->display_url_tooltip; ?>" class="hasTip"
			  value="<?php echo $attachment->display_name ?>" />&nbsp;<?php echo Text::_('ATTACH_OPTIONAL'); ?>
	</td>
 </tr>
<?php endif; ?>
  <tr>
	<td class="key"><label for="description"
			  title="<?php echo Text::_('ATTACH_DESCRIPTION_DESCRIPTION'); ?>"
		  ><?php echo Text::_('ATTACH_DESCRIPTION'); ?></label></td>
	<td>
	   <input type="text" name="description" id="description"
		  title="<?php echo Text::_('ATTACH_DESCRIPTION_DESCRIPTION'); ?>"
		  size="75" maxlength="255" value="<?php echo stripslashes($attachment->description) ?>" />
	</td>
  </tr>
<?php if ( $this->may_publish ): ?>
  <tr>
	<td class="key"><label><?php echo Text::_('ATTACH_PUBLISHED'); ?></label></td>
	<td><?php echo $this->lists['published']; ?></td>
  </tr>
<?php endif; ?>
  <tr>
	<td class="key"><label for="access" class="hasTip" title="<?php echo $this->access_level_tooltip ?>"><?php echo Text::_('JFIELD_ACCESS_LABEL'); ?></label></td>
	<td><?php echo $this->access_level; ?></td>
  </tr>
<?php if ( $this->show_user_field_1 ): ?>
  <tr>
	<td class="key"><label for="user_field_1"><?php echo $this->user_field_1_name; ?></label></td>
	<td><input type="text" name="user_field_1" id="user_field_1" size="75" maxlength="100" value="" /></td>
  </tr>
<?php endif; ?>
<?php if ( $this->show_user_field_2 ): ?>
  <tr>
	<td class="key"><label for="user_field_2"><?php echo $this->user_field_2_name; ?></label></td>
	<td><input type="text" name="user_field_2" id="user_field_2" size="75" maxlength="100" value="" /></td>
  </tr>
<?php endif; ?>
<?php if ( $this->show_user_field_3 ): ?>
  <tr>
	<td class="key"><label for="user_field_3"><?php echo $this->user_field_3_name; ?></label></td>
	<td><input type="text" name="user_field_3" id="user_field_3" size="75" maxlength="100" value="" /></td>
  <tr>
<?php endif; ?>
</table>
	</fieldset>
<?php if ( $this->new_parent ): ?>
	<input type="hidden" name="new_parent" value="1" />
<?php elseif ( $attachment->parent_id ): ?>
	<input type="hidden" name="parent_id" value="<?php echo $attachment->parent_id; ?>" />
<?php endif; ?>
	<input type="hidden" name="MAX_FILE_SIZE" value="524288" />
	<input type="hidden" name="save_type" value="upload" />
	<input type="hidden" name="parent_type" value="<?php echo $attachment->parent_type; ?>" />
	<input type="hidden" name="parent_entity" value="<?php echo $attachment->parent_entity; ?>" />
	<input type="hidden" name="uri_type" value="<?php echo $attachment->uri_type; ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="attachment.add" />
	<input type="hidden" name="from" value="<?php echo $this->from; ?>" />
	<?php if ( $this->from == 'closeme' ): ?>
	<div class="form_buttons" align="center">
	   <input type="submit" name="Submit" class="button"
			  onclick="javascript: submitbutton('attachment.saveNew')"
			  value="<?php echo Text::_('ATTACH_UPLOAD_VERB'); ?>" />
	   <span class="right">
		  <input type="button" name="cancel" value="<?php echo Text::_('ATTACH_CANCEL'); ?>"
				 onClick="window.parent.SqueezeBox.close();" />
	   </span>
	</div>
	<?php endif; ?>
	<?php echo HtmlHelper::_( 'form.token' ); ?>
</form>
<?php

// Show the existing attachments
if ( ($attachment->uri_type == 'file') && $attachment->parent_id ) {
	/** Get the Attachments controller class */
	$controller = new ListController();
	$controller->displayString($attachment->parent_id, $attachment->parent_type, $attachment->parent_entity,
							   'ATTACH_EXISTING_ATTACHMENTS', false, false, true, $this->from);
}
