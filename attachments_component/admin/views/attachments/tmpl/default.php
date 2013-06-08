<?php
/**
 * Attachments component attachments view
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

// Add the attachments admin CSS files
$document = JFactory::getDocument();
$uri = JFactory::getURI();

// load tooltip behavior
JHtml::_('behavior.tooltip');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo JRoute::_('index.php?option=com_attachments'); ?>" method="post" name="adminForm" id="adminForm">
<?php echo $this->loadTemplate('filter');?>
  <table class="adminlist" id="attachmentsList">
	<thead><?php echo $this->loadTemplate('head');?></thead>
	<tbody><?php echo $this->loadTemplate('body');?></tbody>
	<tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
  </table>
  <div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
	<?php echo JHtml::_('form.token'); ?>
  </div>
</form>

