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
		$app = Factory::getApplication();
		$user = $app->getIdentity();
		if ( $user === null OR
			 !($user->authorise('core.edit', 'com_attachments') OR
			 $user->authorise('core.edit.own', 'com_attachments')) ) {
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 62)', 404);
			}

		// For convenience
		$attachment = $this->attachment;
		$parent = $this->parent;

		// Construct derived data
		$attachment->parent_entity_name = Text::_('ATTACH_' . $attachment->parent_entity);
		$attachment->parent_title = $parent->getTitle($attachment->parent_id, $attachment->parent_entity);
		if (!isset($attachment->modifier_name))
		{
			AttachmentsHelper::addAttachmentUserNames($attachment);
		}

		$this->relative_url_checked = $attachment->url_relative ? 'checked="yes"' : '';
		$this->verify_url_checked = $attachment->url_verify ? 'checked="yes"' : '';

		$this->may_publish = $parent->userMayChangeAttachmentState($attachment->parent_id,
																   $attachment->parent_entity,
																   $attachment->created_by
																   );

		// Set up some HTML for display in the form
		$this->lists = Array();
		$this->lists['published'] = HTMLHelper::_('select.booleanlist', 'state',
											 'class="inputbox"', $attachment->state);
		$this->lists['url_valid'] = HTMLHelper::_('select.booleanlist', 'url_valid',
											 'class="inputbox" title="' . Text::_('ATTACH_URL_IS_VALID_TOOLTIP') . '"',
											 $attachment->url_valid);

		// Set up for editing the access level
		if ( $this->params->get('allow_frontend_access_editing', false) ) {
			require_once(JPATH_COMPONENT_ADMINISTRATOR.'/models/fields/accesslevels.php');
			$this->access_level = JFormFieldAccessLevels::getAccessLevels('access', 'access', $attachment->access);
			$this->access_level_tooltip = Text::_('ATTACH_ACCESS_LEVEL_TOOLTIP');
			}

		// Add the stylesheets
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_frontend_form.css', array(), true);
		$lang = $app->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('media/com_attachments/css/attachments_frontend_form_rtl.css', array(), true);
			}

		// Display the form
		parent::display($tpl);
	}

}
