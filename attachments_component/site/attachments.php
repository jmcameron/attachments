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

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\String\StringHelper;

defined('_JEXEC') or die('Restricted access');

/** Load the default controller */
require_once( JPATH_COMPONENT.'/controller.php' );

// Check for requests for named controller
$app = Factory::getApplication();
$input = $app->getInput();
$controller = $input->getWord('controller', False);
if ( $controller ) {
	// Invoke the named controller, if it exists
	$path = JPATH_COMPONENT.'/controllers/'.$controller.'.php';
	$controller = StringHelper::ucfirst($controller);
	if ( File::exists($path) ) {
		require_once( $path );
		$classname	= 'AttachmentsController' . $controller;
		}
	else {
		$errmsg = Text::_('ATTACH_UNKNOWN_CONTROLLER') . ' (ERR 48)';
		throw new Exception($errmsg, 500);
		die;
		}
	}
else {
	$classname	= 'AttachmentsController';
	}

// Invoke the requested function of the controller
$controller = new $classname( array('default_task' => 'noop') );
$controller->execute( $input->getCmd('task') );
$controller->redirect();
