<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use JMCameron\Component\Attachments\Site\Helper\AttachmentsDefines;
use JMCameron\Component\Attachments\Site\Helper\AttachmentsJavascript;
use JMCameron\Component\Attachments\Site\Helper\AttachmentsFileTypes;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


$app = Factory::getApplication();
$user = $app->getIdentity();
$logged_in = $user->get('username') <> '';

$uri = Uri::getInstance();

// Set a few variables for convenience
$attachments = $this->list;
$parent_id = $this->parent_id;
$parent_type = $this->parent_type;
$parent_entity = $this->parent_entity;

$base_url = $this->base_url;

$input = $app->getInput();
$format = $input->getWord('format', '');

$html = '';

if ($format != 'raw') {
    // If any attachments are modifiable, add necessary Javascript for iframe
    if ($this->some_attachments_modifiable) {
        // AttachmentsJavascript::setupModalJavascript();
    }

    // Construct the empty div for the attachments
    if ($parent_id === null) {
        // If there is no parent_id, the parent is being created, use the username instead
        $pid = $user->get('username');
    } else {
        $pid = $parent_id;
    }
    $div_id = 'attachmentsList' . '_' . $parent_type . '_' . $parent_entity  . '_' . (string)$pid;
    $html .= "\n<div class=\"$this->style\" id=\"$div_id\">\n";
}

$html .= "<table>\n";
$html .= "<caption style=\"caption-side:top\">{$this->title}</caption>\n";

// Add the column titles, if requested
if ($this->show_column_titles) {
    $html .= "<thead>\n<tr>";
    $html .= "<th class=\"at_filename\">" . $this->file_url_title . "</th>";
    if ($this->show_description) {
        $html .= "<th class=\"at_description\">" . Text::_('ATTACH_DESCRIPTION') . "</th>";
    }
    if ($this->show_user_field_1) {
        $html .= "<th class=\"at_user_field\">" . $this->user_field_1_name . "</th>";
    }
    if ($this->show_user_field_2) {
        $html .= "<th class=\"at_user_field\">" . $this->user_field_2_name . "</th>";
    }
    if ($this->show_user_field_3) {
        $html .= "<th class=\"at_user_field\">" . $this->user_field_3_name . "</th>";
    }
    if ($this->show_creator_name) {
        $html .= "<th class=\"at_creator_name\">" . Text::_('ATTACH_CREATOR') . "</th>";
    }
    if ($this->show_file_size) {
        $html .= "<th class=\"at_file_size\">" . Text::_('ATTACH_FILE_SIZE') . "</th>";
    }
    if ($this->secure && $this->show_downloads) {
        $html .= "<th class=\"at_downloads\">" . Text::_('ATTACH_DOWNLOADS') . "</th>";
    }
    if ($this->show_created_date) {
        $html .= "<th class=\"at_created_date\">" . Text::_('ATTACH_CREATED') . "</th>";
    }
    if ($this->show_modified_date) {
        $html .= "<th class=\"at_mod_date\">" . Text::_('ATTACH_LAST_MODIFIED') . "</th>";
    }
    if ($this->some_attachments_modifiable && $this->allow_edit) {
        $html .= "<th class=\"at_edit\">&nbsp;</th>";
    }
    $html .= "</tr>\n</thead>\n";
}

$html .= "<tbody>\n";

// Load Bootstrap modal code
if ($this->file_link_open_mode == 'in_a_popup') {
    AttachmentsJavascript::setupModalJavascript();
}

if ($this->use_fontawesome_icons) {
    $faIconsStyle = $this->use_fontawesome_icons_style;
}

// Construct the lines for the attachments
$row_num = 0;
for ($i = 0, $n = count($attachments); $i < $n; $i++) {
    $attachment = $attachments[$i];

    $row_num++;
    if ($row_num & 1 == 1) {
        $row_class = 'odd';
    } else {
        $row_class = 'even';
    }

    if ($attachment->state != 1) {
        $row_class = 'unpublished';
    }

    $html .= '<tr class="' . $row_class . '">';

    // Construct some display items
    if ($this->use_fontawesome_icons) {
        $icon = AttachmentsFileTypes::faIconFilename('', $attachment->file_type);
    } else {
        if (StringHelper::strlen($attachment->icon_filename) > 0) {
            $icon = $attachment->icon_filename;
        } else {
            $icon = 'generic.gif';
        }
    }

    if ($this->show_file_size) {
        $file_size = (int)( $attachment->file_size / 1024.0 );
        if ($file_size == 0) {
            // For files less than 1kB, show the fractional amount (in 1/10 kB)
            $file_size = ( (int)( 10.0 * $attachment->file_size / 1024.0 ) / 10.0 );
        }
    }

    if ($this->show_created_date or $this->show_modified_date) {
        $tz = new DateTimeZone($user->getParam('timezone', $app->get('offset')));
    }

    if ($this->show_created_date) {
        $date = Factory::getDate($attachment->created);
        $date->setTimezone($tz);
        $created = $date->format($this->date_format, true);
    }

    if ($this->show_modified_date) {
        $date = Factory::getDate($attachment->modified);
        $date->setTimezone($tz);
        $last_modified = $date->format($this->date_format, true);
    }

    // Add the filename
    $target = '';
    if ($this->file_link_open_mode == 'new_window') {
        $target = ' target="_blank"';
    }
    $html .= '<td class="at_filename">';
    if (StringHelper::strlen($attachment->display_name) == 0) {
        $filename = $attachment->filename;
    } else {
        $filename = htmlspecialchars(stripslashes($attachment->display_name));
    }
    $actual_filename = $attachment->filename;
    // Uncomment the following two lines to replace '.pdf' with its HTML-encoded equivalent
    // $actual_filename = StringHelper::str_ireplace('.pdf', '.&#112;&#100;&#102;', $actual_filename);
    // $filename = StringHelper::str_ireplace('.pdf', '.&#112;&#100;&#102;', $filename);

    if ($this->show_file_links) {
        if ($attachment->uri_type == 'file') {
            // Handle file attachments
            if ($this->secure) {
                $url = Route::_($base_url . "index.php?option=com_attachments&task=download&id=" .
                               (int)$attachment->id);
            } else {
                // We need to urlencode the filename
                $offset = strlen(AttachmentsDefines::$ATTACHMENTS_SUBDIR .
                                 "/{$attachment->parent_entity}/{$attachment->parent_id}/");
                $url_path = mb_strcut($attachment->url, 0, $offset);
                $url_filename = rawurlencode(mb_strcut($attachment->url, $offset));
                $url = $base_url . $url_path . $url_filename;

                if (strtoupper(substr(PHP_OS, 0, 3) == 'WIN')) {
                    // $url = utf8_encode($url); Deprecated from php 8.2
                    $url = iconv('ISO-8859-1', 'UTF-8', $url);
                }
            }
            $tooltip = Text::sprintf('ATTACH_DOWNLOAD_THIS_FILE_S', $actual_filename);
        } else {
            // Handle URL "attachments"
            if ($this->secure) {
                $url = Route::_($base_url .
                                "index.php?option=com_attachments&task=download&id=" .
                                (int)$attachment->id);
                $tooltip = Text::sprintf('ATTACH_ACCESS_THIS_URL_S', $filename);
            } else {
                // Handle the link url if not logged in but link displayed for guests
                $url = '';
                if (!$logged_in and ($attachment->access != '1')) {
                    $guest_levels = $this->params->get('show_guest_access_levels', array('1'));
                    if (in_array($attachment->access, $guest_levels)) {
                        /** @var \Joomla\CMS\Application\CMSApplication $app */
                        $app = Factory::getApplication();
                        $return = $app->getUserState('com_attachments.current_url', '');
                        $url = Route::_($base_url . 'index.php?option=com_attachments&task=requestLogin' . $return);
                        $target = '';
                    }
                }
                if ($url == '') {
                    $url = $attachment->url;
                }
                $tooltip = Text::sprintf('ATTACH_ACCESS_THIS_URL_S', $attachment->url);
            }
        }

        $show_in_modal = ($this->file_link_open_mode == 'in_a_popup') &&
                         ($attachment->file_type === "application/pdf" ||
                          str_starts_with($attachment->file_type, "text/") ||
                          str_starts_with($attachment->file_type, "image/"));

        AttachmentsJavascript::setupModalJavascript();

        $randomId = base64_encode('show' . $attachment->id . $actual_filename);
        // Remove +,/,= from the $randomId
        $randomId = strtr($randomId, "+/=", "AAA");
        $modalParams['title']  = $this->escape($tooltip);
        $modalParams['url']    = $url;
        $modalParams['height'] = '60vh';
        $modalParams['width']  = '100%';
        $modalParams['bodyHeight'] = '80';
        $modalParams['modalWidth'] = '80';
        if ($this->secure) {
            $url .= "&popup=1";
        }
        /* do not add modal if not needed */
        if ($show_in_modal) {
            $html .= LayoutHelper::render(
                'libraries.html.bootstrap.modal.main',
                [
                    'selector' => 'modal-' . $randomId,
                    'body' => "<iframe
                                src=\"$url\"
                                scrolling=\"auto\"
                                loading=\"lazy\">
                               </iframe>",
                    'params' => $modalParams
                ]
            );
        }

        if ($show_in_modal) {
            $a_class = 'attachment modal-button';
        } else {
            $a_class = 'at_icon';
        }

        $show_link = "<a class=\"" . $a_class . "\" href=\"$url\"$target data-bs-target='#modal-$randomId' title=\"$tooltip\">";
        if ($this->use_fontawesome_icons) {
            $show_link .= '<i class="' . $faIconsStyle . ' ' . $icon . '"></i>';
        } else {
            $show_link .= HTMLHelper::image('com_attachments/file_icons/' . $icon, null, null, true);
        }
        $show_link .= "&nbsp;" . $filename . "</a>";

        $html .= $show_link;
        if (($attachment->uri_type == 'url') && $this->superimpose_link_icons) {
            if ($attachment->url_valid) {
                if ($this->use_fontawesome_icons) {
                    $html .= '<i class="' . $faIconsStyle . ' fa-eye-slash"></i>';
                } else {
                    $html .= HTMLHelper::image(
                        'com_attachments/file_icons/link_arrow.png',
                        '',
                        'class="link_overlay"',
                        true
                    );
                }
            } else {
                if ($this->use_fontawesome_icons) {
                    $html .= '<i class="' . $faIconsStyle . ' fa-eye-slash redicon"></i>';
                } else {
                    $html .= HTMLHelper::image(
                        'com_attachments/file_icons/link_broken.png',
                        '',
                        'class="link_overlay"',
                        true
                    );
                }
            }
        }
    } else {
        $tooltip = Text::sprintf('ATTACH_DOWNLOAD_THIS_FILE_S', $actual_filename);
        if ($this->use_fontawesome_icons) {
            $html .= '<i class="' . $faIconsStyle . ' ' . $icon . '" title="' . $tooltip . '"></i>';
        } else {
            $html .= HTMLHelper::image('com_attachments/file_icons/' . $icon, $tooltip, null, true);
        }
        $html .= '&nbsp;' . $filename;
    }
    $html .= "</td>";

    // Add description (maybe)
    if ($this->show_description) {
        $description = htmlspecialchars(stripslashes($attachment->description));

        $is_empty = 0;
        if (StringHelper::strlen($description) == 0) {
            $description = '&nbsp;';
            $is_empty = 1;
        }

        if ($this->show_column_titles) {
            $html .= "<td class=\"at_description\">$description</td>";
        } else {
            if ($is_empty && $this->params->get('hide_brackets_if_empty')) {
                $html .= "<td class=\"at_description\"></td>";
            } else {
                $html .= "<td class=\"at_description\">[$description]</td>";
            }
        }
    }
    // Show the USER DEFINED FIELDs (maybe)
    if ($this->show_user_field_1) {
        $user_field = stripslashes($attachment->user_field_1);
        $is_empty = 0;
        if (StringHelper::strlen($user_field) == 0) {
            $user_field = '&nbsp;';
            $is_empty = 1;
        }

        if ($this->show_column_titles) {
            $html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
        } else {
            if ($is_empty && $this->params->get('hide_brackets_if_empty')) {
                $html .= "<td class=\"at_user_field\"></td>";
            } else {
                $html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
            }
        }
    }
    if ($this->show_user_field_2) {
        $user_field = stripslashes($attachment->user_field_2);
        $is_empty = 0;
        if (StringHelper::strlen($user_field) == 0) {
            $user_field = '&nbsp;';
            $is_empty = 1;
        }

        if ($this->show_column_titles) {
            $html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
        } else {
            if ($is_empty && $this->params->get('hide_brackets_if_empty')) {
                $html .= "<td class=\"at_user_field\"></td>";
            } else {
                $html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
            }
        }
    }
    if ($this->show_user_field_3) {
        $user_field = stripslashes($attachment->user_field_3);
        $is_empty = 0;
        if (StringHelper::strlen($user_field) == 0) {
            $user_field = '&nbsp;';
            $is_empty = 1;
        }

        if ($this->show_column_titles) {
            $html .= "<td class=\"at_user_field\">" . $user_field . "</td>";
        } else {
            if ($is_empty && $this->params->get('hide_brackets_if_empty')) {
                $html .= "<td class=\"at_user_field\"></td>";
            } else {
                $html .= "<td class=\"at_user_field\">[" . $user_field . "]</td>";
            }
        }
    }
    // Add the creator's username (if requested)
    if ($this->show_creator_name) {
        $html .= "<td class=\"at_creator_name\">{$attachment->creator_name}</td>";
    }

    // Add file size (maybe)
    if ($this->show_file_size) {
        $file_size_str = Text::sprintf('ATTACH_S_KB', $file_size);
        if ($file_size_str == 'ATTACH_S_KB') {
            // Work around until all translations are updated ???
            $file_size_str = $file_size . ' kB';
        }
        $html .= '<td class="at_file_size">' . $file_size_str . '</td>';
    }
    if ($this->show_raw_download &&  $show_in_modal) {
        // avoid beeing scanned by javascript it is not a modal link
        $a_class = 'at_icon';
        $url = Route::_($base_url .
                        "index.php?option=com_attachments&task=download&id=" .
                        (int)$attachment->id . "&raw=1");
        $html .=  '<td class="at_icon">';
        $tooltip = Text::sprintf('ATTACH_DOWNLOAD_THIS_FILE_S', $actual_filename);
        if ($this->use_fontawesome_icons) {
            $html .= "<a class=\"" . $a_class . "\" href=\"$url\"$target title=\"$tooltip\">" .
                '<i class="' . $faIconsStyle . ' fa-download"></i></a></td>';
        } else {
            $html .= "<a class=\"" . $a_class . "\" href=\"$url\"$target title=\"$tooltip\">" .
                HTMLHelper::image("com_attachments/download.gif", "", null, true) . '</a></td>';
        }
    }
    // Show number of downloads (maybe)
    if ($this->secure && $this->show_downloads) {
        $num_downloads = (int)$attachment->download_count;
        $label = '';
        if (! $this->show_column_titles) {
            if ($num_downloads == 1) {
                $label = '&nbsp;' . Text::_('ATTACH_DOWNLOAD_NOUN');
            } else {
                $label = '&nbsp;' . Text::_('ATTACH_DOWNLOADS');
            }
        }
        $html .= '<td class="at_downloads">' . $num_downloads . $label . '</td>';
    }

    // Add the created and modification date (maybe)
    if ($this->show_created_date) {
        $html .= "<td class=\"at_created_date\">$created</td>";
    }
    if ($this->show_modified_date) {
        $html .= "<td class=\"at_mod_date\">$last_modified</td>";
    }

    $update_link = '';
    $delete_link = '';

    $a_class = 'modal-button mx-2';
    // if ( $app->isClient('administrator') ) {
    //  $a_class = 'modal';
    //  }

    // Add the link to edit the attachment, if requested
    if ($this->some_attachments_modifiable && $attachment->user_may_edit && $this->allow_edit) {
        // Create the edit link
        $update_url = str_replace('%d', (string)$attachment->id, $this->update_url);
        $tooltip = Text::_('ATTACH_UPDATE_THIS_FILE') . ' (' . $actual_filename . ')';

        $randomId = base64_encode('update' . $attachment->id . $actual_filename);
        // Remove +,/,= from the $randomId
        $randomId = strtr($randomId, "+/=", "AAA");
        $modalParams['title']  = $this->escape($tooltip);
        $modalParams['url']    = $update_url;
        $modalParams['height'] = '60vh';
        $modalParams['width']  = '100%';
        $modalParams['bodyHeight'] = 75;
        $modalParams['modalWidth'] = 80;
        $html .= LayoutHelper::render(
            'libraries.html.bootstrap.modal.main',
            [
                'selector' => 'modal-' . $randomId,
                'body' => "<iframe
                            src=\"$update_url\"
                            scrolling=\"yes\"
                            loading=\"lazy\">
                           </iframe>",
                'params' => $modalParams
            ]
        );

        $update_link = "<a class=\"$a_class\" type=\"button\" data-bs-toggle='modal' data-bs-target='#modal-$randomId'";
        $update_link .= "title=\"$tooltip\">";
        if ($this->use_fontawesome_icons) {
            $update_link .= '<i class="' . $faIconsStyle . ' fa-edit"></i>';
        } else {
            $update_link .= HTMLHelper::image('com_attachments/pencil.gif', $tooltip, null, true);
        }
        $update_link .= "</a>";
    }

    // Add the link to delete the attachment, if requested
    if ($this->some_attachments_modifiable && $attachment->user_may_delete && $this->allow_edit) {
        // Create the delete link
        $delete_url = str_replace('%d', (string)$attachment->id, $this->delete_url);
        $tooltip = Text::_('ATTACH_DELETE_THIS_FILE') . ' (' . $actual_filename . ')';

        $randomId = base64_encode('delete' . $attachment->id . $actual_filename);
        // Remove +,/,= from the $randomId
        $randomId = strtr($randomId, "+/=", "AAA");
        $modalParams['title']  = $this->escape($tooltip);
        $modalParams['url']    = $delete_url;
        $modalParams['height'] = '100%';
        $modalParams['width']  = '100%';
        $modalParams['bodyHeight'] = 40;
        $modalParams['modalWidth'] = 80;
        $html .= LayoutHelper::render(
            'libraries.html.bootstrap.modal.main',
            [
                'selector' => 'modal-' . $randomId,
                'body' => "<iframe
                            src=\"$delete_url\"
                            scrolling=\"yes\"
                            loading=\"lazy\">
                          </iframe>",
                'params' => $modalParams
            ]
        );

        $delete_link = "<a class=\"$a_class\" type=\"button\" data-bs-toggle='modal' data-bs-target='#modal-$randomId'";
        $delete_link .= "title=\"$tooltip\">";
        if ($this->use_fontawesome_icons) {
            $delete_link .= '<i class="' . $faIconsStyle . ' fa-trash"></i>';
        } else {
            $delete_link .= HTMLHelper::image('com_attachments/delete.gif', $tooltip, null, true);
        }
        $delete_link .= "</a>";
    }

    if ($this->some_attachments_modifiable && $this->allow_edit) {
        $html .= "<td class=\"at_edit\">$update_link $delete_link</td>";
    }

    $html .= "</tr>\n";
}

// Close the HTML
$html .= "</tbody></table>\n";

// If attachments should be opened in a popup on desktop, modify the links
AttachmentsJavascript::modifyLinksForDesktop();

if ($format != 'raw') {
    $html .= "</div>\n";
}

echo $html;
