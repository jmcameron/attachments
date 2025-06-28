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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// No direct access
defined('_JEXEC') or die('Restricted access');

// Add the plugins stylesheet to style the list of attachments
$document = Factory::getApplication()->getDocument();
$uri = Uri::getInstance();

$lists = $this->lists;

// Handle both 2.5 and 3.x
$row_num = Text::_('JGRID_HEADING_ROW_NUMBER');
if ($row_num == 'JGRID_HEADING_ROW_NUMBER') {
    $row_num = '#';
}

?>
<form class="attachmentsBackend" enctype="multipart/form-data"
      name="adminForm" id="adminForm"
      action="<?php echo $this->post_url ?>" method="post">

    <fieldset class="adminform">
    <legend><?php echo Text::sprintf('ATTACH_SELECT_ENTITY_S', $this->parent_entity_name) ?></legend>
<div class="attachments_filter">
    <?php echo Text::_('ATTACH_FILTER'); ?>:
    <input type="text" name="search" id="search" value="<?php echo $lists['search'];?>"
       class="text_area" onchange="document.adminForm.submit();" />
    <button onclick="this.form.submit();"><?php echo Text::_('ATTACH_GO'); ?></button>
    <button onclick="document.getElementById('search').value='';this.form.submit();"><?php echo Text::_('ATTACH_RESET') ?></button>
</div>
    <table class="adminlist" cellspacing="1">
    <thead>
       <tr>
         <th width="5"><?php echo $row_num ?></th>
         <th class="title">
            <?php echo HTMLHelper::_('grid.sort', 'ATTACH_TITLE', 'title', @$lists['order_Dir'], @$lists['order']); ?>
         </th>
         <th width="2%" class="title">
            <?php echo HTMLHelper::_('grid.sort', 'JGRID_HEADING_ID', 'id', @$lists['order_Dir'], @$lists['order']); ?>
         </th>
       </tr>
    </thead>
    <tbody>

<?php
    $k = 0;
    $app = Factory::getApplication();
for ($i = 0, $n = count($this->items); $i < $n; $i++) {
    $item = $this->items[$i];
    ?>
        <tr class="<?php echo "row$k" ?>">
           <td><?php echo $i ?></td>
           <td>
               <a style="cursor: pointer;" onclick="window.parent.jSelectParentArticle('<?php echo $item->id; ?>', '<?php echo str_replace(array("'", "\""), array("\\'", ""), $item->title); ?>', '<?php echo $app->getInput()->get('object'); ?>');">
           <?php echo htmlspecialchars($item->title, ENT_QUOTES, 'UTF-8'); ?></a>
           </td>
           <td><?php echo $item->id; ?></td>
        </tr>
        <?php
        $k = 1 - $k;
}
?>
    </tbody>
    </table>
    </fieldset>
    <input type="hidden" name="parent_type" value="<?php echo $this->parent_type ?>" />
    <input type="hidden" name="parent_entity" value="<?php echo $this->parent_entity ?>" />
    <input type="hidden" name="option" value="<?php echo $this->option;?>" />
    <input type="hidden" name="task" value="selectEntity" />
    <input type="hidden" name="from" value="<?php echo $this->from; ?>" />

    <input type="hidden" name="filter_order" value="<?php echo $lists['order']; ?>" />
    <input type="hidden" name="filter_order_Dir" value="<?php echo $lists['order_Dir']; ?>" />

    <?php echo HTMLHelper::_('form.token'); ?>
</form>
