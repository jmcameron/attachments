<?php
/**
 * Attachments component attachments controller
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');

/**
 * Attachments Controller
 *
 * @package Attachments
 */
class AttachmentsControllerAttachments extends JControllerAdmin
{

	/**
	 * Method to get a model object, loading it if required.
	 *
	 * @param	string	The model name. Optional.
	 * @param	string	The class prefix. Optional.
	 * @param	array	Configuration array for model. Optional.
	 *
	 * @return	object	The model.
	 */
	public function getModel($name = 'Attachments', $prefix = 'AttachmentsModel', $config = array())
	{
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
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
	public function displayString($parent_id, $parent_type, $parent_entity,
								  $title=null, $show_file_links=true, $allow_edit=true,
								  $echo=true, $from=null)
	{
		$document = JFactory::getDocument();

		// Get an instance of the model
		$this->addModelPath(JPATH_SITE.'/components/com_attachments/models');
		$model = $this->getModel('Attachments');
		if ( !$model ) {
			$errmsg = JText::_('ATTACH_ERROR_UNABLE_TO_FIND_MODEL') . ' (ERR 164)';
			JError::raiseError(500, $errmsg);
			}

		$model->setParentId($parent_id, $parent_type, $parent_entity);

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		// Set up to list the attachments for this artticle
		$sort_order = $params->get('sort_order', 'filename');
		$model->setSortOrder($sort_order);

		// If none of the attachments should be visible, exit now
		if ( ! $model->someVisible() ) {
			return false;
			}

		// Get the view
		$this->addViewPath(JPATH_SITE.'/components/com_attachments/views');
		$viewType = $document->getType();
		$view = $this->getView('Attachments', $viewType);
		if ( !$view ) {
			$errmsg = JText::_('ATTACH_ERROR_UNABLE_TO_FIND_VIEW') . ' (ERR 165)';
			JError::raiseError(500, $errmsg);
			}
		$view->setModel($model);

		// Construct the update URL template
		$update_url = "index.php?option=com_attachments&task=edit&cid[]=%d";
		$update_url .= "&from=$from&tmpl=component";
		$view->update_url = $update_url;

		// Construct the delete URL template
		$delete_url = "index.php?option=com_attachments&task=attachment.delete_warning&id=%d";
		$delete_url .= "&parent_type=$parent_type&parent_entity=$parent_entity&parent_id=" . (int)$parent_id;
		$delete_url .= "&from=$from&tmpl=component";
		$view->delete_url = $delete_url;

		// Set some display settings
		$view->title = $title;
		$view->show_file_links = $show_file_links;
		$view->allow_edit = $allow_edit;
		$view->from = $from;

		// Get the view to generate the display output from the template
		if ( $view->display() === true ) {

			// Display or return the results
			if ( $echo ) {
				echo $view->getOutput();
				}
			else {
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
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get ready
		$app = JFactory::getApplication();
		jimport('joomla.filesystem.file');
		require_once(JPATH_SITE.'/components/com_attachments/helper.php');

		// Get the attachments parent manager
		JPluginHelper::importPlugin('attachments');
		$apm = getAttachmentsPluginManager();

		// Get attachments to remove from the request
		$cid = JRequest::getVar('cid', array(), '', 'array');
		$deleted_ids = Array();

		if (count($cid))
		{
			$model		= $this->getModel('Attachment');
			$attachment = $model->getTable();


			// Loop through the attachments and delete them one-by-one
			foreach ($cid as $attachment_id)
			{
				// Load the attachment object
				$id = (int)$attachment_id;
				if ( ($id == 0) OR !$attachment->load($id) ) {
					$errmsg = JText::sprintf('ATTACH_ERROR_CANNOT_DELETE_INVALID_ATTACHMENT_ID_N', $id) . ' (ERR 166)';
					JError::raiseError(500, $errmsg);
					}
				$parent_id = $attachment->parent_id;
				$parent_type = $attachment->parent_type;
				$parent_entity = $attachment->parent_entity;

				// Get the article/parent handler
				JPluginHelper::importPlugin('attachments');
				$apm = getAttachmentsPluginManager();
				if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
					$errmsg = JText::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 167)';
					JError::raiseError(500, $errmsg);
					}
				$parent = $apm->getAttachmentsPlugin($parent_type);

				// If we may not delete it, complain!
				if ( $parent->userMayDeleteAttachment($attachment) )
				{
					// Delete the actual file
					if ( JFile::exists($attachment->filename_sys) )
					{
						JFile::delete($attachment->filename_sys);
						AttachmentsHelper::clean_directory($attachment->filename_sys);
					}
					$deleted_ids[] = $id;
				}
				else
				{
					$parent_entity = $parent->getCanonicalEntityId($parent_entity);
					$errmsg = JText::sprintf('ATTACH_ERROR_NO_PERMISSION_TO_DELETE_S_ATTACHMENT_S_ID_N',
											 $parent_entity, $attachment->filename, $id);
					$app->enqueueMessage($errmsg, 'warning');
				}
			}

			// Delete entries in the attachments table for deleted attachments
			if (!empty($deleted_ids))
			{
				$db = JFactory::getDBO();
				$query = $db->getQuery(true);
				$query->delete('#__attachments')->where("id IN (".implode(',', $deleted_ids).")");
				$db->setQuery($query);
				if (!$db->query()) {
					$errmsg = $db->getErrorMsg() . ' (ERR 168)';
					JError::raiseError(500, $errmsg);
					}
			}
		}

		// Figure out how to redirect
		$from = JRequest::getWord('from');
		$known_froms = array('frontpage', 'article', 'editor', 'closeme');
		if ( in_array( $from, $known_froms ) )
		{
			// Get the parent info from the last attachment
			$parent_id	   = $attachment->parent_id;
			$parent_type   = $attachment->parent_type;
			$parent_entity = $attachment->parent_entity;

			// Get the article/parent handler
			if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
				$errmsg = JText::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 169)';
				JError::raiseError(500, $errmsg);
				}
			$parent = $apm->getAttachmentsPlugin($parent_type);
			$parent_entity = $parent->getCanonicalEntityId($parent_entity);

			// Make sure the parent exists
			// NOTE: $parent_id===null means the parent is being created
			if ( ($parent_id !== null) && !$parent->parentExists($parent_id, $parent_entity) ) {
				$parent_entity_name = JText::_('ATTACH_' . $parent_entity);
				$errmsg = JText::sprintf('ATTACH_ERROR_CANNOT_DELETE_INVALID_S_ID_N',
										 $parent_entity_name, $parent_id) . ' (ERR 170)';
				JError::raiseError(500, $errmsg);
				}

			// If there is no parent_id, the parent is being created, use the username instead
			if ( !$parent_id ) {
				$pid = 0;
				}
			else {
				$pid = (int)$parent_id;
				}

			// Close the iframe and refresh the attachments list in the parent window
			require_once(JPATH_SITE.'/components/com_attachments/javascript.php');
			$uri = JFactory::getURI();
			$base_url = $uri->base(true);
			AttachmentsJavascript::closeIframeRefreshAttachments($base_url, $parent_type, $parent_entity, $pid, $from);
			exit();
		}

		$this->setRedirect( 'index.php?option=' . $this->option);
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
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid = JRequest::getVar('cid', array(), '', 'array');
		$data = array('publish' => 1, 'unpublish' => 0, 'archive' => 2, 'trash' => -2, 'report' => -3);
		$task = $this->getTask();
		$value = JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid))
		{
			JError::raiseError(500, JText::_($this->text_prefix . '_NO_ITEM_SELECTED'));
		}
		else
		{
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			$att_published = $model->publish($cid, $value);
			if (($att_published == false) OR ($att_published == 0))
			{
				JError::raiseError(500, $model->getError());
			}
			else
			{
				if ($value == 1)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_PUBLISHED';
				}
				elseif ($value == 0)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_UNPUBLISHED';
				}
				elseif ($value == 2)
				{
					$ntext = $this->text_prefix . '_N_ITEMS_ARCHIVED';
				}
				else
				{
					$ntext = $this->text_prefix . '_N_ITEMS_TRASHED';
				}
				$this->setMessage(JText::plural($ntext,  $att_published));
			}
		}
		$extension = JRequest::getCmd('extension');
		$extensionURL = ($extension) ? '&extension=' . JRequest::getCmd('extension') : '';
		$this->setRedirect(JRoute::_('index.php?option=' . $this->option . '&view=' . $this->view_list . $extensionURL, false));
	}

}
