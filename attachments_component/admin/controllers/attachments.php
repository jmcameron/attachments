<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
 
// import Joomla controlleradmin library
jimport('joomla.application.component.controlleradmin');
 
/**
 * Attachments Controller
 */
class AttachmentsControllerAttachments extends JControllerAdmin
{

	/**
	 * Proxy for getModel.
	 * @since       1.6
	 */
	public function getModel($name = 'Attachments', $prefix = 'AttachmentsModel') 
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
	public function display($parent_id, $parent_type, $parent_entity,
							$title=null, $show_file_links=true, $allow_edit=true,
							$echo=true, $from=null)
	{
		$document =& JFactory::getDocument();

		// Get an instance of the model
		$this->addModelPath(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'models');
		$model =& $this->getModel('Attachments');
		if ( !$model ) {
			$errmsg = JText::_('ERROR_UNABLE_TO_FIND_MODEL') . ' (ERR 108)';
			JError::raiseError(500, $errmsg);
			}

		$model->setParentId($parent_id, $parent_type, $parent_entity);

		// Get the component parameters
		jimport('joomla.application.component.helper');
		$params =& JComponentHelper::getParams('com_attachments');

		// Set up to list the attachments for this artticle
		$sort_order = $params->get('sort_order', 'filename');
		$model->setSortOrder($sort_order);

		// If none of the attachments should be visible, exit now
		if ( ! $model->someVisible() ) {
			return false;
			}

		// Get the view
		$this->addViewPath(JPATH_SITE.DS.'components'.DS.'com_attachments'.DS.'views');
		$viewType = $document->getType();
		$view =& $this->getView('Attachments', $viewType);
		if ( !$view ) {
			$errmsg = JText::_('ERROR_UNABLE_TO_FIND_VIEW') . ' (ERR 109)';
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
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get attachments to remove from the request
		$cid = JRequest::getVar('cid', array(), '', 'array');

		if (count($cid)) {
			jimport('joomla.filesystem.file');

			$cids = implode(',', $cid);

			$db =& JFactory::getDBO();
			$query = "SELECT * FROM #__attachments WHERE id IN ( $cids )";
			$db->setQuery($query);
			$rows = $db->loadObjectList();

			require_once(JPATH_COMPONENT_SITE.DS.'helper.php');

			// First delete the actual attachment files
			foreach ($rows as $row) {
				if ( JFile::exists($row->filename_sys) ) {
					JFile::delete($row->filename_sys);
					AttachmentsHelper::clean_directory($row->filename_sys);
					}
				}

			// ?? ADD CHECK HERE THAT USER HAS PERMISSIONS TO DELETE...

			// Delete the entries in the attachments table
			$query = "DELETE FROM #__attachments WHERE id IN ( $cids )";
			$db->setQuery($query);
			if (!$db->query()) {
				$errmsg = $db->getErrorMsg() . ' (ERR 28)';
				JError::raiseError(500, $errmsg);
				}

			// Figure out how to redirect
			$from = JRequest::getWord('from');
			$known_froms = array('frontpage', 'article', 'editor', 'closeme');
			if ( in_array( $from, $known_froms ) ) {

				// Get the parent info from the first attachment
				$parent_id	   = $rows[0]->parent_id;
				$parent_type   = $rows[0]->parent_type;
				$parent_entity = $rows[0]->parent_entity;

				// Get the article/parent handler
				JPluginHelper::importPlugin('attachments');
				$apm =& getAttachmentsPluginManager();
				if ( !$apm->attachmentsPluginInstalled($parent_type) ) {
					$errmsg = JText::sprintf('ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 103)';
					JError::raiseError(500, $errmsg);
					}
				$parent =& $apm->getAttachmentsPlugin($parent_type);

				// Make sure the parent exists
				// NOTE: $parent_id===null means the parent is being created
				if ( $parent_id !== null AND !$parent->parentExists($parent_id, $parent_entity) ) {
					$entity_name = JText::_($parent->getEntityName($parent_entity));
					$errmsg = JText::sprintf('ERROR_CANNOT_DELETE_INVALID_S_ID_N',
											 $entity_name, $parent_id) . ' (ERR 104)';
					JError::raiseError(500, $errmsg);
					}
				$parent_entity = $parent->getCanonicalEntity($parent_entity);
				// ??? FIX THIS
				if ( ($parent_type == 'com_content') AND ($parent_entity = 'default') ) {
					$parent_entity = 'article';
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
                   var fn = window.parent.refreshAttachments;
			       window.parent.SqueezeBox.close();
			       fn(\"$base_url\",\"$parent_type\",\"$parent_entity\",$pid,\"$from\");
				   </script>";
				exit();
				}
			}

		$this->setRedirect( 'index.php?option=' . $this->option);
	}

	

}
