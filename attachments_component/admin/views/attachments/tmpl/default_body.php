<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

// Set up a few convenience items
$app = JFactory::getApplication();
$uri = JFactory::getURI();
$params = $this->params;
$secure = $params->get('secure',false);
$superimpose_link_icons = $params->get('superimpose_url_link_icons', true);
$icon_dir = $uri->root(true) . '/components/com_attachments/media/icons/';

// Loop through all the attachments
$k = 0;
$last_parent_id = null;
$last_parent_type = null;
$last_parent_entity = null;


for ($i=0, $n=count( $this->items ); $i < $n; $i++)
{
	$item =& $this->items[$i];

	if ( $item->uri_type == 'file' ) {
		if ( $secure ) {
			$url = JRoute::_("index.php?option=com_attachments&amp;task=attachment.download&amp;id=" . (int)$item->id);
			}
		else {
			$url = $uri->root(true) . '/' . $item->url;
			}
		}
	else {
		$url = $item->url;
		}
	$checked = JHTML::_('grid.id', $i, $item->id );
	$published = JHTML::_('jgrid.published', $item->state, $i, 'attachments.' );

	$size_kb = (int)(10 * $item->file_size / 1024) / 10.0;
	$link = JFilterOutput::ampReplace( 'index.php?option=com_attachments&amp;task=attachment.edit&amp;cid[]='. (int)$item->id );
	$view_parent_title = JText::_('VIEW_ARTICLE_TITLE');
	if ( JString::strlen($item->icon_filename) > 0 )
		$icon_url = $icon_dir . $item->icon_filename;
	else
		$icon_url = $icon_dir . 'generic.gif';
	$link_icon_url = $icon_dir . 'link_arrow.png';
	$link_broken_icon_url = $icon_dir . 'link_broken.png';
	$add_attachment_icon = $uri->root(true) . '/components/com_attachments/media/add_attachment.gif';
	$add_attachment_title = JText::_('ADD_ATTACHMENT_TITLE');
	$edit_attachment_title = JText::_('EDIT_THIS_ATTACHMENT_TITLE');
	$access_attachment_title = JText::_('ACCESS_THIS_ATTACHMENT_TITLE');

	// Set up the create/modify dates
	jimport( 'joomla.utilities.date' );
	$cdate = new JDate($item->create_date, -$app->getCfg('offset'));
	$create_date = $cdate->toFormat("%x %H:%M");
	$mdate = new JDate($item->modification_date, -$app->getCfg('offset'));
	$modification_date = $mdate->toFormat("%x %H:%M");

	$add_attachment_txt = JText::_('ADD_ATTACHMENT');
	if ($item->parent_id != $last_parent_id OR $item->parent_type != $last_parent_type
		OR $item->parent_entity != $last_parent_entity ) {
		$parent_type = $item->parent_type;
		if ( $item->parent_entity != 'default' ) {
			$parent_type .= '.' . $item->parent_entity;
			}
		if ( $item->parent_id == null OR !$item->parent_exists ) {
			$artLine = '<tr><td class="at_parentsep" colspan="'.$this->num_columns.'">';
			$artLine .= '<b>'.$item->parent_entity_type.':</b> <span class="error">'.$item->parent_title.'</span>';
			$artLine .= '</td></tr>';
			}
		else {
			$addAttachLink = 'index.php?option=com_attachments&amp;task=attachment.add&amp;parent_id='. $item->parent_id .
				'&amp;parent_type=' . $parent_type . '&amp;editor=add_to_parent';
			$addAttachLink = JFilterOutput::ampReplace($addAttachLink);
			$artLine = "<tr><td class=\"at_parentsep\" colspan=\"$this->num_columns\">";
			$artLine .= "<b>".$item->parent_entity_type.":</b> <a title=\"$view_parent_title\" " .
				"href=\"".$item->parent_url."\" target=\"_blank\">" . $item->parent_title . "</a>";
			$artLine .= JFilterOutput::ampReplace('&nbsp;&nbsp;&nbsp;&nbsp;');
			$artLine .= "<a class=\"addAttach\" href=\"$addAttachLink\" title=\"$add_attachment_title\">" .
				"<img src=\"$add_attachment_icon\" alt=\"$add_attachment_txt\" /></a>&nbsp;";
			$artLine .= "<a class=\"addAttach\" href=\"$addAttachLink\" title=\"$add_attachment_title\">" .
				"$add_attachment_txt</a>";
			$artLine .= "</td></tr>";
			}
		echo $artLine;
		$k = 0;
		}
	$last_parent_id = $item->parent_id;
	$last_parent_type = $item->parent_type;
	$last_parent_entity = $item->parent_entity;
	$download_verb = JText::_('DOWNLOAD_VERB');
   ?>
	<tr class="<?php echo "row$k"; ?>">
	  <td><?php echo $checked; ?></td>
	  <td class="at_published" align="center"><?php echo $published;?></td>
	  <td class="at_filename">
		 <a href="<?php echo $link; ?>" title="<?php echo $edit_attachment_title; ?>"
		 ><img src="<?php echo $icon_url; ?>" alt="<?php echo $download_verb; ?>" /><?php
		 if ( $item->uri_type == 'url' AND $superimpose_link_icons ) {
			 if ( $item->url_valid ) {
			 echo "<img id=\"link\" src=\"$link_icon_url\">";
			 }
			 else {
			 echo "<img id=\"link\" src=\"$link_broken_icon_url\">";
			 }
			 }
		 ?></a>&nbsp;<a
		 href="<?php echo $link; ?>" title="<?php echo $edit_attachment_title; ?>"
			 ><?php if ( $item->uri_type == 'file' ) {
				echo $item->filename;
				}
				else {
				if ( $item->filename ) {
					echo $item->filename;
					}
				else {
					echo $item->url;
					}
				}
			   ?></a>&nbsp;&nbsp;<a class="downloadAttach" href="<?php echo $url; ?>" target="_blank"
		 title="<?php echo $access_attachment_title; ?>"><?php echo $download_verb;
		  ?></a><a class="downloadAttach" href="<?php echo $url; ?>"  target="_blank"
		 title="<?php echo $access_attachment_title; ?>"
		 ><img src="<?php echo $icon_dir . 'download.gif'; ?>" alt="<?php echo $download_verb; ?>" /></a>
	  </td>
	  <td class="at_description"><?php echo $item->description; ?></td>
	  <?php if ( $params->get('user_field_1_name', '') != '' ): ?>
		 <td class="at_user_field"><?php echo $item->user_field_1; ?></td>
	  <?php endif; ?>
	  <?php if ( $params->get('user_field_2_name', '') != '' ): ?>
		 <td class="at_user_field"><?php echo $item->user_field_2; ?></td>
	  <?php endif; ?>
	  <?php if ( $params->get('user_field_3_name', '') != '' ): ?>
		 <td class="at_user_field"><?php echo $item->user_field_3; ?></td>
	  <?php endif; ?>
	  <td class="at_file_type"><?php echo $item->file_type; ?></td>
	  <td class="at_file_size"><?php echo $size_kb; ?></td>
	  <td class="at_uploader"><?php echo $item->uploader_name; ?></td>
	  <td class="at_create_date"><?php echo $create_date; ?></td>
	  <td class="at_mod_date"><?php echo $modification_date ?></td>
	  <?php if ( $secure ): ?>
		 <td class="at_downloads"><?php echo $item->download_count; ?></td>
	  <?php endif; ?>
	</tr>
	<?php
	$k = 1 - $k;
}
