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

// No direct access
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * View for the uploads
 *
 * @package Attachments
 */
class AttachmentsViewUpload extends JViewLegacy
{

	/**
	 * Display the view
	 */
	public function display($tpl=null)
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.create', 'com_attachments')) {
			return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 63)' );
			}

		// Add the stylesheets for the form
		JHtml::stylesheet('com_attachments/attachments_frontend_form.css', array(), true);
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_frontend_form_rtl.css', array(), true);
			}

		parent::display($tpl);
	}
}
