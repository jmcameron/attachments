<?php
/**
 * Attachments component attachments view
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

// Add the attachments admin CSS files
$document = JFactory::getDocument();
$uri = JFactory::getURI();
$document->addStyleSheet( $uri->base(true) . '/components/com_attachments/media/attachments.css',
						  'text/css', null, array() );
$lang = JFactory::getLanguage();
if ( $lang->isRTL() ) {
	$document->addStyleSheet( $uri->base(true) . '/components/com_attachments/media/attachments_rtl.css',
							  'text/css', null, array() );
	}

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
<div id="componentVersion"><a target="_blank" title="<?php echo JText::_('ATTACH_ATTACHMENTS_PROJECT_URL_DESCRIPTION'); ?>" href="<?php echo $this->project_url ?>"><?php echo JText::sprintf('ATTACH_ATTACHMENTS_VERSION_S', $this->version); ?></a></div>
