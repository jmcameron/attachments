<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

/**
 * Attachments extension definies
 */
class AttachmentsDefines
{
	/** The Attachments extension version number
	 */
	static $ATTACHMENTS_VERSION = '3.0';

	/** Project URL
	 */
	static $PROJECT_URL = 'http://joomlacode.org/gf/project/attachments/';

	/** Supported save types for uploading/updating
	 */
	static $LEGAL_SAVE_TYPES = Array('upload', 'update');


	/** Supported URI types for uploading/updating
	 */
	static $LEGAL_URI_TYPES = Array('file', 'url');
}
