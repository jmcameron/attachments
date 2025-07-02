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

namespace JMCameron\Component\Attachments\Administrator\Controller;

use JMCameron\Component\Attachments\Administrator\Helper\AttachmentsImport;
use JMCameron\Component\Attachments\Administrator\Helper\AttachmentsUpdate;
use JMCameron\Component\Attachments\Site\Helper\AttachmentsJavascript;
use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\Input\Input;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects




/**
 * The controller for utils requests
 * (adapted from administrator/components/com_config/controllers/component.php)
 *
 * @package Attachments
 */
class UtilsController extends BaseController
{
    /**
     * Constructor.
     *
     * @return  BaseController
     */
    public function __construct(
        $config = array('default_task' => 'noop'),
        MVCFactoryInterface $factory = null,
        ?CMSApplication $app = null,
        ?Input $input = null
    ) {
        $config['default_task'] = 'noop';
        parent::__construct($config, $factory, $app, $input);

        // Access check.
        $user = $this->app->getIdentity();
        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 150)', 404);
        }
    }


    /**
     * A noop function so this controller does not have a usable default
     */
    public function noop()
    {
        echo "<h1>" . Text::_('ATTACH_ERROR_NO_UTILS_FUNCTION_SPECIFIED') . "</h1>";
        exit();
    }


    /**
     *
     */


    /**
     * Enqueue a system message.
     *
     * @param   string   $msg   The message to enqueue.
     * @param   string   $type  The message type. Default is message.
     *
     * @return  void
     */
    protected function enqueueSystemMessage($msg, $type = 'message')
    {
        $this->app->enqueueMessage($msg, $type);

        // Not sure why I need the extra saving to the session below,
        // but it it seems necessary because I'm doing it from an iframe.
        $session = $this->app->getSession();
        $session->set('application.queue', $this->app->getMessageQueue());
    }


    /**
     * Add icon filenames for attachments missing an icon
     * (See AttachmentsUpdate::addIconFilenames() in update.php for details )
     */
    public function addIconFilenames()
    {
        // Access check.
        $user = $this->app->getIdentity();

        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 151)', 404);
        }

        $msg = AttachmentsUpdate::addIconFilenames();
        $this->setRedirect('index.php?option=' . $this->input->get("option"), $msg);
    }


    /**
     * Update any null dates in any attachments
     * (See AttachmentsUpdate::updateNullDates() in update.php for details )
     */
    public function updateNullDates()
    {
        // Access check.
        $user = $this->app->getIdentity();

        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 152)', 404);
        }

        $numUpdated = AttachmentsUpdate::updateNullDates();
        $msg = Text::sprintf('ATTACH_UPDATED_N_ATTACHMENTS', $numUpdated);
        $this->setRedirect('index.php?option=' . $this->input->get("option"), $msg);
    }



    /**
     * Disable SQL uninstall of existing attachments (when Attachments is uninstalled)
     * (See AttachmentsUpdate::disableSqlUninstall() in update.php for details )
     */
    public function disableSqlUninstall()
    {
        // Access check.
        $user = $this->app->getIdentity();

        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 153)', 404);
        }

        $msg = AttachmentsUpdate::disableSqlUninstall();

        $input = $this->app->getInput();
        if ($input->getBool('close')) {
            $this->enqueueSystemMessage($msg);

            // Close this window and refresh the parent window
            AttachmentsJavascript::closeModal();
        } else {
            $this->setRedirect('index.php?option=com_attachments', $msg);
        }
    }


    /**
     * Regenerate system filenames
     * (See AttachmentsUpdate::regenerateSystemFilenames() in update.php for details )
     */
    public function regenerateSystemFilenames()
    {
        // Access check.
        $user = $this->app->getIdentity();

        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 154)', 404);
        }

        $msg = AttachmentsUpdate::regenerateSystemFilenames();

        $input = $this->app->getInput();
        if ($input->getBool('close')) {
            $this->enqueueSystemMessage($msg);

            // Close this window and refresh the parent window
            AttachmentsJavascript::closeModal();
        } else {
            $this->setRedirect('index.php?option=' . $this->input->get("option"), $msg);
        }
    }


    /**
     * Remove spaces from system filenames for all attachments
     * (See AttachmentsUpdate::removeSpacesFromSystemFilenames() in update.php for details )
     */
    public function removeSpacesFromSystemFilenames()
    {
        // Access check.
        $user = $this->app->getIdentity();

        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 155)', 404);
        }

        $msg = AttachmentsUpdate::removeSpacesFromSystemFilenames();

        $input = $this->app->getInput();
        if ($input->getBool('close')) {
            $this->enqueueSystemMessage($msg);

            // Close this window and refresh the parent window
            AttachmentsJavascript::closeModal();
        } else {
            $this->setRedirect('index.php?option=' . $this->input->get("option"), $msg);
        }
    }


    /**
     * Update file sizes for all attachments
     * (See AttachmentsUpdate::updateFilezizes() in update.php for details )
     */
    public function updateFilezizes()
    {
        // Access check.
        $user = $this->app->getIdentity();

        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 156)', 404);
        }

        $msg = AttachmentsUpdate::updateFilezizes();

        $input = $this->app->getInput();
        if ($input->getBool('close')) {
            $this->enqueueSystemMessage($msg);

            // Close this window and refresh the parent window
            AttachmentsJavascript::closeModal();
        } else {
            $this->setRedirect('index.php?option=' . $this->input->get("option"), $msg);
        }
    }


    /**
     * Check all files in any attachments
     * (See AttachmentsUpdate::checkFiles() in update.php for details )
     */
    public function checkFiles()
    {
        // Access check.
        $user = $this->app->getIdentity();

        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 157)', 404);
        }

        $msg = AttachmentsUpdate::checkFilesExistence();

        $input = $this->app->getInput();
        if ($input->getBool('close')) {
            $this->enqueueSystemMessage($msg);

            // Close this window and refresh the parent window
            AttachmentsJavascript::closeModal();
        } else {
            $this->setRedirect('index.php?option=' . $this->input->get("option"), $msg);
        }
    }

    /**
     * Validate all URLS in any attachments
     * (See AttachmentsUpdate::validateUrls() in update.php for details )
     */
    public function validateUrls()
    {
        // Access check.
        $user = $this->app->getIdentity();

        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR' . ' (ERR 158)'), 404);
        }

        $msg = AttachmentsUpdate::validateUrls();

        $input = $this->app->getInput();
        if ($input->getBool('close')) {
            $this->enqueueSystemMessage($msg);

            // Close this window and refresh the parent window
            AttachmentsJavascript::closeModal();
        } else {
            $this->setRedirect('index.php?option=' . $this->input->get("option"), $msg);
        }
    }


    /**
     * Validate all URLS in any attachments
     * (See AttachmentsUpdate::reinstallPermissions() in update.php for details )
     */
    public function reinstallPermissions()
    {
        // Access check.
        $user = $this->app->getIdentity();

        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 159)', 404);
        }

        $msg = AttachmentsUpdate::installAttachmentsPermissions();

        $input = $this->app->getInput();
        if ($input->getBool('close')) {
            $this->enqueueSystemMessage($msg);

            // Close this window and refresh the parent window
            AttachmentsJavascript::closeModal();
        } else {
            $this->setRedirect('index.php?option=' . $this->input->get("option"), $msg);
        }
    }


    /**
     * Install attachments data from CSV file
     */
    public function installAttachmentsFromCsvFile()
    {
        // Access check.
        $user = $this->app->getIdentity();

        if ($user === null || !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 160)', 404);
        }

        $input = $this->app->getInput();
        $filename = $input->getString('filename', null);
        if ($filename == null) {
            $errmsg = Text::_('ATTACH_ERROR_MUST_ADD_FILENAME_TO_URL') . ' (ERR 161)';
            throw new \Exception($errmsg, 500);
        }
        $verify_parent = $input->getBool('verify_parent', true);
        $update = $input->getBool('update', false);
        $dry_run = $input->getBool('dry_run', false);

        $status = AttachmentsImport::importAttachmentsFromCSVFile(
            $filename,
            $verify_parent,
            $update,
            $dry_run
        );

        // Abort if it is an error message
        if (is_string($status)) {
            throw new \Exception($status, 500);
        }

        // Otherwise, report the results
        if (is_array($status)) {
            $msg = Text::sprintf('ATTACH_ADDED_DATA_FOR_N_ATTACHMENTS', count($status));
            $this->setRedirect('index.php?option=com_attachments', $msg);
        } else {
            if ($dry_run) {
                $msg = Text::sprintf('ATTACH_DATA_FOR_N_ATTACHMENTS_OK', $status) . ' (ERR 162)';
                throw new \Exception($msg, 500);
            } else {
                $errmsg = Text::sprintf('ATTACH_ERROR_IMPORTING_ATTACHMENTS_S', $status) . ' (ERR 163)';
                throw new \Exception($errmsg, 500);
            }
        }
    }


    /**
     * Test function
     */
    public function test()
    {
        // Access check.
        if (!$this->app->getIdentity()->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR'), 404);
        }

        echo "Test!";

        exit();
    }
}
