<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


// Load the tooltip behavior.
HTMLHelper::_('bootstrap.tooltip');

// Hide the vertical scrollbar using javascript
$document = Factory::getApplication()->getDocument();
$hide_scrollbar = "document.addEventListener('DOMContentLoaded', (event) => {
	   document.documentElement.style.overflow = \"hidden\";
	   document.body.scroll = \"no\";});";
$document->addScriptDeclaration($hide_scrollbar);

?>
<div class="attachmentsAdmin" id="utilsList">
  <h1><?php echo Text::_('ATTACH_ATTACHMENTS_ADMINISTRATIVE_UTILITY_COMMANDS'); ?></h1>
  <ul>
<?php foreach ($this->entries as $link_html) {
    echo "	  <li><h2>$link_html</h2></li>\n";
}
?>
  </ul>
</div>
