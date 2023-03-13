<?php
/**
 * Attachments component Legacy controllerForm class compatibility
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2011-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\MVC\Controller\FormController;

// No direct access.
defined('_JEXEC') or die('Restricted access');


if (!class_exists('JControllerFormLegacy', false))
{
	// Joomla 3.0
	class JControllerFormLegacy extends FormController
	{
	}
}
