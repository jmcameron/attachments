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
	if (!class_exists('JController', false))
	{
		jimport('joomla.application.component.controller');
	}

	class JControllerLegacy extends JController
	{
	}
}


if (!class_exists('JModelLegacy', false))
{
	if (!class_exists('JModel', false))
	{
		jimport('joomla.application.component.model');
	}

	class JModelLegacy extends JModel
	{
	}
}


if (!class_exists('JViewLegacy', false))
{
	if (!class_exists('JView', false))
	{
		jimport('joomla.application.component.view');
	}

	class JViewLegacy extends JView
	{
	}
}
