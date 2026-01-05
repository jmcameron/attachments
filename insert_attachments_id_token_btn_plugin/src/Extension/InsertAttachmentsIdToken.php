<?php

/**
 * Insert Attachments Id Token Button plugin
 *
 * @package Attachments
 * @subpackage Insert_Attachment_Id_Token_Button_Plugin
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Plugin\EditorsXtd\InsertAttachmentsIdToken\Extension;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Editor\Button\Button;
use Joomla\CMS\Event\Editor\EditorButtonsSetupEvent;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Plugin\CMSPlugin;
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
class InsertAttachmentsIdToken extends CMSPlugin implements SubscriberInterface
{
    /**
     * $db and $app are loaded on instantiation
     */
    protected ?DatabaseDriver $db = null;
    protected ?CMSApplication $app = null;
    protected $parent_type = null;
    protected $row = null;
    protected $editor = null;
    protected $id = null;

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
        $app = Factory::getApplication();
        $jinput = $app->getInput();
        $this->editor = $jinput->getString('editor', null);
        $this->id = $jinput->getInt('id', null);
        $button = $this->onDisplay($event->getEditorId(), $jinput->getInt('id', null));

        if ($button && $this->_name) {
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
    public function onDisplay($name, $id)
    {
        // Get the component parameters
        $params = ComponentHelper::getParams('com_attachments');

        // This button should only be displayed in 'custom placement' mode.
        // Check to make sure that is the case
        $placement = $params->get('attachments_placement', 'end');
        if ($placement != 'custom') {
            return false;
        }

        // Get ready for language things
        $lang = $this->app->getLanguage();
        if (!$lang->load('plg_editors-xtd_insert_attachments_id_token', dirname(__FILE__))) {
            // If the desired translation is not available, at least load the English
            $lang->load('plg_editors-xtd_insert_attachments_id_token', JPATH_ADMINISTRATOR, 'en-GB');
        }

        // Load the language file from the frontend
        $lang->load('com_attachments', JPATH_ADMINISTRATOR . '/components/com_attachments');

        $link = "/index.php?option=com_attachments&tmpl=component&parent_id=" . $id;
        $link .= '&amp;editor=' . $name;
        $button = new CMSObject();

        // Finalize the [Add Attachment] button info
        $button->modal = true;
        $button->class = 'btn';
        $button->text = Text::_('INSERT_ATTACHMENTS_ID_TOKEN');
        $button->name = 'paperclipid';
        $button->link = $link;
        $button->icon = 'attachment';
        // phpcs:disable
        $button->iconSVG = '<svg xmlns="https://www.w3.org/2000/svg" height="2em" viewBox="0 0 448 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. --><path d="M364.2 83.8c-24.4-24.4-64-24.4-88.4 0l-184 184c-42.1 42.1-42.1 110.3 0 152.4s110.3 42.1 152.4 0l152-152c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-152 152c-64 64-167.6 64-231.6 0s-64-167.6 0-231.6l184-184c46.3-46.3 121.3-46.3 167.6 0s46.3 121.3 0 167.6l-176 176c-28.6 28.6-75 28.6-103.6 0s-28.6-75 0-103.6l144-144c10.9-10.9 28.7-10.9 39.6 0s10.9 28.7 0 39.6l-144 144c-6.7 6.7-6.7 17.7 0 24.4s17.7 6.7 24.4 0l176-176c24.4-24.4 24.4-64 0-88.4z"/></svg>';
        // phpcs:enable
        $button->options = "{handler: 'iframe', size: {x: 920, y: 530}}";

        return $button;
    }
}
