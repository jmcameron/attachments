<?php

/**
 * Attachments component attachment model
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\Model;

use JMCameron\Plugin\AttachmentsPluginFramework\AttachmentsPluginManager;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Table\Table;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * Attachment Model
 *
 * @package Attachments
 */
class AttachmentModel extends AdminModel
{
    /**
     * Returns a reference to the a Table object, always creating it.
     *
     * @param       type    The table type to instantiate
     * @param       string  A prefix for the table class name. Optional.
     * @param       array   Configuration array for model. Optional.
     * @return      Table   A database object
     * @since       1.6
     */
    public function getTable($type = 'Attachment', $prefix = 'Administrator', $config = array())
    {
        /** @var \Joomla\CMS\MVC\Factory\MVCFactory $mvc */
        $mvc = Factory::getApplication()
                ->bootComponent("com_attachments")
                ->getMVCFactory();
        return $mvc->createTable($type, $prefix, $config);
    }


    /**
     * Override the getItem() command to get some extra info
     *
     * @param   integer $pk The id of the primary key.
     *
     * @return  mixed   Object on success, false on failure.
     */
    public function getItem($pk = null)
    {
        $item = parent::getItem($pk);

        if ($item->id != 0) {
            // If the item exists, get more info
            /** @var \Joomla\Database\DatabaseDriver $db */
            $db = Factory::getContainer()->get('DatabaseDriver');

            // Get the creator name
            $query = $db->getQuery(true);
            $query->select('name')->from('#__users')->where('id = ' . (int)$item->created_by);
            try {
                $db->setQuery($query, 0, 1);
                $item->creator_name = $db->loadResult();
            } catch (\RuntimeException $e) {
                $errmsg = $e->getMessage() . ' (ERR 112)';
                throw new \Exception($errmsg, 500);
            }

            // Get the modifier name
            $query = $db->getQuery(true);
            $query->select('name')->from('#__users')->where('id = ' . (int)$item->modified_by);
            try {
                $db->setQuery($query, 0, 1);
                $item->modifier_name = $db->loadResult();
            } catch (\RuntimeException $e) {
                $errmsg = $e->getMessage() . ' (ERR 113)';
                throw new \Exception($errmsg, 500);
            }

            // Get the parent info (??? Do we really need this?)
            $parent_type = $item->parent_type;
            $parent_entity = $item->parent_entity;
            PluginHelper::importPlugin('attachments');
            $apm = AttachmentsPluginManager::getAttachmentsPluginManager();
            if (!$apm->attachmentsPluginInstalled($parent_type)) {
                $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 114)';
                throw new \Exception($errmsg, 500);
            }
            $item->parent = $apm->getAttachmentsPlugin($parent_type);
        }

        return $item;
    }

    /**
     * Method to get the record form.
     *
     * @param       array   $data           Data for the form.
     * @param       boolean $loadData       True if the form is to load its own data (default case), false if not.
     * @return      mixed   A JForm object on success, false on failure
     * @since       1.6
     */
    public function getForm($data = array(), $loadData = true)
    {
        // Get the form.
        $form = $this->loadForm(
            'com_attachments.attachment',
            'attachment',
            array('control' => 'jform', 'load_data' => $loadData)
        );
        if (empty($form)) {
            return false;
        }
        return $form;
    }


    /**
     * Method to get the data that should be injected in the form.
     *
     * @return      mixed   The data for the form.
     * @since       1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data.
        /** @var \Joomla\CMS\Application\CMSApplication $app */
        $app = Factory::getApplication();
        $data = $app->getUserState('com_attachments.edit.attachment.data', array());
        if (empty($data)) {
            $data = $this->getItem();
        }
        return $data;
    }
}
