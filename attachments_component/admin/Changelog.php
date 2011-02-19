<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');
?>
<pre>
Attachments Extension for Joomla 1.5

2011-??: Release 2.2.1
   - Fixed backend so download links on they open in a new window/tab instead
     of replacing the current admin session.

2011-02-06: Release 2.2
   - Fixed regular expression syntax for named groups to be compatible with
     older versons of PHP.
   - Enabled hiding user-defined fields on the front end if the field names
     end with an asterisk.
   - Fixed problems in the back end related to adding attachments with
     user-defined field names and canceling from the add attachments dialog.
   - Implemented Ajax refresh of attachments lists (instead of redisplaying
     the page)! 
   - Enabled editing attachments lists from article/section/category editor in
     backend and in front end during article creation.
   - Fixed long-standing CSS problem in iframes redisplays.
   - Converted all intval() calls to (int) casts.
   - Added a PHP level constant in both controller.php files to control
     whether the default in add/upload forms is a file or a URL.
   - Cleaned up paths some of the require_once() for helper.php.
   - Various fixes and updates to get add/edit attachments working correctly
     from the article editor on both frontend and backend. 
   - Adjusted the display of add/edit form submit buttons.
   - Converted all <br> or <br/> tags to <br /> to match with Joomla usage. 
   - Minor code cleanups.
   - Added cancel buttons to Delete/Remove popup dialogs.
   - Minor fixes and improvements to html in add/upload/edit forms.
   - Fix to get filename displays in error messages to display correctly on
     Windows for alert messages.
   - Fixes to display URLs as absolute URLs without domain or protocol
     prefixes (as most of Joomla does).
   - Fixes for html generated in some of the forms.
   - Added version number display in the back end (bottom right side of main
     display). 
   - Fixes to get editing attachments from article editor working correctly.
   - Updated backend display filter by whether the parent is archived or does
     not exist (added two language items).
   - Improved display of installation notes to make them more visible.
   - Fixes for PHP 4 compatibility.
   - Changed order of "who can see" and "who can add" options so the default
     is first on the lists.  Updated help file to match.
   - Updated error numbers
   - Converted MySQL install script to use table creation option 'ENGINE='
     instead of deprecated 'TYPE=' option.
   - Fixed utility system filename regeneration functions to work better
   - Updated copyright dates
   - Tested on:
       - Kubuntu 10.10 with Joomla 1.5.22, PHP 5.3.3, MySQL 5.1.49
       - Windows Vista SP2 with Joomla 1.5.22, PHP 5.2.9, MySQL 5.1.33
       - Ubuntu 10.04 LTS with Joomla 1.5.18, PHP 4.4.7, MySQL 5.0.51


2010-10-23: Release 2.1.2
   - Fix to insert_attachments_token_btn_plugin and add_attachment_btn_plugin
     to return objects instead of 'false'.  Apparently some versions of PHP
     need this fix, although the versons I tested with do not (PHP 5.3.2).
   - Fixes to get adding URL during article creation to work. 
   - Disabled adding attachments to sections or categores during creation of
     sections or categories.
   - Fixes to get "Who can add" to work correctly when 'Editor' is selected.
   - Fixed overlooked translation item.
   - Fixed 'regenerate system filenames' to actually work with new 2.0 style
     attachment file locations.
   - Added missing closing '/' on img tags for link overlay images.
   - Tested with Joomla! 1.5.21

2010-10-10: Release 2.1.1
   - Fix to SQL query for attaching to sections or categories whose
     descriptions contain single quotes.

2010-10-09: Release 2.1
   - Includes various fixes for PHP 4 servers.
   - Added template path for attachments list that allows template overrides
     to work for lists of attachments.
   - Fixed bug in filtering in back end that was causing blank pages.
   - Fixed bug when editing from the front page with SEF on.
   - Fixed bug that admins could not add attachments when who_can_add is
     no_one, contrary to the documentation.
   - Fix in backend to prevent display of parent swapping buttons when adding
     an attachment to an existing parent. 
   - Fixes for incorrect uses of $mainframe->isAdmin().
   - Got 'Add Attachments' button working when editing Sections and Categories.
   - Fix to get show_attachments_in_editor_plugin working for Sections and 
     Categories. 
   - Added new plugin to insert {attachments} tokens in the back end editor.
   - Tested with Joomla 1.5.21 on Linux and Windows

2010-05-29: Release 2.0.2
   - Removed debug output.

2010-05-28: Release 2.0.1
   - Removed unneeded static variable in line 25 of
     attachments_for_content/plugins/com_content.php to fix
     problems with PHP 4 servers.
   - Added 'text-align: left' to CSS for attachments upload forms from the
     front end so that 'text-align: justity' in some templates will not mess up
     the upload form.
   - Minor improvements to plugin manual.
   - Fixed some RTL display issues.
   - Fixed file_types.php to prevent errors when unkown file types are
     encountered. 
   - Refactored main admin list code and fixed pagination problem.
   - Updated lists of translators in help file.
   - Another minor fix to main admin list pagination code.
   - Tested with Joomla 1.5.17

2010-05-01: Release 2.0
   - Small adjusmentment of download code to make it easier to insert 
     a string into the name of the downloaded file.
   - Added new option to show the username of the uploader.  Thanks 
     to John Aspect for discussions and testing.
   - Minor adjustments to the upload and update forms to allow longer
     description strings (up to 255 characters).
   - Changed default display to admin/back end display to only show
     attachments for articles that are current and not removed (in
     content trash).   Also added link to display all attachments.
   - Refactored to deal with "articles" as parents.  Added new 
     Attachments plugin framework attachments_extensions to allow 
     attachments to be added to new types of content entities.
     Implemented Attachments plugins for com_content (articles).
   - Updates to allow adding attachments to articles during creation
     from the administrative back end.
   - Implemented Attachments plugin for QuickFAQ items as a test case.
   - Changed the type of the MySql table to be UTF8 compliant.
   - Converted most JRequest::getVar() calls to getInt, getString, etc.
   - Added sorting list by display filename.
   - Move the existing attachment list in the front end upload form
     after the form so that the form is at the top when the list
     attachments is long.
   - Updates to allow adding attachments to articles during creation
     on the front end (during article submission).
   - Added admin utility command to update file sizes.
   - Converted all string functions to use corresponding JString
     functions to improve unicode handling.  Tested with filenames
     with unicode characters and it seems to work.  More testing is
     needed by international users.
   - Fixed code that updates old versions of the attachments table to
     correspond to the new table definitions during the installation
     process.
   - Converted all messages in componenent installation (update.php)
     to use Joomla! translation functions. 
   - Improvement of router.php to make it a bit more efficient.
   - Implemented new model, view, and controller (attachments) for
     lists of attachments.
   - Added support for Open XML / Office 2007 document types.
   - Switched all attachments list construction to use the new
     attachments MVC code. 
   - Added 'No one' to option 'Who can see attachments'.  Added check
     to prevent downloading in this mode (except for Admin).
   - Added 'No one' to option 'Who add attachments'.  Prevents display 
     of the "Add attachments" link in the front end.
   - Deleted obsolute permissions.php file and all references too it.
   - Fixed bug in admin toolbar New button.
   - Fixed bug in admin upload form (missing user defined fields).
   - Renamed admin "Admin" commands to "Utilties", ie administrative
     utility commands. 
   - Added option to suppress obsolete attachments (attachments whose 
     parent is not published).
   - Major refactor to allow attaching attachments to entities other than
     content articles.	Working for sections and categories.  Also working
     with the QuickFAQ extension.  Thanks for feedback on the design of this
     refactor from Ewout Weirda.
   - Fixed bug that was preventing the display of existing attachments when
     adding attachments during article creation on the front end.
   - Implemented combined installer that installs all plugins with the
     component and uninstalls the plugins when the component is installed.
     Changed all installation files to use 'method="upgrade"' so that
     installations can be upgraded instead of having to uninstall and
     reinstall. Thanks to Manuel Ayala for suggesting how to do this.
   - Extensive modificaitons to add the ability to attach URLs.
   - Updates to the admin back end to show error messages in red.
   - Display file/url size of less than 1Kb in tenths of 1KB (front end).
   - Adjusted the CSS for front end and back end forms to improve formatting.
   - Moved Admin Utilities HTML code from controller.php to its own view.
   - Updated MySql table updating for recent column changes.
   - Added option and code to display the list of attachements at any desired
     location in the article (or other supported entity). 
   - Added Admin Utils command to remove all spaces from filenames.
   - Added another div around the attachments list and add attachment button
     (attachmentsContainer) to provide smarter formatting.  Also used new div
     to fix display problem in ja_purity template (with attachments at top).
   - Replaced Open Office icons with ones from Yuuyakeopen (see
     http://eis.bris.ac.uk/~cckhrb/webdev/)
   - Updated Open XML document icons (same as MS Office icons with added X).
   - Updated release number to 1.3.9 (plan to release as 1.4.0).
   - Finished cleaning up translation items in uninstallation script.  Fixed
     minor bug during uninstallation (was not properly warning users to delete 
     the attachments subdirectory).
   - Added a wrench to the Admin Utils toolbar button icon.
   - Renamed Admin Utils icon filename.
   - Added code to handle proper display when caching is enabled. Tested with
     Linux and Windows servers with and without caching.
   - Added system plugin to display the attachments list in the article
     editor. 
   - Changed many JURI::base() calls to JURI::root() calls so that calls from
     admin work correctly.
   - Updates to admin to allow selection of entity items when creating an
     attachment from the back end. 
   - Added ability to sort list of attachments in the back end by column.
   - Updates to admin to allow switching attachments between parent types 
   - Implemented checks to prevent "Add Attachment" button from the front end
     if "who can add" attachments option is set to "No one".
   - Fixed what is stored as 'parent entity' in the attachments table to be
     the proper name (not 'default').  This improves the sorting while listing
     attachments in the back end.
   - Moved help button to the right end of the toolbar (in the admin) to make
     it more consistent with other components.
   - Fixed attachments plugin code to ignore disabled attachments plugins. 
   - Fixes to get installation over version 1.3.4 to work cleanly.
   - Reworked Attachments component parameter editor and created security mode
     updates when the security mode or attachments directory are updated.
   - Updated help file and switched to Restructured Text.
   - Cleaned up all error messages and added unique error numbers.
   - Added 'List parents for' drop-down menu in the back end to control
     whether to list attachments for unpublished parents.
   - Added a second drop-down menu to limit the back end list to parents of
     specific types (entities).
   - PHP Documentation cleanup.
   - Fixed search to ignore attachments for parents that are unpublished.
   - Extended custom titles to work with non-article content items.
   - Changes to fix iframe redisplay formatting problems.
   - Updated 'Hide Attachments for' option to deal with various types of
     content items.
   - Added 'blog' as another option for 'Hide attachments for' option.
   - Updated release number to 2.0 for release
   - Fixed oversight in search to prevent displaying attachments in search
     results if the user should not be able to see the parent.
   - Updated the attachments_component installation XML file to use <folder>
     option to simplify the file.
   - Moved plugin creation manual to main help directory to simplify handling
     of translations.
   - Updates to help file (updated translators, etc).
   - Fixed problem saving changes in secure mode when both file and url
     attachments exist. 
   - Added admin utility command to convert from old-style (v1.3.4)
     attachments (with prefixes) to new-style attachments (in
     separate subdirectories for each article/parent).	
   - Tested with Joomla! version 1.5.15 and 1.5.17.

2008-08-17: Release 1.3.4
   - Fixed plugin so that only items that are com_content will display
     attachments.  This should prevent spurious display of the
     "Add Attachments" link on items/pages that cannot have attachments
     associated with them (such as JEvents pages).
   - Added ALT tag to all <img> HTML entries for xhtml compliance.
   - Fixed add_attachment_btn_plugin to prevent display of the
     "Add Attachment" button in the article editor for non content
     extensions such as JEvents.  

2008-07-22: Release 1.3.3
   - Tested with Joomla! versions 1.5.3 and 1.5.4.
   - Several mods to allow attachments to be listed (if 'anyone' can see)
     even in secure mode.  Added "Request login" page that is shown when
     someone that is not logged in tries to download an attachment.  Added
     component component parameter for registration URL and several related
     translation phrases.
   - Reworked tooltips for Admin page to use behavior.tooltip correctly.
   - Added metadata.xml files to two front-end views to warn users not
     to attach them to menu items.
   - Added a install.noutf8.sql install file for MySQL for when the MySQL
     database does not support UTF-8 directly but is inserting up to three
     bytes per UTF-8 character into the database.
   - Added search plugin along with new language files.	 This plug was
     donated by Paul McDermott.	 Thanks Paul!
   - Enabled sorting attachments lists by custom/user-defined fields.
   - Minor rework of CSS for icons for attachments and add attachments.
   - More minor html fixes.
   - Fix to prevent show the 'Add Attachments' button when creating/editing 
     section and category descriptions.
   - Fix to prevent the 'Add Attachments' button from showing if the 
     attachments list is prevented from showing (even if the user has
     permissions to add attachments).
   - Got rid of all prepending on filenames.  Now all attachment files live
     in subdirectories based on the parent entity and ID.

2008-06-08: Release 1.3.2
   - Minor fixes to html in a couple of forms.

2008-06-03: Release 1.3.1
   - Minor fixes to a few translation terms that were not handled
     correctly in the PHP code.

2008-06-01: Release 1.3
   - Updated special functions.
   - Minor tweaks to html for testing purposes.
   - Enabled display of "Add Attachments" button when article
     is being created.	If clicked, it complains that you must 
     save the article first using a iframe popup.  Added
     warning function for that purpose.
   - Removed some obsolete translation phrases.
   - Added Italian translation (Thanks Lemminkainen!)
   - Added create_date and modification_date to the SQL table.
   - Added option to control listing order of attachments list.
   - Added indeces for some of the fields of the attachments table
     that might be commonly used to order attachments lists.
   - Add different shading on alternate lines of attachments list.
   - Added three optional user-defined fields.
   - Converted article selection to use Joomla! article selector (in
     creation of new attachments and for changing articles).
   - Added admin functions to disable uninstallation of attachments 
     table (for MySQL).
   - Created new update.php file in admin area and set up an "Extras"
     toolbar item to allow access to its functions.  First function
     added invokes the function to disable uninstallation of the
     attachments database table.
   - Added code in installation script to update the Attachments table
     (adds missing columns and missing indexes.)
   - Restored the Attachment logo in the back end.
   - Added file type icons for mp3, wma, and aiff files.
   - Changed display of modification date to include Locale (eg time
     zone offset).  Note that this change required changing the format
     strings to use those of the PHP strftime() function.  Updated
     configuration settings (config.xml) to reflect this change.
   - Fixed code that allowed any user to delete other folks attachments
     (in the 'who can add' == 'any logged-in user' mode).  Now anyone
     who owns an article can delete any attachment on that article.
     Also, now anyone who added an attachment can delete it.
   - Changed the red 'X' to delete an attachment in the attachments lists
     to a delete icon (from silk icons).
   - Adjusted all default time displays to 24h format to avoid problems
     with some locales not supporting am/pm out of the box.
   - Added updating of the download count in secure mode.  Added display
     of number of downloads in the front end lists with config option to
     display or hide the download count.
   - Added fix for uploading files in FTP mode.	  Converted all file 
     manipulation functions from php functions to JFile functions (which)
     use FTP if it is enabled.
   - Attachments plugin changed from onAfterDisplayContent event to 
     onPrepareContent event.  This ensures that it respects the ordering
     requested for plugins (which are apparently mostly onPrepareContent).
   - Reworked permissions checking: Put all permissions function into a 
     separate file (site/permissions.php).   Got rid of 'verify_*' functions
     in helper.php in favor of similar functions in permissions.php.
   - Converted all require() to require_once()
   - Implemented code to update attachments from the frontend.
   - Converted most Javascript alert()/confirm() dialogs to use
     non-Javascript approaches such as reloading the forms, raising
     an http error (code 500), or dialogs/warnings via iframes.
   - Added views for uploading and updating.  Now the file for an attachment
     can be upgraded from the front end or back end.
   - Minor rework to get update_null_dates() working from both the 
     special controller and from the installation script.
   - Changed processing of 'hide attachments for' option to allow
     either '_' or '-' characters between words.
   - Reduced the extra MySQL indeces to only 'article_id' (MySQL adds an
     index for 'id' automatically).  According to MySQL documentation,
     the other indeces will give no benefit.  See:
     http://dev.mysql.com/doc/refman/5.0/en/order-by-optimization.html
   - Added quotes to download Content-Disposition to make sure
     downloading filenames with spaces works in secure mode (does not
     affect non-secure mode).
   - Various fixes from feedback from the testers.
   - Tested with Joomla! 1.5.3 (Vahi))

2008-03-06: Release 1.2.3
   - Added French tranlation by Pascal Adalian (Thanks Pascal!)
   - Added special controller/view with some special functions in the 
     Administrative back end to assist with automated testing of the 
     Attachments extension.  For the most part, these functions duplicate
     functionality available in other admin locations, without Javascript.
     (No new user or admin functionality added; normal user and admin 
     functions are unaffected.)
   - Fixed a small bug in back end to prevent attachments from being
     attached to articles that are in the trash.
   - A few minor tweaks to the English version of the help file.

2008-02-24: Release 1.2.2
   - Bug fix for sites with multiple/non-standard MySQL setups
   - Minor updates to Dutch and Norwegian language files.

2008-02-21: Release 1.2.1
   - Minor adjustments to the Brazilian Portuguese language files.

2008-02-20: Release 1.2
   - Added German translation by Michael Scherer (Thanks Michael!)
   - Added Finnish translation by Tapani Lehtonen (Thanks Tapani!)
   - Added Spanish translation by Carlos Alfaro (Thanks Carlos!)
   - Added Norwegian transaltion by Espen Gjelsvik (Thanks Espen!)
   - Added code to chmod new attachments to '644' (owner read-write, group+world
     read privileges).	This is needed on a few website server configurations,
     and it should harmless on the rest.
   - When an article is moved to another article in the attachment editor
     (in the back end), if the prepend mode is 'article_id', the filename_sys
     and url are renamed to avoid future filename conflicts.
   - Cleaned up all the help files to have valid HTML (using W3C validator).
   - Added fix to SQL code in admin controller.php (LEFT JOIN instead of JOIN).
     This fix seems to keep some older versions of MySQL happy.
   - Fixed jimport bug in admin.attachments.html.php (only affected a few systems).
   - Moved options from the plugin manager to the component manager.  Adjusted
     various language files accordingly; integrated updates from all translators.
   - Fixed some minor HTML problems in front end and back end displays.
   - Scrubbed all *.ini files to replace double-quotes with single quotes to
     avoid problems with tooltips, etc being truncated.
   - Tested with Joomla! 1.5.1 (Seenu)

2008-01-26: Release 1.1
   - Added optional display filename
   - Added max_filename_length plugin parameter to limit displayed names of files.
   - Added file type for *.cab files (Windows compressed archive files).
   - Updated the help file for added database field and plugin option.
   - Updated translation files with new entries.
   - Added new Brazillian Portuguese version.  Thanks to
       Arnaldo Giacomitti ( www.giacomitti.eng.br) and
       Cauan Cabral (www.cauancabral.net) for the translation!
   - Fixed bug that caused failures when using the "Add Attachment" button while
     editing articles from the front end.
   - Disabled display of "Add Attachment" button when creating new article.
   - Changed "Add Attachment" button to bring up the uploading form in an iframe
     so that the article editor is undisturbed (both front end and back end).
   - Add the ability to change the article of an attachment through the
     attachment editing form in the back end (look for [Change Article]).
     NOTE: This version does not rename the actual file, so unexpected filename
     conflicts are possible.  This will be fixed in the next version.
   - Got the spell checker working on my editor and fixed numerous typos.
   - Fixed bugs in categories_to_hide mode.
   - Added CSRF Token checking in forms
   - Fixed uploading from article editor to properly use only the form
     in the iframe and not the whole page.
   - Removed extraneous semicolon in admin upload form.
   - Tested with Joomla! 1.5.0 Stable (Khepri).	 Also did some spotchecks with RC3 
     and RC4.  SEF is still broken with RC2.

2007-12-27: Release 1.0
   - Many updates, changed status from Beta to Stable/Production
   - Added file icons in attachments lists
   - Added new Dutch/nl-NL translation by Parvus (Thanks Parvus!)
   - New 'add_attachment_btn_plugin' allows adding attachments using a new 
     button in the article editor in the administrative back end.
   - Added list of previously added attachments at the top of all upload pages.
   - Added option to open file links in a separate window
   - Added customizable message to upload page that alerts the user that an
     administrator will need to publish an attachment before it is accessible 
     (if not auto-publishing).
   - Added link for stylesheet for the upload form
   - Reworked the html/css structure for the attachments lists to make it easier
     for users to restyle it.
   - Add attachments sytle name to plugin parameters 
   - Restructured the CSS for the main backend list of attachments
   - Cleaned up duplication in CSS files.
   - Added icon to 'Add Attachment' link
   - Made description field longer (255)
   - Added option to show column heads for attachment list tables
   - Added icon to 'Add Attachment' links in backend
   - Added icon for download icon in back end
   - Added display of the uploaders name in the admin back end edit page
   - Added tooltips/titles for main attachments list links (filename, delete)
   - Fixed attachment menu icon in "Components" menu in backend
	 (see http://forum.joomla.org/index.php/topic,221525.0.html)
   - Added plugin option to specify Sections/Categories of articles for which
     the attachments list will not be displayed.
   - Fixed bug in editing the attachment info from the back end (wrong lookup
     of article title.)
   - Fixed problem with SEF mode in RC4.
   - Disabled display of attachments list when in secure mode and the user is
     not logged in.  
   - Tested with Joomla! 1.5 RC4 (Karibu)

2007-11-09: Release 0.9.9e Beta
   - Sorted all lines in the translation files into
     alphabetical order.
	 
2007-11-08: Release 0.9.9d Beta
   - Put Chinese help files into the component
     install file manifest.
   
2007-11-08: Release 0.9.9c Beta
   - Resaved language files in UTF-8 without BOM.

2007-11-07: Release 0.9.9b Beta
   - Added Chinese translations for new options,
     thanks to baijiangpeng (www.joomlagate.com).
   - Minor formating fix on illegal file extension
     and mime type dialog boxes.

2007-11-06: Release 0.9.9 Beta
   - Fixed bug in plugin parameters XML install file that
     prevented plugin parameters from being changed in 
     some Apache/PHP/MySQL combinations.  Many thanks
     to David Alabaster for helping track down this
     tricky bug!
   - Added note in error messages and other locations
     how to change the legal file/mime types (via
     the Media Manager settings).
   - Added plugin options turn off display of attachment 
     descriptions and file sizes.
   - Includes Chinese translations (except for the new options
     added in this release).
   - Tested with Joomla! 1.5 Beta RC3 (Tarkriban)

2007-10-28: Release 0.9.8c Beta Chinese
   - Adds Chinese translation (Traditional and Simplified).
     Note that the help file has not been translated yet.
     Many thanks to baijianpeng for the chinese translations
     (http://www.joomlagate.com)!

2007-10-27: Release 0.9.8b Beta
   - Improved help page regarding errors uploading file
     types not permitted by the Media Manager.

2007-10-26: Release 0.9.8 Beta
   - Added language capability, including initial English
     translation.   Includes a small hack to get the plugin
     language files to work on the front end.
   - The component install script now checks to see
     whether the plugin is installed and prints more
     apropos suggestions during installation.
   - Changed name 'Replacement' titles lists of articles for 
     specific articles to 'Custom' titles.
   - Tested with Joomla! 1.5 Beta RC3 (Tarkriban)

2007-10-21: Release 0.9.7 Beta (Prerelease)
   - Added secure storage and downloading of attachments.
   - Added checks to prevent uploading of file types that are
     not allowed by the media manager.
   - Updated the display of attachments in the administrative
     back end to make it look nicer.
   - Added option to control whether the downloads are done
     in 'inline' mode or in 'attachment' mode.
   - Consolidated download functions between front end and 
     backend so the administrator can download files from
     the attachments display list.
   - Tested with Joomla! 1.5 Beta RC3 (Tarkriban)

2007-10-20: Release 0.9.6 Beta
   - Fixed call-as-reference bug in two locations.
   - Fixed improper setup in add() function admin controller.php
   - Moved site files to their own folder in the component install
     zip file.
   - Added empty index.html files to all directories to eliminate 
     browsing.
   - Tested with Joomla! 1.5 Beta RC3 (Tarkriban)

2007-10-13: Release 0.9.5 Beta
   - Fixed bug in back end for the display of articles.	 Now all
     attachments for the same article are always grouped together.
   - Added ability to add new attachments in the administrative back end.
     Consolidated some of the upload code between the front and back ends.
   - Tested with Joomla! 1.5 Beta RC3 (Tarkriban)

2007-10-09: Release 0.9.4 Beta
   - Added check for attachments upload directory before every 
     upload.  This eliminates the need to create the directory 
     during installation and removes the need to install the 
     component and plugin in any particular order.
   - Removed the check to make sure articles are published
     before allowing attachments to be added.  This means
     articles can be created on the back end and the author
     can add attachments on the front end before the article
     is published.
   - Changed description delimiter to square brackets.
   - Tested with Joomla! 1.5 Beta RC3 (Tarkriban)

2007-10-06: Release 0.9.3 Beta
   - Cleaned up some minor issues on URL/SEF processing.
   - Fixed URL problems in RC3.
   - Tested with Joomla! 1.5 Beta RC3 (Tarkriban)

2007-10-01: Release 0.9.2 Beta
   - Added help page to back end
	(Components > Attachments > help button)
   - Added check to prevent overwriting files with the same name
   - Added prefix before system filenames for uploaded attachments.  
     The default is the article ID prefix. The user can also choose 
     the attachment ID prefix or no prefix.
   - Cleaned up permission checking.  Now administrators can always add
     attachments regardless of their username.	Added more validation
     before saving articles to prevent non-logged in users from adding
     attachments via URL exploits.
   - Added way for the author of an article (or admin) to delete 
     attachments from the front end.  Permissions are checked carefully
     first.
   - Added SEF links for attachment upload and delete.
   - Added minor graphical improvements to to the back end interface
   - Tested with Joomla! 1.5 Beta RC2 (Endelo)

2007-09-24: Bug fix release 0.9.1 Beta
   - Fixed bug in timestamp
   - Tested with Joomla! 1.5 Beta RC2 (Endelo)

2007-09-23: Initial release, version 0.9 Beta
   - Tested with Joomla! 1.5 Beta RC2 (Endelo)
</pre>

<?php
// Local variables:
// mode: text
// End:
?>
