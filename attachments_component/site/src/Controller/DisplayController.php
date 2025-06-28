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

namespace JMCameron\Component\Attachments\Site\Controller;

use JMCameron\Component\Attachments\Site\Helper\AttachmentsDefines;
use JMCameron\Component\Attachments\Site\Helper\AttachmentsHelper;
use JMCameron\Component\Attachments\Site\Helper\AttachmentsJavascript;
use JMCameron\Plugin\AttachmentsPluginFramework\AttachmentsPluginManager;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * The main attachments controller class (for the front end)
 *
 * @package Attachments
 */
class DisplayController extends BaseController
{
    /**
     * A noop function so this controller does not have a usable default
     */
    public function noop()
    {
        $errmsg = Text::_('ATTACH_ERROR_NO_FUNCTION_SPECIFIED') . ' (ERR 0)';
        throw new \Exception($errmsg, 500);
    }


    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  The model name. Optional.
     * @param   string  The class prefix. Optional.
     * @param   array   Configuration array for model. Optional.
     * @return  object  The model.
     */
    public function getModel($name = 'Attachments', $prefix = 'Site', $config = array())
    {
        $model = parent::getModel($name, $prefix, array('ignore_request' => true));
        return $model;
    }


    /**
     * Display a form for uploading a file/url
     */
    public function upload()
    {
        // Access check.
        $user = $this->app->getIdentity();
        if ($user === null or !$user->authorise('core.create', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 1)', 404);
        }

        // Get the parent info
        $input = $this->input;
        $parent_entity = 'default';
        if ($input->getString('article_id')) {
            $pid_info = explode(',', $input->getString('article_id'));
            $parent_type = 'com_content';
        } else {
            $pid_info = explode(',', $input->getString('parent_id'));
            // Be extra cautious and remove all non-cmd characters except for ':'
            $parent_type = preg_replace('/[^A-Z0-9_\.:-]/i', '', $input->getString('parent_type', 'com_content'));

            // If the entity is embedded in the parent type, split them
            if (strpos($parent_type, '.')) {
                $parts = explode('.', $parent_type);
                $parent_type = $parts[0];
                $parent_entity = $parts[1];
            }
            if (strpos($parent_type, ':')) {
                $parts = explode(':', $parent_type);
                $parent_type = $parts[0];
                $parent_entity = $parts[1];
            }
        }

        // Get the parent id
        $parent_id = null;
        if (is_numeric($pid_info[0])) {
            $parent_id = (int)$pid_info[0];
        }

        // See if the parent is new (or already exists)
        $new_parent = false;
        if (count($pid_info) > 1) {
            if ($pid_info[1] == 'new') {
                $new_parent = true;
            }
        }

        // Get the article/parent handler
        PluginHelper::importPlugin('attachments');
        $apm = AttachmentsPluginManager::getAttachmentsPluginManager();
        if (!$apm->attachmentsPluginInstalled($parent_type)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 2)';
            throw new \Exception($errmsg, 500);
        }
        $parent = $apm->getAttachmentsPlugin($parent_type);

        $parent_entity = $parent->getCanonicalEntityId($parent_entity);
        $parent_entity_name = Text::_('ATTACH_' . $parent_entity);

        // Make sure this user can add attachments to this parent
        if (!$parent->userMayAddAttachment($parent_id, $parent_entity, $new_parent)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_NO_PERMISSION_TO_UPLOAD_S', $parent_entity_name) . ' (ERR 3)';
            throw new \Exception($errmsg, 500);
        }

        // Get the title of the parent
        $parent_title = '';
        if (!$new_parent) {
            $parent_title = $parent->getTitle($parent_id, $parent_entity);
        }

        // Use a different template for the iframe view
        $from = $input->getWord('from');
        $Itemid = $input->getInt('Itemid', 1);
        if ($from == 'closeme') {
            $input->set('tmpl', 'component');
        }

        // Get the component parameters
        $params = ComponentHelper::getParams('com_attachments');

        // Make sure the attachments directory exists
        $upload_dir = JPATH_BASE . '/' . AttachmentsDefines::$ATTACHMENTS_SUBDIR;
        $secure = $params->get('secure', false);
        if (!AttachmentsHelper::setup_upload_directory($upload_dir, $secure)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 4)';
            throw new \Exception($errmsg, 500);
        }

        // Determine the type of upload
        $default_uri_type = 'file';
        $uri_type = $input->getWord('uri', $default_uri_type);
        if (!in_array($uri_type, AttachmentsDefines::$LEGAL_URI_TYPES)) {
            // Make sure only legal values are entered
            $uri_type = 'file';
        }

        // Set up the view to redisplay the form with warnings
        $view = $this->getView('Upload', 'html', 'Site');

        // Set up the view
        if ($new_parent) {
            $parent_id_str = (string)$parent_id . ",new";
        } else {
            $parent_id_str = (string)$parent_id;
        }
        AttachmentsHelper::add_view_urls($view, 'upload', $parent_id_str, $parent_type, null, $from);

        // We do not have a real attachment yet so fake it
        $attachment = new \stdClass();

        // Set up the defaults
        $attachment->uri_type = $uri_type;
        $attachment->state = $params->get('publish_default', false);
        $attachment->url = '';
        $attachment->url_relative = false;
        $attachment->url_verify = true;
        $attachment->display_name = '';
        $attachment->description  = '';
        $attachment->user_field_1 = '';
        $attachment->user_field_2 = '';
        $attachment->user_field_3 = '';
        $attachment->parent_id = $parent_id;
        $attachment->parent_type = $parent_type;
        $attachment->parent_entity = $parent_entity;
        $attachment->parent_title = $parent_title;

        $view->attachment = $attachment;

        $view->parent     = $parent;
        $view->new_parent = $new_parent;
        $view->Itemid     = $Itemid;
        $view->from       = $from;

        $view->params = $params;

        $view->error = false;
        $view->error_msg = false;

        // Display the upload form
        $view->display();
    }


    /**
     * Save a new or edited attachment
     */
    public function save()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        // Make sure that the user is logged in
        $user = $this->app->getIdentity();

        // Get the parameters
        $params = ComponentHelper::getParams('com_attachments');

        // Get the article/parent handler
        $input = $this->input;
        $new_parent = $input->getBool('new_parent', false);
        $parent_type = $input->getCmd('parent_type', 'com_content');
        $parent_entity = $input->getCmd('parent_entity', 'default');
        PluginHelper::importPlugin('attachments');
        PluginHelper::importPlugin('content');
        $apm = AttachmentsPluginManager::getAttachmentsPluginManager();
        if (!$apm->attachmentsPluginInstalled($parent_type)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 5)';
            throw new \Exception($errmsg, 500);
        }
        $parent = $apm->getAttachmentsPlugin($parent_type);

        $parent_entity = $parent->getCanonicalEntityId($parent_entity);
        $parent_entity_name = Text::_('ATTACH_' . $parent_entity);

        // Make sure we have a valid parent ID
        $parent_id = $input->getInt('parent_id', -1);
        if (
            !$new_parent && (($parent_id == 0) ||
                               ($parent_id == -1) ||
                               !$parent->parentExists($parent_id, $parent_entity))
        ) {
            $errmsg = Text::sprintf(
                'ATTACH_ERROR_INVALID_PARENT_S_ID_N',
                $parent_entity_name,
                $parent_id
            ) . ' (ERR 6)';
            throw new \Exception($errmsg, 500);
        }

        // Verify that this user may add attachments to this parent
        if (!$parent->userMayAddAttachment($parent_id, $parent_entity, $new_parent)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_NO_PERMISSION_TO_UPLOAD_S', $parent_entity_name) . ' (ERR 7)';
            throw new \Exception($errmsg, 500);
        }

        // Get the Itemid
        $Itemid = $input->getInt('Itemid', 1);

        // How to redirect?
        $from = $input->getWord('from', 'closeme');
        $uri = Uri::getInstance();
        $base_url = $uri->base(false);
        if ($from) {
            if ($from == 'frontpage') {
                $redirect_to = $uri->root(true);
            } elseif ($from == 'article') {
                $redirect_to = Route::_($base_url . "index.php?option=com_content&view=article&id=$parent_id", false);
            } else {
                $redirect_to = $uri->root(true);
            }
        } else {
            $redirect_to = $uri->root(true);
        }

        // See if we should cancel
        if ($_POST['submit'] == Text::_('ATTACH_CANCEL')) {
            $msg = Text::_('ATTACH_UPLOAD_CANCELED');
            $this->setRedirect($redirect_to, $msg);
            return;
        }

        // Figure out if we are uploading or updating
        $save_type = StringHelper::strtolower($input->getWord('save_type'));
        if (!in_array($save_type, AttachmentsDefines::$LEGAL_SAVE_TYPES)) {
            $errmsg = Text::_('ATTACH_ERROR_INVALID_SAVE_PARAMETERS') . ' (ERR 8)';
            throw new \Exception($errmsg, 500);
        }

        // If this is an update, get the attachment id
        $attachment_id = false;
        if ($save_type == 'update') {
            $attachment_id = $input->getInt('id');
        }

        // Bind the info from the form
        $attachment = $this->factory->createTable('Attachment', 'Administrator');
        if ($attachment_id && !$attachment->load($attachment_id)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_CANNOT_UPDATE_ATTACHMENT_INVALID_ID_N', $attachment_id) . ' (ERR 9)';
            throw new \Exception($errmsg, 500);
        }
        if (!$attachment->bind($input->post->getArray())) {
            $errmsg = $attachment->getError() . ' (ERR 10)';
            throw new \Exception($errmsg, 500);
        }

        // Note what the old uri type is, if updating
        $old_uri_type = null;
        if ($save_type  == 'update') {
            $old_uri_type = $attachment->uri_type;
        }

        // Figure out what the new URI is
        if ($save_type == 'upload') {
            // See if we are uploading a file or URL
            $new_uri_type = $input->getWord('uri_type');
            if ($new_uri_type && !in_array($new_uri_type, AttachmentsDefines::$LEGAL_URI_TYPES)) {
                // Make sure only legal values are entered
                $new_uri_type = '';
            }

            // Fix the access level
            if (!$params->get('allow_frontend_access_editing', false)) {
                $attachment->access = $params->get('default_access_level', AttachmentsDefines::$DEFAULT_ACCESS_LEVEL_ID);
            }
        } elseif ($save_type == 'update') {
            // See if we are updating a file or URL
            $new_uri_type = $input->getWord('update');
            if ($new_uri_type && !in_array($new_uri_type, AttachmentsDefines::$LEGAL_URI_TYPES)) {
                // Make sure only legal values are entered
                $new_uri_type = '';
            }

            // Since URLs can be edited, we always evaluate them from scratch
            if (($new_uri_type == '') && ($old_uri_type == 'url')) {
                $new_uri_type = 'url';
            }

            // Double-check to see if the URL changed
            $old_url = $input->getString('old_url');
            if (!$new_uri_type && $old_url && ($old_url != $attachment->url)) {
                $new_uri_type = 'url';
            }
        }

        // Get more info about the type of upload/update
        $verify_url = false;
        $relative_url = false;
        if ($new_uri_type == 'url') {
            if ($input->getWord('verify_url') == 'verify') {
                $verify_url = true;
            }
            if ($input->getWord('relative_url') == 'relative') {
                $relative_url = true;
            }
        }

        $app = Factory::getApplication();
        // Handle the various ways this function might get invoked
        if ($save_type == 'upload') {
            $attachment->created_by = $user->get('id');
            $attachment->parent_id = $parent_id;

            $app->triggerEvent('onContentBeforeSave', [
                'com_attachments.attachment',
                $attachment,
                null,
                true
            ]);
        } else {
            $app->triggerEvent('onContentBeforeSave', [
                'com_attachments.attachment',
                $attachment,
                null,
                false
            ]);
        }

        // Update the modified info
        $now = Factory::getDate();
        $attachment->modified_by = $user->get('id');
        $attachment->modified = $now->toSql();

        // Set up a couple of items that the upload function may need
        $parent->new = $new_parent;
        if ($new_parent) {
            $attachment->parent_id = null;
            $parent->title = '';
        } else {
            $attachment->parent_id = $parent_id;
            $parent->title = $parent->getTitle($parent_id, $parent_entity);
        }

        // Upload new file/url and create/update the attachment
        if ($new_uri_type == 'file') {
            // Upload a new file
            $msg = AttachmentsHelper::upload_file($attachment, $parent, $attachment_id, $save_type);
            // NOTE: store() is not needed if upload_file() is called since it does it
        } elseif ($new_uri_type == 'url') {
            $attachment->url_relative = $relative_url;
            $attachment->url_verify = $verify_url;

            // Upload/add the new URL
            $msg = AttachmentsHelper::add_url(
                $attachment,
                $parent,
                $verify_url,
                $relative_url,
                $old_uri_type,
                $attachment_id
            );
            // NOTE: store() is not needed if add_url() is called since it does it
        } else {
            // Save the updated attachment info
            if (!$attachment->store()) {
                $errmsg = $attachment->getError() . ' (ERR 11)';
                throw new \Exception($errmsg, 500);
            }

            $lang =  $this->app->getLanguage();
            $lang->load('com_attachments', JPATH_SITE);

            $msg = Text::_('ATTACH_ATTACHMENT_UPDATED');
        }

        if ($save_type == 'upload') {
            $app->triggerEvent('onContentAfterSave', [
                'com_attachments.attachment',
                $attachment,
                null,
                true
            ]);
        } else {
            $app->triggerEvent('onContentAfterSave', [
                'com_attachments.attachment',
                $attachment,
                null,
                false
            ]);
        }

        // If we are supposed to close this iframe, do it now.
        if (in_array($from, $parent->knownFroms())) {
            // If there is no parent_id, the parent is being created, use the username instead
            if ($new_parent) {
                $pid = 0;
            } else {
                $pid = (int)$parent_id;
            }

            // Close the iframe and refresh the attachments list in the parent window
            $base_url = $uri->root(true);
            $lang = $input->getCmd('lang', '');
            AttachmentsJavascript::closeIframeRefreshAttachments($base_url, $parent_type, $parent_entity, $pid, $lang, $from);
            exit();
        }

        $this->setRedirect($redirect_to, $msg);
    }


    /**
     * Download a file
     */
    public function download()
    {
        // Get the attachment ID
        $id = $this->input->getInt('id');
        if (!is_numeric($id)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 12)';
            throw new \Exception($errmsg, 500);
        }
        $raw = $this->input->getInt('raw', 0);
        $popup = $this->input->getInt('popup', 0);
        // NOTE: The helper download_attachment($id) function does the access check
        AttachmentsHelper::download_attachment($id, $raw, $popup);
    }


    /**
     * Delete an attachment
     */
    public function delete()
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $input = $this->input;

        // Make sure we have a valid attachment ID
        $id = $input->getInt('id');
        if (is_numeric($id)) {
            $id = (int)$id;
        } else {
            $errmsg = Text::sprintf('ATTACH_ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 13)';
            throw new \Exception($errmsg, 500);
        }

        // Get the attachment info
        /** @var \JMCameron\Component\Attachments\Site\Model\AttachmentModel $model */
        $model = $this->getModel('Attachment');
        $model->setId($id);
        $attachment = $model->getAttachment();
        if (!$attachment) {
            $errmsg = Text::sprintf('ATTACH_ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 14)';
            throw new \Exception($errmsg, 500);
        }
        $filename_sys  = $attachment->filename_sys;
        $filename      = $attachment->filename;
        $parent_id     = $attachment->parent_id;
        $parent_type   = $attachment->parent_type;
        $parent_entity = $attachment->parent_entity;

        // Get the article/parent handler
        PluginHelper::importPlugin('attachments');
        $apm = AttachmentsPluginManager::getAttachmentsPluginManager();
        if (!$apm->attachmentsPluginInstalled($parent_type)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 15)';
            throw new \Exception($errmsg, 500);
        }
        $parent = $apm->getAttachmentsPlugin($parent_type);

        $parent_entity_name = Text::_('ATTACH_' . $parent_entity);

        // Check to make sure we can edit it
        if (!$parent->userMayDeleteAttachment($attachment)) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 16)', 404);
        }

        // Make sure the parent exists
        // NOTE: $parent_id===null means the parent is being created
        if (($parent_id !== null) && !$parent->parentExists($parent_id, $parent_entity)) {
            $errmsg = Text::sprintf(
                'ATTACH_ERROR_CANNOT_DELETE_INVALID_S_ID_N',
                $parent_entity_name,
                $parent_id
            ) . ' (ERR 17)';
            throw new \Exception($errmsg, 500);
        }

        // See if this user can edit (or delete) the attachment
        if (!$parent->userMayDeleteAttachment($attachment)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_NO_PERMISSION_TO_DELETE_S', $parent_entity_name) . ' (ERR 18)';
            throw new \Exception($errmsg, 500);
        }

        // First delete the actual attachment files (if any)
        if ($filename_sys) {
            if (File::exists($filename_sys)) {
                File::delete($filename_sys);
            }
        }

        // Delete the entries in the attachments table
        $query = $db->getQuery(true);
        $query->delete('#__attachments')->where('id = ' . (int)$id);
        $db->setQuery($query);
        if (!$db->execute()) {
            $errmsg = $db->getErrorMsg() . ' (ERR 19)';
            throw new \Exception($errmsg, 500);
        }

        PluginHelper::importPlugin('content');
        Factory::getApplication()->triggerEvent('onContentAfterDelete', [
            'com_attachments.attachment',
            $attachment,
            null,
            false
        ]);

        // Clean up after ourselves
        AttachmentsHelper::clean_directory($filename_sys);

        // Get the Itemid
        $Itemid = $input->getInt('Itemid', 1);

        $msg = Text::_('ATTACH_DELETED_ATTACHMENT') . " '$filename'";

        // Figure out how to redirect
        $from = $input->getWord('from', 'closeme');
        $uri = Uri::getInstance();
        if (in_array($from, $parent->knownFroms())) {
            // If there is no parent_id, the parent is being created, use the username instead
            if (!$parent_id) {
                $pid = 0;
            } else {
                $pid = (int)$parent_id;
            }

            // Close the iframe and refresh the attachments list in the parent window
            $base_url = $uri->root(true);
            $lang = $input->getCmd('lang', '');
            AttachmentsJavascript::closeIframeRefreshAttachments($base_url, $parent_type, $parent_entity, $pid, $lang, $from);
            exit();
        } else {
            $redirect_to = $uri->root(true);
        }

        $this->setRedirect($redirect_to, $msg);
    }


    /**
     * Show the warning for deleting an attachment
     */
    public function delete_warning()
    {
        $input = $this->input;
        // Make sure we have a valid attachment ID
        $attachment_id = $input->getInt('id');
        if (is_numeric($attachment_id)) {
            $attachment_id = (int)$attachment_id;
        } else {
            $errmsg = Text::sprintf('ATTACH_ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $attachment_id) . ' (ERR 20)';
            throw new \Exception($errmsg, 500);
        }

        // Get the attachment record
        $attachment = $this->factory->createTable('Attachment', 'Administrator');
        if (!$attachment->load($attachment_id)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $attachment_id) . ' (ERR 21)';
            throw new \Exception($errmsg, 500);
        }

        // Get the parent object
        $parent_type = $attachment->parent_type;
        PluginHelper::importPlugin('attachments');
        $apm = AttachmentsPluginManager::getAttachmentsPluginManager();
        if (!$apm->attachmentsPluginInstalled($parent_type)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 22)';
            throw new \Exception($errmsg, 500);
        }
        $parent = $apm->getAttachmentsPlugin($parent_type);

        // Check to make sure we can edit it
        $parent_id = $attachment->parent_id;
        if (!$parent->userMayDeleteAttachment($attachment)) {
            $errmsg = Text::_('ATTACH_ERROR_NO_PERMISSION_TO_DELETE_ATTACHMENT') . ' (ERR 23)';
            throw new \Exception($errmsg, 500);
        }

        // Set up the view
        $view = $this->getView('Warning', 'html', 'Site', ['option' => $input->getCmd('option')]);
        $view->parent_id = $parent_id;
        $view->from = $input->getWord('from', 'closeme');
        $view->tmpl = $input->getWord('tmpl');

        // Prepare for the query
        $view->warning_title = Text::_('ATTACH_WARNING');
        if ($attachment->uri_type == 'file') {
            $fname = "( {$attachment->filename} )";
        } else {
            $fname = "( {$attachment->url} )";
        }
        $view->warning_question = Text::_('ATTACH_REALLY_DELETE_ATTACHMENT') . '<br/>' . $fname;

        $base_url = Uri::base(false);
        $delete_url = $base_url . "index.php?option=com_attachments&task=delete&id=$attachment_id";
        $delete_url = Route::_($delete_url);
        $view->action_url = $delete_url;
        $view->action_button_label = Text::_('ATTACH_DELETE');

        $view->display();
    }


    /**
     * Display a form for updating/editing an attachment
     */
    public function update()
    {
        // Call with: index.php?option=com_attachments&task=update&id=1&tmpl=component
        //        or: component/attachments/update/id/1/tmpl/component

        $input = $this->input;

        // Make sure we have a valid attachment ID
        $id = $input->getInt('id');
        if (is_numeric($id)) {
            $id = (int)$id;
        } else {
            $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 24)';
            throw new \Exception($errmsg, 500);
        }

        // Get the attachment record
        /** @var \JMCameron\Component\Attachments\Site\Model\AttachmentModel $model */
        $model = $this->getModel('Attachment');
        $model->setId($id);
        $attachment = $model->getAttachment();
        if (!$attachment) {
            $errmsg = Text::sprintf('ATTACH_ERROR_CANNOT_UPDATE_ATTACHMENT_INVALID_ID_N', $id) . ' (ERR 25)';
            throw new \Exception($errmsg, 500);
        }

        // Get the component parameters
        $params = ComponentHelper::getParams('com_attachments');

        // Get the article/parent handler
        $parent_id = $attachment->parent_id;
        $parent_type = $attachment->parent_type;
        $parent_entity = $attachment->parent_entity;
        PluginHelper::importPlugin('attachments');
        $apm = AttachmentsPluginManager::getAttachmentsPluginManager();
        if (!$apm->attachmentsPluginInstalled($parent_type)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 26)';
            throw new \Exception($errmsg, 500);
        }
        $parent = $apm->getAttachmentsPlugin($parent_type);

        // Check to make sure we can edit it
        if (!$parent->userMayEditAttachment($attachment)) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 27)', 404);
        }

        // Set up the entity name for display
        $parent_entity_name = Text::_('ATTACH_' . $parent_entity);

        // Verify that this user may add attachments to this parent
        $user = $this->app->getIdentity();
        $new_parent = false;
        if ($parent_id === null) {
            $parent_id = 0;
            $new_parent = true;
        }

        // Make sure the attachments directory exists
        $upload_dir = JPATH_BASE . '/' . AttachmentsDefines::$ATTACHMENTS_SUBDIR;
        $secure = $params->get('secure', false);
        if (!AttachmentsHelper::setup_upload_directory($upload_dir, $secure)) {
            $errmsg = Text::sprintf('ATTACH_ERROR_UNABLE_TO_SETUP_UPLOAD_DIR_S', $upload_dir) . ' (ERR 28)';
            throw new \Exception($errmsg, 500);
        }

        // Make sure the update parameter is legal
        $update = $input->getWord('update');
        if ($update && !in_array($update, AttachmentsDefines::$LEGAL_URI_TYPES)) {
            $update = false;
        }

        // Suppress the display filename if we are switching from file to url
        $display_name = $attachment->display_name;
        if ($update && ($update != $attachment->uri_type)) {
            $attachment->display_name = '';
        }

        // Set up the view
        $view = $this->getView('Update', 'html', 'Site');
        $from = $input->getWord('from', 'closeme');
        AttachmentsHelper::add_view_urls(
            $view,
            'update',
            $parent_id,
            $attachment->parent_type,
            $id,
            $from
        );

        $view->update =     $update;
        $view->new_parent = $new_parent;

        $view->attachment = $attachment;

        $view->parent     = $parent;
        $view->params     = $params;

        $view->from       = $from;
        $view->Itemid     = $input->getInt('Itemid', 1);

        $view->error = false;
        $view->error_msg = false;

        $view->display();
    }


    /**
     * Return the attachments list as HTML (for use by Ajax)
     */
    public function attachmentsList()
    {
        $input = $this->input;
        $parent_id = $input->getInt('parent_id', false);
        $parent_type = $input->getWord('parent_type', '');
        $parent_entity = $input->getWord('parent_entity', 'default');
        $show_links = $input->getBool('show_links', true);
        $allow_edit = $input->getBool('allow_edit', true);
        $from = $input->getWord('from', 'closeme');
        $title = '';

        $response = '';

        if (($parent_id === false) || ($parent_type == '')) {
            return '';
        }

        // NOTE: I cannot find anything about AttachmentsRemapper class.
        // Could it be old unnecessary code that needs deletion?
        // ------------------------------------------------------
        // Allow remapping of parent ID (eg, for Joomfish)
        $lang = $input->getWord('lang', '');
        if ($lang and jimport('attachments_remapper.remapper')) {
            $parent_id = AttachmentsRemapper::remapParentID($parent_id, $parent_type, $parent_entity);
        }

        /** @var \JMCameron\Component\Attachments\Site\Controller\AttachmentsController $controller */
        $controller = $this->factory->createController('Attachments', 'Site', [], $this->app, $this->input);
        $response = $controller->displayString(
            $parent_id,
            $parent_type,
            $parent_entity,
            $title,
            $show_links,
            $allow_edit,
            false,
            $from
        );
        echo $response;
    }

    /**
     * Request the user log in
     */
    public function requestLogin()
    {
        // Set up the view to redisplay the form with warnings
        $view = $this->getView('Warning', 'html', 'Site');

        // Display the view
        $view->return_url = $this->input->getString('return');
        $view->display(null, false, false);
    }
}
