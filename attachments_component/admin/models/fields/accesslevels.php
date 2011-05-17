<?php
/**
 * @version		$Id: filters.php 21097 2011-04-07 15:38:03Z dextercowley $
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/**
 * Form Field class list of access levels the user has access to
 *
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @since		1.6
 */
class JFormFieldAccessLevels extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	public $type = 'AccessLevels';


	/**
	 * Method to get the field input markup.
	 *
	 * TODO: Add access check.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		$user   = JFactory::getUser();
		$user_levels = implode(',', $user->authorisedLevels());

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.*');
		$query->from('#__viewlevels AS a');
		$query->where('a.id in ('.$user_levels.')');
		$query->order('a.ordering ASC');

		// Get the levels
		$db->setQuery($query);
		$levels = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
			return null;
		}

		// Construct the drop-down list
		$level_options = Array();

		foreach ( $levels as $level ) {
			$level_options[] = JHTML::_('select.option', $level->id, JText::_($level->title));
			}

		return JHTML::_('select.genericlist',  $level_options, $this->name,
						'class="inputbox" size="1"', 'value', 'text', $this->value,
						'jform_'.$this->fieldname
						);
	}
}