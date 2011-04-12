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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view' );

/**
 * View for editing the component parameters
 * (adapted from administrator/components/com_config/views/component/view.php) 
 *
 * @package Attachments
 */
class AttachmentsViewParams extends JView
{
	/**
	 * Display the params view
	 */
	function display($tpl = null)
	{
		$this->addToolBar();

		parent::display($tpl);

		// Deactivate the main menu
		// ??? JRequest::setVar( 'hidemainmenu', 1 );
	}

// 			<button type="button" onclick="Joomla.submitform('component.apply', this.form);">
// 			<button type="button" onclick="Joomla.submitform('component.save', this.form);">
// 			<button type="button" onclick="window.parent.SqueezeBox.close();">

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar() 
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('COM_ATTACHMENTS_CONFIGURATION'), 'attachments.png');
		JToolBarHelper::apply('params.apply');
		JToolBarHelper::save('params.save');
		JToolBarHelper::cancel('params.cancel', 'JTOOLBAR_CLOSE');
	}
	
}

?>
