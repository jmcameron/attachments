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

// No direct access
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );


/**
 * View for the uploads
 *
 * @package Attachments
 */
class AttachmentsViewUpload extends JView
{

	/**
	 * Display the view
	 */
	function display($tpl=null, $error=false, $error_msg=false)
	{
		// ??? Pull these back to the controller and pass via $this?
		$this->error = $error;
		$this->error_msg = $error_msg;

		parent::display($tpl);
	}
}

?>
