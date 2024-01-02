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

defined('_JEXEC') or die('Restricted access');

// Execute the requested task
$mvc = Factory::getApplication()
    ->bootComponent("com_attachments")
    ->getMVCFactory();
$controller = $mvc->createController('Attachments');
$controller->execute(Factory::getApplication()->getInput()->get('task'));
$controller->redirect();
