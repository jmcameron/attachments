<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
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
	public function display($tpl = null)
	{
		$this->addToolBar();

		parent::display($tpl);
	}

	/**
	 * Setting the toolbar
	 */
	protected function addToolBar()
	{
		JRequest::setVar('hidemainmenu', true);
		JToolBarHelper::title(JText::_('ATTACH_CONFIGURATION'), 'attachments.png');
		JToolBarHelper::apply('params.apply');
		JToolBarHelper::save('params.save');
		JToolBarHelper::cancel('params.cancel', 'JTOOLBAR_CLOSE');
	}

}
