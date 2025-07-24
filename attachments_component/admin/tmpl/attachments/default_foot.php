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


?>
<tr>
<td colspan="<?php echo $this->num_columns - 3; ?>"><?php echo $this->pagination->getListFooter(); ?></td>
<td colspan="3">
    <div id="componentVersion">
        <a target="_blank" title="<?php echo Text::_('ATTACH_ATTACHMENTS_PROJECT_URL_DESCRIPTION'); ?>"
           href="<?php echo $this->project_url ?>">
            <?php echo Text::sprintf('ATTACH_ATTACHMENTS_VERSION_S', $this->version); ?>
        </a>
    </div>
</td>
</tr>
