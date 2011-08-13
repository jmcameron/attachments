How to Migrate Attachments from Joomla 1.5 to Joomla 1.6+
=========================================================

This file describes how to migrate your attachments from your Joomla 1.5 site
to a Joomla 1.6+ site.  Note this procedure applies to Joomla 1.6 and 1.7
equally. 

The process involves creating a Comma-Separated-File (CSV) migration file
containing the data for the attachments on the Joomla 1.5 system.  The CSV
migration file is used to recreate the Attachments data on the Joomla 1.6+
system.  The attachment files can be moved directly without modification.

Necessary Conditions for Successful Attachments Migration
---------------------------------------------------------

 * The article ID's and Titles must be identical in the Joomla 1.5 system and
   the Joomla 1.6+ system.  If there are differences, the migration file will
   need to be edited manually.  It is particularly important that the
   article/parent ID's are the same since these are embedded in the attachment
   file paths.  If the article ID's are different, the parent directories for
   the attachments files will need to be renamed on the Joomla 1.6 side.

 * The user ID and username of the users that created the Attachments on the
   Joomla 1.5 system must be the same as those on the Joomla 1.6+ system.  If
   there are differences, the migration file will need to be edited manually.
 

Migrating the Attachments Data
------------------------------

The process to migrate the data for the Attachments involves several steps:

  1.  This procedure should not affect your Joomla 1.5 site, but it will
      affect your Joomla 1.6+ site, so it is a good idea to back up your
      Joomla 1.6+ site before proceeding.  You may also want to back up your
      Joomla 1.5 site.

Export the attachments information from your Joomla 1.5 site
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 2.  Delete any Joomla 1.5 attachments you do not want to migrate

 3.  If your Joomla 1.5 site uses Attachments version 2.2, download this
     file:

	 :download:`special.php.txt <special.php.txt>`

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

Importing your old Attachments to your Joomla 1.6/1.7+ site
~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

 6.   Although not strictly necessary, it is probably a good idea to delete all
      attachments on your Joomla 1.6 site.  If you do not do this, you will
      probably want to carefully note all of the existing attachments on your
      Joomla 1.6 site before proceeding.  Doing this step will make it easier to
      delete any new attachments if the migration process does not go smoothly.

 7.   Copy the CSV migration file ('migrate_attachments.csv') and the archive of
      attachments files to your Joomla 1.6 site.

 8.   Upload the migration file to some place on your Joomla 1.6 server file
      system.   **Note the exact location and path to the file on the server.**

 9.   Log into the back end of your Joomla 1.6 system as an administrator.

 10.  Go to the Attachments page and execute this command manually on your
      Joomla 1.6 system (type in the full URL by hand)::
 
	  http://<yoursite>/administrator/index.php?option=com_attachments&task=utils.installAttachmentsFromCsvFile&filename=/path/to/migrate_attachments.csv&dry_run=1

      .. note:: You must use the full path to the migration file on the server file system.

      If there are problems with the article/parent ID's, tiles, or user IDs
      or usernames, the command will abort and alert you to the nature of the
      problem.  You can edit the migration file until your get it to complete
      successfully.  Until this works without error, you should definitely use
      the '&dry_run=true' option on the command so nothing is changed.

      Once you get the message::

	  Data for attachments is okay. 
	  Rerun without 'dry_run' option to add attachments.

      you will know that the migration should work.  Because of the 'dry_run'
      flag that is part of the URL, no changes will occur on your website.  To
      actually create the data for the attachments, remove the '&dry_run=true'
      option and execute the command again.  You should see a success message::

	  Added data for 4 attachments!

      where '4' will be replaced with the number of attachments in the
      migration file.

 11.  Unzip the attachments file in the same directory on your Joomla 1.6 site
      as they were on your Joomla 1.5 site.  This step requires that the
      article/parent IDs are identical between the 1.5 and 1.6 systems.  If
      this is not true, some directory renaming will be necessary to ensure
      that the name of the parent directory above each attachment matches the
      article/parent ID.

 12.  In the back end on the Joomla 1.6 site, got to the Attachments page and
      execute the 'Utilities' command (on the right end of the toolbar).
      Click on the item:

	  **Regenerate system filenames**

      This should fix the system filenames for all of the newly migrated File
      attachments.  The URL attachments will not be affected.

 13.  Test the newly migrated attachments (try downloading them on the back
      end or front end).

That should complete the process.  If you have any difficulties with this
process, please contact me:

-Jonathan Cameron,   jmcameron@jmcameron.net
