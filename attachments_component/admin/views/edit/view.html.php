<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Access check.
if (!JFactory::getUser()->authorise('core.edit', 'com_attachments')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

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
		JToolBarHelper::title(JText::_('ATTACH_EDIT_ATTACHMENT'));
		JToolBarHelper::apply('attachment.apply');
		JToolBarHelper::save('attachment.save');
		JToolBarHelper::cancel('attachment.cancel', 'JTOOLBAR_CLOSE');
	}
}
