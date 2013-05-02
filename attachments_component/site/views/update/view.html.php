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

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/views/view.php');

/** Load the Attachments helper */
require_once(JPATH_SITE.'/components/com_attachments/helper.php');


/**
 * View for the uploads
 *
 * @package Attachments
 */
class AttachmentsViewUpdate extends AttachmentsFormView
{
	/**
	 * Display the view
	 */
	public function display($tpl=null)
	{
		// Access check.
		if ( !(JFactory::getUser()->authorise('core.edit', 'com_attachments') OR
			   JFactory::getUser()->authorise('core.edit.own', 'com_attachments')) ) {
			return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 61)');
			}

		// Add the stylesheets
		JHtml::stylesheet('com_attachments/attachments_frontend_form.css', array(), true);
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_frontend_form_rtl.css', array(), true);
			}

		// Construct derived data
		$this->url_relative_checked = $this->attachment->url_relative ? 'checked="yes"' : '';
		$this->verify_url_checked = $this->attachment->url_verify ? 'checked="yes"' : '';

		$this->lists = Array();
		$this->lists['published'] = JHtml::_('select.booleanlist', 'state',
											 'class="inputbox"', $this->attachment->state);
		$this->lists['url_valid'] = JHtml::_('select.booleanlist', 'url_valid',
											 'class="inputbox" title="' . JText::_('ATTACH_URL_IS_VALID_TOOLTIP') . '"',
											 $this->attachment->url_valid);

		if (!isset($this->attachment->modifier_name))
		{
			AttachmentsHelper::addAttachmentUserNames($this->attachment);
		}
			
		// Display the form
		parent::display($tpl);
	}

}
