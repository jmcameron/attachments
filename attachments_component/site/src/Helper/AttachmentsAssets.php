<?php

/**
 * Attachments web assets
 *
 * @package Attachments
 */

namespace JMCameron\Component\Attachments\Site\Helper;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Registers and enables Attachments stylesheets.
 */
final class AttachmentsAssets
{
    public static function useStyle(string $name): void
    {
        $assetName = 'com_attachments.' . $name;
        $webAssetManager = Factory::getApplication()->getDocument()->getWebAssetManager();

        if (!$webAssetManager->assetExists('style', $assetName)) {
            $webAssetManager->registerStyle($assetName, 'com_attachments/css/' . $name . '.css');
        }

        $webAssetManager->useStyle($assetName);
    }
}
