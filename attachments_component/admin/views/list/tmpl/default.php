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
$uri = JFactory::getURI();

// Add the plugins stylesheet to style the list of attachments
$document =&  JFactory::getDocument();

$app = JFactory::getApplication();
$document->addStyleSheet( $uri->root(true) . '/plugins/content/attachments.css',
			  'text/css', null, array() );

$lang =& JFactory::getLanguage();
if ( $lang->isRTL() ) {
	$document->addStyleSheet( $uri->root(true) . '/plugins/content/attachments_rtl.css',
				  'text/css', null, array() );
	}

$lists = $this->lists;

$list_for_parents = $lists['list_for_parents'];

$params = $this->params;

$secure = $params->get('secure',false);
$superimpose_link_icons = $params->get('superimpose_url_link_icons', true);

$icon_dir = $uri->root(true) . '/components/com_attachments/media/icons/';

$num_columns = 9;

?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
<div class="attachments_filter">
	<table>
	<tbody>
	<tr>
	<td width="100%">
	<?php echo JText::_( 'FILTER' ); ?>:
	<input type="text" name="search" id="search" value="<?php echo $lists['search'];?>"
	   class="text_area" onchange="document.adminForm.submit();" />
	<button onclick="this.form.submit();"><?php echo JText::_( 'GO' ); ?></button>
	<button onclick="document.getElementById('search').value='';this.form.submit();">
	   <?php echo JText::_( 'RESET' ); ?></button>
	<button id="reset_order" onclick="document.getElementById('filter_order').value='';document.getElementById('filter_order_Dir').value='';this.form.submit();">
	   <?php echo JText::_( 'RESET_ORDER' ); ?></button>
	</td>
	<td nowrap="nowrap">
	<?php echo JText::_('LIST_ATTACHMENTS_FOR_COLON') ?>
	<?php echo $lists['list_for_parents_menu'] ?> &nbsp; <?php echo $lists['filter_entity_menu'] ?>
	</tr>
	</tbody>
	</table>
</div>
 <table class="adminlist">
 <thead>
   <tr>
	 <th class="at_published" width="20">
	<input type="checkbox" name="toggle" value=""
		   onclick="checkAll(<?php echo count( $this->attachments ); ?>);" />
	 </th>
	 <th class="at_published" width="5%" nowrap="nowrap"><?php echo JHTML::_('grid.sort', JText::_('PUBLISHED'),
							 'a.published', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <th class="at_filename"><?php echo JHTML::_('grid.sort', JText::_('ATTACHMENT_FILENAME'),
							 'a.filename', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <th class="at_description"><?php echo JHTML::_('grid.sort', JText::_('DESCRIPTION'),
							 'a.description', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <?php if ( $params->get('user_field_1_name', '') != '' ):	$num_columns++; ?>
	<th class="at_user_field"><?php echo JHTML::_('grid.sort', $params->get('user_field_1_name', ''),
								'a.user_field_1', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <?php endif; ?>
	 <?php if ( $params->get('user_field_2_name', '') != '' ):	$num_columns++; ?>
	<th class="at_user_field"><?php echo JHTML::_('grid.sort', $params->get('user_field_2_name', ''),
								'a.user_field_2', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <?php endif; ?>
	 <?php if ( $params->get('user_field_3_name', '') != '' ):	$num_columns++; ?>
		<th class="at_user_field"><?php echo JHTML::_('grid.sort', $params->get('user_field_3_name', ''),
								'a.user_field_3', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <?php endif; ?>
	 <th class="at_file_type"><?php echo JHTML::_('grid.sort', JText::_('FILE_TYPE'),
							 'a.file_type', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <th class="at_file_size"><?php echo JHTML::_('grid.sort', JText::_('FILE_SIZE_KB'),
							 'a.file_size', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <th class="at_uploader"><?php echo JHTML::_('grid.sort', JText::_('UPLOADER'),
							 'u.name', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <th class="at_create_date"><?php echo JHTML::_('grid.sort', JText::_('CREATED'),
								'a.create_date', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <th class="at_mod_date"><?php echo JHTML::_('grid.sort', JText::_('LAST_MODIFIED'),
							 'a.modification_date', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <?php if ( $secure ):	$num_columns++; ?>
	 <th class="at_downloads"><?php echo JHTML::_('grid.sort', JText::_('DOWNLOADS'),
								'a.download_count', @$lists['order_Dir'], @$lists['order'] ) ?></th>
	 <?php endif; ?>
   </tr>
 </thead>
 <?php
 // jimport('joomla.filter.output');   // Probably not needed on most systems
 $k = 0;
 $last_parent_id = null;
 $last_parent_type = null;
 $last_parent_entity = null;

for ($i=0, $n=count( $this->attachments ); $i < $n; $i++) {
	$row =& $this->attachments[$i];
	 if ( $list_for_parents == 'published' AND !$row->parent_published ) {
		 continue;
		 }
	 if ( $list_for_parents == 'unpublished' AND $row->parent_published ) {
		 continue;
		 }
	 if ( $list_for_parents == 'archived' AND !$row->parent_archived ) {
		 continue;
		 }
	 if ( $list_for_parents == 'none' AND $row->parent_exists ) {
		 continue;
		 }
	 if ( $row->uri_type == 'file' ) {
		 if ( $secure ) {
			 $url = JRoute::_("index.php?option=com_attachments&amp;task=download&amp;id=" . (int)$row->id);
			 }
		 else {
			 $url = $uri->root(true) . '/' . $row->url;
			 }
		 }
	 else {
		 $url = $row->url;
		 }
	 $checked = JHTML::_('grid.id', $i, $row->id );
	 $published = JHTML::_('grid.published', $row, $i );
	 $size = (int)(10 * $row->file_size / 1024) / 10.0;
	 $link = JFilterOutput::ampReplace( 'index.php?option=' . $option . '&amp;task=edit&amp;cid[]='. (int)$row->id );
	 $view_parent_title = JText::_('VIEW_ARTICLE_TITLE');
	 if ( JString::strlen($row->icon_filename) > 0 )
		 $icon_url = $icon_dir . $row->icon_filename;
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
	 $cdate = new JDate($row->create_date, -$app->getCfg('offset'));
	 $create_date = $cdate->toFormat("%x %H:%M");
	 $mdate = new JDate($row->modification_date, -$app->getCfg('offset'));
	 $modification_date = $mdate->toFormat("%x %H:%M");

	 $add_attachment_txt = JText::_('ADD_ATTACHMENT');
	 if ($row->parent_id != $last_parent_id OR $row->parent_type != $last_parent_type
		 OR $row->parent_entity != $last_parent_entity ) {
		 $parent_type = $row->parent_type;
		 if ( $row->parent_entity != 'default' ) {
			 $parent_type .= ':' . $row->parent_entity;
			 }
		 if ( $row->parent_id == null OR !$row->parent_exists ) {
			 $artLine = '<tr><td class="at_parentsep" colspan="'.$num_columns.'">';
			 $artLine .= '<b>'.$row->parent_entity_type.':</b> <span class="error">'.$row->parent_title.'</span>';
			 $artLine .= '</td></tr>';
			 }
		 else {
			 $addAttachLink = 'index.php?option=' . $option . '&amp;task=add&amp;parent_id='. $row->parent_id .
				 '&amp;parent_type=' . $parent_type . '&amp;editor=add_to_parent';
			 $addAttachLink = JFilterOutput::ampReplace($addAttachLink);
			 $artLine = "<tr><td class=\"at_parentsep\" colspan=\"$num_columns\">";
			 $artLine .= "<b>".$row->parent_entity_type.":</b> <a title=\"$view_parent_title\" " .
				 "href=\"".$row->parent_url."\">" . $row->parent_title . "</a>";
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
	 $last_parent_id = $row->parent_id;
	 $last_parent_type = $row->parent_type;
	 $last_parent_entity = $row->parent_entity;
	 $download_verb = JText::_('DOWNLOAD_VERB');
   ?>
	<tr class="<?php echo "row$k"; ?>">
	  <td><?php echo $checked; ?></td>
	  <td class="at_published" align="center"><?php echo $published;?></td>
	  <td class="at_filename">
		 <a href="<?php echo $link; ?>" title="<?php echo $edit_attachment_title; ?>"
		 ><img src="<?php echo $icon_url; ?>" alt="<?php echo $download_verb; ?>" /><?php
		 if ( $row->uri_type == 'url' AND $superimpose_link_icons ) {
			 if ( $row->url_valid ) {
			 echo "<img id=\"link\" src=\"$link_icon_url\">";
			 }
			 else {
			 echo "<img id=\"link\" src=\"$link_broken_icon_url\">";
			 }
			 }
		 ?></a>&nbsp;<a
		 href="<?php echo $link; ?>" title="<?php echo $edit_attachment_title; ?>"
			 ><?php if ( $row->uri_type == 'file' ) {
				echo $row->filename;
				}
				else {
				if ( $row->filename ) {
					echo $row->filename;
					}
				else {
					echo $row->url;
					}
				}
			   ?></a>&nbsp;&nbsp;<a class="downloadAttach" href="<?php echo $url; ?>" target="_blank"
		 title="<?php echo $access_attachment_title; ?>"><?php echo $download_verb;
		  ?></a><a class="downloadAttach" href="<?php echo $url; ?>"  target="_blank"
		 title="<?php echo $access_attachment_title; ?>"
		 ><img src="<?php echo $icon_dir . 'download.gif'; ?>" alt="<?php echo $download_verb; ?>" /></a>
	  </td>
	  <td class="at_description"><?php echo $row->description; ?></td>
	  <?php if ( $params->get('user_field_1_name', '') != '' ): ?>
		 <td class="at_user_field"><?php echo $row->user_field_1; ?></td>
	  <?php endif; ?>
	  <?php if ( $params->get('user_field_2_name', '') != '' ): ?>
		 <td class="at_user_field"><?php echo $row->user_field_2; ?></td>
	  <?php endif; ?>
	  <?php if ( $params->get('user_field_3_name', '') != '' ): ?>
		 <td class="at_user_field"><?php echo $row->user_field_3; ?></td>
	  <?php endif; ?>
	  <td class="at_file_type"><?php echo $row->file_type; ?></td>
	  <td class="at_file_size"><?php echo $size; ?></td>
	  <td class="at_uploader"><?php echo $row->uploader_name; ?></td>
	  <td class="at_create_date"><?php echo $create_date; ?></td>
	  <td class="at_mod_date"><?php echo $modification_date ?></td>
	  <?php if ( $secure ): ?>
		 <td class="at_downloads"><?php echo $row->download_count; ?></td>
	  <?php endif; ?>
	</tr>
	<?php
	$k = 1 - $k;
	}
 ?>
<tfoot>
 <tr>
 <td colspan="<?php echo $num_columns; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
 </tr>
</tfoot>
</table>
<input type="hidden" name="option" value="<?php echo $option;?>" />
<input type="hidden" name="task" value="" />
<input type="hidden" name="boxchecked" value="0" />

<input type="hidden" id="filter_order" name="filter_order" value="<?php echo $lists['order']; ?>" />
<input type="hidden" id="filter_order_Dir" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />

</form>
<div id="componentVersion"><?php echo JText::sprintf('ATTACHMENTS_VERSION_S', $this->version); ?></div>
