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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * View for the utils controller
 *
 * @package Attachments
 */
class AttachmentsViewUtils extends JViewLegacy
{
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 */
	public function display($tpl = null)
	{
		// Access check.
		if (!JFactory::getUser()->authorise('core.admin', 'com_attachments'))
		{
			return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 171)');
		}

		parent::display($tpl);
	}
}
