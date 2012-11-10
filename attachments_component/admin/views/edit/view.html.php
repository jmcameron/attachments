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
if ( !( JFactory::getUser()->authorise('core.edit', 'com_attachments') OR
		JFactory::getUser()->authorise('core.edit.own', 'com_attachments') ) )
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERRN)');
}


jimport( 'joomla.application.component.view');

/**
 * HTML View class for editing new attachments
 *
 * @package Attachments
 */
class AttachmentsViewEdit extends JViewLegacy
{
	/**
	 * Display the edit view
	 */
	public function display($tpl = null)
	{
		// Prevent unallowed editing
		if (!$this->attachment->parent->userMayEditAttachment($this->attachment))
		{
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERRN)');
		}

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
