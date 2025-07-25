<?php

/**
 * Attachments component attachments view
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


$lists = $this->lists;

?>
  <div class="attachments_filter">
    <table>
    <tbody>
    <tr>
    <td width="100%">
    <label class="filter-search-lbl" for="filter_search"><?php echo Text::_('JSEARCH_FILTER_LABEL'); ?></label>
    <input type="text" name="filter_search" id="filter_search"
           value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
           class="text_area" onchange="this.form.submit();" />
    <button class="filter_button" onclick="this.form.submit();"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>
    <button class="filter_button" onclick="document.getElementById('filter_search').value='';this.form.submit();">
       <?php echo Text::_('JSEARCH_FILTER_CLEAR'); ?></button>
    <button class="filter_button" id="reset_order" onclick="Joomla.tableOrdering('','asc','');">
       <?php echo Text::_('ATTACH_RESET_ORDER'); ?></button>
    </td>
    <td nowrap="nowrap">
    <?php echo Text::_('ATTACH_LIST_ATTACHMENTS_FOR_COLON') ?>
    <?php echo $lists['filter_parent_state_menu'] ?> &nbsp; <?php echo $lists['filter_entity_menu'] ?>
    </tr>
    </tbody>
    </table>
  </div>
