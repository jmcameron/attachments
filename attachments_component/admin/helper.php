<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('_JEXEC') or die('Restricted access');

/**
 * A class for attachments helper functions
 *
 * @package Attachments
 */
class AttachmentsHelper
{
	public static function getpost() {
		if (version_compare(JVERSION, '4.0', 'ge')){
			return JFactory::getApplication()->input->getArray(array());
		}
		else {
			return call_user_func_array('AttachmentsHelper::get', ['post']);
		}
	}
	
	public static function get(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			if ($params[0] == 'post '){
				return JFactory::getApplication()->input->getInputForRequestMethod('POST');
			} else {
				return call_user_func_array(array(JFactory::getApplication()->input, 'get'), $params);
			}
		}
		else {
			return call_user_func_array('AttachmentsHelper::get', $params);
		}
	}
	
	public static function getVar(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			return call_user_func_array(array(JFactory::getApplication()->input, 'getVar'), $params);
		}
		else {
			return call_user_func_array('AttachmentsHelper::getVar', $params);
		}
	}
	public static function setVar(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			call_user_func_array(array(JFactory::getApplication()->input, 'setVar'), $params);
		}
		else {
			call_user_func_array('AttachmentsHelper::setVar', $params);
		}
	}

	public static function getCmd(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			return call_user_func_array(array(JFactory::getApplication()->input, 'getCmd'), $params);
		}
		else {
			return call_user_func_array('AttachmentsHelper::getCmd', $params);
		}
	}

	public static function getInt(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			$recordId = call_user_func_array(array(JFactory::getApplication()->input, 'getInt'), $params);
		}
		else {
			$recordId	= (int)call_user_func_array('AttachmentsHelper::getInt', $params);
		}
	}
	
	public static function getWord(...$params) {
		if (version_compare(JVERSION, '4.0', 'ge')){
			$recordId = call_user_func_array(array(JFactory::getApplication()->input, 'getWord'), $params);
		}
		else {
			$recordId	= (int)call_user_func_array('AttachmentsHelper::getWord', $params);
		}
	}
	
	static function getJURIbase()
	{
		$JURI = JURI::base();
		return preg_replace('/\/$/', '', $JURI);
	}


}
