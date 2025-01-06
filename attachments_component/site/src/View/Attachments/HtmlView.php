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

namespace JMCameron\Component\Attachments\Site\View\Attachments;

use JMCameron\Component\Attachments\Site\Helper\AttachmentsJavascript;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;

// No direct access
defined('_JEXEC') or die('Restricted Access');

/**
 * View for a list of attachments
 *
 * @package Attachments
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Construct the output for the view/template.
	 *
	 * NOTE: This only constructs the output; it does not display it!
	 *		 Use getOutput() to actually display it.
	 *
	 * @param string $tpl template name (optional)
	 *
	 * @return if there are no attachments for this article,
	 *		   if everything is okay, return true
	 *		   if there is an error, return the error code
	 */
	public function display($tpl = null)
	{
		/** @var \Joomla\CMS\Application\CMSApplication $app */
		$app = Factory::getApplication();
		$document = $app->getDocument();
		$input = $app->getInput();
		if ( $input->getWord('format', '') == 'raw' ) {
			// Choose raw text even though it is actually html
			$document->setMimeEncoding('text/plain');
			}

		// Add javascript
		$uri = Uri::getInstance();
		AttachmentsJavascript::setupJavascript();

		// Get the model
		/** @var \JMCameron\Component\Attachments\Site\Model\AttachmentsModel $model */
		$model = $this->getModel('Attachments');
		if ( !$model ) {
			$errmsg = Text::_('ATTACH_ERROR_UNABLE_TO_FIND_MODEL') . ' (ERR 63)';
			throw new \Exception($errmsg, 500);
			}

		// See if there are any attachments
		$list = $model->getAttachmentsList();
		if ( ! $list ) {
			return null;
			}

		// if we have attachments, add the stylesheets for the attachments list
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_list.css');
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_list_dark.css');
		$lang = $app->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('media/com_attachments/css/attachments_list_rtl.css');
			}

		// Add the default path
		$this->addTemplatePath(JPATH_SITE.'/components/com_attachments/tmpl/attachments');

		// Set up the correct path for template overloads
		// (Do this after previous addTemplatePath so that template overrides actually override)
		$templateDir = JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_attachments/attachments';
		$this->addTemplatePath($templateDir);

		// Load the language files from the attachments plugin
		$lang =	 $app->getLanguage();
		$lang->load('plg_content_attachments', JPATH_SITE.'/plugins/content/attachments');

		// Get the component parameters
		$params = ComponentHelper::getParams('com_attachments');

		// See whether the user-defined fields should be shown
		$from = $input->getWord('from', 'closeme');
		$layout = $input->getWord('layout');
		$tmpl = $input->getWord('tmpl');
		$task = $input->getWord('task');
		$show_hidden_user_fields = false;
		if ( $app->isClient('administrator') || ($from == 'editor') || ($layout == 'edit') || ($tmpl == 'component') ) {
			$show_hidden_user_fields = true;
			}
		if ( $task == 'attachmentsList' ) {
			// Always hide the hidden user fields on Ajax requests
			$show_hidden_user_fields = false;
			}

		// User field 1
		$show_user_field_1 = false;
		$user_field_1_name = $params->get('user_field_1_name');
		if ( $user_field_1_name ) {
			if ( $show_hidden_user_fields || ($user_field_1_name[StringHelper::strlen($user_field_1_name)-1] != '*') ) {
				$show_user_field_1 = true;
				$this->user_field_1_name = $user_field_1_name;
				}
			}
		$this->show_user_field_1 = $show_user_field_1;

		// User field 2
		$show_user_field_2 = false;
		$user_field_2_name = $params->get('user_field_2_name');
		if ( $user_field_2_name ) {
			if ( $show_hidden_user_fields || ($user_field_2_name[StringHelper::strlen($user_field_2_name)-1] != '*') ) {
				$show_user_field_2 = true;
				$this->user_field_2_name = $user_field_2_name;
				}
			}
		$this->show_user_field_2 = $show_user_field_2;

		// User field 3
		$show_user_field_3 = false;
		$user_field_3_name = $params->get('user_field_3_name');
		if ( $user_field_3_name ) {
			if ( $show_hidden_user_fields || ($user_field_3_name[StringHelper::strlen($user_field_3_name)-1] != '*') ) {
				$show_user_field_3 = true;
				$this->user_field_3_name = $user_field_3_name;
				}
			}
		$this->show_user_field_3 = $show_user_field_3;

		// Set up for the template
		$parent_id = $model->getParentId();
		$parent_type = $model->getParentType();
		$parent_entity = StringHelper::strtolower($model->getParentEntity());
		// ?? fix this!
		if ( ($parent_type == 'com_content') && ($parent_entity == 'default') ) {
			$parent_entity = 'article';
			}
		$this->parent_id = $parent_id;
		$this->parent_type = $parent_type;
		$this->parent_entity = $parent_entity;
		$this->parent_title = $model->getParentTitle();
		$this->parent_entity_name = $model->getParentEntityName();

		$this->some_attachments_visible = $model->someVisible();
		$this->some_attachments_modifiable = $model->someModifiable();

		$this->from = $from;

		$this->list = $list;

		$this->secure = $params->get('secure', false);

		$this->params = $params;

		// Get the display options
		$this->superimpose_link_icons = $params->get('superimpose_url_link_icons', true);
		$this->style = $params->get('attachments_table_style', 'attachmentsList');
		$this->show_column_titles = $params->get('show_column_titles', false);
		$this->show_description = $params->get('show_description', true);
		$this->show_creator_name =	$params->get('show_creator_name', false);
		$this->show_file_size = $params->get('show_file_size', true);
		$this->show_downloads = $params->get('show_downloads', false);
		$this->show_created_date = $params->get('show_created_date', false);
		$this->show_modified_date =	$params->get('show_modified_date', false);
		$this->file_link_open_mode = $params->get('file_link_open_mode', 'in_same_window');
		$this->show_raw_download = $params->get('show_raw_download', false);

		// Set up the file/url titleshow_mod_date
		if ( $this->show_column_titles ) {
			switch ( $model->types() ) {
			case 'file':
				$this->file_url_title = Text::_('ATTACH_FILE');
				break;
			case 'url':
				$this->file_url_title = Text::_('ATTACH_URL');
				break;
			default:
				$this->file_url_title = Text::_('ATTACH_FILE_URL');
				}
			}

		if ( $this->show_created_date OR $this->show_modified_date ) {
			$this->date_format = $params->get('date_format', '%Y-%m-%d %I:%M%P');
			}

		// Get the attachments list title
		$title = $this->title;
		if ( !$title || (StringHelper::strlen($title) == 0) ) {
			$title = 'ATTACH_ATTACHMENTS_TITLE';
			}
		$parent = $model->getParentClass();
		$title = $parent->attachmentsListTitle($title, $parent_id, $parent_entity);
		$this->title = $title; // Note: assume it is translated

		// Construct the path for the icons
		$uri = Uri::getInstance();
		$base_url = $uri->root(false);
		$this->base_url = $base_url;
		$this->icon_url_base = $base_url . 'components/com_attachments/media/icons/';

		// Get the output of the template
		try {
			$this->loadTemplate($tpl);
			return true;
		} catch (\Exception $e) {
			return $e->getCode();
		}
	}

	/**
	 * Get the output
	 *
	 * @return string the output
	 */
	public function getOutput()
	{
		return $this->_output;
	}

}
