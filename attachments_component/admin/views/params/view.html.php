<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Access check.
if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
	return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 174)');
	}

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * View for editing the component parameters
 * (adapted from administrator/components/com_config/views/component/view.php)
 *
 * @package Attachments
 */
class AttachmentsViewParams extends JViewLegacy
{
	/**
	 * Display the params view
	 */
	public function display($tpl = null)
	{
		// Add the style sheets
		JHtml::stylesheet('com_attachments/attachments_admin_form.css', Array(), true);
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_admin_form_rtl.css', Array(), true);
			}

		$this->addToolBar();

		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('ATTACH_CONFIGURATION'), 'attachments.png');
		JToolBarHelper::apply('params.apply');
		JToolBarHelper::save('params.save');
		JToolBarHelper::cancel('params.cancel', 'JTOOLBAR_CLOSE');
	}

}
