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

namespace JMCameron\Component\Attachments\Administrator\View\Add;

use JMCameron\Component\Attachments\Administrator\Field\AccessLevelsField;
use JMCameron\Component\Attachments\Site\Helper\AttachmentsJavascript;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// no direct access

defined( '_JEXEC' ) or die('Restricted access');

// Access check.
$app = Factory::getApplication();
$user = $app->getIdentity();
if ($user === null || !$user->authorise('core.create', 'com_attachments')) {
	throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 172)', 404);
}

/**
 * HTML View class for adding new attachments
 *
 * @package Attachments
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Display the add/create view
	 */
	public function display($tpl = null)
	{
		// For convenience below
		$attachment = $this->attachment;
		$parent_id = $attachment->parent_id;
		$parent_type = $attachment->parent_type;
		$parent_entity = $attachment->parent_entity;
		$attachment->parent_entity_name = Text::_('ATTACH_' . $attachment->parent_entity);
		$parent_entity_name = $attachment->parent_entity_name;
		$params = $this->params;

		// Prevent unallowed editing PID PE
		if (!$this->parent->userMayAddAttachment($parent_id, $parent_entity, $this->new_parent))
		{
			$errmsg = Text::sprintf('ATTACH_ERROR_NO_PERMISSION_TO_UPLOAD_S',
									 $attachment->parent_entity_name);
			throw new \Exception($errmsg . ' (ERR 173)', 403);
		}

		// Construct derived data
		$this->relative_url_checked = $attachment->url_relative ? 'checked="yes"' : '';
		$this->verify_url_checked = $attachment->url_verify ? 'checked="yes"' : '';

		// Construct some tooltips
		$this->enter_url_tooltip = Text::_('ATTACH_ENTER_URL') . '::' . Text::_('ATTACH_ENTER_URL_TOOLTIP');
		$this->display_filename_tooltip = Text::_('ATTACH_DISPLAY_FILENAME') . '::' . Text::_('ATTACH_DISPLAY_FILENAME_TOOLTIP');
		$this->display_url_tooltip = Text::_('ATTACH_DISPLAY_URL') . '::' . Text::_('ATTACH_DISPLAY_URL_TOOLTIP');

		// Add the published selection
		$this->lists = Array();
		$this->lists['published'] = HTMLHelper::_('select.booleanlist', 'state',
											 'class="inputbox"', $attachment->state);

		// Set up the access field
		$this->access_level = AccessLevelsField::getAccessLevels('access', 'access', null);
		$this->access_level_tooltip = Text::_('JFIELD_ACCESS_LABEL') . '::' . Text::_('JFIELD_ACCESS_DESC');

		// Handle user field 1
		$show_user_field_1 = false;
		$user_field_1_name = $params->get('user_field_1_name', '');
		if ( $user_field_1_name != '' ) {
			$show_user_field_1 = true;
			$this->user_field_1_name =	$user_field_1_name;
			}
		$this->show_user_field_1 =	$show_user_field_1;

		// Handle user field 2
		$show_user_field_2 = false;
		$user_field_2_name = $params->get('user_field_2_name', '');
		if ( $user_field_2_name != '' ) {
			$show_user_field_2 = true;
			$this->user_field_2_name =	$user_field_2_name;
			}
		$this->show_user_field_2 =	$show_user_field_2;

		// Handle user field 3
		$show_user_field_3 = false;
		$user_field_3_name = $params->get('user_field_3_name', '');
		if ( $user_field_3_name != '' ) {
			$show_user_field_3 = true;
			$this->user_field_3_name =	$user_field_3_name;
			}
		$this->show_user_field_3 =	$show_user_field_3;

		// Set up to toggle between uploading file/urls
		if ( $attachment->uri_type == 'file' ) {
			$upload_toggle_button_text = Text::_('ATTACH_ENTER_URL_INSTEAD');
			$upload_toggle_tooltip = Text::_('ATTACH_ENTER_URL_INSTEAD_TOOLTIP');
			$upload_toggle_url = 'index.php?option=com_attachments&amp;task=attachment.add&amp;uri=url';
			}
		else {
			$upload_toggle_button_text = Text::_('ATTACH_SELECT_FILE_TO_UPLOAD_INSTEAD');
			$upload_toggle_tooltip = Text::_('ATTACH_SELECT_FILE_TO_UPLOAD_INSTEAD_TOOLTIP');
			$upload_toggle_url = 'index.php?option=com_attachments&amp;task=attachment.add&amp;uri=file';
			}
		if ( $this->from == 'closeme' ) {
			$upload_toggle_url .= '&amp;tmpl=component';
			}
		if ( $this->from ) {
			$upload_toggle_url .= '&amp;from=' . $this->from;
			}

		// Update the toggle URL to not forget if the parent is not simply an article
		if ( !( ($parent_type == 'com_content') && ($parent_entity == 'default')) ) {
			$upload_toggle_url .= "&amp;parent_type=$parent_type";
			if ( $parent_entity != 'default' ) {
				$upload_toggle_url .= ".$parent_entity";
				}
			}

		// If this is for an existing content item, modify the URL appropriately
		if ( $this->new_parent ) {
			$upload_toggle_url .= "&amp;parent_id=0,new";
			}
		elseif ( $parent_id && ($parent_id != -1) ) {
			$upload_toggle_url .= "&amp;parent_id=$parent_id";
			}

		$app = Factory::getApplication();
		$input = $app->getInput();
		if ( $input->getWord('editor') ) {
			$upload_toggle_url .= "&amp;editor=" . $input->getWord('editor');
			}

		$this->upload_toggle_button_text = $upload_toggle_button_text;
		$this->upload_toggle_url = $upload_toggle_url;
		$this->upload_toggle_tooltip = $upload_toggle_tooltip;

		// Set up the 'select parent' button
		$this->selpar_label =  Text::sprintf('ATTACH_SELECT_ENTITY_S_COLON', $parent_entity_name);
		$this->selpar_btn_text =  '&nbsp;' . Text::sprintf('ATTACH_SELECT_ENTITY_S', $parent_entity_name) . '&nbsp;';
		$this->selpar_btn_tooltip =	 Text::sprintf('ATTACH_SELECT_ENTITY_S_TOOLTIP', $parent_entity_name);
		$this->selpar_btn_url =	 $this->parent->getSelectEntityURL($parent_entity);

		// Add the style sheets
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin_form.css');
		$lang = $app->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin_form_rtl.css');
			}

		// Set up modal
		AttachmentsJavascript::setupModalJavascript();

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
		ToolbarHelper::title(Text::_('ATTACH_ADD_ATTACHMENT'));

		ToolBarHelper::apply('attachment.applyNew');
		ToolBarHelper::save('attachment.saveNew');
		ToolBarHelper::save2new('attachment.save2New');

		ToolBarHelper::cancel('attachment.cancel', 'JTOOLBAR_CANCEL');
	}
}
