<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

jimport( 'joomla.application.component.view');

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * HTML View class for asking the user to log in
 *
 * @package Attachments
 */
class AttachmentsViewLogin extends JViewLegacy
{
	/**
	 * Display the login view
	 */
	public function display($tpl = null)
	{
		// Add CSS for styling
		AttachmentsHelper::addStyleSheet( JURI::base(true) . '/plugins/content/attachments/attachments.css' );

		// Is the user already logged in?
		$user = JFactory::getUser();
		$this->logged_in = $user->get('username') <> '';

		// Get the component parameters for the registration and login URL
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_attachments');

		$register_url = $params->get('register_url', "index.php?option=com_users&view=registration");
		$register_url = JRoute::_($register_url);
		$this->register_url = $register_url;

		$login_url = $params->get('login_url', "index.php?option=com_users&view=login");
		$login_url = JRoute::_($login_url);
		$this->login_url = $login_url;
		

		// Deal with RTL styling
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			AttachmentsHelper::addStyleSheet( JURI::base() . '/plugins/content/attachments/attachments_rtl.css' );
			}

		// Get the warning message
		$this->must_be_logged_in = JText::_('ATTACH_WARNING_MUST_LOGIN_TO_DOWNLOAD_ATTACHMENT');

		// Get a phrase from the login module to create the account
		$lang->load('com_users');
		$register = JText::_('COM_USERS_REGISTER_DEFAULT_LABEL');
		$this->register_label = $register;

		$login = JText::_('JLOGIN');
		$this->login_label = $login;

		parent::display($tpl);
	}
}
