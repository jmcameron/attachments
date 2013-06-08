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

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * View for the special controller
 * (adapted from administrator/components/com_config/views/component/view.php)
 *
 * @package Attachments
 */
class AttachmentsViewWarning extends JViewLegacy
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Add the style sheets
		JHtml::stylesheet('com_attachments/attachments_admin_form.css', Array(), true);
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_admin_form_rtl.css', Array(), true);
			}

		parent::display($tpl);
	}
}
