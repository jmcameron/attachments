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
 
// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );
 
// import Joomla view library
jimport('joomla.application.component.view');

 
/**
 * HTML View class for adding new attachments
 *
 * @package Attachments
 */
class AttachmentsViewEntity extends JView
{
	/**
	 * Display the entity view
	 */
	public function display($tpl = null)
	{
		parent::display($tpl);
	}
}
