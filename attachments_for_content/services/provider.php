<?php

/**
 * Attachments plugins for content
 *
 * @package Attachments
 * @subpackage Attachments_Plugin_For_Content
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use JMCameron\Plugin\Attachments\AttachmentsForContent\Extension\AttachmentsForContent;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

return new class () implements ServiceProviderInterface {
    /**
     * Registers the service provider with a DI container.
     *
     * @param   Container  $container  The DI container.
     *
     * @return  void
     *
     * @since   4.3.0
     */
    public function register(Container $container)
    {
        if (!PluginHelper::isEnabled('attachments', 'attachments_for_content')) {
            return;
        }
        // Only register the plugin if com_attachments is installed and enabled
        if (!class_exists("JMCameron\\Component\\Attachments\\Administrator\\Helper\\AttachmentsPermissions") || 
            !class_exists("JMCameron\\Plugin\\AttachmentsPluginFramework\\AttachmentsPluginManager") || 
            !class_exists("JMCameron\\Plugin\\AttachmentsPluginFramework\\PlgAttachmentsFramework") || 
            !ComponentHelper::isEnabled('com_attachments') ||
            !PluginHelper::isEnabled('attachments', 'framework')) {

            // Show an error message if the plugin is not available
            $lang = Factory::getApplication()->getLanguage();
            $lang->load('plg_attachments_attachments_for_content', JPATH_PLUGINS . '/attachments/attachments_for_content');
            Factory::getApplication()->enqueueMessage(
                Text::_('ATTACH_ATTACHMENTS_FOR_CONTENT_COM_ATTACHMENTS_COMPONENT_NOT_AVAILABLE'),
                'error'
            );

            return;
        }

        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = new AttachmentsForContent(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('attachments', 'attachments_for_content')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
