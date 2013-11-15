<?php
/**
 * Attachments component
 *
 * @package     Attachments
 * @subpackage  Attachments_Component
 *
 * @author      Jonathan M. Cameron <jmcameron@jmcameron.net>
 * @copyright   Copyright (C) 2011-2013 Jonathan M. Cameron, All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        http://joomlacode.org/gf/project/attachments/frs/
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

/**
 * Attachments extension definies
 *
 * @package  Attachments
 *
 * @since    2.2
 */
class AttachmentsDefines
{
	/** The Attachments extension version number
	 */
	public static $ATTACHMENTS_VERSION = '3.1.3';

	/** The Attachments extension version date
	 */
	public static $ATTACHMENTS_VERSION_DATE = 'November 15, 2013';

	/** Project URL
	 */
	public static $PROJECT_URL = 'http://joomlacode.org/gf/project/attachments3/';

	/** Supported save types for uploading/updating
	 */
	public static $LEGAL_SAVE_TYPES = Array('upload', 'update');

	/** Supported URI types for uploading/updating
	 */
	public static $LEGAL_URI_TYPES = Array('file', 'url');

	/** Default access level (if default_access_level parameter is not set)
	 *
	 * 1 = Public
	 * 2 = Registered
	 */
	public static $DEFAULT_ACCESS_LEVEL_ID = '2';

	/** Default 'Public' access level (in case it is different on this system)
	 */
	public static $PUBLIC_ACCESS_LEVEL_ID = '1';

	/** Default permissions for new attachments rules
	 *
	 * These are the default settings for the custom ACL permissions for the
	 * Attachments extension.
	 * 
	 * Be careul if you edit this to conform to the proper json syntax!
	 *
	 * NB: Unfortunately, the syntax for setting a static variable does not
	 *	   allow breaking the string up with dots to join the parts to make
	 *	   this easier to read.
	 */
	public static $DEFAULT_ATTACHMENTS_ACL_PERMISSIONS = '{"attachments.delete.own":{"6":1,"3":1},"attachments.edit.state.own":{"6":1,"4":1},"attachments.edit.state.ownparent":{"6":1,"4":1},"attachments.edit.ownparent":{"6":1,"3":1},"attachments.delete.ownparent":{"6":1,"3":1}}';

	/** Maximum filename length (MUST match the `filename` SQL definition)
	 */
	public static $MAXIMUM_FILENAME_LENGTH = 256;

	/** Maximum filename path length (MUST match the `filename_sys` SQL definition)
	 */
	public static $MAXIMUM_FILENAME_SYS_LENGTH = 512;

	/** Maximum URL length (MUST match the `url` SQL definition)
	 */
	public static $MAXIMUM_URL_LENGTH = 1024;

	/** Attachments subdirectory
	 *
	 * NOTE: If you have any existing attachments, follow one of these procedures
	 *
	 *	  1. If you do not care to keep any existing attachments, follow these steps:
	 *			- Suspend front-end operation of your website
	 *			- In the back end attachments page, delete all attachments
	 *			- Delete the attachments directory
	 *			- Change the value below and save this file
	 *			- Resume front-end operation of your website
	 *
	 *	  2. If you are simply renaming the attachments directory, do the following
	 *		 steps:
	 *			- Suspend front-end operation of your website
	 *			- Rename the attachments directory (must be within the top
	 *			  directory of your website)
	 *			- Change the value below and save this file
	 *			- In the back end attachments page, under the "Utilities" command
	 *			  on the right end of the toolbar, choose the "Regenerate system filenames"
	 *			  command
	 *			- Resume front-end operation of your website
	 */
	public static $ATTACHMENTS_SUBDIR = 'attachments';
}
