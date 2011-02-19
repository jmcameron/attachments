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

/** Setup for the toolbar */
require_once( JApplicationHelper::getPath( 'toolbar_html' ) );

/** Dispatch each toolbar function */

switch($task)
{
	case 'add':
		TOOLBAR_attachments::_EDIT(false);
		break;

	case 'edit':
		TOOLBAR_attachments::_EDIT(true);
		break;

	case 'editParams':
		TOOLBAR_attachments::_EDIT_PARAMS();
		break;

	default:
		TOOLBAR_attachments::_DEFAULT();
		break;
}
?>