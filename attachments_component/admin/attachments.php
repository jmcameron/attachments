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

defined('_JEXEC') or die('Restricted access');

// Access check
if (!JFactory::getUser()->authorise('core.manage', 'com_attachments')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 28)');
}

// Import the controller library
jimport('joomla.application.component.controller');

// Execute the requested task
$controller = JController::getInstance('Attachments');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
