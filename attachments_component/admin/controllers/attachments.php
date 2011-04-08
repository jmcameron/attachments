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
	 * Delete attachment(s)
	 */
	function delete()
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
				   window.parent.SqueezeBox.close();
				   parent.refreshAttachments(\"$base_url\",\"$parent_type\",\"$parent_entity\",$pid,\"$from\");
				   </script>";
				exit();
				}
			}

		$this->setRedirect( 'index.php?option=' . $this->option);
	}


}
