<?php
/**
 * Attachments component attachments model
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
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
		$options = new JObject();
		$options->element = $this->element;
		$options->multiple = $this->multiple;
		$options->always_public = $this->fieldname == 'show_guest_access_levels';
		return $this->getAccessLevels($this->name, 'jform_'.$this->fieldname, $this->value, $options);
	}


	/**
	 * Get the access levels HTML selector
	 *
	 * @param string $for_id the id for the select input
	 * @param string $fieldname the name of the field
	 * @param int $level_value the value of the level(s) to be initially selected
	 */
	public static function getAccessLevels($for_id, $fieldname, $level_value=null, $options=null)
	{
		$user	= JFactory::getUser();
		$user_access_levels = array_unique($user->getAuthorisedViewLevels());

		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		// Get the access levels this user is permitted
		$query->select('a.*');
		$query->from('#__viewlevels AS a');
		if ( !$user->authorise('core.admin') ) {
			// Users that are not super-users can ONLY see the the view levels that they are authorized for
			$query->where('a.id in ('.implode(',', $user_access_levels).')');
			}
		$query->order('a.ordering ASC');
		$query->order($query->qn('title') . ' ASC');
		$db->setQuery($query);
		$levels = $db->loadObjectList();
		if ( $db->getErrorNum() ) {
			$errmsg = $db->stderr() . ' (ERR 116)';
			JError::raiseError(500, $errmsg);
			}

		// Make sure there is a $level_value
		if ( $level_value === null ) {
			jimport('joomla.application.component.helper');
			$params = JComponentHelper::getParams('com_attachments');
			$level_value = $params->get('default_access_level', AttachmentsDefines::$DEFAULT_ACCESS_LEVEL_ID);
			}

		// Make sure the $level_value is in an array
		if (!is_array($level_value)) {
			$level_value = Array($level_value);
			}

		// Make sure the $level_value is in the user's authorised levels (except for super-user)
		if ( !$user->authorise('core.admin') ) {

			// Filter out any non-permitted access levels
			$ok_access_levels = Array();
			foreach ($level_value as $lval) {
				if (in_array($lval, $user_access_levels)) {
					$ok_access_levels[] = $lval;
					}
				}

			// Make sure there is at least one access level left
			if (empty($ok_access_levels)) {
				// pick one arbitrarily
				$sorted_access_levels = sort($user_access_levels, SORT_NUMERIC);
				$level_value = Array($sorted_access_levels[0]);
				}
			else {
				$level_value = $ok_access_levels;
				}
			}

		// Deal with multiple vs non-multiple selections
		if (isset($options->multiple) and $options->multiple) {

			// Make sure Public is always selected, if desired
			$public = AttachmentsDefines::$PUBLIC_ACCESS_LEVEL_ID;
			if ($options->always_public) {
				if ( !in_array($public, $level_value) ) {
					array_unshift($level_value, $public);
					}
				}
			}
		else {
			if (count($level_value) > 1) {
				// If not multiple, only one selection is allowed, arbitrarily pick the first one
				// (Not sure this will ever be necessary)
				$level_value = Array($level_value[0]);
				}
			}

		// Construct the attributes for the list
		$attr = '';
		if ( $options === null ) {
			$attr = 'class="inputbox" size="1"';
			}
		else {
			$attr .= $options->element['class'] ? ' class="' . (string) $options->element['class'] . '"' : '';
			$attr .= ((string) $options->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
			$attr .= $options->element['size'] ? ' size="' . (int) $options->element['size'] . '"' : '';
			$attr .= $options->multiple ? ' multiple="multiple"' : '';
			}

		// Construct the list
		$level_options = Array();
		foreach ( $levels as $level ) {
			// NOTE: We do not translate the access level titles
			$level_options[] = JHtml::_('select.option', $level->id, $level->title);
			}
		return JHtml::_('select.genericlist',  $level_options, $for_id,
						$attr, 'value', 'text', $level_value, $fieldname
						);
	}
}
