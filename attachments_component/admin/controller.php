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

defined('_JEXEC') or die('Restricted access');

/**
 * Define the main attachments controller class
 *
 * @package Attachments
 */
class AttachmentsController extends JController
{

	/** List the attachments
	 *
	 * @return void
	 */
	function display($cachable = false)
	{
		// Set the default view (if not specified)
		JRequest::setVar('view', JRequest::getCmd('view', 'Attachments'));

		// Call parent to display
		parent::display($cachable);
	}
	
}

