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

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\BaseController as JControllerLegacy;
use Joomla\CMS\Factory as JFactory;
/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/controller.php');


// Execute the requested task
$controller = JControllerLegacy::getInstance('Attachments');
$app = JFactory::getApplication();
$controller->execute($app->getInput()->get('task'));
$controller->redirect();
