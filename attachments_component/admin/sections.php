<?php
/**
* @version		$Id: section.php 14401 2010-01-26 14:10:00Z louis $
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
 * Renders a sections multi-select element
 *
 * By: Jonathan M. Cameron
 *
 * Adapted from /libraries/joomla/html/parameter/element/section.php
 * thanks to secteur (http://forum.joomla.org/viewtopic.php?p=1347838)
 *
 * @package 	Joomla.Framework
 * @subpackage		Parameter
 * @since		1.5
 */

class JElementSections extends JElement
{
	/**
	* Element name
	*
	* @access	protected
	* @var		string
	*/
	var	$_name = 'Sections';

	function fetchElement($name, $value, &$node, $control_name)
	{
		$db =& JFactory::getDBO();

		// Get the section list
		$query = 'SELECT id, title FROM #__sections WHERE published = 1 AND scope = "content" ORDER BY title';
		$db->setQuery($query);
		$options = $db->loadObjectList();

		// Determine how many options to show and the class of the selector
		$class = "inputbox";
		$size = ( $node->attributes('size') ? $node->attributes('size') : 6 );

		return JHTML::_('select.genericlist',  $options, ''.$control_name.'['.$name.'][]',
						' multiple="multiple" size="' . $size . '" class="'.$class.'"',
						'id', 'title', $value, $control_name.$name);
	}
}
