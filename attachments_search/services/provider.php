<?php

/**
 * Attachments search plugin
 *
 * @package Attachments
 * @subpackage Attachments_Search_Plugin
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use JMCameron\Plugin\Search\Attachments\Extension\Attachments;
use Joomla\CMS\Language\Text;

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
        if (!PluginHelper::isEnabled('search', 'attachments')) {
            return;
        }
        // Only register the plugin if com_attachments and attachments_framework are installed and enabled
        if (!class_exists("JMCameron\\Plugin\\AttachmentsPluginFramework\\AttachmentsPluginManager") || 
            !PluginHelper::isEnabled('attachments', 'framework')) {

            // Show an error message if the plugin is not available
            $lang = Factory::getApplication()->getLanguage();
            $lang->load('plg_search_attachments', JPATH_PLUGINS . '/search/attachments');
            Factory::getApplication()->enqueueMessage(
                Text::_('ATTACH_SEARCH_PLUGIN_ATTACHMENTS_FRAMEWORK_NOT_AVAILABLE'),
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
                    (array) PluginHelper::getPlugin('search', 'attachments')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
