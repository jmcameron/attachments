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

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * HTML View class for asking the user to log in
 *
 * @package Attachments
 */
class AttachmentsViewLogin extends HtmlView
{
	/**
	 * Display the login view
	 */
	public function display($tpl = null)
	{
		// Add the stylesheets
		HTMLHelper::stylesheet('com_attachments/attachments_frontend_form.css', array(), true);
		$app = Factory::getApplication();
		$lang = $app->getLanguage();
		if ( $lang->isRTL() ) {
			HTMLHelper::stylesheet('com_attachments/attachments_frontend_form_rtl.css', array(), true);
			}

		// Is the user already logged in?
		$user = $app->getIdentity();
		$this->logged_in = $user->get('username') <> '';

		// Get the component parameters for the registration and login URL
		$params = ComponentHelper::getParams('com_attachments');

		$base_url = Uri::base(false);
		$register_url = $params->get('register_url', 'index.php?option=com_users&view=registration');
		$register_url = Route::_($base_url . $register_url);
		$this->register_url = $register_url;

		// Construct the login URL
		$return = '';
		if ( $this->return_url ) {
			$return = '&return=' . $this->return_url;
			}
		$base_url = Uri::base(false);
		$login_url = $params->get('login_url', 'index.php?option=com_users&view=login') . $return;
		$this->login_url = Route::_($base_url . $login_url);
		
		// Get the warning message
		$this->must_be_logged_in = Text::_('ATTACH_WARNING_MUST_LOGIN_TO_DOWNLOAD_ATTACHMENT');

		// Get a phrase from the login module to create the account
		$lang->load('com_users');
		$register = Text::_('COM_USERS_REGISTER_DEFAULT_LABEL');
		$this->register_label = $register;

		$login = Text::_('JLOGIN');
		$this->login_label = $login;

		parent::display($tpl);
	}
}
