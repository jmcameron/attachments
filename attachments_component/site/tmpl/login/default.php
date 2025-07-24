<?php

/**
 * Attachments component
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
<div class="requestLogin">
<?php if ($this->logged_in) : ?>
    <h1><?php echo Text::_('ATTACH_WARNING_YOU_ARE_ALREADY_LOGGED_IN'); ?></h1>
<?php else : ?>
    <h1><?php echo $this->must_be_logged_in; ?></h1>
    <h2><a href="<?php echo $this->login_url; ?>"><?php echo $this->login_label; ?></a></h2>
    <h2><a href="<?php echo $this->register_url; ?>"><?php echo $this->register_label; ?></a></h2>
<?php endif; ?>
</div>
