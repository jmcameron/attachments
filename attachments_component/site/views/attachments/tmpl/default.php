<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Load the Attachments helper
require_once(JPATH_SITE.'/components/com_attachments/helper.php'); /* ??? Needed? */
require_once(JPATH_SITE.'/components/com_attachments/javascript.php');

$user = JFactory::getUser();
$logged_in = $user->get('username') <> '';

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
		AttachmentsJavascript::setupModalJavascript();
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
	if ( $this->show_creator_name ) {
		$html .= "<th class=\"at_creator_name\">" . JText::_('ATTACH_CREATOR') . "</th>";
		}
	if ( $this->show_file_size ) {
		$html .= "<th class=\"at_file_size\">" . JText::_('ATTACH_FILE_SIZE') . "</th>";
		}
	if ( $this->secure && $this->show_downloads ) {
		$html .= "<th class=\"at_downloads\">" . JText::_('ATTACH_DOWNLOADS') . "</th>";
		}
	if ( $this->show_created_date ) {
		$html .= "<th class=\"at_created_date\">" . JText::_('ATTACH_CREATED') . "</th>";
		}
	if ( $this->show_modified_date ) {
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
		$icon = $attachment->icon_filename;
	else
		$icon = 'generic.gif';

	if ( $this->show_file_size) {
		$file_size = (int)( $attachment->file_size / 1024.0 );
		if ( $file_size == 0 ) {
			// For files less than 1kB, show the fractional amount (in 1/10 kB)
			$file_size = ( (int)( 10.0 * $attachment->file_size / 1024.0 ) / 10.0 );
			}
		}

	if ( $this->show_created_date OR $this->show_modified_date ) {
		jimport( 'joomla.utilities.date' );
		$tz = new DateTimeZone( $user->getParam('timezone', $app->getCfg('offset')) );
		}

	if ( $this->show_created_date ) {
		$date = JFactory::getDate($attachment->created);
		$date->setTimezone($tz);
		$created = $date->format($this->date_format, true);
		}

	if ( $this->show_modified_date ) {
		$date = JFactory::getDate($attachment->modified);
		$date->setTimezone($tz);
		$last_modified = $date->format($this->date_format, true);
		}

	// Add the filename
	$target = '';
	if ( $this->file_link_open_mode == 'new_window')
		$target = ' target="_blank"';
	$html .= '<td class="at_filename">';
	if ( JString::strlen($attachment->display_name) == 0 )
		$filename = $attachment->filename;
	else
		$filename = htmlspecialchars(stripslashes($attachment->display_name));
	$actual_filename = $attachment->filename;
	// Uncomment the following two lines to replace '.pdf' with its HTML-encoded equivalent
	// $actual_filename = JString::str_ireplace('.pdf', '.&#112;&#100;&#102;', $actual_filename);
	// $filename = JString::str_ireplace('.pdf', '.&#112;&#100;&#102;', $filename);

	if ( $this->show_file_links ) {
		if ( $attachment->uri_type == 'file' ) {
			// Handle file attachments
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
			// Handle URL "attachments"
			if ( $this->secure ) {
				$url = JRoute::_("index.php?option=com_attachments&task=download&id=" . (int)$attachment->id);
				$tooltip = JText::sprintf('ATTACH_ACCESS_THIS_URL_S', $filename);
				}
			else {
				// Handle the link url if not logged in but link displayed for guests
				$url = '';
				if ( !$logged_in AND ($attachment->access != '1')) {
					$guest_levels = $this->params->get('show_guest_access_levels', Array('1'));
					if ( in_array($attachment->access, $guest_levels) ) {
						$app = JFactory::getApplication();
						$return = $app->getUserState('com_attachments.current_url', '');
						$url = JRoute::_('index.php?option=com_attachments&task=requestLogin' . $return);
						$target = '';
						}
					}
				if ( $url == '' ) {
					$url = $attachment->url;
					}
				$tooltip = JText::sprintf('ATTACH_ACCESS_THIS_URL_S', $attachment->url);
				}
			}
		$html .= "<a class=\"at_icon\" href=\"$url\"$target title=\"$tooltip\">";
		$html .= JHtml::image('com_attachments/file_icons/'.$icon, $tooltip, null, true);
		if ( ($attachment->uri_type == 'url') && $this->superimpose_link_icons ) {
			if ( $attachment->url_valid ) {
				$html .= JHtml::image('com_attachments/file_icons/link_arrow.png', '', 'class="link_overlay"', true);
				}
			else {
				$html .= JHtml::image('com_attachments/file_icons/link_broken.png', '', 'class="link_overlay"', true);
				}
			}
		$html .= "</a>";
		$html .= "<a class=\"at_url\" href=\"$url\"$target title=\"$tooltip\">$filename</a>";
		}
	else {
		$tooltip = JText::sprintf('ATTACH_DOWNLOAD_THIS_FILE_S', $actual_filename);
		$html .= JHtml::image('com_attachments/file_icons/'.$icon, $tooltip, null, true);
		$html .= '&nbsp;' . $filename;
		}
	$html .= "</td>";

	// Add description (maybe)
	if ( $this->show_description ) {
		$description = htmlspecialchars(stripslashes($attachment->description));
		if ( JString::strlen($description) == 0)
			$description = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_description\">$description</td>";
		else
			$html .= "<td class=\"at_description\">[$description]</td>";
		}

	// Show the USER DEFINED FIELDs (maybe)
	if ( $this->show_user_field_1 ) {
		$user_field = stripslashes($attachment->user_field_1);
		if ( JString::strlen($user_field) == 0 )
			$user_field = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
		else
			$html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
		}
	if ( $this->show_user_field_2 ) {
		$user_field = stripslashes($attachment->user_field_2);
		if ( JString::strlen($user_field) == 0 )
			$user_field = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
		else
			$html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
		}
	if ( $this->show_user_field_3 ) {
		$user_field = stripslashes($attachment->user_field_3);
		if ( JString::strlen($user_field) == 0 )
			$user_field = '&nbsp;';
		if ( $this->show_column_titles )
			$html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
		else
			$html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
		}

	// Add the creator's username (if requested)
	if ( $this->show_creator_name ) {
		$html .= "<td class=\"at_creator_name\">{$attachment->creator_name}</td>";
		}

	// Add file size (maybe)
	if ( $this->show_file_size ) {
		$file_size_str = JText::sprintf('ATTACH_S_KB', $file_size);
		if ( $file_size_str == 'ATTACH_S_KB' ) {
			// Work around until all translations are updated ???
			$file_size_str = $file_size . ' kB';
			}
		$html .= '<td class="at_file_size">' . $file_size_str . '</td>';
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

	// Add the created and modification date (maybe)
	if ( $this->show_created_date ) {
		$html .= "<td class=\"at_created_date\">$created</td>";
		}
	if ( $this->show_modified_date ) {
		$html .= "<td class=\"at_mod_date\">$last_modified</td>";
		}

	$update_link = '';
	$delete_link = '';

	// Add the link to delete the parent, if requested
	if ( $this->some_attachments_modifiable && $attachment->user_may_edit && $this->allow_edit ) {

		// Create the edit link
		$update_url = str_replace('%d', (string)$attachment->id, $this->update_url);
		$tooltip = JText::_('ATTACH_UPDATE_THIS_FILE') . ' (' . $actual_filename . ')';
		$update_link = '<a class="modal-button" type="button" href="' . $update_url . '"';
		$update_link .= " rel=\"{handler: 'iframe', size: {x: 920, y: 600}}\" title=\"$tooltip\">";
		$update_link .= JHtml::image('com_attachments/pencil.gif', $tooltip, null, true);
		$update_link .= "</a>";
		}

	if ( $this->some_attachments_modifiable && $attachment->user_may_delete && $this->allow_edit ) {

		// Create the delete link
		$delete_url = str_replace('%d', (string)$attachment->id, $this->delete_url);
		$tooltip = JText::_('ATTACH_DELETE_THIS_FILE') . ' (' . $actual_filename . ')';
		$delete_link = '<a class="modal-button" type="button" href="' . $delete_url . '"';
		$delete_link .= " rel=\"{handler: 'iframe', size: {x: 600, y: 400}, iframeOptions: {scrolling: 'no'}}\" title=\"$tooltip\">";
		$delete_link .= JHtml::image('com_attachments/delete.gif', $tooltip, null, true);
		$delete_link .= "</a>";
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
