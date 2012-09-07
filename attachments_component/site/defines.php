<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2011-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

/**
 * Attachments extension definies
 *
 * @package Attachments
 */
class AttachmentsDefines
{
	/** The Attachments extension version number
	 */
	static $ATTACHMENTS_VERSION = '3.0.4';

	/** Project URL
	 */
	static $PROJECT_URL = 'http://joomlacode.org/gf/project/attachments/';

	/** Supported save types for uploading/updating
	 */
	static $LEGAL_SAVE_TYPES = Array('upload', 'update');

	/** Supported URI types for uploading/updating
	 */
	static $LEGAL_URI_TYPES = Array('file', 'url');

	/** Default access level (if default_access_level parameter is not set)
	 *
	 * 1 = Public
	 * 2 = Registered
	 */
	static $DEFAULT_ACCESS_LEVEL_ID = 2;

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
	static $DEFAULT_ATTACHMENTS_ACL_PERMISSIONS = '{"attachments.delete.own":{"6":1,"3":1},"attachments.edit.state.own":{"6":1,"4":1},"attachments.edit.state.ownparent":{"6":1,"4":1},"attachments.edit.ownparent":{"6":1,"3":1},"attachments.delete.ownparent":{"6":1,"3":1}}';


	/** Maximum filename length (MUST match the `filename` SQL definition)
	 */
	static $MAXIMUM_FILENAME_LENGTH = 80;
	

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
	static $ATTACHMENTS_SUBDIR = 'attachments';

	/**
	 * Temporary option for category attachments display
	 *
	 * Necessary until a Joomla bug is fixed.
	 */
	static $USE_ON_CONTENT_PREPARE_FOR_CATEGORY = false;

}
