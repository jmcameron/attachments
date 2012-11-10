<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * View for the uploads
 *
 * @package Attachments
 */
class AttachmentsViewUpdate extends JViewLegacy
{
	/**
	 * Display the view
	 */
	public function display($tpl=null)
	{
		// Access check.
		if ( !(JFactory::getUser()->authorise('core.edit', 'com_attachments') OR
			   JFactory::getUser()->authorise('core.edit.own', 'com_attachments')) ) {
			return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 61)');
			}

		parent::display($tpl);
	}


}
