Attachments Extension for Joomla 1.5
====================================

Attachments Version 2.2

This extension allows uploaded files to be attached to articles and
other types of content.  It includes options for controlling who can
see the attachments and who can add them.  This extension consists of
three plugins and a component.

If you wish to subscribe to an email list for announcements about
this extension, please subscribe using this web page:

    http:/jmcameron.net/attachments/email-list.html

Jonathan Cameron
jmcameron@jmcameron.net (feedback is welcome!)
http://joomlacode.org/gf/project/attachments/

Copyright (C) 2007-2010 Jonathan M. Cameron, All Rights Reserved
License http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL

NOTES:

Once the attachment plugins and component have been installed, attachments
should work (the necessary plugins are automatically enabled during
installation).

The default after installation is for the links to the attachments to be
visible to anyone that is logged in and for the link to add attachments only
to be visible to the author of the article (or anyone with appropriate
permissions).  Both of these options can be modified via the attachments
parameters which can be changed in the Component manager administrative back
end.  (Under 'Components', select 'Attachments', then click on the
'Parameters' button near the right end of the tool bar.)

If your files are sensitive or private, use the secure option since by
default the attachment files are saved in a directory that is publicly
accessible to anyone that knows the full URL for the attachment file.

Once an attachment is uploaded, it is not visible until it is published.  To
publish an attachment, go to the administrative back end and select
"Attachments" under the "Components" menu.  This will show a list of
attachments and has controls to publish the attachments.  The option to
publish attachments automatically after they are uploaded can be selected via
the Attachments component manager parameter editor.

Every time a file is uploaded, the existence of the upload subdirectory is
checked and it will be created if if it does not exist.  The upload
directory defaults to 'attachments' under the main Joomla website root 
directory (this can be changed using the Attachments option).  If the 
'Attachments' extension is unable to create the subdirectory for uploads, 
you must create it yourself (and you may have problems uploading files).  

This extension respects the options in the Media Manager regarding what 
types of files can be uploaded.  If you can't attach certain file types 
(such as zip files), go to the "Global Configuration" item under the "Site" 
menu in the administrative back end.  Click on the "System" tab and look for
the "Media Settings" section.  You can add appropriate file extensions or 
mime types there.

This extension supports several languages including English, Bulgarian,
Catalan, Croatian, Chinese (Simplified and Traditional), Dutch, Finnish,
French, German, Greek, Hungarian, Italian, Norwegian, Persian, Polish,
Brazilian Portuguese, Romanian, Russian, Serbian, Slovakian, Spanish, and
Swedish. Note that some of the language packs will not be available for version
2.0 until their translations are updated.  Language packs for these languages
are available on the at:

     http://joomlacode.org/gf/project/attachments/frs/

If you would like to help translate the extension to any other language,
please contact the author (see above).

More help is available in the administrative back end.  Select "Attachments"
under the "Components" menu.  Click on the help icon on the right end of the
toolbar to get more help and to see known limitations of this software.

Finally, if you have difficulties with the Attachments extension and cannot 
find an answer here or on the help page, try the frequently asked questions 
forum or help forum:

     http://joomlacode.org/gf/project/attachments/forum/
