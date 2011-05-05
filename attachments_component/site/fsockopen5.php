<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Call the fsockopen() function and catch any errors
 *
 * NOTE: The reason this function is in a separate file is because it contains
 *		 PHP5-specific code (the try/catch).  In order run in PHP 4, this file
 *		 is not included('require_once') and therefore does not cause trouble.
 *
 * @param &object &$u the URL info object
 * @param &object &$errno for the fsockopen() call to return the error number
 * @param &string &$errstr for the fsockopen() call to return the error string
 * @param int $timeout number of seconds for fsockopen() to allow
 * @param bool $verify whether the existance of the URL should be checked
 *
 * @return $fp is the call was succesful, false if not
 */
function &fsockopen_protected(&$u, &$errno, &$errstr, $timeout, $verify)
{
	set_error_handler(create_function('$a, $b, $c, $d',
									  'throw new Exception("fsockopen error");'), E_ALL);
	try {
		$fp = fsockopen($u->domain, $u->port, $errno, $errstr, $timeout);
		restore_error_handler();
		}
	catch (Exception $e) {
		restore_error_handler();
		if ( $verify ) {
			$u->error = true;
			$u->error_code = 'url_check_exception';
			$u->err_msg = $e->getMessage();
			return false;
			}
		}

	return $fp;
}
