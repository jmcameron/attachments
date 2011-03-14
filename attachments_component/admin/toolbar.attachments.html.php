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
 * Class for the Attachments toolbar
 *
 * @package Attachments
 */
class TOOLBAR_attachments
{
	/**
	 * Set up the toolbar for add/edit
	 *
	 * @param bool $edit true for editing, false for creating
	 */
	function _EDIT($edit)
	{
		$text = ( $edit ? JText::_( 'Edit' ) : JText::_( 'New' ) );

		JToolBarHelper::title( JText::_( 'ATTACHMENT' ).': [ '. $text.' ]', 'attachments.png' );

		if ( $edit ) {
			JToolBarHelper::save();
			JToolBarHelper::apply();
			// for existing attachments the button is renamed `close`
			JToolBarHelper::cancel( 'cancel', 'Close' );
			}
		else {
			JToolBarHelper::save('saveNew', 'Save');
			JToolBarHelper::apply('applyNew', 'Apply');
			JToolBarHelper::cancel('myCancel', 'Cancel');
			}
	}

	/**
	 * Set up the toolbar for editing parameters
	 */
	function _EDIT_PARAMS()
	{
		$text = JText::_( 'EDIT_OPTIONS' );

		JToolBarHelper::title( JText::_( 'ATTACHMENTS' ).': [ '. $text.' ]', 'attachments.png' );

		JToolBarHelper::save('saveParams', 'Save');
		JToolBarHelper::apply('applyParams', 'Apply');
		JToolBarHelper::cancel( 'cancel', 'Cancel' );
	}

	/**
	 * Set up the toolbar for the default case (regular component display)
	 */
	function _DEFAULT()
	{
		JToolBarHelper::title( JText::_( 'ATTACHMENTS' ), 'attachments');

		JToolBarHelper::publishList();
		JToolBarHelper::unpublishList();
		JToolBarHelper::editListX();
		JToolBarHelper::addNewX();
		JToolBarHelper::deleteList();

		// JToolBarHelper::customX('editParams', 'options', '', JText::_('OPTIONS'), false);
		JToolBarHelper::preferences('com_attachments');

		// Add a button for extra admin commands
		$bar =&  JToolBar::getInstance('toolbar');
		$bar->appendButton( 'Popup', 'adminUtils', $alt='UTILITIES',
							'index.php?option=com_attachments&amp;task=adminUtils&amp;tmpl=component',
							$width='700', $height='500' );

		JToolBarHelper::help('help', true);
	}
}

?>
