<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

// Load the tooltip behavior.
// JHtml::_('bootstrap.tooltip');

// Add the CSS for the attachments list (whether we need it or not)
HTMLHelper::stylesheet('media/com_attachments/css/attachments_help.css');

// If the user specifies 'show=codes' in the url, the language item codes will
// be shown by default.  Note that they can still be toggled with the toggles
// at the top right and bottom right of the page.
$app = Factory::getApplication();
if ($app->getInput()->getCmd('show') == 'codes') {
    HTMLHelper::stylesheet('media/com_attachments/css/attachments_help_show_codes.css');
}

// Define the section constants
define('SECT_INTRO', 1);
define('SECT_NEW_V4', 2);
define('SECT_FEAT', 3);
define('SECT_UPLOAD', 4);
define('SECT_SETNGS', 5);
define('SECT_PERMS', 6);
define('SECT_ACCESS', 7);
define('SECT_DISPLY', 8);
define('SECT_ATTACH', 9);
define('SECT_FILES', 10);
define('SECT_STYLE', 11);
define('SECT_ICONS', 12);
define('SECT_UTILS', 13);
define('SECT_WARN', 14);
define('SECT_UPGRAD', 15);
define('SECT_UNINST', 16);
define('SECT_MIGRAT', 17);
define('SECT_TRANS', 18);
define('SECT_ACKNOW', 19);
define('SECT_CONTCT', 20);

$this->saveSectionInfo(SECT_INTRO, 'introduction', 'ATTACH_HELP_010000_SECTION_TITLE');
$this->saveSectionInfo(SECT_NEW_V4, 'v4-features', 'ATTACH_HELP_020000_SECTION_TITLE');
$this->saveSectionInfo(SECT_FEAT, 'features', 'ATTACH_HELP_030000_SECTION_TITLE');
$this->saveSectionInfo(SECT_UPLOAD, 'uploading', 'ATTACH_HELP_040000_SECTION_TITLE');
$this->saveSectionInfo(SECT_SETNGS, 'settings', 'ATTACH_HELP_050000_SECTION_TITLE');
$this->saveSectionInfo(SECT_PERMS, 'permissions', 'ATTACH_HELP_060000_SECTION_TITLE');
$this->saveSectionInfo(SECT_ACCESS, 'access-levels', 'ATTACH_HELP_070000_SECTION_TITLE');
$this->saveSectionInfo(SECT_DISPLY, 'display-filenames', 'ATTACH_HELP_080000_SECTION_TITLE');
$this->saveSectionInfo(SECT_ATTACH, 'attaching-urls', 'ATTACH_HELP_090000_SECTION_TITLE');
$this->saveSectionInfo(SECT_FILES, 'attached-to-what', 'ATTACH_HELP_100000_SECTION_TITLE');
$this->saveSectionInfo(SECT_STYLE, 'css-styling', 'ATTACH_HELP_110000_SECTION_TITLE');
$this->saveSectionInfo(SECT_ICONS, 'file-type-icons', 'ATTACH_HELP_120000_SECTION_TITLE');
$this->saveSectionInfo(SECT_UTILS, 'admin-utilities', 'ATTACH_HELP_130000_SECTION_TITLE');
$this->saveSectionInfo(SECT_WARN, 'warnings', 'ATTACH_HELP_140000_SECTION_TITLE');
$this->saveSectionInfo(SECT_UPGRAD, 'upgrading', 'ATTACH_HELP_150000_SECTION_TITLE');
$this->saveSectionInfo(SECT_UNINST, 'uninstalling', 'ATTACH_HELP_160000_SECTION_TITLE');
$this->saveSectionInfo(SECT_TRANS, 'translations', 'ATTACH_HELP_180000_SECTION_TITLE');
$this->saveSectionInfo(SECT_ACKNOW, 'acknowledgments', 'ATTACH_HELP_190000_SECTION_TITLE');
$this->saveSectionInfo(SECT_CONTCT, 'contact', 'ATTACH_HELP_200000_SECTION_TITLE');


// A few other miscellaneous items

$tlc = Text::_('ATTACH_HELP_TOGGLE_LANGUAGE_CODES');

$onContentPrepare = "<tt class=\"docutils literal\">'onContentPrepare'</tt>";

$main_title_tooltip = $this->constructTooltip('ATTACH_HELP_000000_MAIN_TITLE');
$main_version_tooltip = $this->constructTooltip('ATTACH_HELP_000100_MAIN_VERSION');

$toggle_img = Uri::root(true) . '/media/system/images/tooltip.png';

?>
<div class="help-document">
<div class="header">
    <a id="tc_toggle" title="<?php echo $tlc ?>" href="<?php echo $this->toggledURL() ?>"><img src="<?php echo $toggle_img ?>"></a>
    <h1 class="title" <?php echo $main_title_tooltip ?>><?php echo $this->logo_img ?><?php echo Text::_('ATTACH_HELP_000000_MAIN_TITLE') ?></h1>
    <hr class="header"/>
</div>
<div class="main">

<p class="version"><strong><?php echo $this->version . ' - ' . $this->date ?></strong></p>
<p><strong<?php echo $main_version_tooltip ?>><?php echo Text::_('ATTACH_HELP_000100_MAIN_VERSION') ?></strong></p>

<?php
   // ------------------------------------------------------------
   // Add the table of contents
   $this->tableOfContents('ATTACH_HELP_000200_MAIN_CONTENTS');

   // ------------------------------------------------------------
   // Introduction
   $this->startSection(SECT_INTRO);
      $this->addParagraph('ATTACH_HELP_010100_TEXT');
      $this->addWarning('ATTACH_HELP_010200_WARNING');
      $this->addParagraph('ATTACH_HELP_010400_TEXT', array( '{SECT_TRANS}' => $this->sectionLink(SECT_TRANS) ));
   $this->endSection(SECT_INTRO);

   // ------------------------------------------------------------
   // New features in Version 3.1
   $this->startSection(SECT_NEW_V4);
      $this->startList();
         $this->addListElement('ATTACH_HELP_020050_TEXT');
         $this->addListElement('ATTACH_HELP_020100_TEXT');
         $this->addListElement('ATTACH_HELP_020200_TEXT');
         $this->addListElement('ATTACH_HELP_020300_TEXT');
         $this->addListElement('ATTACH_HELP_020400_TEXT');
      $this->endList();
   $this->endSection(SECT_NEW_V4);

   // ------------------------------------------------------------
   // Major features of the Attachments Extension
   $this->startSection(SECT_FEAT);
      $this->startList();
         $this->addListElement('ATTACH_HELP_030100_TEXT', array( '{SECT_PERMS}' => $this->sectionLink(SECT_PERMS) ));
         $this->addListElement('ATTACH_HELP_030200_TEXT', array( '{SECT_ACCESS}' => $this->sectionLink(SECT_ACCESS) ));
         $this->addListElement('ATTACH_HELP_030300_TEXT');
         $this->addListElement('ATTACH_HELP_030400_TEXT');
         $this->addListElement('ATTACH_HELP_030500_TEXT');
         $this->addListElement('ATTACH_HELP_030600_TEXT');
         $this->addListElement('ATTACH_HELP_030700_TEXT');
         $this->addListElement('ATTACH_HELP_030800_TEXT');
            $this->startList();
               $this->addListElement('ATTACH_HELP_030900_TEXT');
               $this->addListElement('ATTACH_HELP_031000_TEXT');
               $this->addListElement('ATTACH_HELP_031100_TEXT');
               $this->addListElement('ATTACH_HELP_031200_TEXT');
               $this->addListElement(
                   'ATTACH_HELP_031300_TEXT',
                   array( '{SECT_FILES}' => $this->sectionLink(SECT_FILES),
                                            '{ONCONTENTPREPARE}' => $onContentPrepare)
               );
               $this->endList();
               $this->endList();
               $this->endSection(SECT_FEAT);

   // ------------------------------------------------------------
   // Uploading Restrictions
               $this->startSection(SECT_UPLOAD);
               $this->addParagraph('ATTACH_HELP_040100_TEXT');
               $this->addWarning('ATTACH_HELP_040200_WARNING');
               $this->endSection(SECT_UPLOAD);

   // ------------------------------------------------------------
   // Attachments Settings
               $this->startSection(SECT_SETNGS);
               $this->addParagraph('ATTACH_HELP_050100_TEXT');

      // Basic Options
               $this->startSubSection(array( 'id' => 'basic-options',
                                    'code' => 'ATTACH_HELP_050200_SUBSECTION_TITLE'));
               echo $this->image(
                   'options-basic.png',
                   'ATTACH_HELP_050200_SUBSECTION_TITLE',
                   'class="float-right drop-shadow"'
               ) . "\n";
               $this->startList();
               $this->addDefinitionListElement(
                   'ATTACH_ATTACHMENTS_PUBLISHED_BY_DEFAULT',
                   'ATTACH_HELP_050300_TEXT'
               );
               $this->addDefinitionListElement(
                   'ATTACH_AUTO_PUBLISH_WARNING',
                   'ATTACH_HELP_050400_TEXT'
               );
               $this->addDefinitionListElement(
                   'ATTACH_DEFAULT_ACCESS_LEVEL',
                   'ATTACH_DEFAULT_ACCESS_LEVEL_DESCRIPTION'
               );
               $this->addDefinitionListElement('ATTACH_HELP_050600_TEXT', 'ATTACH_USER_DEFINED_FIELD_NAME_DESCRIPTION');
               $this->addHint('ATTACH_HELP_050700_HINT_TEXT');
               $this->addDefinitionListElement('ATTACH_MAX_FILENAME_URL_LENGTH', 'ATTACH_MAX_FILENAME_URL_LENGTH_DESCRIPTION');
               $this->addDefinitionListElement(
                   'ATTACH_WHERE_SHOULD_ATTACHMENTS_BE_PLACED',
                   'ATTACH_WHERE_SHOULD_ATTACHMENTS_BE_PLACED_DESCRIPTION',
                   null,
                   false
               );
               $this->startList();
                  $this->addListElement('ATTACH_HELP_051000_TEXT');
                  $this->addListElement('ATTACH_HELP_051100_TEXT');
                  $this->addListElement('ATTACH_HELP_051200_TEXT', null, false);
                  $this->addWarning('ATTACH_HELP_051300_WARNING');
                  $this->endListElement();
                  $this->addParagraph('ATTACH_HELP_051400_TEXT', null, 'noindent');
                  $this->addPreBlock("&lt;span class=&quot;hide&quot;&gt;{attachments}&lt;/span&gt;");
                  $this->addParagraph('ATTACH_HELP_051500_TEXT', null, 'noindent');
                  $this->addListElement('ATTACH_HELP_051700_TEXT');
                  $this->addListElement('ATTACH_HELP_051800_TEXT');
                  $this->endListElement();
               $this->endList();
               $this->endListElement();
               $this->addDefinitionListElement(
                   'ATTACH_ALLOW_FRONTEND_ACCESS_LEVEL_EDITING',
                   'ATTACH_ALLOW_FRONTEND_ACCESS_LEVEL_EDITING_DESCRIPTION'
               );
               $this->endList();
               $this->endSubSection('basic-options');

      // Formatting options
               $this->startSubSection(array( 'id' => 'formatting-options',
                                    'code' => 'ATTACH_HELP_052000_SUBSECTION_TITLE'));
               echo $this->image(
                   'options-formatting.png',
                   'ATTACH_HELP_052000_SUBSECTION_TITLE',
                   'class="float-right drop-shadow"'
               ) . "\n";
               $this->startList();
               $this->addDefinitionListElement('ATTACH_SHOW_COLUMN_TITLES', 'ATTACH_SHOW_COLUMN_TITLES_DESCRIPTION');
               $this->addDefinitionListElement('ATTACH_SHOW_ATTACHMENT_DESCRIPTION', 'ATTACH_SHOW_ATTACHMENT_DESCRIPTION_DESCRIPTION');
               $this->addDefinitionListElement('ATTACH_HIDE_BRACKETS_IF_EMPTY', 'ATTACH_HIDE_BRACKETS_IF_EMPTY_DESCRIPTION');
               $this->addDefinitionListElement('ATTACH_SHOW_ATTACHMENT_CREATOR', 'ATTACH_SHOW_ATTACHMENT_CREATOR_DESCRIPTION');
               $this->addDefinitionListElement('ATTACH_SHOW_ATTACHMENT_FILE_SIZE', 'ATTACH_SHOW_ATTACHMENT_FILE_SIZE_DESCRIPTION');
               $this->addDefinitionListElement('ATTACH_SHOW_NUMBER_OF_DOWNLOADS', 'ATTACH_SHOW_NUMBER_OF_DOWNLOADS_DESCRIPTION');
                $this->addWarning('ATTACH_HELP_052600_WARNING');
               $this->endListElement();
               $this->addDefinitionListElement('ATTACH_SHOW_CREATION_DATE', 'ATTACH_HELP_052650_TEXT');
               $this->addDefinitionListElement('ATTACH_SHOW_MODIFICATION_DATE', 'ATTACH_HELP_052700_TEXT');
               $this->addDefinitionListElement('ATTACH_FORMAT_STRING_FOR_DATES', 'ATTACH_FORMAT_STRING_FOR_DATES_DESCRIPTION');
               $this->addDefinitionListElement(
                   'ATTACH_SHOW_FONTAWESOME_ICONS',
                   'ATTACH_SHOW_FONTAWESOME_ICONS_DESCRIPTION',
                   null,
                   false
               );
               $this->addDefinitionListElement(
                   'ATTACH_SHOW_FONTAWESOME_ICONS_STYLE',
                   'ATTACH_SHOW_FONTAWESOME_ICONS_STYLE_DESCRIPTION'
               );
                $this->startList();
                  $this->addListElement('ATTACH_SHOW_FONTAWESOME_ICONS_STYLE_FAS');
                  $this->addListElement('ATTACH_SHOW_FONTAWESOME_ICONS_STYLE_FAR');
                  $this->addListElement('ATTACH_SHOW_FONTAWESOME_ICONS_STYLE_FAL');
                  $this->addListElement('ATTACH_SHOW_FONTAWESOME_ICONS_STYLE_FAD');
                $this->endList();
               $this->endListElement();
               $this->addDefinitionListElement('ATTACH_ATTACHMENTS_LIST_ORDER', 'ATTACH_HELP_052900_TEXT');
                    $this->startList('ol', 'arabic simple');
                    $this->addListElement(
                        'ATTACH_HELP_053000_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_FILENAME'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_053100_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_FILENAME_DESCENDING'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_053200_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_FILE_SIZE'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_053300_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_FILE_SIZE_DESCENDING'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_053400_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_DESCRIPTION'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_053500_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_DESCRIPTION_DESCENDING'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_053600_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_DISPLAY_FILENAME_OR_URL'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_053700_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_DISPLAY_FILENAME_OR_URL_DESCENDING'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_053800_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_CREATOR'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_053900_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_CREATED_DATE'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_054000_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_CREATED_DATE_DESCENDING'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_054100_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_MODIFICATION_DATE'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_054200_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_MODIFICATION_DATE_DESCENDING'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_054300_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_SORT_ATTACHMENT_ID'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_054400_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_USER_DEFINED_FIELD_1'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_054500_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_USER_DEFINED_FIELD_2'))
                    );
                    $this->addListElement(
                        'ATTACH_HELP_054600_TEXT',
                        array('{LABEL}' => Text::_('ATTACH_USER_DEFINED_FIELD_3'))
                    );
                    $this->endList();
                    $this->endListElement();
                    $this->endList();
                    $this->endSubSection('formatting-options');

      // Visibility Options
                    $this->startSubSection(array( 'id' => 'visibility-options',
                                    'code' => 'ATTACH_HELP_055000_SUBSECTION_TITLE'));
                    echo $this->image(
                        'options-visibility.png',
                        'ATTACH_HELP_055000_SUBSECTION_TITLE',
                        'class="float-right drop-shadow"'
                    ) . "\n";
                    $this->addParagraph('ATTACH_HELP_055100_TEXT');
                    $this->startList();
                    $this->addDefinitionListElement('ATTACH_HIDE_ATTACHMENTS_ON_FRONTPAGE', 'ATTACH_HIDE_ATTACHMENTS_ON_FRONTPAGE_DESCRIPTION');
                    $this->addDefinitionListElement('ATTACH_HIDE_ATTACHMENTS_WITH_READMORE', 'ATTACH_HIDE_ATTACHMENTS_WITH_READMORE_DESCRIPTION');
                    $this->addDefinitionListElement('ATTACH_HIDE_ATTACHMENTS_ON_BLOGS', 'ATTACH_HIDE_ATTACHMENTS_ON_BLOGS_DESCRIPTION');
                    $this->addDefinitionListElement('ATTACH_HIDE_ATTACHMENTS_EXCEPT_ON_ARTICLE_VIEWS', 'ATTACH_HIDE_ATTACHMENTS_EXCEPT_ON_ARTICLE_VIEWS_DESCRIPTION');
                    $this->addDefinitionListElement('ATTACH_ALWAYS_SHOW_CATEGORY_ATTACHMENTS', 'ATTACH_ALWAYS_SHOW_CATEGORY_ATTACHMENTS_DESCRIPTION');
                    $this->addDefinitionListElement('ATTACH_HIDE_ATTACHMENTS_FOR_CATEGORIES', 'ATTACH_HIDE_ATTACHMENTS_FOR_CATEGORIES_DESCRIPTION');
                    $this->addDefinitionListElement(
                        'ATTACH_SHOW_GUEST_ACCESS_LEVELS',
                        'ATTACH_HELP_055800_TEXT',
                        array('{DESCRIPTION}' => Text::_('ATTACH_SHOW_GUEST_ACCESS_LEVELS_DESCRIPTION'))
                    );
                    $this->addDefinitionListElement('ATTACH_HIDE_ADD_ATTACHMENTS_LINK', 'ATTACH_HIDE_ADD_ATTACHMENTS_LINK_DESCRIPTION');
                    $this->endList();
                    $this->endSubSection('visibility-options');

      // Advanced Options
                    $this->startSubSection(array( 'id' => 'advanced-options',
                                    'code' => 'ATTACH_HELP_057000_SUBSECTION_TITLE'));
                    echo $this->image(
                        'options-advanced.png',
                        'ATTACH_HELP_057000_SUBSECTION_TITLE',
                        'class="float-right drop-shadow"'
                    ) . "\n";
                    $this->startList();
                    $this->addDefinitionListElement(
                        'ATTACH_MAX_ATTACHMENT_SIZE',
                        'ATTACH_HELP_057050_TEXT',
                        array('{DESCRIPTION}' => Text::_('ATTACH_MAX_ATTACHMENT_SIZE_DESCRIPTION'),
                                         '{SECT_WARN}' => $this->sectionLink(SECT_WARN))
                    );
                    $this->addDefinitionListElement('ATTACH_FORBIDDEN_FILENAME_CHARACTERS', 'ATTACH_FORBIDDEN_FILENAME_CHARACTERS_DESCRIPTION');
                    $this->addDefinitionListElement('ATTACH_SANITIZE_FILENAME', 'ATTACH_SANITIZE_FILENAME_DESCRIPTION');
                    $this->addDefinitionListElement(
                        'ATTACH_CSS_STYLE_FOR_ATTACHMENTS_TABLES',
                        'ATTACH_HELP_057200_TEXT',
                        array( '{DESCRIPTION}' => Text::_('ATTACH_CSS_STYLE_FOR_ATTACHMENTS_TABLES_DESCRIPTION'),
                                            '{SECT_STYLE}' => $this->sectionLink(SECT_STYLE))
                    );
                    $this->addDefinitionListElement('ATTACH_FILE_LINK_OPEN_MODE', 'ATTACH_FILE_LINK_OPEN_MODE_DESCRIPTION');
                    $blk1 = "<pre class=\"literal-block\">content/attachments/language/qq-QQ/qq-QQ.plg_content_attachments.ini</pre>\n";
                    $blk2 = "<pre class=\"literal-block\">language/overrides/en-GB.override.ini</pre>\n";
                    $this->addDefinitionListElement(
                        'ATTACH_CUSTOM_TITLES_FOR_ATTACHMENTS_LISTS',
                        'ATTACH_HELP_057400_TEXT',
                        array('{DESCRIPTION}' => Text::_('ATTACH_CUSTOM_TITLES_FOR_ATTACHMENTS_LISTS_DESCRIPTION')),
                        false
                    );
                    $this->addNote(
                        array('ATTACH_HELP_057500_NOTE', 'ATTACH_HELP_057600_NOTE'),
                        array('{BLOCK1}' => $blk1, '{BLOCK2}' => $blk2)
                    );
                    $this->endListElement();
                    $this->addDefinitionListElement('ATTACH_TIMEOUT_FOR_CHECKING_LINKS', 'ATTACH_TIMEOUT_FOR_CHECKING_LINKS_DESCRIPTION');
                    $this->addDefinitionListElement('ATTACH_SUPERIMPOSE_URL_LINK_ICONS', 'ATTACH_SUPERIMPOSE_URL_LINK_ICONS_DESCRIPTION');
                    $this->addDefinitionListElement(
                        'ATTACH_SUPPRESS_OBSOLETE_ATTACHMENTS',
                        'ATTACH_HELP_057900_TEXT',
                        array('{DESCRIPTION}' => Text::_('ATTACH_SUPPRESS_OBSOLETE_ATTACHMENTS_DESCRIPTION'))
                    );
                    $this->addDefinitionListElement('ATTACH_URL_TO_LOGIN', 'ATTACH_URL_TO_LOGIN_DESCRIPTION');
                    $this->addDefinitionListElement('ATTACH_URL_TO_REGISTER', 'ATTACH_URL_TO_REGISTER_DESCRIPTION');
                    $this->endList();
                    $this->endSubSection('advanced-options');

      // Security Options
                    $this->startSubSection(array( 'id' => 'security-options',
                                    'code' => 'ATTACH_HELP_058000_SUBSECTION_TITLE'));
                    echo $this->image(
                        'options-security.png',
                        'ATTACH_HELP_058000_SUBSECTION_TITLE',
                        'class="float-right drop-shadow"'
                    ) . "\n";
                    $this->startList();
                    $this->addDefinitionListElement('ATTACH_SECURE_ATTACHMENT_DOWNLOADS', array('ATTACH_HELP_058100_TEXT', 'ATTACH_HELP_058200_TEXT',
                                         'ATTACH_HELP_058300_TEXT'));
                    $this->addHint('ATTACH_HELP_058400_HINT');
                    $this->endListElement();
                    $this->addDefinitionListElement('ATTACH_DOWNLOAD_MODE_FOR_SECURE_DOWNLOADS', 'ATTACH_HELP_058500_TEXT');
                    $this->startList();
                      $this->addListElement('ATTACH_HELP_058600_TEXT');
                      $this->addListElement('ATTACH_HELP_058700_TEXT');
                    $this->endList();
                    $this->addParagraph('ATTACH_HELP_058800_TEXT');
                    $this->addDefinitionListElement('ATTACH_SHOW_RAW_DOWNLOAD', 'ATTACH_SHOW_RAW_DOWNLOAD_DESCRIPTION');

                    $this->endListElement();
                    $this->endList();
                    $this->endSubSection('security-options');

      // Permissions Options
                    $this->startSubSection(array( 'id' => 'permissions-options',
                                    'code' => 'ATTACH_HELP_059000_SUBSECTION_TITLE'));
                    $this->addParagraph('ATTACH_HELP_059100_TEXT', array( '{SECT_PERMS}' => $this->sectionLink(SECT_PERMS) ));
                    $this->endSubSection('permissions-options');

                    $this->endSection(SECT_SETNGS);

   // ------------------------------------------------------------
   // Attachments Permissions
                    $this->startSection(SECT_PERMS);
                    $this->addParagraph('ATTACH_HELP_060100_TEXT');
                    $this->addParagraph('ATTACH_HELP_060200_TEXT');
                    $this->addImportant('ATTACH_HELP_060300_IMPORTANT');
                    $this->addParagraph('ATTACH_HELP_060400_TEXT');
                    $this->addFigure(
                        'options-permissions.png',
                        'ATTACH_HELP_060500_TEXT',
                        'ATTACH_HELP_060600_TEXT',
                        'figure width50',
                        'class="drop-shadow"'
                    );

                    $this->addParagraph('ATTACH_HELP_060700_TEXT');
                    $this->startPermissionsTable(
                        'ATTACH_HELP_060800_TEXT',
                        'ATTACH_HELP_060900_TEXT',
                        'ATTACH_HELP_061000_TEXT'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_ADMIN_COMPONENT',
                        'ATTACH_PERMISSION_ADMIN_COMPONENT_DESC',
                        'core.admin'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_MANAGE_COMPONENT',
                        'ATTACH_PERMISSION_MANAGE_COMPONENT_DESC',
                        'core.manage'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_CREATE',
                        'ATTACH_PERMISSION_CREATE_DESC',
                        'core.create'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_DELETE',
                        'ATTACH_PERMISSION_DELETE_DESC',
                        'core.delete'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_EDIT',
                        'ATTACH_PERMISSION_EDIT_DESC',
                        'core.edit'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_EDITSTATE',
                        'ATTACH_PERMISSION_EDITSTATE_DESC',
                        'core.edit.state'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_EDITOWN',
                        'ATTACH_PERMISSION_EDITOWN_DESC',
                        'core.edit.own'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_EDITSTATE_OWN',
                        'ATTACH_PERMISSION_EDITSTATE_OWN_DESC',
                        'attachments.edit.state.own'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_DELETEOWN',
                        'ATTACH_PERMISSION_DELETEOWN_DESC',
                        'attachments.delete.own'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_EDIT_OWNPARENT',
                        'ATTACH_PERMISSION_EDIT_OWNPARENT_DESC',
                        'attachments.edit.ownparent'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_EDITSTATE_OWNPARENT',
                        'ATTACH_PERMISSION_EDITSTATE_OWNPARENT_DESC',
                        'attachments.edit.state.ownparent'
                    );
                    $this->addPermissionsTableRow(
                        'ATTACH_PERMISSION_DELETE_OWNPARENT',
                        'ATTACH_PERMISSION_DELETE_OWNPARENT_DESC',
                        'attachments.delete.ownparent'
                    );
                    $this->endPermissionsTable();

   // Default permissions
                    $this->startSubSection(array( 'id' => 'default-permissions',
                                 'code' => 'ATTACH_HELP_062000_SUBSECTION_TITLE'));
                    $this->addParagraph('ATTACH_HELP_062100_TEXT');
                    $this->addAdmonition(
                        'important hide-title',
                        'ATTACH_HELP_IMPORTANT',
                        'ATTACH_HELP_062200_IMPORTANT',
                        null,
                        false
                    );
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_062300_TEXT');
                    $this->addListElement('ATTACH_HELP_062400_TEXT');
                    $this->addListElement('ATTACH_HELP_062500_TEXT');
                    $this->addListElement('ATTACH_HELP_062600_TEXT');
                    $this->endList();
                    $this->endAdmonition();
                    $this->addParagraph('ATTACH_HELP_062700_TEXT');
                    $this->endSubSection('default-permissions');

   // Permissions for common scenarios
                    $this->startSubSection(array( 'id' => 'common-permissions-scenarios',
                                 'code' => 'ATTACH_HELP_063000_SUBSECTION_TITLE'));
                    $this->addParagraph('ATTACH_HELP_063100_TEXT');
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_063200_TEXT', null, false);
                    $this->addLineBreak();
                    $this->addParagraph('ATTACH_HELP_063300_TEXT');
                    $this->addFigure(
                        'permissions-scenario1.png',
                        'ATTACH_HELP_063400_TEXT',
                        '',
                        '',
                        'class="drop-shadow"'
                    );
                    $this->endListElement();
                    $this->addListElement('ATTACH_HELP_063500_TEXT', null, false);
                    $this->addLineBreak();
                    $this->addParagraph('ATTACH_HELP_063600_TEXT');
                    $this->addFigure(
                        'permissions-scenario2.png',
                        'ATTACH_HELP_063700_TEXT',
                        '',
                        '',
                        'class="drop-shadow"'
                    );
                    $this->addParagraph('ATTACH_HELP_063800_TEXT');
                    $this->addParagraph('ATTACH_HELP_063900_TEXT');
                    $this->endListElement();
                    $this->endList();
                    $this->addParagraph('ATTACH_HELP_064000_TEXT');
                    $this->endSubSection('common-permissions-scenarios');

   // Permissions for common scenarios
                    $this->startSubSection(array( 'id' => 'other-notes-on-permissions',
                                 'code' => 'ATTACH_HELP_065000_SUBSECTION_TITLE'));
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_065100_TEXT');
                    $this->endList();
                    $this->endSubSection('other-notes-on-permissions');

                    $this->endSection(SECT_PERMS);

   // ------------------------------------------------------------
   // Access Levels Visibility Control
                    $this->startSection(SECT_ACCESS);
                    $this->addParagraph('ATTACH_HELP_070100_TEXT');
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_070200_TEXT');
                    $this->addListElement('ATTACH_HELP_070300_TEXT');
                    $this->endList();
                    $this->addParagraph('ATTACH_HELP_070400_TEXT');
                    $this->addParagraph('ATTACH_HELP_070500_TEXT');
                    $this->addNote('ATTACH_HELP_070600_NOTE');

                    $this->endSection(SECT_ACCESS);

   // ------------------------------------------------------------
   // Display Filename or URL
                    $this->startSection(SECT_DISPLY);
                    $this->addParagraph('ATTACH_HELP_080100_TEXT');
                    $this->endSection(SECT_DISPLY);

   // ------------------------------------------------------------
   // Attaching URLs
                    $this->startSection(SECT_ATTACH);
                    $this->addParagraph('ATTACH_HELP_090100_TEXT');
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_090200_TEXT');
                    $this->addListElement('ATTACH_HELP_090300_TEXT');
                    $this->endList();
                    $this->addParagraph('ATTACH_HELP_090400_TEXT');
                    $this->endSection(SECT_ATTACH);

   // ------------------------------------------------------------
   // What Can Files Be Attached To?
                    $this->startSection(SECT_FILES);
                    $this->addParagraph(
                        'ATTACH_HELP_100100_TEXT',
                        array('{ONCONTENTPREPARE}' => $onContentPrepare)
                    );
                    $this->addWarning('ATTACH_HELP_100200_WARNING');
                    $this->addParagraph('ATTACH_HELP_100300_TEXT');
                    $this->startList();
                    $manual_url = 'http://jmcameron.net/attachments/';
                    $manual_url_text = Text::sprintf('ATTACH_HELP_100400_TEXT', $manual_url);
                    $this->addListElementHtml("<a class=\"reference external\" href=\"$manual_url\">$manual_url_text</a>");
                    $this->endList();
                    $this->addWarning('ATTACH_HELP_100500_WARNING');
                    $this->endSection(SECT_FILES);

   // ------------------------------------------------------------
   // CSS Styling of Attachment Lists
                    $this->startSection(SECT_STYLE);
                    $this->addParagraph('ATTACH_HELP_110100_TEXT');
                    $this->addParagraph('ATTACH_HELP_110200_TEXT');
                    $this->addPreBlock("media/com_attachments/css/attachments_list.css\n\n  to\n\ntemplates/TEMPLATE/css/com_attachments/");
                    $this->addParagraph('ATTACH_HELP_110300_TEXT');
                    $this->endSection(SECT_STYLE);

   // ------------------------------------------------------------
   // File Type Icons
                    $this->startSection(SECT_ICONS);
                    $this->addParagraph('ATTACH_HELP_120100_TEXT');
                    $this->startList('ol');
                    $this->addListElement('ATTACH_HELP_120200_TEXT');
                    $this->addListElement('ATTACH_HELP_120300_TEXT');
                    $this->addListElement('ATTACH_HELP_120400_TEXT', null, false);
                    $this->addParagraph('ATTACH_HELP_120500_TEXT', null, 'paragraph');
                    $this->endListElement();
                    $this->endList();
                    $this->endSection(SECT_ICONS);

   // ------------------------------------------------------------
   // Administrative Utility Commands
                    $this->startSection(SECT_UTILS);
                    $this->addParagraph('ATTACH_HELP_130100_TEXT');
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_130200_TEXT');
                    $this->addListElement('ATTACH_HELP_130300_TEXT');
                    $this->addListElement('ATTACH_HELP_130400_TEXT', null, false);
                    $this->addParagraph('ATTACH_HELP_130500_TEXT');
                    $this->startList();
                      $this->addListElement('ATTACH_HELP_130600_TEXT');
                      $this->addListElement('ATTACH_HELP_130700_TEXT');
                    $this->endList();
                    $this->endListElement();
                    $this->addListElement('ATTACH_HELP_130800_TEXT');
                    $this->addListElement('ATTACH_HELP_130900_TEXT');
                    $this->addListElement('ATTACH_HELP_131000_TEXT');
                    $this->addListElement('ATTACH_HELP_131100_TEXT');
                    $this->endList();
                    $this->addNote('ATTACH_HELP_131200_NOTE');
                    $this->endSection(SECT_UTILS);

   // ------------------------------------------------------------
   // Warnings
                    $this->startSection(SECT_WARN);
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_140100_TEXT');
                    $this->addListElement('ATTACH_HELP_140200_TEXT');
                    $this->addListElement('ATTACH_HELP_140300_TEXT');
                    $this->addListElement('ATTACH_HELP_140400_TEXT', null, false);
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_140500_TEXT');
                    $this->addListElement('ATTACH_HELP_140600_TEXT');
                    $this->addListElement(
                        'ATTACH_HELP_140700_TEXT',
                        array('{DEFACCLEVEL}' => Text::_('ATTACH_DEFAULT_ACCESS_LEVEL'))
                    );
                    $this->addListElement('ATTACH_HELP_140800_TEXT');
                    $this->addListElement('ATTACH_HELP_140900_TEXT');
                    $this->addListElement('ATTACH_HELP_141000_TEXT');
                    $this->addListElement('ATTACH_HELP_141100_TEXT');
                    $this->endList();
                    $this->endListElement();
                    $this->addListElement('ATTACH_HELP_141200_TEXT', null, false);
                    $this->addPreBlock("php_value upload_max_filesize 32M\nphp_value post_max_size 32M");
                    $this->addParagraph('ATTACH_HELP_141300_TEXT');
                    $this->endListElement();
                    $this->addListElement(
                        'ATTACH_HELP_141400_TEXT',
                        array('{LOCALHOST}' => '<tt class="docutils literal">localhost</tt>'),
                        false
                    );
                    $this->addPreBlock("C:\Windows\System32\drivers\etc\hosts");
                    $this->addParagraph(
                        'ATTACH_HELP_141500_TEXT',
                        array('{IPV6HOST1}' => '<tt class="docutils literal">::1</tt>',
                                       '{HOSTS}' => '<tt class="docutils literal">hosts</tt>')
                    );
                    $this->endListElement();
                    $this->addListElement('ATTACH_HELP_141600_TEXT');
                    $this->addListElement('ATTACH_HELP_141700_TEXT');
                    $this->addListElement('ATTACH_HELP_141800_TEXT', null, false);
                    $this->startList();
                    $forums_url = 'http://joomlacode.org/gf/project/attachments3/forum/';
                    $forums_url_text = $this->replace(
                        Text::sprintf('ATTACH_HELP_141900_TEXT', $forums_url),
                        array('{FORUMS}' => $forums_url)
                    );
                    $this->addListElementHtml("<a class=\"reference external\" href=\"$forums_url\">$forums_url_text</a>");
                    $this->endList();
                    $this->endListElement();


                    $this->endList();
                    $this->endSection(SECT_WARN);

   // ------------------------------------------------------------
   // Upgrading
                    $this->startSection(SECT_UPGRAD);
                    $this->addParagraph('ATTACH_HELP_150100_TEXT');
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_150200_TEXT');
                    $this->addListElement('ATTACH_HELP_150300_TEXT');
                    $this->endList();
                    $this->endSection(SECT_UPGRAD);

   // ------------------------------------------------------------
   // Uninstalling
                    $this->startSection(SECT_UNINST);
                    $this->startList();
                    $this->addListElement(
                        'ATTACH_HELP_160100_TEXT',
                        array('{ATTACH_DIR}' => "attachments",
                                      '{ATTACH_TBL}' => "#_attachments",
                                      '{SECT_UTILS}' =>  $this->sectionLink(SECT_UTILS),
                                      '{DISABLE_SQL}' => Text::_('ATTACH_DISABLE_MYSQL_UNINSTALLATION'),
                                      )
                    );
                    $this->addListElement('ATTACH_HELP_160200_TEXT');
                    $this->addListElement('ATTACH_HELP_160300_TEXT', null, false);
                    $this->addPreBlock(Text::_('ATTACH_HELP_160400_TEXT'));
                    $this->addParagraph('ATTACH_HELP_160500_TEXT');
                    $this->endListElement();
                    $this->endList();
                    $this->endSection(SECT_UNINST);


   // ------------------------------------------------------------
   // Translations
                    $this->startSection(SECT_TRANS);
                    $this->addParagraph('ATTACH_HELP_180100_TEXT');
                    $this->addParagraph('ATTACH_HELP_180200_TEXT');
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_181000_TEXT');
                    $this->addListElement('ATTACH_HELP_181100_TEXT');
                    $this->addListElement('ATTACH_HELP_181200_TEXT');
                    $this->addListElement('ATTACH_HELP_181300_TEXT');
                    $this->addListElement('ATTACH_HELP_181400_TEXT');
                    $this->addListElement('ATTACH_HELP_181500_TEXT');
                    $this->addListElement('ATTACH_HELP_181600_TEXT');
                    $this->addListElement('ATTACH_HELP_181700_TEXT');
                    $this->addListElement('ATTACH_HELP_181800_TEXT');
                    $this->addListElement('ATTACH_HELP_181900_TEXT');
                    $this->addListElement('ATTACH_HELP_182000_TEXT');
                    $this->addListElement('ATTACH_HELP_182100_TEXT');
                    $this->addListElement('ATTACH_HELP_182200_TEXT');
                    $this->addListElement('ATTACH_HELP_182300_TEXT');
                    $this->addListElement('ATTACH_HELP_182400_TEXT');
                    $this->addListElement('ATTACH_HELP_182500_TEXT');
                    $this->addListElement('ATTACH_HELP_182600_TEXT');
                    $this->addListElement('ATTACH_HELP_182700_TEXT');
                    $this->addListElement('ATTACH_HELP_182800_TEXT');
                    $this->addListElement('ATTACH_HELP_182900_TEXT');
                    $this->addListElement('ATTACH_HELP_183000_TEXT');
                    $this->addListElement('ATTACH_HELP_183100_TEXT');
                    $this->addListElement('ATTACH_HELP_183200_TEXT');
                    $this->addListElement('ATTACH_HELP_183300_TEXT');
                    $this->addListElement('ATTACH_HELP_183400_TEXT');
                    $this->addListElement('ATTACH_HELP_183500_TEXT');
                    $this->addListElement('ATTACH_HELP_183600_TEXT');
                    $this->addListElement('ATTACH_HELP_183700_TEXT');
                    $this->endList();
                    $this->addParagraph(
                        'ATTACH_HELP_185000_TEXT',
                        array( '{SECT_CONTCT}' => $this->sectionLink(SECT_CONTCT) )
                    );
                    $this->endSection(SECT_TRANS);

   // ------------------------------------------------------------
   // Acknowledgments
                    $this->startSection(SECT_ACKNOW);
                    $this->addParagraph('ATTACH_HELP_190100_TEXT');
                    $this->startList();
                    $this->addListElement('ATTACH_HELP_190200_TEXT');
                    $this->addListElement('ATTACH_HELP_191000_TEXT', null, false);
                    $this->startList();
                    $this->addListElementLink(
                        'http://www.famfamfam.com/lab/icons/silk/',
                        'ATTACH_HELP_191100_TEXT'
                    );
                    $this->addListElementLink(
                        'http://www.zap.org.au/documents/icons/file-icons/sample.html',
                        'ATTACH_HELP_191200_TEXT'
                    );
                    $this->addListElementLink(
                        'http://www.brandspankingnew.net/archive/2006/06/doctype_icons_2.html',
                        'ATTACH_HELP_191300_TEXT'
                    );
                    $this->addListElementLink(
                        'http://eis.bris.ac.uk/~cckhrb/webdev/',
                        'ATTACH_HELP_191400_TEXT'
                    );
                    $this->addListElementLink(
                        'http://sweetie.sublink.ca',
                        'ATTACH_HELP_191500_TEXT'
                    );
                    $this->endList();
                    $this->addParagraph('ATTACH_HELP_191600_TEXT');
                    $this->endListElement();
                    $this->addListElement('ATTACH_HELP_192000_TEXT');
                    $this->addListElement('ATTACH_HELP_192100_TEXT');
                    $this->addListElement('ATTACH_HELP_192200_TEXT');
                    $this->addListElement('ATTACH_HELP_192300_TEXT');
                    $this->endList();
                    $this->endSection(SECT_ACKNOW);

   // ------------------------------------------------------------
   // Contact
                    $this->startSection(SECT_CONTCT);
                    $email_url = 'mailto:jmcameron&#64;jmcameron.net';
                    $email_url_text = 'jmcameron&#64;jmcameron.net';
                    $email_link = "<a class=\"reference external\" href=\"$email_url\">$email_url_text</a>";
                    $this->addParagraph('ATTACH_HELP_200100_TEXT', array('{LINK}' => $email_link));
                    $this->endSection(SECT_CONTCT);

                    ?>

<a id="tc_toggle" href="<?php echo $this->toggledURL() ?>" title="<?php echo $tlc ?>"><img src="<?php echo $toggle_img ?>"></a>
</div><!-- end div.main -->
</div><!-- end div.document -->