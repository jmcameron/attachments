<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

// Hide the vertical scrollbar using javascript
$document = JFactory::getDocument();
$hide_scrollbar = "window.addEvent('domready', function() {
	   document.documentElement.style.overflow = \"hidden\";
	   document.body.scroll = \"no\";});";
$document->addScriptDeclaration($hide_scrollbar);

?>
<div class="attachmentsAdmin" id="utilsList">
  <h1><?php echo JText::_('ATTACH_ATTACHMENTS_ADMINISTRATIVE_UTILITY_COMMANDS'); ?></h1>
  <ul>
<?php foreach ($this->entries as $link_html)
{
	echo "	  <li><h2>$link_html</h2></li>\n";
}
?>
  </ul>
</div>
