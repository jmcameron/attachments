<?php

/**
 * Add Attachments Button plugin
 *
 * @package Attachments
 * @subpackage Add_Attachment_Button_Plugin
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use JMCameron\Plugin\EditorsXtd\AddAttachment\Extension\AddAttachment;
use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Component\ComponentHelper;
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
        if (!PluginHelper::isEnabled('editors-xtd', 'add_attachment')) {
            return;
        }
        // Only register the plugin if com_attachments is installed and enabled
        if (!class_exists("JMCameron\\Component\\Attachments\\Site\\Helper\\AttachmentsJavascript") || 
            !class_exists("JMCameron\\Plugin\\AttachmentsPluginFramework\\AttachmentsPluginManager") || 
            !ComponentHelper::isEnabled('com_attachments') ||
            !PluginHelper::isEnabled('attachments', 'framework')) {

            // Show an error message if the plugin is not available
            $lang = Factory::getApplication()->getLanguage();
            $lang->load('plg_editors-xtd_add_attachment', JPATH_PLUGINS . '/editors-xtd/add_attachment');
            Factory::getApplication()->enqueueMessage(
                Text::_('ATTACH_ADD_ATTACHMENT_BTN_COM_ATTACHMENTS_COMPONENT_NOT_AVAILABLE'),
                'error'
            );

            return;
        }

        $container->set(
            PluginInterface::class,
            function (Container $container) {
                $dispatcher = $container->get(DispatcherInterface::class);
                $plugin     = new AddAttachment(
                    $dispatcher,
                    (array) PluginHelper::getPlugin('editors-xtd', 'add_attachment')
                );
                $plugin->setApplication(Factory::getApplication());

                return $plugin;
            }
        );
    }
};
