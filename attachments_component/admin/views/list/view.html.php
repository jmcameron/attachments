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
class AttachmentsViewList extends JView
{
	/**
	 * Display the list view
	 */
	function display($tpl = null)
	{
		echo $this->loadTemplate($tpl);
	}
}

?>
