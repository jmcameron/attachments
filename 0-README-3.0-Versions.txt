Versions of Attachments-3.0 for Joomla 2.5 and 3.x

2018-03-26: Release 3.2.6
   - 2017-05-27 Fixed category attachments for 3.7+ changes
   - 2017-05-28 Another fix for category article list menu item
   - 2017-07-12 Fixed issue with article selection
   - 2017-07-13 Fixed issue with admin attachment list popups not working
   - 2018-03-25 Added fixes to clean user inputs (fix SQL injection vulnerability)
   - Tested with Joomla 3.8.6

2017-04-30: Release 3.2.5
   - 2016-05-13 Fix for problem with delete buttons
   - 2017-04-27 Fix for Jooma 3.7.0
   - Tested with Joomla 3.6.5 and 3.7.0
   - 50066 Downloads

2016-05-07: Release 3.2.4
   - 2015-09-05 Fixed admin [Reset Order] button
   - 2015-10-11 Fixed issue with uploads in Joomla 3.4.4
   - 2016-04-15 Fixed showing attachments in TinyMCE editor
   - 2016-04-20 Fixed modal issue with TinyMCE on J3.5.1 on backend
   - 2016-04-24 Fixed modal issue with TinyMCE on J3.5.1 on frontend
   - Tested on Joomla 3.4.8 and 3.5.1
   - 62311 Downloads

2015-03-20: Release 3.2.3
   - Fixed issue with category blog lists for Joomla 3.4
   - Fixed article com_media confusion issue for Joomla 3.4
   - Tested with Joomla 2.5.28 and 3.4.0
   - 81225 Downloads

3.2.2 - Released March 2, 2015
   - 2015-02-20 Fixed some small issues related to supporting Attachments for JEvents
   - 2015-02-22 Fixes for add/insert attachments editor button icons
   - 12271 Downloads

3.2.1 - Released February 15, 2015
   - 2015-02-15 Fixed problem with filenames of the downloaded files
   - 7441 Downloads

3.2.0 - Released February 1, 2015
   - 2014-01-18 Adjustment to migration importer to better handle UTF-8
   - 2014-01-19 Fix to disable attachments display on category list title (J3)
   - 2014-01-26 Fix to migration importer to strip of any leading BOM
   - 2014-01-26 Fix for icon on editor-xtd add attachment button (J3)
   - 2014-05-30 Fixes to be more robust to partial/failed uninstalls
   - 2015-06-08 Disable all attachments plugins if component is uninstalled.
       This should prevent problems when incorrectly uninstalling only the 
       component. To uninstall correctly, uninstall the Attachments Package;
       it will uninstall the component and all plugins.
   - 2014-08-22 Refactored displaying attachments lists in editors
   - 2014-08-23 Disable all attachments plugins if uninstalling
       the Attachments content plugin, the Attachments plugin framework 
       plugin, and the Attachments for content plugin. See 2014-06-08 note.
   - 2014-08-23 Added checking for supported databases during installation.
   - 2014-09-14 Refactor of Attachments plugin API to better support other components.
   - 2014-09-17 Fixed bug in joomfish/lanternfish handling
   - 2014-09-18 More updates for joomfish/lanternfish handling
   - 2014-10-14 Cleaned up Javascript of refresh function
   - 2014-11-04 Fix for editor add-attachments button issue
   - 2014-11-16 Fixed bug when creating article from front end
   - 2014-11-28 Fix to better handle PDF mime type
   - 2014-12-12 Fix for missing 'Add Attachment' button for Joomla 3.4
   - 2015-01-24 Fixed missing admin utils icon in Joomla 3.3.6
   - 2015-02-01 Moved repos from joomlacode.org to github.com
   - 2015-02-01 Switched to semver-compatible version numbering
        (This version number is not quite semver compliant but future ones will be)
   - 2015-02-01 Tested with Joomla 3.3.6 and 2.5.28 on Ubuntu
   - 5000+ Downloads (estimate)

3.1.3 - Released November 15, 2013
   - Joomla 3.2 Compatibility Release
   - Allow display of attachments lists in editors for non-article
   - Fixed migration importer handle obsolete 'section' attachments
   - Fixed bug in 'Show attachments to non logged-in users' option
   - Fixed issue with displaying options with Joomla 3.2.
   - 58627 Downloads

3.1.2 - Released August 14, 2013
   - SECURITY RELEASE
   - Fixed bug in max_attachments_size handling.
   - Fixed issue with add-attachment button not showing correctly in Joomla 3.x.
   - Robustness fixes for attachment plugins.
   - Enabled scrolling on upload forms to ensure that all existing attachments
     can be seen, even when there are many.
   - Fixed issue with missing 'KB' language item in attachments list.  
     Changed 'KB' to 'kB' to match Wikipedia 'kilobyte' article
   - Added fix to display attachments for non-menu category blog views.
   - Fix to return user logging in to the prior page.
   - Fix security issue with PHP files qqq.php.  (note trailing period)
   - 19457 Downloads

3.1.1 - Released July 11, 2013
   - SECURITY RELEASE
   - Prevent uploading image file exploits (Security Fix for VEL)
   - 9000 Downloads

3.1 - Released June 8, 2013
   - Supports both Joomla 2.5.7+ and 3.x!
   - Many fixes and improvements. See
        administrator/components/com_attachments/Changelog.php
   - Improved permissions handling for backend users with limited permissions.
   - New option to display attachments to the public that require logging in to download.
   - URL attachments are now handled in a secure way (download count works for both).
   - Now supports Joomfish (with additional plugin).
   - Refactored to allow template overrides of all CSS files and images.
   - More robust file downloading.
   - Many fixes and improvements.
   - 9624 Downloads

3.0.5 - Released April 25, 2013
   - Fixed bug causing problems when running on Joomla 2.5.10.
   - 1027 Uploads from attachments-3.0.4 release area before switching to
     attachments-3.0.5 release area on April 26, 2013.
   - 10482 Downloads

3.0.4 - Released September 7, 2012
   - Fixed bug causing crashes when component (only) is uninstalled.
     To uninstall Attachments uninstall the 'Attachments Package'.
   - 33255 Downloads

3.0.3 - Released August 11, 2012
   - For details, see administrator/components/com_attachments/Changelog.php
   - Fixed many bugs
   - Cleaned up strict PHP issues
   - Minor improvements
   - 6008 Downloads

3.0.2 - Released September 17, 2011
   - 2011-09-17 Fixed bug in delete dialog while editing article on front end
   - 2011-09-17 Fixed save2New issue for Joomla 1.6.x
   - 2011-09-12 Improved display of frontend upload/update forms
   - 31246 Downlaods

3.0.1 - Released September 9, 2011
   - 2011-09-09 Added missing error numbers in import code
   - 2011-09-08 Added Save+New button in admin form to add attachments
   - 2011-09-07 Fixed error in token IDs for admin unpublish messages
   - 2011-09-06 Fixed bug in migration import code that prevented proper error
     messages when imports fail (eg, file not found, etc).  Changed the dry_run
     success message to show number of attachments found in CSV file
   - 995 Downloads

3.0 - Released August 28, 2011
   - Release 3.0 is major update and refactor to work on Joomla 1.6, 1.7, 
     and later.  It will not work on Joomla 1.5.
   - 1481 Downloads

