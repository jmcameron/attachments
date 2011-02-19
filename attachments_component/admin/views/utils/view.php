<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * View for the special controller
 * (adapted from administrator/components/com_config/views/component/view.php)
 *
 * @package Attachments
 */
class AttachmentsViewAdminUtils extends JView
{
	/**
	 * Display the view
	 */
	function display()
	{
?>
<div class="attachmentsAdmin">
  <h1><?php echo JText::_('ATTACHMENTS_ADMINISTRATIVE_UTILITY_COMMANDS'); ?></h1>
  <ul>
<?php foreach ($this->entries as $link_html) {
		  echo "	  <li><h2>$link_html</h2></li>\n";
	}
?>
  </ul>
</div>
<?php
	}
}
