<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

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
		$app = Factory::getApplication();
		$user = $app->getIdentity();
		if ($user === null OR !$user->authorise('core.create', 'com_attachments')) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 64)', 404);
			return;
			}

		// For convenience below
		$attachment = $this->attachment;
		$parent = $this->parent;

		// Set up for editing the access level
		if ( $this->params->get('allow_frontend_access_editing', false) ) {
			require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
			$this->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', null);
			$this->access_level_tooltip = Text::_('ATTACH_ACCESS_LEVEL_TOOLTIP');
			}

		// Set up publishing info
		$this->may_publish = $parent->userMayChangeAttachmentState($attachment->parent_id,
																   $attachment->parent_entity, $user->id);
		if ( $this->may_publish ) {
			$this->publish = HTMLHelper::_('select.booleanlist', 'state', 'class="inputbox"', $attachment->state);
			}

		// Construct derived data
		$attachment->parent_entity_name = Text::_('ATTACH_' . $attachment->parent_entity);
		$attachment->parent_title = $parent->getTitle($attachment->parent_id, $attachment->parent_entity);

		$this->relative_url_checked = $attachment->url_relative ? 'checked="yes"' : '';
		$this->verify_url_checked = $attachment->url_verify ? 'checked="yes"' : '';

		// Add the stylesheets for the form
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_frontend_form.css');
		$lang = $app->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('media/com_attachments/css/attachments_frontend_form_rtl.css');
			}

		// Display the upload form
		parent::display($tpl);
	}


}
