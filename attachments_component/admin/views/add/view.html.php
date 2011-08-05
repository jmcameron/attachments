<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

// Access check.
if (!JFactory::getUser()->authorise('core.create', 'com_attachments')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}


jimport( 'joomla.application.component.view');

/**
 * HTML View class for adding new attachments
 *
 * @package Attachments
 */
class AttachmentsViewAdd extends JView
{
	/**
	 * Display the add/create view
	 */
	public function display($tpl = null)
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
		JToolBarHelper::title(JText::_('ATTACH_ADD_ATTACHMENT'));
		JToolBarHelper::apply('attachment.applyNew');
		JToolBarHelper::save('attachment.saveNew');
		JToolBarHelper::cancel('attachment.cancel', 'JTOOLBAR_CANCEL');
	}
}
