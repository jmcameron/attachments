<?php
/**
 * Attachments component Legacy classes compatibility
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2011-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

if (!class_exists('JControllerLegacy', false))
{
	if (version_compare(JVERSION, '3.0', 'ge'))
	{
		// Joomla 3.0
		jimport('legacy.controller.legacy');
	}
	else if (version_compare(JVERSION, '2.5', 'ge'))
	{
		// Joomla 2.5
		if (!class_exists('JController', false))
		{
			jimport('joomla.application.component.controller');
		}
		class JControllerLegacy extends JController
		{
		}
	}
}


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


if (!class_exists('JModelLegacy', false))
{
	if (version_compare(JVERSION, '3.0', 'ge'))
	{
		// Joomla 3.0
		jimport('legacy.model.legacy');
	}
	else if (version_compare(JVERSION, '2.5', 'ge'))
	{
		// Joomla 2.5
		if (!class_exists('JModel', false))
		{
			jimport('joomla.application.component.model');
		}

		class JModelLegacy extends JModel
		{
		}
	}
}


if (!class_exists('JViewLegacy', false))
{
	if (version_compare(JVERSION, '3.0', 'ge'))
	{
		// Joomla 3.0
		jimport('legacy.view.legacy');
	}
	else if (version_compare(JVERSION, '2.5', 'ge'))
	{
		// Joomla 2.5
		if (!class_exists('JView', false))
		{
			jimport('joomla.application.component.view');
		}

		class JViewLegacy extends JView
		{
		}
	}
}
