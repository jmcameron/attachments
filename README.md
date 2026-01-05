# attachments 4.2.0

## Quick Start

Download <a href="https://github.com/jmcameron/attachments/releases/latest" target="_blank">latest version</a> of package

## Requirements

Joomla 4.0+ ; the extensions is also compatible with Joomla 5.0+ and Joomla 6.0+

## Translations

The translations for 
- fr-FR (France)
- gb-GB (Great Britain)
- gr-GR (Greek)
are included in current package

The other translations are managed with separate packages under repo [attachments_translations](https://github.com/jmcameron/attachments_translations)


## 2025-11-07 version 4.2.0

- Announce compatibility with Joomla 6.0 [#164](/../../issues/164)
- better look for attachemnts edit in backend [#156](/../../pull/156)
- add the possibility to insert attachments from other articles issue [#157](/../../issues/157)
- Force creation of site attachment model  issue [#159](/../../issues/159)
- Allow installation of the component from the joomla cli [#163](/../../pull/163)


## 2025-08-01 version 4.1.6

- fix issue [#140](/../../issues/140) Accessibility issue
- fix issue [#145](/../../issues/145) The add attachment link on the articles of the front page does not correspond to the right article
- Fix model ids for adding, editing, deleting attachments [#146](/../../pull/146)
- Remove some unknown legacy code
- Fixes for installation [#148](/../../pull/148)
- Fixes some links in copyrights
- Fixes for JED Checker warnings
- Fix issue [#150](/../../issues/150) Missing attachments.xml brakes joomla 


## 2025-06-22 version 4.1.5

- fix issue [#137](/../../issues/137) PSR12 compatibility

## 2025-06-22 version 4.1.4

- Introduce new extended filtering, new backend list visualisation that is more coherent with Joomla! interface, 
  introduce bootstrap styling to list and filtering controls.
- fix issue [#135](/../../issues/135)  Cannot add attachment to category

**Notes:**
- Searching attachments using the id has changed in the backend. You can now use id: to find an attachment using its id.

## 2025-06-22 version 4.1.3

- fix issue [#119](/../../issues/119) Install - checksum verification failed
- fix issue [#121](/../../issues/121) Accessibility warning
- Fix issue [#125](/../../issues/125) Wrong variables names
- Fix issue [#128](/../../issues/128) Verify URL existence is not working
- Adding FontAwesome feature - alternative for legacy gif icons.

## 2025-04-02 version 4.1.2

- Deprecation warning [#115](/../../issues/115)
- Deprecation warning [#113](/../../issues/113)

## 2025-02-07 version 4.1.1

- improve help pages for english/french
- Check if $attachment_id[$i] exists [#103](/../../pull/103)
- Add download link to be able to view attachement in a popup or download it [#102](/../../pull/102)

## 2025-01-01 version 4.1.0

- Make filename safe adapt {PR #23](/../../pull/23 ) for Joomla 4+ [#98](/../../pull/98)
- Hide empty brackets Adapt [pull/8](/../../pull/8) to joomla 4+ [#96](/../../pull/96)
- Add a simple check for existence of old version version of attachments plugin framework [#97](/../../pull/97)
- show attachments in a popup Issue [#92](/../../issues/92)
- Add finder plugin [#89] (/../../pull/89)
- Uncaught exception when deleting an attachment Issue [#90](/../../issues/90)
- Add new button for {attachments id=xxx } [#85](/../../pull/85)
- Update plugin with id param {attachements id=xxx} [#81](/../../pull/81)
- Update php minimum version and add hashes of zip file [#83](/../../issues/83)

## 2024-11-09 version 4.0.4

- fix uninstall issue [#79](/../../issues/79)
- add translation for Greek
- re-add translations for French; import them from old translation package

## 2024-10-28 version 4.0.3

- Some cosmetic changes to better fit the new admin template for joomla 4 & 5
- Rawurlencode filenames when not using secure download links
- Fix attachment parent info saving for Joomla 5 for new articles
- Correct add attachment in backend
- Correct ReferenceError: submitbutton is not defined
  in add Attachment for article
- Fix plugin warnings
- Add a hint if no extensions are allowed due to joomla upgrade
- Fix Deprecated: explode() when upload a file (FE) [#64](/../../issues/64)
- Allow plugins to be loaded from cli. Fixes [#61](/../../issues/61)
- Merge pull request [#60](/../../pull/60) from mckillo/master
  Some small fix about install, string, datetime, deprecated

## 2024-07-12 version 4.0.2

- add server update
- attachments variable is always an array. Check if not empty
- Fix warnings when there are no attachments available
- Rework plugin insert attachment token
- Fix admin edit attachment dialog tested OK
- Show attachments as links in admin while editing an article
- Fix admin edit attachment dialog
- Provide sane values for the database [#50](/../../issues/50)


## 2024-04-26 version 4.0.1

- correction for Blank page in popup after adding file [#41](/../../issues/41)
- Show attachments for blog articles issue [#40](/../../issues/40)
- Add scrollbar to iframes
- Fix modal not closing
- The Event::getArguments changed in Joomla 5
- Reorganize code to work with both joomla 4 and 5

## Migration from attachments 3

- make backup
- remove "Attachments - Plugin Framework" before upgrading to Joomla 4. 
  If the site is already migrated to Joomla 4, you should first remove the folder ```plugins/attachments/attachments_plugin_framework``` 
  and then remove it from the backend.
- after upgrading the site to Joomla 4 install attachments 4.0.x
- check if all parts of the Attachment extension are enabled

## Version for Joomla 3.x

This version is still maintained under branch https://github.com/jmcameron/attachments/tree/joomla_3.x
