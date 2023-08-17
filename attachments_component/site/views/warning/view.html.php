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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * View for warnings
 *
 * @package Attachments
 */
class AttachmentsViewWarning extends HtmlView
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Add the stylesheets
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_frontend_form.css', array(), true);
		$lang = Factory::getApplication()->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('media/com_attachments/css/attachments_frontend_form_rtl.css', array(), true);
			}

		parent::display($tpl);
	}
}
