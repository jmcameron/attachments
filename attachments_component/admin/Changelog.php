<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');
?>
<pre>
Attachments 3.x Extension for Joomla 2.5+

2013-11-15: Release 3.1.3 (Joomla 3.2 Compatibility Release)
    - 2013-09-21 Allow display of attachments lists in editors for non-article
    - 2013-10-13 Fixed migration importer handle obsolete 'section' attachments
    - 2013-10-27 Fixed bug in 'Show attachments to non logged-in users' option
    - 2013-11-11 Fixed issue with displaying options with Joomla 3.2.

2013-08-14: Release 3.1.2 (Security Release)
    - 2013-07-24 Fixed bug in max_attachments_size handling.
    - 2013-07-25 Added line numbers to error messages for import (for migration).
    - 2013-07-26 Fixed issue in main attachments plugin that may have caused
	    warnings (although it operated correctly).
    - 2013-07-26 Fixed issue with add-attachment button not showing correctly
	    in Joomla 3.x.
    - 2013-07-26 Robustness fixes for attachment plugins.
    - 2013-07-27 Enabled scrolling on upload forms to ensure that all existing
	    attachments can be seen, even when there are many.
    - 2013-07-27 Fixed issue with missing 'KB' language item in attachments list.
        Changed 'KB' to 'kB' to match Wikipedia 'kilobyte' article
    - 2013-07-30 Minor fixes for robustness
    - 2013-07-31 Added fix to display attachments for non-menu category blog views.
	- 2013-08-02 Fix to return user logging in to the prior page.
	- 2013-08-14 Fix security issue with PHP files qqq.php.  (note trailing period)

2013-07-11: Release 3.1.1 (Security Release)
    - 2013-07-11 Prevent uploading image file exploits (Security Fix for VEL)

2013-06-08: Release 3.1
	- 2012-08-18 Added attachments list sort option: description (reversed, z-a).
	- 2012-09-08 Several updates to support upgrade to Joomla 3.0:
	   - Updated deprecated classes/functions (JRules->JAccessRules,
		 user->authorisedLevels(), user->getAuthorisedViewLevels()).
	   - Converted all 'X' toolbar items to non-X versions (eg, addNewX->addNew,
		 editListX->editList, deleteListX->deleteList) for future compatibility with
		 Joomla 3.0.
	   - Converted several Joomal classes to legacy version for future compatibility
		 for Joomla 3.0 (JModel->JModelLegacy, JController->JControllerLegacy,
		 JView->JViewLegacy).
	   - NOTE that these legacy classes only work with Joomla 2.5 or later, so
		 earlier versions of Joomla are not longer supported (including Joomla 1.7).
	- 2012-10-13 Added in missing translation term for error messages.
	- 2012-10-17 Fixed issue in display of insert_attachments_editor editor button.
		(Primarily affected joomlaCK editor.)
	- 2012-10-25 Added optional display of 'Created' date in front end displays
		of attachments lists.	Changed modification date format to be date format
		since it now applies to both creation and modification dates.
	- 2012-11-10 Fixed many permissions issues for backend users with limited
		permissions.
	- 2012-11-10 Renumbered all error numbers.
	- 2012-11-10 Added extra code to handle legacy classes for Joomla version 2.5+
		and earlier.  (The new legacy classes were introduced in Joomla version 2.5.6)
	- 2012-11-11 Cleaned up some permissions issues with adding/editing attachments
		in the backend by non-super-user.
	- 2012-11-11 Changed all JError::raiseWarning() to JError::raiseError().
		Apparently, raiseWarning is not well supported by Joomla.	Switched the
		permissions related errors to 403 in the backend for nicer error handling.
	- 2012-11-12 Improved config variable (show_creator --> show_creator_name).
	- 2012-11-12 Fixed stylesheet issue in editor by moving stylesheet additions 
		from show_attachments::contentAfterRender() to add_attachment::onDisplay()
		since afterRender is too late to add stylesheets.
	- 2012-11-12 Fix to show_attachments to handle various article editors better.
	- 2012-11-16 Added maximum attachment file size limit option.  Check actual file size
		when uploaded to ensure that is not larger than the attachments limit or the PHP
		upload size limit.
	- 2012-11-22 Cosmetic code cleanups in attachments_plugi/attachments.php.
	- 2012-11-30 Added support for downloading files with mod_xsendfile (if available).
	- 2012-12-05 Implemented fix for downloading large files by send the file in 8K chunks.
	- 2012-12-16 Various updates, fixes, updates and tweaks for Joomla 3.0 compatibility.
		Updated date display format string to use JDate::format function syntax.
		(This is a work in progress; still some rough edges.)
	- 2012-12-16 Switched all JHTML to JHtml everywhere.
	- 2012-12-30 Added options for sorting my 'filename descending' and 'display name descending'.
	- 2013-01-05 Updated documentation for date display format string.
	- 2013-01-24 Fix bug that cased problems when there are spaces in the site base URL.
	- 2013-02-22 Updates to catch up with Joomla deprecations (eg JRequest::checkToken()).
	- 2013-02-22 Fixed display of Attachments options in Joomla 3.x.
	- 2013-02-22 Got rid of old references to Joomla 1.7 (no longer compatible).
	- 2013-02-22 Cosmetic improvements to frontend update/update dialogs.		
	- 2013-03-01 Added ability to display non-public attachments to non-logged in users
		but require they log in before actually being able to access the attachments.
	- 2013-03-05 Fixed issue creating attachment for an article being created in Joomla 3.x.
	- 2013-03-06 Fixed improper handling of legacy classes in Joomla 2.5.x.
	- 2013-03-06 Split legacy.php into separate files for each class (for efficiency).
	- 2013-03-19 Better handling of downloads for MS IE browsers.
	- 2013-03-29 Safer db storage and html display for display_filename and description.
	- 2013-03-30 Abort installation if debris from failed install exists
		 (to make installation more robust for Joomla 3.x)
	- 2013-03-31 Handle URLs in secure mode better (do not expose URL).
		 Thanks to Daniel Guidry for the idea for this fix!
	- 2012-04-02 Added paperclip icon in backend Attachments manager page for Joomle 3.x.
	- 2012-04-05 Refactored css/js files to use central media/com_attachments folder.
	- 2012-04-05 Fixed bug that was exposing registered attachments in non-secure mode.
		(Introduced in the 2013-03-01 mod to display links for non-public attachments.)
	- 2012-04-05 Minor cosmetic adjustments to form displays in frontend and backend.
	- 2012-04-06 Refactored handling of image files to use central media folder
		to allow template overrides of images.
	- 2012-04-12 Converted admin utils view to use template (so it can be overridden).
	- 2012-04-12 Removed several unnecessary imports (for views and controllers).
	- 2012-04-13 In admin edit form, only show attachments if updating file.
	- 2012-04-13 Fixed missing editor add-attachment/insert-token button icons for Joomla 3.
		Other small tweaks to the CSS files for the 'add attachment' link.
	- 2012-04-13 Added quickicon plugin for both Joomla 2.5 and 3.x.
	- 2012-04-19 Converted help page to use a view with template and translation tokens.
	- 2012-05-03 Refactored/cleaned up add/edit/update/upload views and controller/helper
	   code that invokes the views.
	- 2012-05-04 Refactored and straightened out onPrepareContent callbacks to use fixes
	   in Joomla 2.5.10+ and Joomla 3.1+.
	- 2012-05-05 Small improvements to help view coding.
    - 2013-05-14 In admin add dialog, moved alt parents selector to top right.
	- 2013-05-15 Policy change: let super-user see attachments for all access levels.
    - 2013-05-17 Updated help page for Attachments 3.1 release.
    - 2013-05-19 Cleaned up code for attachments plugin framework and attachments_for_content.
	- 2013-05-31 Fixed a few minor issues that came up in testing.  
		Tested on Joomla 2.5.11 on Linux (Firefox), Windows (Firefox, IE, Chrome).

2012-09-07: Release 3.0.4
	- 2012-09-07 Fixed bug causing crashes when component (only) is uninstalled.

2012-08-11: Release 3.0.3
	- 2011-09-27 Fixed errors in the English version of the help file
	- 2011-10-30 Changes access/view level dialog to show all access levels to Super-User.
	- 2011-10-30 Fixed issue that caused the backend to crash when users disabled the framework plugin.
	- 2011-10-31 More updates to make things fail more gracefully if the framework plugin is disabled.
	- 2011-12-01 Fix to prevent incorrectly displaying attachments for creating article from category layout.
	- 2012-02-10 Added trim() function to import code to clean field names from CSV files.
	- 2012-02-10 Minor translation fix to search plugin code.
	- 2012-02-11 Fix pagination so that is remembers the limit start.
	- 2012-02-14 Fixed bug in handling errors when checking URLs.
	- 2012-02-23 Cleaned up quoting in DB calls.
	- 2012-03-08 Added jimports for JController to a few files.
	- 2012-03-09 IE-specific fix for downloading filenames with special characters in Internet Explorer.
				 Thanks to crassus168 (chris@gamehit.net) for suggestions for this fix.
	- 2012-03-10 Fixed handling of showing attachments for editing articles
				 from category blog and category list.
	- 2012-03-10 Updated minor translation fix to search plugin code to resolve issues in Joomla 2.5+.
	- 2012-03-19 Fixed issue with displayString refactor (front end upload failing).
	- 2012-03-23 Fixed bug in sorting by Creator name in backend attachments list.
	- 2012-03-23 Fixed issue with redisplay after editing/deleting attachments from
				 category blog view on front end.
	- 2012-03-23 Suppress extra info messages during installation.
	- 2012-04-01 Fixed bug when adding URLs during creating an article from front end.
	- 2012-04-03 Fix to make sure that pre-existing orphaned attachments are displayed
				 when creating an article.
	- 2012-04-06 Removed code to translate access levels.  Apparently they are not translated!
	- 2012-04-06 Added code to warn user there are bad attachments (eg, ones whose parents are uninstalled).
	- 2012-04-17 Fixed several 'strict PHP' issues.
	- 2012-04-20 Fixed issue with the display of attachments for categories.
	- 2012-04-20 Fixed issue with attachments list display in editor displaying badly when the
				 'toggle editor' button is used.
	- 2012-05-07 Fixed issue with missing translation items.
	- 2012-05-11 Added trunction of filenames if longer than the filename field in the database.
	- 2012-05-13 Added warning messages when a filename is truncated.
	- 2012-05-13 Updated behavior: Do not kill display-name when updating/replacing a file.
	- 2012-05-14 Fixed issue with adding attachments while editing an article (in category list/blog)
	- 2012-05-15 Updated most of the error numbers, added a warning about potential templates
				 problems in the category blog view
	- 2012-05-16 Updated the CSS rules for the attachments display to be more robust.
	- 2012-08-04 Fixed frontend display of category attachments using regular onContentPrepare event.
	- 2012-08-10 Updated minimum supported version of Joomla to 1.7.
				 Generalized error message when trying to install Attachments on an old/unsupported
				 version of Joomla.
	- 2012-08-11 Tested with Joomla 2.5+.6 on Linux and Windows.

2011-09-17: Release 3.0.2
	- 2011-09-12 Improved display of frontend upload/update forms
	- 2011-09-17 Fixed bug in delete dialog while editing article on front end
	- 2011-09-17 Fixed save2New issue for Joomla 1.6.x

2011-09-09: Release 3.0.1
	- 2011-09-06 Fixed bug in migration import code that prevented proper error
	  messages when imports fail (eg, file not found, etc).	 Changed the dry_run
	  success message to show number of attachments found in CSV file
	- 2011-09-07 Fixed error in token IDs for admin unpublish messages
	- 2011-09-08 Added Save+New button in admin form to add attachments
	- 2011-09-09 Added missing error numbers in import code

2011-08-28: Release 3.0
	- Derived from unreleased 2.3
	- Significant refactoring, reimplementing, new features, cleanups, etc.
	- Testing several RC versions by over 140 testers

</pre>
