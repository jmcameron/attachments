<?php
/**
 * Attachments component attachments model
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

/** Load the Attachements defines */
require_once(JPATH_SITE.'/components/com_attachments/defines.php');

/**
 * Form Field class list of access levels the user has access to
 *
 * @package Attachments
 * @subpackage Attachments_Component
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


	/**
	 * Get the access levels HTML selector
	 *
	 * @param string $for_id the id for the select input
	 * @param string $fieldname the name of the field
	 * @param int $level_value the value of the level to be initially selected
	 */
	public static function getAccessLevels($for_id, $fieldname, $level_value=null)
	{
		$user	= JFactory::getUser();
		$user_access_levels = array_unique($user->authorisedLevels());

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('a.*');
		$query->from('#__viewlevels AS a');
		if ( !$user->authorise('core.admin') ) {
			// Users that are not super-users can ONLY see the the view levels that they are authorized for
			$query->where('a.id in ('.implode(',', $user_access_levels).')');
			}
		$query->order('a.ordering ASC');
		$query->order($query->qn('title') . ' ASC');

		// Get the levels
		$db->setQuery($query);
		$levels = $db->loadObjectList();
		if ( $db->getErrorNum() ) {
			$errmsg = $db->stderr() . ' (ERR 100)';
			JError::raiseError(500, $errmsg);
			}

		// Make sure there is a $level_value
		if ( $level_value === null ) {
			jimport('joomla.application.component.helper');
			$params = JComponentHelper::getParams('com_attachments');
			$level_value = $params->get('default_access_level', AttachmentsDefines::$DEFAULT_ACCESS_LEVEL_ID);
			}

		// Make sure the $level_value is in the user's authorised levels
		if ( !in_array($level_value, $user_access_levels) ) {
			// If not, set $level_value to the lowest legal value
			$registered = 2;
			if ( in_array($registered, $user_access_levels) ) {
				$level_value = $registered;
				}
			else {
				$sorted_access_levels = sort($user_access_levels, SORT_NUMERIC);
				$level_value = $sorted_access_levels[0];
				}
			}

		// Construct the drop-down list
		$level_options = Array();
		foreach ( $levels as $level ) {
			// NOTE: We do not translate the access level titles
			$level_options[] = JHTML::_('select.option', $level->id, $level->title);
			}
		return JHTML::_('select.genericlist',  $level_options, $for_id,
						'class="inputbox" size="1"', 'value', 'text', $level_value,
						$fieldname
						);
	}
}
