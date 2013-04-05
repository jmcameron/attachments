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

defined( '_JEXEC' ) or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.create', 'com_attachments')) {
	return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 172)');
}

jimport( 'joomla.application.component.view');

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * HTML View class for adding new attachments
 *
 * @package Attachments
 */
class AttachmentsViewAdd extends JViewLegacy
{
	/**
	 * Display the add/create view
	 */
	public function display($tpl = null)
	{
		// Prevent unallowed editing PID PE
		if (!$this->parent->userMayAddAttachment($this->parent_id, $this->parent_entity, $this->new_parent))
		{
			$errmsg = JText::sprintf('ATTACH_ERROR_NO_PERMISSION_TO_UPLOAD_S', $this->parent_entity_name);
			return JError::raiseError(403, $errmsg . ' (ERR 178)');
		}

		// Add the style sheets
		JHtml::stylesheet('com_attachments/attachments_admin_form.css', Array(), true);
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_admin_form_rtl.css', Array(), true);
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
		JToolBarHelper::title(JText::_('ATTACH_ADD_ATTACHMENT'));

		JToolBarHelper::apply('attachment.applyNew');
		JToolBarHelper::save('attachment.saveNew');
		JToolBarHelper::save2new('attachment.save2New');

		JToolBarHelper::cancel('attachment.cancel', 'JTOOLBAR_CANCEL');
	}
}
