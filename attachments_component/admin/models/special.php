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

jimport( 'joomla.application.component.model' );

/**
 * Model for the attachments special controller
 * (Copied from administrator/components/com_config/models/component.php) 
 *
 * @package Attachments
 */
class AttachmentsModelSpecial extends JModel
{
	/**
	 * Get the params for the configuration variables
	 */
	function &getParams()
	{
		static $instance;

		if ($instance == null)
		{
			$table =& JTable::getInstance('component');
			$table->loadByOption( 'com_attachments' );

			// work out file path
			if ($path = JRequest::getString( 'path' )) {
				$path = JPath::clean( JPATH_SITE.DS.$path );
				JPath::check( $path );
				} else {
				$option = preg_replace( '#\W#', '', $table->option );
				$path	= JPATH_ADMINISTRATOR.DS.'components'.DS.$option.DS.'config.xml';
				}
						
			jimport('joomla.filesystem.file');
			if (JFile::exists( $path )) {
				$instance = new JParameter( $table->params, $path );
				} else {
				$instance = new JParameter( $table->params );
				}
			}
		return $instance;
		}
}

?>
