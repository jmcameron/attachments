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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


// Set up a few convenience items
$params = $this->params;
$secure = $params->get('secure', false);
$lists = $this->lists;
$list_for_parents = $lists['list_for_parents'];

$listOrder    = $this->escape($this->state->get('list.ordering'));
$listDirn    = $this->escape($this->state->get('list.direction'));

?>
<tr>
    <th class="w-1 text-center ">
        <?php echo HTMLHelper::_('grid.checkall'); ?>
    </th>
    <?php if (!$this->editor) : ?>
        <th scope="col" >
            <?php echo HTMLHelper::_('searchtools.sort', 'ATTACH_PUBLISHED', 'a.state', $listDirn, $listOrder); ?>
        </th>
    <?php endif; ?>
    <th scope="col" class="w-25 d-none d-md-table-cell">
    <?php echo
        HTMLHelper::_(
            'searchtools.sort',
            'ATTACH_ATTACHMENT_FILENAME_URL',
            'a.filename',
            $listDirn,
            $listOrder
        ) ?>
    </th>
    <th scope="col" class="w-25 d-none d-md-table-cell">
        <?php echo HTMLHelper::_(
            'searchtools.sort',
            'ATTACH_DESCRIPTION',
            'a.description',
            $listDirn,
            $listOrder
        ) ?>
    </th>
    <th scope="col" class="w-10 d-none d-md-table-cell text-center">
        <?php echo HTMLHelper::_(
            'searchtools.sort',
            'JFIELD_ACCESS_LABEL',
            'a.access',
            $listDirn,
            $listOrder
        ) ?>
    </th>
    <?php if ($params->get('user_field_1_name')) : ?>
        <th scope="col" class="w-25 d-none d-md-table-cell">
            <?php echo Text::_($params->get('user_field_1_name', '')); ?>
       </th>
    <?php endif; ?>
    <?php if ($params->get('user_field_2_name')) : ?>
        <th scope="col" class="w-25 d-none d-md-table-cell">
            <?php echo Text::_($params->get('user_field_2_name', '')); ?>
        </th>
    <?php endif; ?>
    <?php if ($params->get('user_field_3_name')) : ?>
        <th scope="col" class="w-25 d-none d-md-table-cell">
            <?php echo Text::_($params->get('user_field_3_name', '')); ?>
        </th>
    <?php endif; ?>
    <th scope="col" class="w-25 d-none d-md-table-cell">
        <?php echo HTMLHelper::_(
            'searchtools.sort',
            'ATTACH_FILE_TYPE',
            'a.file_type',
            $listDirn,
            $listOrder
        ) ?>
    </th>
    <th scope="col" class="w-10 d-none d-md-table-cell text-center">
        <?php echo HTMLHelper::_(
            'searchtools.sort',
            'ATTACH_FILE_SIZE_KB',
            'a.file_size',
            $listDirn,
            $listOrder
        ) ?>
    </th>
    <th scope="col" class="w-25 d-none d-md-table-cell">
        <?php echo HTMLHelper::_(
            'searchtools.sort',
            'ATTACH_CREATOR',
            'u1.name',
            $listDirn,
            $listOrder
        ) ?>
    </th>
    <th scope="col" class="w-50 d-none d-md-table-cell">
        <?php echo HTMLHelper::_(
            'searchtools.sort',
            'JGLOBAL_CREATED',
            'a.created',
            $listDirn,
            $listOrder
        ) ?>
    </th>
    <th scope="col" class="w-50 d-none d-md-table-cell">
        <?php echo HTMLHelper::_(
            'searchtools.sort',
            'ATTACH_LAST_MODIFIED',
            'a.modified',
            $listDirn,
            $listOrder
        ) ?>
    </th>
    <?php if ($secure) : ?>
        <th scope="col" class="w-10 d-none d-md-table-cell">
            <?php echo HTMLHelper::_(
                'searchtools.sort',
                'ATTACH_DOWNLOADS',
                'a.download_count',
                $listDirn,
                $listOrder
            ) ?>
        </th>
    <?php endif; ?>
</tr>


