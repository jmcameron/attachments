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

// Import the controller library
jimport('joomla.application.component.controller');

// Execute the requested task
$controller = JControllerLegacy::getInstance('Attachments');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
