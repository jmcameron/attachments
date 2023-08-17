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

// no direct access

defined( '_JEXEC' ) or die('Restricted access');

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');

 
/**
 * HTML View class for adding new attachments
 *
 * @package Attachments
 */
class AttachmentsViewEntity extends HtmlView
{
	/**
	 * Display the entity view
	 */
	public function display($tpl = null)
	{
		// Add the style sheets
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin.css', Array(), true);
		$lang = Factory::getApplication()->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin_rtl.css', Array(), true);
			}

		parent::display($tpl);
	}
}
