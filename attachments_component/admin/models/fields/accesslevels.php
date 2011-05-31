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
		return $this->getAccessLevels($this->name, 'jform_'.$this->fieldname, $this->value);
	}


	public function getAccessLevels($for_name, $fieldname, $level_value=null)
	{
		$user   = JFactory::getUser();
		$user_levels = array_unique($user->authorisedLevels());

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.*');
		$query->from('#__viewlevels AS a');
		$query->where('a.id in ('.implode(',', $user_levels).')');
		$query->order('a.ordering ASC');

		// Get the levels
		$db->setQuery($query);
		$levels = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseWarning(500, $db->getErrorMsg());
			}

		// Make sure there is a $level_value
		if ( $level_value === null ) {
			jimport('joomla.application.component.helper');
			$params =& JComponentHelper::getParams('com_attachments');
			$level_value = $params->get('default_access_level', 2);
			}

		// Make sure the $level_value is in the user's authorised levels
		if ( !in_array($level_value, $user_levels) ) {
			// If not, set $level_value to the lowest legal value
			$registered = 2;
			if ( in_array($registered, $user_levels) ) {
				$level_value = $registered;
				}
			else {
				$sorted_user_levels = sort($user_levels, SORT_NUMERIC);
				$level_value = $sorted_user_levels[0];
				}
			}

		// Construct the drop-down list
		$level_options = Array();
		foreach ( $levels as $level ) {
			$level_options[] = JHTML::_('select.option', $level->id, JText::_($level->title));
			}
		return JHTML::_('select.genericlist',  $level_options, $for_name,
						'class="inputbox" size="1"', 'value', 'text', $level_value,
						$fieldname
						);
	}
}