# attachments 4.0.2

## Quick Start

Download <a href="https://github.com/jmcameron/attachments/releases/latest" target="_blank">latest version</a> of package

## Requirements

Joomla 4.0+ compatible also with Joomla 5.0+

## Changes since version 4.0.1

- add server update
- attachments variable is always an array. Check if not empty
- Fix warnings when there are no attachments available
- Rework plugin insert attachment token
- Fix admin edit attachment dialog tested OK
- Show attachments as links in admin while editing an article
- Fix admin edit attachment dialog
- Provide sane values for the database [#50](/../../issues/50)


## Changes since 4.0 beta

- correction for Blank page in popup after adding file [#41](/../../issues/41)
- Show attachments for blog articles issue [#40](/../../issues/40)
- Add scrollbar to iframes
- Fix modal not closing
- The Event::getArguments changed in Joomla 5
- Reorganize code to work with both joomla 4 and 5

## Migration from attachments 3
- make backup
- remove "Attachments - Plugin Framework" before upgrading to Joomla 4. If the site is already migrated to Joomla 4, you should first remove the folder ```plugins/attachments/attachments_plugin_framework``` and then remove it from the backend.
- after upgrading the site to Joomla 4 install attachments 4.0.x
- check if all parts of the Attachment extension are enabled

## Version for Joomla 3.x

This version is still maintained under branch https://github.com/jmcameron/attachments/tree/joomla_3.x
