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

/** Get the Controller class */
require_once( JPATH_COMPONENT.DS.'controller.php' ); 

JTable::addIncludePath(JPATH_COMPONENT.DS.'tables'); 

/* Add the Attachments CSS file */
$document =& JFactory::getDocument();
$document->addStyleSheet( JURI::base(true) . '/components/com_attachments/attachments.css', 
						  'text/css', null, array() );
$lang =& JFactory::getLanguage(); 
if ( $lang->isRTL() ) {
	$document->addStyleSheet( JURI::base(true) . '/components/com_attachments/attachments_rtl.css', 
							  'text/css', null, array() );
	}

// Check for requests for named controller
$controller = JRequest::getWord('controller', False);
if ( $controller ) {
	// Invoke the named controller, if it exists
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	jimport('joomla.filesystem.file');
	if ( JFile::exists($path) ) {
		require_once( $path );
		$classname	= 'AttachmentsController' . JString::ucfirst($controller);
		$controller = new $classname( );
		$controller->execute( JRequest::getCmd( 'task' ) );
		$controller->redirect();
		}
	else {
		echo "<h1>Error! Unable to find controller '$controller'!</h1><br />";
		exit();
		}
	}

// Use default controller
$controller = new AttachmentsAdminController( array('default_task' => 'listAttachments') );
$task = JRequest::getCmd( 'task' );
$controller->execute( JRequest::getCmd( 'task' ) ); 
$controller->redirect();

?>
