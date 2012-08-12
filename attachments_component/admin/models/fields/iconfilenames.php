<?php
/**
 * Attachments component icon filenames selector
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

/**
 * Supports an HTML select list of icon filenames
 *
 * @package		Attachments
 */
class JFormFieldIconfilenames extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'Iconfilenames';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	public function getInput()
	{
		// Initialize variables.
		$html = array();

		// Construct the list of legal icon filenames
		$icon_filenames = array();
		require_once(JPATH_COMPONENT_SITE.'/file_types.php');
		foreach ( AttachmentsFileTypes::unique_icon_filenames() as $ifname) {
			$icon_filenames[] = JHTML::_('select.option', $ifname);
			}
		$icon_list = JHTML::_('select.genericlist',	 $icon_filenames, 'jform[icon_filename]',
							  'class="inputbox" size="1"', 'value', 'text', $this->value,
							  'jform_icon_filename'
							  );

		// Is it readonly?
		if ((string) $this->element['readonly'] == 'true') {
			// Create a read-only list (no name) with a hidden input to store the value.
			$html[] = $icon_list;
			$html[] = '<input type="hidden" name="'.$this->name.'" value="'.$this->value.'"/>';
			}
		else {
			// Create a regular list.
			$html[] = $icon_list;
		}

		return implode($html);
	}
}