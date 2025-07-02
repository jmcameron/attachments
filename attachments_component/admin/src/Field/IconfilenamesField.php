<?php

/**
 * Attachments component icon filenames selector
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Administrator\Field;

use JMCameron\Component\Attachments\Site\Helper\AttachmentsFileTypes;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Supports an HTML select list of icon filenames
 *
 * @package     Attachments
 */
class IconfilenamesField extends FormField
{
    /**
     * The form field type.
     *
     * @var     string
     * @since   1.6
     */
    protected $type = 'Iconfilenames';

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     * @since   1.6
     */
    public function getInput()
    {
        // Initialize variables.
        $html = array();

        // Construct the list of legal icon filenames
        $icon_filenames = array();
        foreach (AttachmentsFileTypes::unique_icon_filenames() as $ifname) {
            $icon_filenames[] = HTMLHelper::_('select.option', $ifname);
        }
        $icon_list = HTMLHelper::_(
            'select.genericlist',
            $icon_filenames,
            'jform[icon_filename]',
            'class="inputbox" size="1"',
            'value',
            'text',
            $this->value,
            'jform_icon_filename'
        );

        // Is it readonly?
        if ((string) $this->element['readonly'] == 'true') {
            // Create a read-only list (no name) with a hidden input to store the value.
            $html[] = $icon_list;
            $html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';
        } else {
            // Create a regular list.
            $html[] = $icon_list;
        }

        return implode($html);
    }
}
