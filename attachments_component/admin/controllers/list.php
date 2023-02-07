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

defined('_JEXEC') or die('Restricted access');

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/controller.php');


/**
 * Class for a controller for dealing with lists of attachments
 *
 * @package Attachments
 */
class AttachmentsControllerList extends JControllerLegacy
{
	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 *
	 * @return	JControllerForm
	 */
	public function __construct( $default = array('default_task' => 'noop') )
	{
		parent::__construct( $default );
	}


	/**
	 * A noop function so this controller does not have a usable default
	 */
	public function noop()
	{
		$errmsg = JText::_('ATTACH_ERROR_NO_FUNCTION_SPECIFIED') . ' (ERR 119)';
		JError::raiseError(500, $errmsg);
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
		require_once(JPATH_SITE.'/components/com_attachments/models/attachments.php');
		$model = new AttachmentsModelAttachments();
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
			$errmsg = JText::_('ATTACH_ERROR_UNABLE_TO_FIND_VIEW') . ' (ERR 120)';
			JError::raiseError(500, $errmsg);
			}
		$view->setModel($model);

		// Construct the update URL template
		$update_url = "index.php?option=com_attachments&task=attachment.edit&cid[]=%d";
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


}
