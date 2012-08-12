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

$user = JFactory::getUser();
$app = JFactory::getApplication();
$uri = JFactory::getURI();

// Set a few variables for convenience
$attachments = $this->list;
$parent_id = $this->parent_id;
$parent_type = $this->parent_type;
$parent_entity = $this->parent_entity;

$base_url = $this->base_url;

$format = JRequest::getWord('format', '');

$html = '';

if ( $format != 'raw' ) {

	// If any attachments are modifiable, add necessary Javascript for iframe
	if ( $this->some_attachments_modifiable ) {
		JHTML::_('behavior.modal', 'a.modal-button');
		}

	/** Load the attachments helper to add the stylesheet */
	require_once(JPATH_SITE.'/components/com_attachments/helper.php');
	AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments.css' );

	// Handle RTL styling (if necessary)
	$lang = JFactory::getLanguage();
	if ( $lang->isRTL() ) {
		AttachmentsHelper::addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments_rtl.css' );
		}

	// Construct the empty div for the attachments
	if ( $parent_id === null ) {
		// If there is no parent_id, the parent is being created, use the username instead
		$pid = $user->get('username');
		}
	else {
		$pid = $parent_id;
		}
	$div_id = 'attachmentsList' . '_' . $parent_type . '_' . $parent_entity	 . '_' . (string)$pid;
	$html .= "\n<div class=\"$this->style\" id=\"$div_id\">\n";
	}

$html .= "<table>\n";
$html .= "<caption>{$this->title}</caption>\n";

// Add the column titles, if requested
if ( $this->show_column_titles ) {
	$html .= "<thead>\n<tr>";
	$html .= "<th class=\"at_filename\">" . $this->file_url_title . "</th>";
	if ( $this->show_description ) {
		$html .= "<th class=\"at_description\">" . JText::_('ATTACH_DESCRIPTION') . "</th>";
		}
	if ( $this->show_user_field_1 ) {
		$html .= "<th class=\"at_user_field\">" . $this->user_field_1_name . "</th>";
		}
	if ( $this->show_user_field_2 ) {
		$html .= "<th class=\"at_user_field\">" . $this->user_field_2_name . "</th>";
		}
	if ( $this->show_user_field_3 ) {
		$html .= "<th class=\"at_user_field\">" . $this->user_field_3_name . "</th>";
		}
	if ( $this->show_creator ) {
		$html .= "<th class=\"at_creator_name\">" . JText::_('ATTACH_CREATOR') . "</th>";
		}
	if ( $this->show_file_size ) {
		$html .= "<th class=\"at_file_size\">" . JText::_('ATTACH_FILE_SIZE') . "</th>";
		}
	if ( $this->secure && $this->show_downloads ) {
		$html .= "<th class=\"at_downloads\">" . JText::_('ATTACH_DOWNLOADS') . "</th>";
		}
	if ( $this->show_mod_date ) {
		$html .= "<th class=\"at_mod_date\">" . JText::_('ATTACH_LAST_MODIFIED') . "</th>";
		}
	if ( $this->some_attachments_modifiable && $this->allow_edit ) {
		$html .= "<th class=\"at_edit\">&nbsp;</th>";
		}
	$html .= "</tr>\n</thead>\n";
	}

$html .= "<tbody>\n";

// Construct the lines for the attachments
$row_num = 0;
for ($i=0, $n=count($attachments); $i < $n; $i++) {
	$attachment = $attachments[$i];

	$row_num++;
	if ( $row_num & 1 == 1) {
		$row_class = 'odd';
		}
	else {
		$row_class = 'even';
		}

	if ($attachment->state != 1) {
		$row_class = 'unpublished';
		}

	$html .= '<tr class="'.$row_class.'">';
		
	// Construct some display items
	if ( JString::strlen($attachment->icon_filename) > 0 )
		$icon_url = $this->icon_url_base . $attachment->icon_filename;
	else
		$icon_url = $this->icon_url_base . 'generic.gif';
	$link_icon_url = $this->icon_url_base . 'link_arrow.png';
	$link_broken_icon_url = $this->icon_url_base . 'link_broken.png';

	if ( $this->show_file_size) {
		$file_size = (int)( $attachment->file_size / 1024.0 );
		if ( $file_size == 0 ) {
			// For files less than 1K, show the fractional amount (in 1/10 KB)
			$file_size = ( (int)( 10.0 * $attachment->file_size / 1024.0 ) / 10.0 );
			}
		}

	if ( $this->show_mod_date ) {
		jimport( 'joomla.utilities.date' );
		$tz = new DateTimeZone( $user->getParam('timezone', $app->getCfg('offset')) );
		$date = JFactory::getDate($attachment->modified);
		$date->setTimezone($tz);
		$last_modified = $date->toFormat($this->mod_date_format, true);
		}

	// Add the filename
	$target = '';
	if ( $this->file_link_open_mode == 'new_window')
		$target = ' target="_blank"';
	$html .= '<td class="at_filename">';
	if ( JString::strlen($attachment->display_name) == 0 )
		$filename = $attachment->filename;
	else
		$filename = $attachment->display_name;
	$actual_filename = $attachment->filename;
	// Uncomment the following two lines to replace '.pdf' with its HTML-encoded equivalent
	// $actual_filename = JString::str_ireplace('.pdf', '.&#112;&#100;&#102;', $actual_filename);
	// $filename = JString::str_ireplace('.pdf', '.&#112;&#100;&#102;', $filename);
	if ( $this->show_file_links ) {
		if ( $attachment->uri_type == 'file' ) {
			if ( $this->secure ) {
				$url = JRoute::_("index.php?option=com_attachments&task=download&id=" . (int)$attachment->id);
				}
			else {
				$url = $base_url . $attachment->url;
				if (strtoupper(substr(PHP_OS,0,3) == 'WIN')) {
				   $url = utf8_encode($url);
				   }
				}
			$tooltip = JText::sprintf('ATTACH_DOWNLOAD_THIS_FILE_S', $actual_filename);
			}
		else {
			$url = $attachment->url;
			$tooltip = JText::sprintf('ATTACH_ACCESS_THIS_URL_S', $attachment->url);
			}
		$html .= "<a class=\"at_icon\" href=\"$url\"$target title=\"$tooltip\"><img src=\"$icon_url\" alt=\"$tooltip\" />";
		if ( ($attachment->uri_type == 'url') && $this->superimpose_link_icons ) {
			if ( $attachment->url_valid ) {
				$html .= "<img id=\"link\" src=\"$link_icon_url\" />";
				}
			else {
				$html .= "<img id=\"link\" src=\"$link_broken_icon_url\" />";
				}
			}
		$html .= "</a>";
		$html .= "<a class=\"at_url\" href=\"$url\"$target target=\"_blank\" title=\"$tooltip\">$filename</a>";
		}
	else {
		$tooltip = JText::sprintf('ATTACH_DOWNLOAD_THIS_FILE_S', $actual_filename);
		$html .= "<img src=\"$icon_url\" alt=\"$tooltip\" />&nbsp;";
		$html .= $filename;
		}
	$html .= "</td>";

	// Add description (maybe)
	if ( $this->show_description ) {
		$description = $attachment->description;
		if ( JString::strlen($description) == 0)
			$description = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_description\">$description</td>";
		else
			$html .= "<td class=\"at_description\">[$description]</td>";
		}

	// Show the USER DEFINED FIELDs (maybe)
	if ( $this->show_user_field_1 ) {
		$user_field = $attachment->user_field_1;
		if ( JString::strlen($user_field) == 0 )
			$user_field = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
		else
			$html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
		}
	if ( $this->show_user_field_2 ) {
		$user_field = $attachment->user_field_2;
		if ( JString::strlen($user_field) == 0 )
			$user_field = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
		else
			$html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
		}
	if ( $this->show_user_field_3 ) {
		$user_field = $attachment->user_field_3;
		if ( JString::strlen($user_field) == 0 )
			$user_field = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
		else
			$html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
		}

	// Add the creator's username (if requested)
	if ( $this->show_creator ) {
		$html .= "<td class=\"at_creator_name\">{$attachment->creator_name}</td>";
		}

	// Add file size (maybe)
	if ( $this->show_file_size ) {
		$html .= "<td class=\"at_file_size\">$file_size Kb</td>";
		}

	// Show number of downloads (maybe)
	if ( $this->secure && $this->show_downloads ) {
		$num_downloads = (int)$attachment->download_count;
		$label = '';
		if ( ! $this->show_column_titles ) {
			if ( $num_downloads == 1 )
				$label = '&nbsp;' . JText::_('ATTACH_DOWNLOAD_NOUN');
			else
				$label = '&nbsp;' . JText::_('ATTACH_DOWNLOADS');
			}
		$html .= '<td class="at_downloads">'. $num_downloads.$label.'</td>';
		}

	// Add the modification date (maybe)
	if ( $this->show_mod_date ) {
		$html .= "<td class=\"at_mod_date\">$last_modified</td>";
		}

	$update_link = '';
	$delete_link = '';

	// Add the link to delete the parent, if requested
	if ( $this->some_attachments_modifiable && $attachment->user_may_edit && $this->allow_edit ) {

		// Create the edit link
		$update_url = sprintf($this->update_url, (int)$attachment->id);
		$update_img = $base_url . 'components/com_attachments/media/pencil.gif';
		$tooltip = JText::_('ATTACH_UPDATE_THIS_FILE') . ' (' . $actual_filename . ')';
		$update_link = '<a class="modal-button" type="button" href="' . $update_url . '"';
		$update_link .= " rel=\"{handler: 'iframe', size: {x: 920, y: 580}}\"";
		$update_link .= " title=\"$tooltip\"><img src=\"$update_img\" alt=\"$tooltip\" /></a>";
		}

	if ( $this->some_attachments_modifiable && $attachment->user_may_delete && $this->allow_edit ) {

		// Create the delete link
		$delete_url = sprintf($this->delete_url, (int)$attachment->id);
		$delete_img = $base_url . 'components/com_attachments/media/delete.gif';
		$tooltip = JText::_('ATTACH_DELETE_THIS_FILE') . ' (' . $actual_filename . ')';
		$delete_link = '<a class="modal-button" type="button" href="' . $delete_url . '"';
		$delete_link .= " rel=\"{handler: 'iframe', size: {x: 600, y: 300}}\"";
		$delete_link .= " title=\"$tooltip\"><img src=\"$delete_img\" alt=\"$tooltip\" /></a>";
		}

	if ( $this->some_attachments_modifiable && $this->allow_edit ) {
		$html .= "<td class=\"at_edit\">$update_link $delete_link</td>";
		}

	$html .= "</tr>\n";
	}

// Close the HTML
$html .= "</tbody></table>\n";

if ( $format != 'raw' ) {
	$html .= "</div>\n";
	}

echo $html;
