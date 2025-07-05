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

use JMCameron\Plugin\AttachmentsPluginFramework\AttachmentsPluginManager;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/**
 * Define the main attachments controller class
 *
 * @package Attachments
 */
class DisplayController extends BaseController
{
    /** List the attachments
     *
     * @param   bool   $cachable   If true, the view output will be cached
     * @param   array  $urlparams  (ignored)
     * @return void
     */
    public function display($cachable = false, $urlparams = false)
    {
        $input = $this->app->getInput();
        // Set the default view (if not specified)
        $input->set('view', $input->getCmd('view', 'Attachments'));

        // Call parent to display
        parent::display($cachable);
    }


    /**
     * Set up to display the entity selection view
     *
     * This allows users to select entities (sections, categories, and other
     * content items that are supported with Attachments plugins).
     */
    public function selectEntity()
    {
        $input = $this->app->getInput();
        // Get the parent type
        $parent_type = $input->getCmd('parent_type');
        if (!$parent_type) {
            $errmsg = Text::sprintf('ATTACH_ERROR_INVALID_PARENT_TYPE_S', $parent_type) . ' (ERR 65)';
            throw new \Exception($errmsg, 500);
        }

        // Parse the parent type and entity
        $parent_entity = $input->getCmd('parent_entity', 'default');
        if (strpos($parent_type, '.')) {
            $parts = explode('.', $parent_type);
            $parent_type = $parts[0];
            $parent_entity = $parts[1];
        }

        // Get the content parent object
        PluginHelper::importPlugin('attachments');
        $apm = AttachmentsPluginManager::getAttachmentsPluginManager();
        $parent = $apm->getAttachmentsPlugin($parent_type);
        $parent_entity = $parent->getCanonicalEntityId($parent_entity);
        $parent_entity_name = Text::_('ATTACH_' . $parent_entity);

        // Get the URL to repost (for filtering)
        $post_url = $parent->getSelectEntityURL($parent_entity);

        // Set up the display lists
        $lists = array();

        // table ordering
        $filter_order =
            $this->app->getUserStateFromRequest(
                'com_attachments.selectEntity.filter_order',
                'filter_order',
                '',
                'cmd'
            );
        $filter_order_Dir =
            $this->app->getUserStateFromRequest(
                'com_attachments.selectEntity.filter_order_Dir',
                'filter_order_Dir',
                '',
                'word'
            );
        $lists['order_Dir'] = $filter_order_Dir;
        $lists['order']     = $filter_order;

        // search filter
        $search_filter = $this->app->getUserStateFromRequest(
            'com_attachments.selectEntity.search',
            'search',
            '',
            'string'
        );
        $lists['search'] = $search_filter;

        // Get the list of items to display
        $items = $parent->getEntityItems($parent_entity, $search_filter);

        // Set up the view
        $view = $this->getView('Entity', 'html', '', array('option' => $input->getCmd('option')));
        $view->from = 'closeme';
        $view->post_url = $post_url;
        $view->parent_type = $parent_type;
        $view->parent_entity = $parent_entity;
        $view->parent_entity_name = $parent_entity_name;
        $view->lists = $lists;
        $view->items = $items;

        $view->display();
    }



    /**
     * Display links for the admin Utility functions
     */
    public function adminUtils()
    {
        // Access check
        $user = $this->app->getIdentity();
        if ($user === null or !$user->authorise('core.admin', 'com_attachments')) {
            throw new \Exception(Text::_('JERROR_ALERTNOAUTHOR') . ' (ERR 66)', 404);
        }

        // Set up the tooltip behavior
        $opts = array( 'hideDelay' => 0, 'showDelay' => 0 );
        HTMLHelper::_('bootstrap.tooltip', '.hasTip', $opts);

        // Set up url/link/tooltip for each command
        $uri = Uri::getInstance();
        $url_top = $uri->base(true) . "/index.php?option=com_attachments&amp;controller=special";
        $closeme = '&amp;tmpl=component&amp;close=1';

        // Set up the array of entries
        $entries = array();

        // Set up the HTML for the 'Disable MySQL uninstallation' command
        $disable_mysql_uninstall_url =
			"$url_top&amp;task=utils.disableSqlUninstall" . $closeme;
        $disable_mysql_uninstall_tooltip =
            Text::_('ATTACH_DISABLE_MYSQL_UNINSTALLATION') . '::' .
            Text::_('ATTACH_DISABLE_MYSQL_UNINSTALLATION_TOOLTIP');
        $entries[] = HTMLHelper::_(
            'tooltip',
            $disable_mysql_uninstall_tooltip,
            null,
            null,
            Text::_('ATTACH_DISABLE_MYSQL_UNINSTALLATION'),
            $disable_mysql_uninstall_url
        );

        // Set up the HTML for the 'Reinstall Attachments Permissions' command
        $reinstallPermissions_url = "$url_top&amp;task=utils.reinstallPermissions" . $closeme;
        $reinstallPermissions_tooltip = Text::_('ATTACH_REINSTALL_PERMISSIONS') . '::' .
                                         Text::_('ATTACH_REINSTALL_PERMISSIONS_TOOLTIP');
        $entries[] = HTMLHelper::_(
            'tooltip',
            $reinstallPermissions_tooltip,
            null,
            null,
            Text::_('ATTACH_REINSTALL_PERMISSIONS'),
            $reinstallPermissions_url
        );

        // Set up the HTML for the 'Regenerate attachment system filenames' command
        $regenerateSystemFilenames_url =
            "$url_top&amp;task=utils.regenerateSystemFilenames" . $closeme;
        $regenerateSystemFilenames_tooltip =
            Text::_('ATTACH_REGENERATE_ATTACHMENT_SYSTEM_FILENAMES') . '::' .
            Text::_('ATTACH_REGENERATE_ATTACHMENT_SYSTEM_FILENAMES_TOOLTIP');
        $entries[] = HTMLHelper::_(
            'tooltip',
            $regenerateSystemFilenames_tooltip,
            null,
            null,
            Text::_('ATTACH_REGENERATE_ATTACHMENT_SYSTEM_FILENAMES'),
            $regenerateSystemFilenames_url
        );

        // Set up the HTML for the 'Remove spaces from system filenames' command
        $unspacify_system_filenames_url =
            "$url_top&amp;task=utils.removeSpacesFromSystemFilenames" . $closeme;
        $unspacify_system_filenames_tooltip =
            Text::_('ATTACH_DESPACE_ATTACHMENT_SYSTEM_FILENAMES')   . '::' .
            Text::_('ATTACH_DESPACE_ATTACHMENT_SYSTEM_FILENAMES_TOOLTIP');
        $entries[] = HTMLHelper::_(
            'tooltip',
            $unspacify_system_filenames_tooltip,
            null,
            null,
            Text::_('ATTACH_DESPACE_ATTACHMENT_SYSTEM_FILENAMES'),
            $unspacify_system_filenames_url
        );

        // Set up the HTML for the 'Update attachment file sizes' command
        $updateFileSizes_url =
            "$url_top&amp;task=utils.updateFileSizes" . $closeme;
        $updateFileSizes_tooltip =
            Text::_('ATTACH_UPDATE_ATTACHMENT_FILE_SIZES') . '::' .
            Text::_('ATTACH_UPDATE_ATTACHMENT_FILE_SIZES_TOOLTIP');
        $entries[] = HTMLHelper::_(
            'tooltip',
            $updateFileSizes_tooltip,
            null,
            null,
            Text::_('ATTACH_UPDATE_ATTACHMENT_FILE_SIZES'),
            $updateFileSizes_url
        );

        // Set up the HTML for the 'Check Files' command
        $checkFiles_url = "$url_top&amp;task=utils.checkFiles" . $closeme;
        $checkFiles_tooltip = Text::_('ATTACH_CHECK_FILES') . '::' . Text::_('ATTACH_CHECK_FILES_TOOLTIP');
        $entries[] = HTMLHelper::_(
            'tooltip',
            $checkFiles_tooltip,
            null,
            null,
            Text::_('ATTACH_CHECK_FILES'),
            $checkFiles_url
        );

        // Set up the HTML for the 'Validate URLs' command
        $validateUrls_url = "$url_top&amp;task=utils.validateUrls" . $closeme;
        $validateUrls_tooltip = Text::_('ATTACH_VALIDATE_URLS') . '::' . Text::_('ATTACH_VALIDATE_URLS_TOOLTIP');
        $entries[] = HTMLHelper::_(
            'tooltip',
            $validateUrls_tooltip,
            null,
            null,
            Text::_('ATTACH_VALIDATE_URLS'),
            $validateUrls_url
        );

        // Test ???
        // $utils_test_url = "$url_top&amp;task=utils.test" . $closeme;
        // $utils_test_tooltip = 'Test';
        // $entries[] = HTMLHelper::_('tooltip', $utils_test_tooltip, null, null, 'TEST', $utils_test_url);

        // Get the view
        $view = $this->getView('Utils', 'html');
        $view->entries = $entries;

        $view->display();
    }


    /**
     * Return the attachments list as HTML (for use by Ajax)
     */
    public function attachmentsList()
    {
        $input = $this->app->getInput();
        $parent_id = $input->getInt('parent_id', false);
        $parent_type = $input->getWord('parent_type', '');
        $parent_entity = $input->getWord('parent_entity', 'default');
        $show_links = $input->getBool('show_links', true);
        $allow_edit = $input->getBool('allow_edit', true);
        $from = $input->getWord('from', 'closeme');
        $title = '';

        $response = '';

        if (($parent_id === false) || ($parent_type == '')) {
            return '';
        }

        // Allow remapping of parent ID (eg, for Joomfish)
        $lang = $input->getWord('lang', '');
        // NOTE: I cannot find anything about AttachmentsRemapper class.
        // Could it be old unnecessary code that needs deletion?
        // ------------------------------------------------------
        if ($lang and jimport('attachments_remapper.remapper')) {
            $parent_id = AttachmentsRemapper::remapParentID($parent_id, $parent_type, $parent_entity);
        }

        /** @var \Joomla\CMS\MVC\Factory\MVCFactory $mvc */
        $mvc = $this->app
            ->bootComponent("com_attachments")
            ->getMVCFactory();
        /** @var \JMCameron\Component\Attachments\Administrator\Controller\ListController $controller */
        $controller = $mvc->createController('List', 'Administrator', [], $this->app, $this->app->getInput());

        $response = $controller->displayString(
            $parent_id,
            $parent_type,
            $parent_entity,
            $title,
            $show_links,
            $allow_edit,
            false,
            $from
        );
        echo $response;
    }



    /** Show the help page
     */
    public function help()
    {
        // Set up the view
        $view = $this->getView('Help', 'html');

        // Load language(s)
        $view->lang = $this->app->getLanguage();

        // Now load the help page file
        // (Load the component file first since the help pages share some items)
        if ($view->lang->getTag() != 'en-GB') {
            // First load English for any untranslated items
            $view->lang->load('com_attachments', JPATH_ADMINISTRATOR . '/components/com_attachments', 'en-GB');
            $view->lang->load('com_attachments.help', JPATH_ADMINISTRATOR . '/components/com_attachments', 'en-GB');
        }

        // Load current language
        $view->lang->load('com_attachments', JPATH_ADMINISTRATOR . '/components/com_attachments', null, true);
        $view->lang->load('com_attachments.help', JPATH_ADMINISTRATOR . '/components/com_attachments', null, true);

        // Call parent to display
        $view->display();
    }
}
