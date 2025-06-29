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

namespace JMCameron\Component\Attachments\Administrator\View\Utils;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * View for the utils controller
 *
 * @package Attachments
 */
class HtmlView extends BaseHtmlView
{
    /**
     * Display the view
     *
     * @param   string  $tpl  A template file to load. [optional]
     *
     */
    public function display($tpl = null)
    {
        // Access check.
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        if ($user === null or !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 171)', 404);
            return;
        }

        parent::display($tpl);
    }
}
