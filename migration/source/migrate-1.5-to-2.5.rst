How to Migrate Attachments from Joomla 1.5 to Joomla 2.5+
=========================================================

This file describes how to migrate your attachments from your Joomla 1.5 site
to a Joomla 2.5+ or 3.x site.  Note this procedure applies to Joomla 2.5+ and
3.x equally.

The process involves creating a Comma-Separated-File (CSV) migration file
containing the data for the attachments on the Joomla 1.5 system.  The CSV
migration file is used to recreate the Attachments data on the Joomla 2.5+
system.  The attachment files can be moved directly without modification.

Necessary Conditions for Successful Attachments Migration
---------------------------------------------------------

 * The Attachments extension for your Joomla 1.5 site should be upgraded to
   version 2.2.

 * The Joomla version for your 2.5+ site should be 2.5.7 or later.

 * You should install a released version of Attachments 3.1 (or later) on
   your Joomla 2.5+ system before proceeding.  

   .. warning::
      If you use a tool to migrate your site from Joomla 1.5 to Joomla
      2.5+, it may try to migrate the Attachments extension.  This will
      not work properly.  Before you install Attachments, you will need to
      uninstall any old version of Attachments and make sure that all the
      files related to Attachments extensions are deleted.  If you have any
      difficulty doing this, please contact me (see my email at the bottom of
      this page).

 * The article ID's and Titles must be identical in the Joomla 1.5 system and
   the Joomla 2.5+ system.  If there are differences, the migration file
   will need to be edited manually.  It is particularly important that the
   article/parent ID's are the same since these are embedded in the attachment
   file paths.  If the article ID's are different, the parent directories for
   the attachments files will need to be renamed on the Joomla 2.5+ side.

 * The user ID and username of the users that created the Attachments on the
   Joomla 1.5 system must be the same as those on the Joomla 2.5+ system.
   If there are differences, the migration file will need to be edited
   manually.

 * The attachment files must exist for all the attachments.  
 
Migrating the Attachments Data
------------------------------

The process to migrate the data for the Attachments involves several steps:

  1.  This procedure should not affect your Joomla 1.5 site, but it will
      affect your Joomla 2.5+ site, so it is a good idea to back up your
      Joomla 2.5+ site before proceeding.  You may also want to back up
      your Joomla 1.5 site as well.


Export the attachments information from your Joomla 1.5 site
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 2.  Delete any Joomla 1.5 attachments you do not want to migrate.  It is very
     important that all attachments have one attachment file associated with
     them!

 3.  Assuming your Joomla 1.5 site uses Attachments version 2.2, download this
     file:

	 :download:`special.php.txt <special.php.txt>`  <--- *This is a link!*

     Rename the file to 'special.php' on your local computer and upload it to
     your Joomla 1.5 site to replace the file::

	 administrator/components/com_attachments/controllers/special.php

     This replacement file adds a function to export the Attachments into a
     CSV migration file.

     .. note:: If your Joomla 1.5 site is running a version of Attachments
	later than version 2.2, you do not need to do this step.

	If your Joomla 1.5 site is running a version of Attachments before
	version 2.2, you will need to upgrade to version 2.2 or later.

 4.  Log into the back end of your Joomla 1.5 site as an administrator and
     execute this command manually (by typing in the full URL)::

	http://<yoursite>/administrator/index.php?option=com_attachments&controller=special&task=export_attachments_to_csv_file

     This will ask you to save a CSV migration file named
     'migrate_attachments.csv' to your local computer.

 5.  Copy all of the attachments files on your Joomla 1.5 site by creating a
     zip/tar archive of this directory on your Joomla 1.5 site::

	attachments

Importing your old Attachments to your Joomla 2.5+ site
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 6.  Although not strictly necessary, it is probably a good idea to delete all
     attachments on your Joomla 2.5+ site.  If you do not do this, you will
     want to carefully note all of the existing attachments on your Joomla
     2.5+ site before proceeding.  Doing this step will make it easier to
     delete any new attachments if the migration process does not go smoothly.

 7.  All the migrated attachments will be set to the same Access Level.
     Depending on your site, you may want this to be 'Public' or 'Registered'
     (or other access level of your choice).  The default is 'Registered'.  To
     choose which access level should be set for all the migrated attachments
     do this on the Joomla 2.5+ site:

	* Go to the Attachments page in the back end (under Components),
	* Click on the "Options" button on the toolbar,
	* Select the 'Basic' tab and
	* Set the 'Default Access Level' to the desired access level.

 8.  Copy the archive (eg, zip file) of attachments files to your Joomla
     2.5+ site.  

 9.  Upload or copy the migration file ('migrate_attachments.csv') to some
     place on your Joomla 2.5+ server file system.  **Note the exact location
     and path to the file on the server.** If the Joomla 1.5 site and the
     Joomla 2.5+ sites are on the same computer, simply note the full path to
     the migration file.  

     .. warning:: THIS IS IMPORTANT!  If your Joomla 2.5+ is on some other
        computer, such as a NAS web server, you must copy the migration file
        to that computer!  It will not be uploaded by the migration software!

 10. Log into the back end of your Joomla 2.5+ system as an administrator.

 11. Go to the Attachments page and execute this command manually on your
     Joomla 2.5+ system (type in the full URL by hand)::
 
	  http://<yoursite>/administrator/index.php?option=com_attachments&task=utils.installAttachmentsFromCsvFile&filename=/path/to/migrate_attachments.csv&dry_run=1

     where '/path/to/migrate_attachments.csv' is the full path to the
     'migrate_attachments.csv' file.

     .. note:: You must use the full path to the migration file on the server
        file system.  On a Windows system, this path may look like a Windows
        path including a drive letter such as 'C:\\' at the beginning of the
        path.  The begining path on a Linux web server might look like this:
        '/path/to/joomla' (no drive letter).  Note that this is NOT an URL
        (starting with https: or ftp: ).

	If the server has difficulty opening your migration file or you are
        confused about this, log into the back end of your NEW 2.5+ webserver
        as an administrator.  Go to the menu entry: Site\ >\ Global\ Configuration 
	Click on the "Systems Settings" tab and look at the "Path to log
        folder" entry to get the first part of the path -- assuming you put
        the migration file along with the Joomla server files.

     If there are problems with the article/parent ID's, titles, or user IDs
     or usernames, the command will abort and alert you to the nature of the
     problem.  You can then edit the migration file manually until your get
     it to complete successfully.

     .. warning:: 
        When you edit the migration CSV file, make very sure you use an
        editor that does not insert a Byte Order Marker (BOM).  On windows,
        use a text editor such as notepad or pspad.  For suggestions on how
        to prevent or remove the BOM for other editors, try searching for::

          eliminate byte order marker emacs

        where 'emacs' should be replaced by the name of your text editor.

	**DO NOT USE** a spreadsheet program like **'Excel'** since there is a
	good chance it will add extra characters and result in errors when you
	try to use it to import the attachments.

     Until processing this file works without error, you should definitely
     use the '&dry_run=true' option on the command so nothing is changed.

     Once you get the message::

     	  Data for attachments is okay. 
     	  Rerun without 'dry_run' option to add attachments.

     you will know that the migration will probably work.  Because of the
     'dry_run' flag that is part of the URL, no changes will occur on your
     website.  To actually create the data for the attachments, remove the
     '&dry_run=true' option and execute the command again.  You should see a
     success message::

     	  Added data for 4 attachments!

     where '4' will be replaced with the number of attachments in the
     migration file.

     .. note:: 

        The 'dry_run' process does not catch all types of errors such as
        missing categories, etc.  If you encounter errors running the real
        import (without 'dry_run'), it may be necessary to do necessary
        fixes, empty the \*_attachments table and repeat the import process
        until all errors are eliminated.

 12.  Unzip the attachments file in the same directory on your Joomla 2.5+
      site as they were on your Joomla 1.5 site.  This step requires that the
      article/parent IDs are identical between the 1.5 and 2.5+ systems.
      If this is not true, some directory renaming will be necessary to ensure
      that the name of the parent directory above each attachment matches the
      article/parent ID.

      .. note:: 

         If your web server is a Linux system, you may need to adjust the user
         and group ownership of the files so that your webserver process can
         access and updated them as needed.  Please consult a system
         adminstrator for your web server to determine what ownership is
         necessary.

 13.  In the back end on the Joomla 2.5+ site, go to the Attachments page
      and execute the 'Utilities' command (on the right end of the toolbar).
      Click on the item:

	  **Regenerate system filenames**

      This should fix the system filenames for all of the newly migrated File
      attachments.  The URL attachments will not be affected.

 14.  Test the newly migrated attachments (try downloading them on the back
      end or front end).

That should complete the process.  If you have any difficulties with this
process, please contact me:

-Jonathan Cameron,   jmcameron@jmcameron.net

..  LocalWords:  Joomla CSV username php csv usernames filenames
