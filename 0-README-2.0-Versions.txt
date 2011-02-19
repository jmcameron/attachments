Versions of Attachments-2.0

2.2 - Released February 6, 2011
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
   - Added a PHP-level constant in both controller.php files to control
     whether the default in add/upload forms is a file or a URL.
   - Cleaned up paths some of the require_once() for helper.php.
   - Various fixes and updates to get add/edit attachments working correctly
     from the article editor on both frontend and backend. 
   - Adjusted the display of add/edit form submit buttons.
   - Converted all <br> or <br/> tags to <br /> to match Joomla usage. 
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
   - Updates to help file.
   - Updated copyright dates
   - Tested on:
       - Kubuntu 10.10 with Joomla 1.5.22, PHP 5.3.3, MySQL 5.1.49
       - Windows Vista SP2 with Joomla 1.5.22, PHP 5.2.9, MySQL 5.1.33
       - Ubuntu 10.04 LTS with Joomla 1.5.18, PHP 4.4.7, MySQL 5.0.51


2.1.2 - Released October 23, 2010
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
   - 17088 downloads

2.1.1 - Released October 10, 2010
   - Fix for attaching to sections or categories whose
     descriptions contain single quotes. (Fixed SQL query.)
   - 2484 downloads

2.1 - Released October 9, 2010
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
   - Tested with Joomla 1.5.21 on Linux and Windows (FF, IE8, Chrome)
   - 216 downloads

2.0.2 - Released May 29, 2010
   * Removed debug output in back end display of attachments
   * 21288 downloads

2.0.1 - Released May 28, 2010
   * A few minor fixes and improvements from the initial 2.0, including fixing
     the pagination for the attachments listing in the admin back end.
   * 225 downloads

2.0 - Released May 1, 2010
   * Release 2.0 is major update.  Attachments 2.0 has been significantly
     refactored and enhanced.  It adds many new features and improvements
     including simplified installation, ability to "attach" URLs, improved
     options to control where attachments are displayed, files are saved in
     separate directories (no more prefixing!), more flexibility to "Who can
     see" and "Who can update" options, unicode handling in filenames,
     significant improvements in the adminstrative back end, and a new
     capability to add attachments to content items other than articles (with
     additional plugins).
   * 5666 downloads


