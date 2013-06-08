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

// No direct access
defined('_JEXEC') or die();

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/views/view.php');


/**
 * View for the uploads
 *
 * @package Attachments
 */
class AttachmentsViewUpload extends AttachmentsFormView
{

	/**
	 * Display the view
	 */
	public function display($tpl=null)
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.create', 'com_attachments')) {
			return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 64)' );
			}

		// For convenience below
		$attachment = $this->attachment;
		$parent = $this->parent;

		// Set up for editing the access level
		if ( $this->params->get('allow_frontend_access_editing', false) ) {
			require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
			$this->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', null);
			$this->access_level_tooltip = JText::_('ATTACH_ACCESS_LEVEL_TOOLTIP');
			}

		// Set up publishing info
		$user = JFactory::getUser();
		$this->may_publish = $parent->userMayChangeAttachmentState($attachment->parent_id,
																   $attachment->parent_entity, $user->id);
		if ( $this->may_publish ) {
			$this->publish = JHtml::_('select.booleanlist', 'state', 'class="inputbox"', $attachment->state);
			}

		// Construct derived data
		$attachment->parent_entity_name = JText::_('ATTACH_' . $attachment->parent_entity);
		$attachment->parent_title = $parent->getTitle($attachment->parent_id, $attachment->parent_entity);

		$this->relative_url_checked = $attachment->url_relative ? 'checked="yes"' : '';
		$this->verify_url_checked = $attachment->url_verify ? 'checked="yes"' : '';

		// Add the stylesheets for the form
		JHtml::stylesheet('com_attachments/attachments_frontend_form.css', array(), true);
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_frontend_form_rtl.css', array(), true);
			}

		// Display the upload form
		parent::display($tpl);
	}


}
