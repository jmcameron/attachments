<?php

/**
 * Attachments component attachments controller
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\Controller;

use JMCameron\Component\Attachments\Site\Helper\AttachmentsHelper;
use JMCameron\Component\Attachments\Site\Helper\AttachmentsJavascript;
use JMCameron\Plugin\AttachmentsPluginFramework\AttachmentsPluginManager;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * Attachments Controller
 *
 * @package Attachments
 */
class AttachmentsController extends AdminController
{
    /**
     * Method to get a model object, loading it if required.
     *
     * @param   string  The model name. Optional.
     * @param   string  The class prefix. Optional.
     * @param   array   Configuration array for model. Optional.
     *
     * @return  BaseDatabaseModel|boolean   The model.
     */
    public function getModel($name = 'Attachments', $prefix = 'Administrator', $config = array())
    {
        return parent::getModel($name, $prefix, array_merge(array('ignore_request' => true), $config));
    }


    /**
     * Display the attachments list
     *
     * @param int $parent_id the id of the parent
     * @param string $parent_type the type of parent
     * @param string $parent_entity the type entity of the parent
     * @param string $title title to be shown above the list of articles.  If null, use system defaults.
     * @param bool $show_file_links enable showing links for the filenames
     * @param bool $allow_edit enable showing edit/delete links (if permissions are okay)
     * @param bool $echo if true the output will be echoed; otherwise the results are returned.
     * @param string $from The 'from' info
     *
     * @return the string (if $echo is false)
     */
    public function displayString(
        $parent_id,
        $parent_type,
        $parent_entity,
        $title = null,
        $show_file_links = true,
        $allow_edit = true,
        $echo = true,
        $from = null
    ) {
        $document = $this->app->getDocument();

        // Get an instance of the model
        /** @var \JMCameron\Component\Attachments\Site\Model\AttachmentsModel $model */
        $model = $this->getModel('Attachments', 'Site');
        if (!$model) {
            $errmsg = Text::_('ATTACH_ERROR_UNABLE_TO_FIND_MODEL') . ' (ERR 164)';
            throw new \Exception($errmsg, 500);
        }

        $model->setParentId($parent_id, $parent_type, $parent_entity);

        // Get the component parameters
        $params = ComponentHelper::getParams('com_attachments');

        // Set up to list the attachments for this article
        $sort_order = $params->get('sort_order', 'filename');
        $model->setSortOrder($sort_order);

        // If none of the attachments should be visible, exit now
        if (! $model->someVisible()) {
            return false;
        }

        // Get the view
        $viewType = $document->getType();
        /** @var \JMCameron\Component\Attachments\Site\View\Attachments\HtmlView $view */
        $view = $this->getView('Attachments', $viewType, 'Site');
        if (!$view) {
            $errmsg = Text::_('ATTACH_ERROR_UNABLE_TO_FIND_VIEW') . ' (ERR 165)';
            throw new \Exception($errmsg, 500);
        }
        $view->setModel($model);

        // Construct the update URL template
        $update_url = "index.php?option=com_attachments&task=edit&cid[]=%d";
        $update_url .= "&from=$from&tmpl=component";
        $view->update_url = $update_url;

        // Construct the delete URL template
        $delete_url = "index.php?option=com_attachments&task=attachment.deleteWarning&id=%d";
        $delete_url .= "&parent_type=$parent_type&parent_entity=$parent_entity&parent_id=" . (int)$parent_id;
        $delete_url .= "&from=$from&tmpl=component";
        $view->delete_url = $delete_url;

        // Set some display settings
        $view->title = $title;
        $view->show_file_links = $show_file_links;
        $view->allow_edit = $allow_edit;
        $view->from = $from;

        // Get the view to generate the display output from the template
        if ($view->display() === true) {
            // Display or return the results
            if ($echo) {
                echo $view->getOutput();
            } else {
                return $view->getOutput();
            }
        }

        return false;
    }


    /**
     * Delete attachment(s)
     */
    public function delete()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        // Get the attachments parent manager
        PluginHelper::importPlugin('attachments');
        $apm = AttachmentsPluginManager::getAttachmentsPluginManager();

        // Get attachments to remove from the request
        $input = $this->app->getInput();
        $cid = $input->get('cid', array(), 'array');
        $deleted_ids = array();

        /** @var \JMCameron\Component\Attachments\Administrator\Model\AttachmentModel $model */
        $model      = $this->getModel('Attachment');
        $attachment = $model->getTable();

        // Load content plugin for onContentAfterDelete
        PluginHelper::importPlugin('content');

        if (count($cid)) {
            // Loop through the attachments and delete them one-by-one
            foreach ($cid as $attachment_id) {
                // Load the attachment object
                $id = (int)$attachment_id;
                if (($id == 0) or !$attachment->load($id)) {
                    $errmsg = Text::sprintf('ATTACH_ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 166)';
                    throw new \Exception($errmsg, 500);
                }
                $parent_id = $attachment->parent_id;
                $parent_type = $attachment->parent_type;
                $parent_entity = $attachment->parent_entity;

                // Get the article/parent handler
                PluginHelper::importPlugin('attachments');
                $apm = AttachmentsPluginManager::getAttachmentsPluginManager();
                if (!$apm->attachmentsPluginInstalled($parent_type)) {
                    $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 167)';
                    throw new \Exception($errmsg, 500);
                }
                $parent = $apm->getAttachmentsPlugin($parent_type);

                // If we may not delete it, complain!
                if ($parent->userMayDeleteAttachment($attachment)) {
                    // Delete the actual file
                    if (File::exists($attachment->filename_sys)) {
                        File::delete($attachment->filename_sys);
                        AttachmentsHelper::cleanDirectory($attachment->filename_sys);
                    }
                    $deleted_ids[] = $id;

                    Factory::getApplication()->triggerEvent('onContentAfterDelete', [
                        'com_attachments.attachment',
                        $attachment,
                        null,
                        false
                    ]);
                } else {
                    $parent_entity = $parent->getCanonicalEntityId($parent_entity);
                    $errmsg = Text::sprintf(
                        'ATTACH_ERROR_NO_PERMISSION_TO_DELETE_S_ATTACHMENT_S_ID_N',
                        $parent_entity,
                        $attachment->filename,
                        $id
                    );
                    $this->app->enqueueMessage($errmsg, 'warning');
                }
            }

            // Delete entries in the attachments table for deleted attachments
            if (!empty($deleted_ids)) {
                $db = Factory::getContainer()->get('DatabaseDriver');
                $query = $db->getQuery(true);
                $query->delete('#__attachments')->where("id IN (" . implode(',', $deleted_ids) . ")");
                $db->setQuery($query);
                if (!$db->execute()) {
                    $errmsg = $db->getErrorMsg() . ' (ERR 168)';
                    throw new \Exception($errmsg, 500);
                }
            }
        }

        // Figure out how to redirect
        $from = $input->getWord('from');
        $known_froms = array('frontpage', 'article', 'editor', 'closeme');
        if (in_array($from, $known_froms)) {
            // Get the parent info from the last attachment
            $parent_id     = $attachment->parent_id;
            $parent_type   = $attachment->parent_type;
            $parent_entity = $attachment->parent_entity;

            // Get the article/parent handler
            if (!$apm->attachmentsPluginInstalled($parent_type)) {
                $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 169)';
                throw new \Exception($errmsg, 500);
            }
            $parent = $apm->getAttachmentsPlugin($parent_type);
            $parent_entity = $parent->getCanonicalEntityId($parent_entity);

            // Make sure the parent exists
            // NOTE: $parent_id===null means the parent is being created
            if (($parent_id !== null) && !$parent->parentExists($parent_id, $parent_entity)) {
                $parent_entity_name = Text::_('ATTACH_' . $parent_entity);
                $errmsg = Text::sprintf(
                    'ATTACH_ERROR_CANNOT_DELETE_INVALID_S_ID_N',
                    $parent_entity_name,
                    $parent_id
                ) . ' (ERR 170)';
                throw new \Exception($errmsg, 500);
            }

            // If there is no parent_id, the parent is being created, use the username instead
            if (!$parent_id) {
                $pid = 0;
            } else {
                $pid = (int)$parent_id;
            }

            // Close the iframe and refresh the attachments list in the parent window
            $uri = Uri::getInstance();
            $base_url = $uri->base(true);
            $lang = $input->getCmd('lang', '');
            AttachmentsJavascript::closeIframeRefreshAttachments(
                $base_url,
                $parent_type,
                $parent_entity,
                $pid,
                $lang,
                $from,
                false
            );
            exit();
        }

        $this->setRedirect('index.php?option=' . $this->option);
    }


    /**
     * Method to publish a list of items
     * (Adapted from JControllerAdmin)
     *
     * @return  void
     *
     * @since   11.1
     */
    public function publish()
    {
        // Check for request forgeries
        Session::checkToken() or die(Text::_('JINVALID_TOKEN'));

        // Get items to publish from the request.
        $app = Factory::getApplication();
        $input = $app->getInput();
        $cid = $input->get('cid', array(), 'array');
        $data = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
        $task = $this->getTask();
        $value = ArrayHelper::getValue($data, $task, 0, 'int');

        if (empty($cid)) {
            throw new \Exception(Text::_($this->text_prefix . '_NO_ITEM_SELECTED'), 500);
        } else {
            // Get the model.
            /** @var \JMCameron\Component\Attachments\Administrator\Model\AttachmentsModel $model */
            $model = $this->getModel();

            // Make sure the item ids are integers
            ArrayHelper::toInteger($cid);

            // Publish the items.
            $att_published = $model->publish($cid, $value);
            if (($att_published == false) or ($att_published == 0)) {
                throw new \Exception($model->getError(), 500);
            } else {
                PluginHelper::importPlugin('content');
                $app->triggerEvent('onContentChangeState', [
                    'com_attachments.attachment',
                    $cid,
                    $value
                ]);

                if ($value == 1) {
                    $ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
                } elseif ($value == 0) {
                    $ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
                } elseif ($value == 2) {
                    $ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
                } else {
                    $ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
                }
                $this->setMessage(Text::plural($ntext, $att_published));
            }
        }
        $extension = $input->getCmd('extension');
        $extensionURL = ($extension) ? '&extension=' . $input->getCmd('extension') : '';
        $this->setRedirect(Route::_('index.php?option=' . $this->option . '&view=' .
                            $this->view_list . $extensionURL, false));
    }
}
