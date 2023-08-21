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

namespace JMCameron\Component\Attachments\Administrator\View\Params;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Access check.
$app = Factory::getApplication();
$user = $app->getIdentity();
if ($user === null OR !$user->authorise('core.admin', 'com_attachments')) {
	throw new Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 174)', 404);
	}

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * View for editing the component parameters
 * (adapted from administrator/components/com_config/views/component/view.php)
 *
 * @package Attachments
 */
class HtmlView extends BaseHtmlView
{
	/**
	 * Display the params view
	 */
	public function display($tpl = null)
	{
		// Add the style sheets
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin_form.css');
		$lang = Factory::getApplication()->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin_form_rtl.css');
			}

		$this->addToolBar();

		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		$app = Factory::getApplication();
		$app->set('hidemainmenu', true);
		ToolbarHelper::title(Text::_('ATTACH_CONFIGURATION'), 'attachments.png');
		ToolbarHelper::apply('params.apply');
		ToolbarHelper::save('params.save');
		ToolbarHelper::cancel('params.cancel', 'JTOOLBAR_CLOSE');
	}

}
