<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

/** Load the default controller */
require_once( JPATH_COMPONENT.DS.'controller.php' );

// Check for requests for named controller
$controller = JRequest::getWord('controller', False);
if ( $controller ) {
	// Invoke the named controller, if it exists
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	$controller = JString::ucfirst($controller);
	jimport('joomla.filesystem.file');
	if ( JFile::exists($path) ) {
		require_once( $path );
		$classname	= 'AttachmentsController' . $controller;
		}
	else {
	$errmsg = JText::_('UNKNOWN_CONTROLLER') . ' (ERR 79)';
	JError::raiseError(500, $errmsg);
		}
	}
else {
	$classname	= 'AttachmentsController';
	}

// Invoke the requested function of the controller
$controller = new $classname( array('default_task' => 'noop') );
$controller->execute( JRequest::getCmd('task') );
$controller->redirect();
