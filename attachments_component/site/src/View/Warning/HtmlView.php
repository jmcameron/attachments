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

namespace JMCameron\Component\Attachments\Site\View\Warning;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/**
 * View for warnings
 *
 * @package Attachments
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		// Add the stylesheets
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_frontend_form.css');
		$lang = Factory::getApplication()->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('media/com_attachments/css/attachments_frontend_form_rtl.css');
			}

		parent::display($tpl);
	}
}
