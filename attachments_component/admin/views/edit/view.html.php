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
if ( !( JFactory::getUser()->authorise('core.edit', 'com_attachments') OR
		JFactory::getUser()->authorise('core.edit.own', 'com_attachments') ) )
{
	return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 176)');
}

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');

/** Load the Attachments helper */
require_once(JPATH_SITE.'/components/com_attachments/helper.php');


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
			$errmsg = JText::_('ATTACH_ERROR_NO_PERMISSION_TO_EDIT');
			return JError::raiseError(403, $errmsg . ' (ERR 177)');
		}

		// Add the style sheets
		JHtml::stylesheet('com_attachments/attachments_admin_form.css', Array(), true);
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_admin_form_rtl.css', Array(), true);
			}

		// Construct derived data
		$this->url_relative_checked = $this->attachment->url_relative ? 'checked="yes"' : '';
		$this->verify_url_checked = $this->attachment->url_verify ? 'checked="yes"' : '';
		if (!isset($this->attachment->modifier_name))
		{
			AttachmentsHelper::addAttachmentUserNames($this->attachment);
		}

		// Set the toolbar
		$this->addToolBar();

		// Display the form
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
