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

namespace JMCameron\Component\Attachments\Administrator\View\Warning;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use JMCameron\Component\Attachments\Site\Helper\AttachmentsAssets;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * View for the special controller
 * (adapted from administrator/components/com_config/views/component/view.php)
 *
 * @package Attachments
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        // Add the style sheets
        AttachmentsAssets::useStyle('attachments_admin_form');
        $lang = Factory::getApplication()->getLanguage();
        if ($lang->isRTL()) {
            AttachmentsAssets::useStyle('attachments_admin_form_rtl');
        }

        parent::display($tpl);
    }
}
