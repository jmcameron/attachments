<?php

/**
 * Add Attachments Button plugin
 *
 * @package Attachments
 * @subpackage Add_Attachment_Button_Plugin
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Plugin\EditorsXtd\AddAttachment\Extension;

use JMCameron\Component\Attachments\Site\Helper\AttachmentsJavascript;
use JMCameron\Plugin\AttachmentsPluginFramework\AttachmentsPluginManager;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseDriver;
use Joomla\Event\SubscriberInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Button that allows you to add attachments from the editor
 *
 * @package Attachments
 */
class AddAttachment extends CMSPlugin implements SubscriberInterface
{
    /**
     * $db and $app are loaded on instantiation
     */
    protected ?DatabaseDriver $db = null;
    protected ?CMSApplication $app = null;

    /**
     * Load the language file on instantiation
     *
     * @var    boolean
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     */
    public static function getSubscribedEvents(): array
    {
        return ['onEditorButtonsSetup' => 'onEditorButtonsSetup'];
    }

    public function onEditorButtonsSetup(EditorButtonsSetupEvent $event)
    {
        $subject  = $event->getButtonsRegistry();
        $disabled = $event->getDisabledButtons();

        if (\in_array($this->_name, $disabled)) {
            return;
        }

        $this->loadLanguage();

        $button = $this->onDisplay($event->getEditorId());

        if ($button) {
            $subject->add(new Button($this->_name, $button->getProperties()));
        }
    }

    /**
     * Add Attachment button
     *
     * @param string $name The name of the editor form
     * @param int $asset The asset ID for the entity being edited
     * @param int $author The ID of the author of the entity
     *
     * @return CMSObject button
     */
    public function onDisplay($name)
    {
        $input = $this->app->getInput();

        // Avoid displaying the button for anything except for registered parents
        $parent_type = $input->getCmd('option');
        if (!$parent_type) {
            return;
        }
        $parent_entity = 'default';
        $editor = 'article';

        // Handle categories specially (since they are really com_content)
        if ($parent_type == 'com_categories') {
            $parent_type = 'com_content';
            $parent_entity = 'category';
            $editor = 'category';
        }

        // Get the parent ID (id or first of cid array)
        //     NOTE: $parent_id=0 means no id (usually means creating a new entity)
        $cid = $input->get('cid', array(0), 'array');
        $parent_id = 0;
        if (count($cid) > 0) {
            $parent_id = (int)$cid[0];
        }
        if ($parent_id == 0) {
            $a_id = $input->getInt('a_id');
            if (!is_null($a_id)) {
                $parent_id = (int)$a_id;
            }
        }
        if ($parent_id == 0) {
            $nid = $input->getInt('id');
            if (!is_null($nid)) {
                $parent_id = (int)$nid;
            }
        }

        // Check for the special case where we are creating an article from a category list
        $item_id = $input->getInt('Itemid');
        $menu = $this->app->getMenu();
        $menu_item = $menu->getItem($item_id);
        if ($menu_item and ($menu_item->query['view'] == 'category') and empty($a_id)) {
            $parent_entity = 'article';
            $parent_id = null;
        }

        // Get the article/parent handler
        PluginHelper::importPlugin('attachments');
        $apm = AttachmentsPluginManager::getAttachmentsPluginManager();
        if (!$apm->attachmentsPluginInstalled($parent_type)) {
            // Exit if there is no Attachments plugin to handle this parent_type
            return;
        }
        // Figure out where we are and construct the right link and set
        $base_url = Uri::root(true);
        if ($this->app->isClient('administrator')) {
            $base_url = str_replace('/administrator', '', $base_url);
        }

        // Set up the Javascript framework
        AttachmentsJavascript::setupJavascript();

        // Get the parent handler
        $parent = $apm->getAttachmentsPlugin($parent_type);
        $parent_entity = $parent->getCanonicalEntityId($parent_entity);

        if ($parent_id == 0) {
            # Last chance to get the id in extension editors
            $view = $input->getWord('view');
            $layout = $input->getWord('layout');
            $parent_id = $parent->getParentIdInEditor($parent_entity, $view, $layout);
        }

        // Make sure we have permissions to add attachments to this article or category
        if (!$parent->userMayAddAttachment($parent_id, $parent_entity, $parent_id == 0)) {
            return;
        }

        // Add the regular css file
        HTMLHelper::stylesheet('media/com_attachments/css/attachments_list.css');
        HTMLHelper::stylesheet('media/com_attachments/css/attachments_list_dark.css');
        HTMLHelper::stylesheet('media/com_attachments/css/add_attachment_button.css');

        // Handle RTL styling (if necessary)
        $lang = $this->app->getLanguage();
        if ($lang->isRTL()) {
            HTMLHelper::stylesheet('media/com_attachments/css/attachments_list_rtl.css');
            HTMLHelper::stylesheet('media/com_attachments/css/add_attachment_button_rtl.css');
        }

        // Load the language file from the frontend
        $lang->load('com_attachments', JPATH_ADMINISTRATOR . '/components/com_attachments');

        $link = $parent->getEntityAddUrl($parent_id, $parent_entity, 'closeme');
        $link .= '&amp;editor=' . $editor;

        $button = new CMSObject();

        // Finalize the [Add Attachment] button info
        $button->modal = true;
        $button->class = 'btn';
        $button->text = Text::_('ATTACH_ADD_ATTACHMENT');
        $button->name = 'paperclip';
        $button->link = $link;
        $button->icon = 'attachment';
        // phpcs:disable
        $button->iconSVG = '<svg xmlns="http://www.w3.org/2000/svg" height="2em" viewBox="0 0 448 512">
        <!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
        <path d="M364.2 83.8c-24.4-24.4-64-24.4-88.4 0l-184 184c-42.1 42.1-42.1 110.3 0 152.4s110.3 42.1 152.4 0l152-152c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-152 152c-64 64-167.6 64-231.6 0s-64-167.6 0-231.6l184-184c46.3-46.3 121.3-46.3 167.6 0s46.3 121.3 0 167.6l-176 176c-28.6 28.6-75 28.6-103.6 0s-28.6-75 0-103.6l144-144c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-144 144c-6.7 6.7-6.7 17.7 0 24.4s17.7 6.7 24.4 0l176-176c24.4-24.4 24.4-64 0-88.4z"/>
        </svg>';
        // phpcs:enable
        $button->options = "{handler: 'iframe', size: {x: 920, y: 530}}";

        return $button;
    }
}
