<?php
/**
* @version		$Id: category.php 14401 2010-01-26 14:10:00Z louis $
* @package		Joomla.Framework
* @subpackage	Parameter
* @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is within the rest of the framework
defined('JPATH_BASE') or die();

/**
 * Renders a categories multi-select element
 *
 * By: Jonathan M. Cameron
 *
 * Adapted from /libraries/joomla/html/parameter/element/category.php
 * thanks to secteur (http://forum.joomla.org/viewtopic.php?p=1347838)
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementCategories extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Categories';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db = &JFactory::getDBO();

		// ??? NEEDS TO BE REWORKED TO REMOVE SECTIONS
		$section	= $node->attributes('section');

		if (!isset ($section)) {
			// alias for section
			$section = $node->attributes('scope');
			if (!isset ($section)) {
				$section = 'content';
			}
		}

		// Get the category list
		if ($section == 'content') {
			// This might get a conflict with the dynamic translation - TODO: search for better solution
			$query = 'SELECT c.id, CONCAT_WS( "/",s.title, c.title ) AS title' .
				' FROM #__categories AS c' .
				' LEFT JOIN #__sections AS s ON s.id=c.section' .
				' WHERE c.published = 1' .
				' AND s.scope = '.$db->Quote($section).	
			' ORDER BY s.title, c.title';
		} else {
			$query = 'SELECT c.id, c.title' .
				' FROM #__categories AS c' .
				' WHERE c.published = 1' .
				' AND c.section = '.$db->Quote($section).
				' ORDER BY c.title';
		}
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Determine how many options to show and the class of the selector
		$class = "inputbox";
		$size = ( $node->attributes('size') ? $node->attributes('size') : 6 );

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.'][]',
						' multiple="multiple" size="' . $size . '" class="'.$class.'"',
						'id', 'title', $value, $control_name.$name );
	}
}