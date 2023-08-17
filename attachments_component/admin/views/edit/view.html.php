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
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

// Access check.
$user = Factory::getApplication()->getIdentity();
if ( $user === null OR
	 !( $user->authorise('core.edit', 'com_attachments') OR
		$user->authorise('core.edit.own', 'com_attachments') ) )
{
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 177)', 404);
	return;
}

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');

/** Load the Attachments helper */
require_once(JPATH_SITE.'/components/com_attachments/helper.php');

/** Include the Attachments javascript classes */
require_once(JPATH_SITE.'/components/com_attachments/javascript.php');


/**
 * HTML View class for editing new attachments
 *
 * @package Attachments
 */
class AttachmentsViewEdit extends HtmlView
{
	/**
	 * Display the edit view
	 */
	public function display($tpl = null)
	{
		// For convenience
		$attachment = $this->attachment;

		// Prevent unallowed editing
		if (!$this->attachment->parent->userMayEditAttachment($attachment))
		{
			$errmsg = Text::_('ATTACH_ERROR_NO_PERMISSION_TO_EDIT');
			throw new Exception($errmsg . ' (ERR 178)', 403);
			return;
		}

		// Construct derived data
		$attachment->parent_entity_name = Text::_('ATTACH_' . $attachment->parent_entity);
		if (!isset($attachment->modifier_name))
		{
			AttachmentsHelper::addAttachmentUserNames($attachment);
		}

		// Compute the attachment size in kB
		$attachment->size_kb = (int)( 10 * $attachment->file_size / 1024.0 ) / 10.0;

		// set up lists for form controls
		$this->lists = array();
		$this->lists['published'] = HTMLHelper::_('select.booleanlist', 'state',
									   'class="inputbox"', $attachment->state);
		$this->lists['url_valid'] = HTMLHelper::_('select.booleanlist', 'url_valid',
									   'class="inputbox" title="' . Text::_('ATTACH_URL_IS_VALID_TOOLTIP') . '"',
									   $attachment->url_valid);

		// Construct the drop-down list for legal icon filenames
		$icon_filenames = array();
		require_once(JPATH_COMPONENT_SITE.'/file_types.php');
		foreach ( AttachmentsFileTypes::unique_icon_filenames() as $ifname)
		{
			$icon_filenames[] = HTMLHelper::_('select.option', $ifname);
		}
		$this->lists['icon_filenames'] =HTMLHelper::_('select.genericlist',	 $icon_filenames,
												 'icon_filename', 'class="inputbox" size="1"', 'value', 'text',
												 $attachment->icon_filename);

		// If switching from article to URL default url_verify to true
		if (($attachment->uri_type == 'file') AND ($this->update == 'url')) {
			$attachment->url_verify = true;
			}

		// Set up for checkboxes
		$this->relative_url_checked = $attachment->url_relative ? 'checked="yes"' : '';
		$this->verify_url_checked = $attachment->url_verify ? 'checked="yes"' : '';

		// Set up some tooltips
		$this->enter_url_tooltip = Text::_('ATTACH_ENTER_URL') . '::' . Text::_('ATTACH_ENTER_URL_TOOLTIP');
		$this->display_filename_tooltip = Text::_('ATTACH_DISPLAY_FILENAME') . '::' . Text::_('ATTACH_DISPLAY_FILENAME_TOOLTIP');
		$this->display_url_tooltip = Text::_('ATTACH_DISPLAY_URL') . '::' . Text::_('ATTACH_DISPLAY_URL_TOOLTIP');
		$this->download_count_tooltip = Text::_('ATTACH_NUMBER_OF_DOWNLOADS') . '::' . Text::_('ATTACH_NUMBER_OF_DOWNLOADS_TOOLTIP');

		// Set up mootools/modal
		AttachmentsJavascript::setupModalJavascript();

		// Add the style sheets
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin_form.css', Array(), true);
		$lang = $app->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin_form_rtl.css', Array(), true);
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
		$app = Factory::getApplication();
		$app->getInput()->set('hidemainmenu', true);
		ToolbarHelper::title(Text::_('ATTACH_EDIT_ATTACHMENT'));

		ToolbarHelper::apply('attachment.apply');
		ToolbarHelper::save('attachment.save');
		ToolbarHelper::cancel('attachment.cancel', 'JTOOLBAR_CLOSE');
	}
}
