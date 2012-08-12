<?php
/**
 * Attachments component attachments controller
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
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
			$errmsg = JText::_('ATTACH_ERROR_UNABLE_TO_FIND_MODEL') . ' (ERR 84)';
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
			$errmsg = JText::_('ATTACH_ERROR_UNABLE_TO_FIND_VIEW') . ' (ERR 85)';
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
		// If this persion may delete any attachment, let them!
		if (!JFactory::getUser()->authorise('core.delete', 'com_attachments')) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
			}

		// Check for request forgeries
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get attachments to remove from the request
		$cid = JRequest::getVar('cid', array(), '', 'array');

		if (count($cid)) {

			$cids = implode(',', $cid);

			// Get all the attachments to be deleted
			$db = JFactory::getDBO();
			$query = $db->getQuery(true);
			$query->select('*')->from('#__attachments')->where("id IN ( $cids )");
			$db->setQuery($query);
			$attachments = $db->loadObjectList();
			if ( $db->getErrorNum() ) {
				$errmsg = $db->stderr() . ' (ERR 86)';
				JError::raiseError(500, $errmsg);
				}

			// First delete the actual attachment files
			jimport('joomla.filesystem.file');
			require_once(JPATH_COMPONENT_SITE.'/helper.php');
			foreach ($attachments as $attachment) {
				if ( JFile::exists($attachment->filename_sys) ) {
					JFile::delete($attachment->filename_sys);
					AttachmentsHelper::clean_directory($attachment->filename_sys);
					}
				}

			// Delete the entries in the attachments table
			$query = $db->getQuery(true);
			$query->delete('#__attachments')->where("id IN ( $cids )");
			$db->setQuery($query);
			if (!$db->query()) {
				$errmsg = $db->getErrorMsg() . ' (ERR 87)';
				JError::raiseError(500, $errmsg);
				}

			// Figure out how to redirect
			$from = JRequest::getWord('from');
			$known_froms = array('frontpage', 'article', 'editor', 'closeme');
			if ( in_array( $from, $known_froms ) ) {

				// Get the parent info from the first attachment
				$parent_id	   = $attachments[0]->parent_id;
				$parent_type   = $attachments[0]->parent_type;
				$parent_entity = $attachments[0]->parent_entity;

				// Get the article/parent handler
				JPluginHelper::importPlugin('attachments');
				$apm = getAttachmentsPluginManager();
				if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
					$errmsg = JText::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 88)';
					JError::raiseError(500, $errmsg);
					}
				$parent = $apm->getAttachmentsPlugin($parent_type);
				$parent_entity = $parent->getCanonicalEntityId($parent_entity);

				// Make sure the parent exists
				// NOTE: $parent_id===null means the parent is being created
				if ( ($parent_id !== null) && !$parent->parentExists($parent_id, $parent_entity) ) {
					$parent_entity_name = JText::_('ATTACH_' . $parent_entity);
					$errmsg = JText::sprintf('ATTACH_ERROR_CANNOT_DELETE_INVALID_S_ID_N',
											 $parent_entity_name, $parent_id) . ' (ERR 89)';
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
				$uri = JFactory::getURI();
				$base_url = $uri->base(true);
				echo "<script type=\"text/javascript\">
				window.parent.refreshAttachments(\"$base_url\",\"$parent_type\",\"$parent_entity\",$pid,\"$from\");
				window.parent.SqueezeBox.close();
				</script>";
				exit();
				}
			}

		$this->setRedirect( 'index.php?option=' . $this->option);
	}



}
