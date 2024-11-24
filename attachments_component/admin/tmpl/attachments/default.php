<?php
/**
 * Attachments component attachments view
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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;


// No direct access
defined('_JEXEC') or die('Restricted access');

// Add the attachments admin CSS files
$app = Factory::getApplication();
$document = $app->getDocument();
$uri = Uri::getInstance();

// load tooltip behavior
HTMLHelper::_('bootstrap.tooltip');

$listOrder	= $this->escape($this->state->get('list.ordering'));
$listDirn	= $this->escape($this->state->get('list.direction'));

// Make sure the translations are available
$lang = $app->getLanguage();
?>
<form action="<?php echo Route::_('index.php?option=com_attachments'); ?>" method="post" name="adminForm" id="adminForm">
<?php if ( $this->editor ) : ?>
		<p><?php echo Text::_('ATTACH_ADD_ATTACHMENT_IDS_DESCRIPTION'); ?></p>
		<button onclick="insertAttachmentsIdToken(jQuery, '<?php echo $this->editor ?>');"><?php echo Text::_('ATTACH_ADD_ATTACHMENT_IDS'); ?></button>
<?php endif; ?>
<?php 
	if (!$this->editor ) {
		echo $this->loadTemplate('filter');
	}
?>
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
	<?php echo HTMLHelper::_('form.token'); ?>
  </div>
  
</form>


