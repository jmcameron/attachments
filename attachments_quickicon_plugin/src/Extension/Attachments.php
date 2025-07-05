<?php

/**
 * Attachments quickicon plugin
 *
 * @package Attachments
 * @subpackage Attachments_Quickicon_Plugin
 *
 * @copyright Copyright (C) 2013-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Plugin\Quickicon\Attachments\Extension;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\SubscriberInterface;
use Joomla\Module\Quickicon\Administrator\Event\QuickIconsEvent;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Attachments quickicon plugin class
 *
 * @package     Attachments
 * @subpackage  Attachments.Quickicon_Plugin
 */
class Attachments extends CMSPlugin implements SubscriberInterface
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
     * @param  QuickIconsEvent $event    The event object
     *
     * @return array A list of icon definition associative arrays, consisting of the
     *               keys link, image, text and access.
     *
     * @since       2.5
     */
    public function onGetIcons(QuickIconsEvent $event)
    {
        $context = $event->getContext();
        $user = Factory::getApplication()->getIdentity();
        // See if we should show the icon
        if (
            $context != $this->params->get('context', 'mod_quickicon') ||
            $user === null ||
            !$user->authorise('core.manage', 'com_attachments')
        ) {
            return;
        }

        // Add the CSS file
        HTMLHelper::stylesheet('media/com_attachments/css/attachments_quickicon.css');

        $image = 'icon-attachment';

        $result = $event->getArgument('result', []);

        // Return the icon info for the quickicon system
        $result[] = [
            [
                'link' => 'index.php?option=com_attachments',
                'image' => $image,
                'text' => Text::_('PLG_QUICKICON_ATTACHMENTS_ICON'),
                'id' => 'plg_quickicon_attachments'
            ]
        ];

        $event->setArgument('result', $result);
    }
}
