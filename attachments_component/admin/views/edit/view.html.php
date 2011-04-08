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
 
// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
 
jimport( 'joomla.application.component.view');
 
/**
 * HTML View class for editing new attachments
 *
 * @package Attachments
 */
class AttachmentsViewEdit extends JView
{
	/**
	 * Display the edit view
	 */
	function display($tpl = null)
	{
		// Set the toolbar
		$this->addToolBar();

		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('EDIT_ATTACHMENT'));
		JToolBarHelper::save('attachment.save');
		JToolBarHelper::cancel('attachment.cancel', 'JTOOLBAR_CLOSE');
	}
}

?>

