<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


return new class implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(
            new MVCFactory("\\JMCameron\\Component\\Attachments")
        );
        $container->registerServiceProvider(
            new ComponentDispatcherFactory(
                "\\JMCameron\\Component\\Attachments"
            )
        );
        $container->set(ComponentInterface::class, function (
            Container $container
        ) {
            $component = new MVCComponent(
                $container->get(ComponentDispatcherFactoryInterface::class)
            );
            $component->setMVCFactory(
                $container->get(MVCFactoryInterface::class)
            );

            return $component;
        });
    }
};
