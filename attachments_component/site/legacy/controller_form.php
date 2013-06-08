<?php
/**
 * Attachments component Legacy controllerForm class compatibility
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2011-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');


if (!class_exists('JControllerFormLegacy', false))
{
	if (version_compare(JVERSION, '3.0', 'ge'))
	{
		// Joomla 3.0
		jimport('legacy.controller.form');
		class JControllerFormLegacy extends JControllerForm
		{
		}
	}
	else if (version_compare(JVERSION, '2.5', 'ge'))
	{
		// Joomla 2.5
		if (!class_exists('JControllerForm', false))
		{
			jimport('joomla.application.component.controllerform');
		}
		class JControllerFormLegacy extends JControllerForm
		{
		}
	}
}
