<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\View\Entity;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * HTML View class for adding new attachments
 *
 * @package Attachments
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Display the entity view
     */
    public function display($tpl = null)
    {
        // Add the style sheets
        HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin.css');
        $lang = Factory::getApplication()->getLanguage();
        if ($lang->isRTL()) {
            HTMLHelper::stylesheet('media/com_attachments/css/attachments_admin_rtl.css');
        }

        parent::display($tpl);
    }
}
