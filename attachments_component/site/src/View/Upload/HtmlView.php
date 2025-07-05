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

namespace JMCameron\Component\Attachments\Site\View\Upload;

use JMCameron\Component\Attachments\Administrator\Field\AccessLevelsField;
use JMCameron\Component\Attachments\Site\View\AttachmentsFormView;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * View for the uploads
 *
 * @package Attachments
 */
class HtmlView extends AttachmentsFormView
{
    /**
     * Display the view
     */
    public function display($tpl = null)
    {
        // Access check.
        $app = Factory::getApplication();
        $user = $app->getIdentity();
        if ($user === null or !$user->authorise('core.create', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 64)', 404);
            return;
        }

        // For convenience below
        $attachment = $this->attachment;
        $parent = $this->parent;

        // Set up for editing the access level
        if ($this->params->get('allow_frontend_access_editing', false)) {
            $this->access_level = AccessLevelsField::getAccessLevels('access', 'access', null);
            $this->access_level_tooltip = Text::_('ATTACH_ACCESS_LEVEL_TOOLTIP');
        }

        // Set up publishing info
        $this->may_publish = $parent->userMayChangeAttachmentState(
            $attachment->parent_id,
            $attachment->parent_entity,
            $user->id
        );
        if ($this->may_publish) {
            $this->publish = HTMLHelper::_('select.booleanlist', 'state', 'class="inputbox"', $attachment->state);
        }

        // Construct derived data
        $attachment->parent_entity_name = Text::_('ATTACH_' . $attachment->parent_entity);
        $attachment->parent_title = $parent->getTitle($attachment->parent_id, $attachment->parent_entity);

        $this->relative_url_checked = $attachment->url_relative ? 'checked="yes"' : '';
        $this->verify_url_checked = $attachment->url_verify ? 'checked="yes"' : '';

        // Add the stylesheets for the form
        HTMLHelper::stylesheet('media/com_attachments/css/attachments_frontend_form.css');
        $lang = $app->getLanguage();
        if ($lang->isRTL()) {
            HTMLHelper::stylesheet('media/com_attachments/css/attachments_frontend_form_rtl.css');
        }

        // Display the upload form
        parent::display($tpl);
    }
}
