<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');
?>
<pre>
Attachments 3.x Extension for Joomla 1.7/2.5+

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
   - 2012-03-10 Updated minor translation fix to search plugin code to resolve issues in Joomla 2.5.
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
   - 2012-08-11 Tested with Joomla 2.5.6 on Linux and Windows.

2011-09-17: Release 3.0.2
   - 2011-09-12 Improved display of frontend upload/update forms
   - 2011-09-17 Fixed bug in delete dialog while editing article on front end
   - 2011-09-17 Fixed save2New issue for Joomla 1.6.x

2011-09-09: Release 3.0.1
   - 2011-09-06 Fixed bug in migration import code that prevented proper error
     messages when imports fail (eg, file not found, etc).  Changed the dry_run
     success message to show number of attachments found in CSV file
   - 2011-09-07 Fixed error in token IDs for admin unpublish messages
   - 2011-09-08 Added Save+New button in admin form to add attachments
   - 2011-09-09 Added missing error numbers in import code

2011-08-28: Release 3.0
   - Derived from unreleased 2.3
   - Significant refactoring, reimplementing, new features, cleanups, etc.
   - Testing several RC versions by over 140 testers

</pre>
