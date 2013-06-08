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
defined('_JEXEC') or die('Restricted Access');

/** Load the Attachments helper */
require_once(JPATH_SITE.'/components/com_attachments/helper.php');  /* ??? Needed? */
require_once(JPATH_SITE.'/components/com_attachments/javascript.php');

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * View for a list of attachments
 *
 * @package Attachments
 */
class AttachmentsViewAttachments extends JViewLegacy
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
		jimport('joomla.application.component.helper');

		$document = JFactory::getDocument();
		if ( JRequest::getWord('format', '') == 'raw' ) {
			// Choose raw text even though it is actually html
			$document->setMimeEncoding('text/plain');
			}

		// Add javascript
		$uri = JFactory::getURI();
		AttachmentsJavascript::setupJavascript();

		// Get the model
		$model = $this->getModel('Attachments');
		if ( !$model ) {
			$errmsg = JText::_('ATTACH_ERROR_UNABLE_TO_FIND_MODEL') . ' (ERR 63)';
			JError::raiseError( 500, $errmsg);
			}

		// See if there are any attachments
		$list = $model->getAttachmentsList();
		if ( ! $list ) {
			return null;
			}

		// if we have attachments, add the stylesheets for the attachments list
		JHtml::stylesheet('com_attachments/attachments_list.css', array(), true);
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_list_rtl.css', array(), true);
			}

		// Add the default path
		$this->addTemplatePath(JPATH_SITE.'/components/com_attachments/views/attachments/tmpl');

		// Set up the correct path for template overloads
		// (Do this after previous addTemplatePath so that template overrides actually override)
		$app = JFactory::getApplication();
		$templateDir = JPATH_SITE.'/templates/'.$app->getTemplate().'/html/com_attachments/attachments';
		$this->addTemplatePath($templateDir);

		// Load the language files from the attachments plugin
		$lang =  JFactory::getLanguage();
		$lang->load('plg_content_attachments', JPATH_SITE.'/plugins/content/attachments');

		// Get the component parameters
		$params = JComponentHelper::getParams('com_attachments');

		// See whether the user-defined fields should be shown
		$from = JRequest::getWord('from', 'closeme');
		$layout = JRequest::getWord('layout');
		$tmpl = JRequest::getWord('tmpl');
		$task = JRequest::getWord('task');
		$show_hidden_user_fields = false;
		if ( $app->isAdmin() || ($from == 'editor') || ($layout == 'edit') || ($tmpl == 'component') ) {
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
			if ( $show_hidden_user_fields || ($user_field_1_name[JString::strlen($user_field_1_name)-1] != '*') ) {
				$show_user_field_1 = true;
				$this->user_field_1_name = $user_field_1_name;
				}
			}
		$this->show_user_field_1 = $show_user_field_1;

		// User field 2
		$show_user_field_2 = false;
		$user_field_2_name = $params->get('user_field_2_name');
		if ( $user_field_2_name ) {
			if ( $show_hidden_user_fields || ($user_field_2_name[JString::strlen($user_field_2_name)-1] != '*') ) {
				$show_user_field_2 = true;
				$this->user_field_2_name = $user_field_2_name;
				}
			}
		$this->show_user_field_2 = $show_user_field_2;

		// User field 3
		$show_user_field_3 = false;
		$user_field_3_name = $params->get('user_field_3_name');
		if ( $user_field_3_name ) {
			if ( $show_hidden_user_fields || ($user_field_3_name[JString::strlen($user_field_3_name)-1] != '*') ) {
				$show_user_field_3 = true;
				$this->user_field_3_name = $user_field_3_name;
				}
			}
		$this->show_user_field_3 = $show_user_field_3;

		// Set up for the template
		$parent_id = $model->getParentId();
		$parent_type = $model->getParentType();
		$parent_entity = JString::strtolower($model->getParentEntity());
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

		// Set up the file/url titleshow_mod_date
		if ( $this->show_column_titles ) {
			switch ( $model->types() ) {
			case 'file':
				$this->file_url_title = JText::_('ATTACH_FILE');
				break;
			case 'url':
				$this->file_url_title = JText::_('ATTACH_URL');
				break;
			default:
				$this->file_url_title = JText::_('ATTACH_FILE_URL');
				}
			}

		if ( $this->show_created_date OR $this->show_modified_date ) {
			$this->date_format = $params->get('date_format', '%Y-%m-%d %I:%M%P');
			}

		// Get the attachments list title
		$title = $this->title;
		if ( !$title || (JString::strlen($title) == 0) ) {
			$title = 'ATTACH_ATTACHMENTS_TITLE';
			}
		$parent = $model->getParentClass();
		$title = $parent->attachmentsListTitle($title, $parent_id, $parent_entity);
		$this->title = $title; // Note: assume it is translated

		// Construct the path for the icons
		$uri = JFactory::getURI();
		$base_url = $uri->root(true) . '/';
		$this->base_url = $base_url;
		$this->icon_url_base = $base_url . 'components/com_attachments/media/icons/';

		// Get the output of the template
		$result = $this->loadTemplate($tpl);
		if (JError::isError($result)) {
			return $result;
			}

		return true;
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
