<?php
/**
 * Attachments quickicon plugin
 *
 * @package Attachments
 * @subpackage Attachments.Quickicon_Plugin
 *
 * @copyright Copyright (C) 2013-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

/**
 * Attachments quickicon plugin class
 *
 * @package		Attachments
 * @subpackage	Attachments.Quickicon_Plugin
 */
class PlgQuickiconAttachments extends CMSPlugin implements SubscriberInterface
{
	/**
	 * $db and $app are loaded on instantiation
	 */
	protected ?DatabaseDriver $db = null;
	protected ?CMSApplication $app = null;

	/**
	 * Load the language file on instantiation
	 *
	 * @var    boolean
	 */
	protected $autoloadLanguage = true;
	
	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onGetIcons' => 'onGetIcons',
		];
	}

	/**
	 * This method is called when the Quick Icons module is constructing its set
	 * of icons. You can return an array which defines a single icon and it will
	 * be rendered right after the stock Quick Icons.
	 *
	 * @param  Event $event	 The event object
	 *
	 * @return array A list of icon definition associative arrays, consisting of the
	 *				 keys link, image, text and access.
	 *
	 * @since		2.5
	 */
	public function onGetIcons(Event $event)
	{
		[$context] = $event->getArguments();
		$user = Factory::getApplication()->getIdentity();
		// See if we should show the icon
		if ($context != $this->params->get('context', 'mod_quickicon') ||
			$user === null ||
			!$user->authorise('core.manage', 'com_attachments'))
		{
			return;
		}

		// Add the CSS file
		HTMLHelper::stylesheet('media/com_attachments/css/attachments_quickicon.css');

		$image = 'flag-2';
		$icon = Uri::root() . '/media/com_attachments/images/attachments_logo48.png';

		// Return the icon info for the quickicon system
		return
			array(
				array(
					'link' => 'index.php?option=com_attachments',
					'image' => $image,
					'icon' => $icon,
					'text' => Text::_('PLG_QUICKICON_ATTACHMENTS_ICON'),
					'id' => 'plg_quickicon_attachment'));
	}
}
