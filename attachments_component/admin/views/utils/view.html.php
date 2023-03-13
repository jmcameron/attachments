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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * View for the utils controller
 *
 * @package Attachments
 */
class AttachmentsViewUtils extends HtmlView
{
	/**
	 * Display the view
	 *
	 * @param	string	$tpl  A template file to load. [optional]
	 *
	 */
	public function display($tpl = null)
	{
		// Access check.
		$app = Factory::getApplication();
		$user = $app->getIdentity();
		if ($user === null OR !$user->authorise('core.admin', 'com_attachments'))
		{
			throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 171)', 404);
			return;
		}

		parent::display($tpl);
	}
}
