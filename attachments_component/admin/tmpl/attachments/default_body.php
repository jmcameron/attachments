<?php

/**
 * Attachments component attachments view
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use JMCameron\Component\Attachments\Site\Helper\AttachmentsFileTypes;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Filter\OutputFilter;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


// Set up a few convenience items
$app = Factory::getApplication();
$user = $app->getIdentity();
$uri = Uri::getInstance();
$params = $this->params;
$secure = $params->get('secure', false);
$superimpose_link_icons = $params->get('superimpose_url_link_icons', true);
$icon_dir = $uri->root(true) . '/components/com_attachments/media/icons/';
$use_fontawesome_icons = $params->get('use_fontawesome_icons', false);
$use_fontawesome_icons_style = $params->get('use_fontawesome_icons_style', 'fas');

// Loop through all the attachments
$k = 0;
$last_parent_id = null;
$last_parent_type = null;
$last_parent_entity = null;


for ($i = 0, $n = count($this->items); $i < $n; $i++) {
    $item = $this->items[$i];

    if ($item->uri_type == 'file') {
        if ($secure) {
            $url = Route::_("index.php?option=com_attachments&amp;task=attachment.download&amp;id=" . (int)$item->id);
        } else {
            $url = $uri->root(true) . '/' . $item->url;
        }
    } else {
        $url = $item->url;
    }
    $checked = HTMLHelper::_('grid.id', $i, $item->id);
    $published = HTMLHelper::_('jgrid.published', $item->state, $i, 'attachments.');
    $access = $this->level_name[$item->access];

    $size_kb = (int)(10 * $item->file_size / 1024) / 10.0;
    $link = OutputFilter::ampReplace('index.php?option=com_attachments&amp;task=attachment.edit&amp;cid[]=' .
                                    (int)$item->id);
    $view_parent_title = Text::_('ATTACH_VIEW_ARTICLE_TITLE');
    if ($use_fontawesome_icons) {
        $icon = AttachmentsFileTypes::faIconFilename('', $item->file_type);
    } else {
        if (StringHelper::strlen($item->icon_filename) > 0) {
            $icon = $item->icon_filename;
        } else {
            $icon = 'generic.gif';
        }
    }
    $add_attachment_title = Text::_('ATTACH_ADD_ATTACHMENT_TITLE');
    $edit_attachment_title = Text::_('ATTACH_EDIT_THIS_ATTACHMENT_TITLE');
    $access_attachment_title = Text::_('ATTACH_ACCESS_THIS_ATTACHMENT_TITLE');

    // Set up the create/modify dates
    $tz = new DateTimeZone($user->getParam('timezone', $app->get('offset')));

    $cdate = Factory::getDate($item->created);
    $cdate->setTimeZone($tz);
    $created = $cdate->format("Y-m-d H:i", true);

    $mdate = Factory::getDate($item->modified);
    $mdate->setTimeZone($tz);
    $modified = $mdate->format("Y-m-d H:i", true);

    $add_attachment_txt = Text::_('ATTACH_ADD_ATTACHMENT');
    if (
        ($item->parent_id != $last_parent_id) || ($item->parent_type != $last_parent_type)
         || ($item->parent_entity != $last_parent_entity)
    ) {
        $parent_type = $item->parent_type;
        if ($item->parent_entity != 'default') {
            $parent_type .= '.' . $item->parent_entity;
        }
        if (($item->parent_id == null) || !$item->parent_exists) {
            $artLine = '<tr><td class="at_parentsep" colspan="' . $this->num_columns . '">';
            $artLine .= '<b>' . $item->parent_entity_type . ':</b> 
                        <span class="error">' .
                            $item->parent_title .
                        '</span>';
            $artLine .= '</td></tr>';
        } else {
            $addAttachLink = 'index.php?option=com_attachments&amp;task=attachment.add&amp;parent_id=' .
                             $item->parent_id .
                            '&amp;parent_type=' . $parent_type . '&amp;editor=add_to_parent';
            $addAttachLink = OutputFilter::ampReplace($addAttachLink);
            $artLine = "<tr><td class=\"at_parentsep\" colspan=\"$this->num_columns\">";
            $artLine .= "<b>" . $item->parent_entity_type . ":</b> <a title=\"$view_parent_title\" " .
                "href=\"" . $item->parent_url . "\" target=\"_blank\">" . $item->parent_title . "</a>";
            $artLine .= OutputFilter::ampReplace('&nbsp;&nbsp;&nbsp;&nbsp;');
            $artLine .= "<a class=\"addAttach\" href=\"$addAttachLink\" title=\"$add_attachment_title\">";
            if ($use_fontawesome_icons) {
                $artLine .= '<i class="' . $use_fontawesome_icons_style . ' fa-paperclip"></i>';
            } else {
                $artLine .= HTMLHelper::image('com_attachments/add_attachment.gif', $add_attachment_txt, null, true);
            }
            $artLine .= "</a>&nbsp;<a class=\"addAttach\" href=\"$addAttachLink\" title=\"$add_attachment_title\">" .
                "$add_attachment_txt</a>";
            $artLine .= "</td></tr>";
        }
        echo $artLine;
        $k = 0;
    }
    $last_parent_id = $item->parent_id;
    $last_parent_type = $item->parent_type;
    $last_parent_entity = $item->parent_entity;
    $download_verb = Text::_('ATTACH_DOWNLOAD_VERB');
    ?>
    <tr class="<?php echo "row$k"; ?>">
        <td class="w-1 text-center "><?php echo $checked; ?></td>
        <?php if (!$this->editor) : ?>
            <td class="w-1 text-center"><?php echo $published;?></td>
        <?php endif; ?>
        <td class="w-25 d-none d-md-table-cell">
            <?php if (!$this->editor) : ?>
            <a href="<?php echo $link; ?>" title="<?php echo $edit_attachment_title; ?>" >
            <?php endif; ?>
                <?php
                if ($use_fontawesome_icons) {
                    echo '<i class="' . $use_fontawesome_icons_style . ' ' . $icon . '"></i>';
                } else {
                    echo HTMLHelper::image('com_attachments/file_icons/' . $icon, $download_verb, null, true);
                }
                if (($item->uri_type == 'url') && $superimpose_link_icons) {
                    if ($item->url_valid) {
                        if ($use_fontawesome_icons) {
                            echo '&nbsp;<i class="' . $use_fontawesome_icons_style . ' fa-eye-slash"></i>';
                        } else {
                            echo HTMLHelper::image(
                                'com_attachments/file_icons/link_arrow.png',
                                '',
                                'class="link_overlay"',
                                true
                            );
                        }
                    } else {
                        if ($use_fontawesome_icons) {
                            echo '&nbsp;<i class="' . $use_fontawesome_icons_style . ' fa-eye-slash redicon"></i>';
                        } else {
                            echo HTMLHelper::image(
                                'com_attachments/file_icons/link_broken.png',
                                '',
                                'class="link_overlay"',
                                true
                            );
                        }
                    }
                }
                ?>
                <?php if (!$this->editor) : ?>
            </a>
                <?php endif; ?>
            &nbsp;<a
                    href="<?php echo $link; ?>" title="<?php echo $edit_attachment_title; ?>"
            ><?php if ($item->uri_type == 'file') {
                    echo $item->filename;
             } else {
                 if ($item->filename) {
                     echo $item->filename;
                 } else {
                     echo $item->url;
                 }
             }
                ?></a>&nbsp;&nbsp;
          <?php if (!$use_fontawesome_icons) : ?>
          <a class="downloadAttach" href="<?php echo $url; ?>" target="_blank"
         title="<?php echo $access_attachment_title; ?>"><?php echo $download_verb;
            ?></a><a class="downloadAttach" href="<?php echo $url; ?>"  target="_blank"
         title="<?php echo $access_attachment_title; ?>"
          ><?php echo HTMLHelper::image('com_attachments/download.gif', $download_verb, null, true); ?></a>
          <?php else :
              echo '<a class="downloadAttach" href="' . $url . '"  target="_blank"
		 title="' . $access_attachment_title . '"
		  ><i class="' . $use_fontawesome_icons_style . ' fa-download"></i></a>';
          endif; ?>
        </td>
        <td class="w-25 d-none d-md-table-cell"><?php echo htmlspecialchars(stripslashes($item->description)); ?></td>
        <td class="w-10 d-none d-md-table-cell text-center"><?php echo $access; ?></td>
        <?php if ($params->get('user_field_1_name', '') != '') : ?>
            <td class="w-25 d-none d-md-table-cell"><?php echo stripslashes($item->user_field_1); ?></td>
        <?php endif; ?>
        <?php if ($params->get('user_field_2_name', '') != '') : ?>
            <td class="w-25 d-none d-md-table-cell"><?php echo stripslashes($item->user_field_2); ?></td>
        <?php endif; ?>
        <?php if ($params->get('user_field_3_name', '') != '') : ?>
            <td class="w-25 d-none d-md-table-cell"><?php echo stripslashes($item->user_field_3); ?></td>
        <?php endif; ?>
        <td class="w-25 d-none d-md-table-cell"><?php echo $item->file_type; ?></td>
        <td class="w-10 d-none d-md-table-cell text-center"><?php echo $size_kb; ?></td>
        <td class="w-25 d-none d-md-table-cell"><?php echo $item->creator_name; ?></td>
        <td class="w-25 d-none d-md-table-cell"><?php echo $created; ?></td>
        <td class="w-25 d-none d-md-table-cell"><?php echo $modified ?></td>
        <?php if ($secure) : ?>
            <td class="w-10 d-none d-md-table-cell"><?php echo $item->download_count; ?></td>
        <?php endif; ?>
    </tr>
    <?php
    $k = 1 - $k;
}
