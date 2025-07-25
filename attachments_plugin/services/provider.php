<?php

/**
 * Attachments quickicon plugin
 *
 * @package Attachments
 * @subpackage Attachments_Quickicon_Plugin
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use JMCameron\Plugin\Content\Attachments\Extension\Attachments;
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
        // Only register the plugin if com_attachments and attachments_framework are installed and enabled
        if (!class_exists("JMCameron\\Component\\Attachments\\Site\\Helper\\AttachmentsHelper") || 
            !class_exists("JMCameron\\Component\\Attachments\\Site\\Helper\\AttachmentsJavascript") || 
            !class_exists("JMCameron\\Plugin\\AttachmentsPluginFramework\\AttachmentsPluginManager") ||
            !ComponentHelper::isEnabled('com_attachments') ||
            !PluginHelper::isEnabled('attachments', 'framework')) {

            // Show an error message if the plugin is not available
            $lang = Factory::getApplication()->getLanguage();
            $lang->load('plg_content_attachments', JPATH_PLUGINS . '/content/attachments');
            Factory::getApplication()->enqueueMessage(
                Text::_('ATTACH_ATTACHMENTS_PLUGIN_COM_ATTACHMENTS_COMPONENT_NOT_AVAILABLE'),
                'error'
            );

            return;
        }

        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = new Attachments(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('content', 'attachments')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
