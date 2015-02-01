<?php
/**
 * Attachments quickicon plugin
 *
 * @package Attachments
 * @subpackage Attachments.Quickicon_Plugin
 *
 * @copyright Copyright (C) 2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

jimport('joomla.plugin.plugin');


/**
 * Attachments quickcion plugin class
 *
 * @package		Attachments
 * @subpackage  Attachments.Quickicon_Plugin
 */
class PlgQuickiconAttachments extends JPlugin
{
    /*
     * Constructor.
	 *
	 * @access		protected
	 * @param		object	$subject The object to observe
	 * @param		array	$config  An array that holds the plugin configuration
     */
    public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
    }


	/**
	 * This method is called when the Quick Icons module is constructing its set
	 * of icons. You can return an array which defines a single icon and it will
	 * be rendered right after the stock Quick Icons.
	 *
	 * @param  $context  The calling context
	 *
	 * @return array A list of icon definition associative arrays, consisting of the
	 *				 keys link, image, text and access.
	 *
	 * @since       2.5
	 */
	public function onGetIcons($context)
	{
		// See if we should show the icon
		if ($context != $this->params->get('context', 'mod_quickicon') ||
			!JFactory::getUser()->authorise('core.manage', 'com_attachments'))
		{
			return;
		}

		// Add the CSS file
		JHtml::stylesheet('com_attachments/attachments_quickicon.css', array(), true);

		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			$image = 'flag-2';
			$icon = JUri::root() . '/media/com_attachments/images/attachments_logo48.png';
		}
		else
		{
			$image = JUri::root() . '/media/com_attachments/images/attachments_logo48.png';
			$icon = '';
		}

		// Return the icon info for the quickicon system
		return
			array(
				array(
					'link' => 'index.php?option=com_attachments',
					'image' => $image,
					'icon' => $icon,
					'text' => JText::_('PLG_QUICKICON_ATTACHMENTS_ICON'),
					'id' => 'plg_quickicon_attachment'));
    }
}
