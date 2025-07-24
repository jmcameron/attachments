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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


// Add the attachments admin CSS files
$document = Factory::getApplication()->getDocument();
$uri = Uri::getInstance();

// load tooltip behavior
HTMLHelper::_('bootstrap.tooltip');

$listOrder  = $this->escape($this->state->get('list.ordering'));
$listDirn   = $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo Route::_('index.php?option=com_attachments'); ?>"
      method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                if (!$this->editor) {
                    echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
                };
                ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span>
                        <span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <?php if ($this->editor) : ?>
                        <p><?php echo Text::_('ATTACH_ADD_ATTACHMENT_IDS_DESCRIPTION'); ?></p>
                        <button onclick="insertAttachmentsIdToken(jQuery, '<?php echo $this->editor ?>');">
                            <?php echo Text::_('ATTACH_ADD_ATTACHMENT_IDS'); ?>
                         </button>
                    <?php endif; ?>
                    <table class="table itemList" id="attachmentsList">
                        <thead><?php echo $this->loadTemplate('head');?></thead>
                        <tbody><?php echo $this->loadTemplate('body');?></tbody>
                        <tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
                    </table>
                    <div>
                        <input type="hidden" name="task" value="" />
                        <input type="hidden" name="boxchecked" value="0" />
                        <?php echo HTMLHelper::_('form.token'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</form>
