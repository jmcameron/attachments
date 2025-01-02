# attachments 4.0.4

## Quick Start

Download <a href="https://github.com/jmcameron/attachments/releases/latest" target="_blank">latest version</a> of package

## Requirements

Joomla 4.0+ compatible also with Joomla 5.0+

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
