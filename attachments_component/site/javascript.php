<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2018 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * A class for attachments javascript functions
 *
 * @package Attachments
 */
class AttachmentsJavascript
{
	/**
	 * Set up the appropriate Javascript framework (Mootools or jQuery)
	 */
	public static function setupJavascript($add_refresh_script = true)
	{
		HTMLHelper::_('bootstrap.framework', true);

		if ($add_refresh_script)
		{
			HTMLHelper::script('com_attachments/attachments_refresh.js', false, true);
		}
	}


	/**
	 * Close the iframe
	 */
	public static function closeIframeRefreshAttachments($base_url, $parent_type, $parent_entity, $parent_id, $lang, $from)
	{
		echo "<script type=\"text/javascript\">
			window.parent.refreshAttachments(\"$base_url\",\"$parent_type\",\"$parent_entity\",$parent_id,\"$lang\",\"$from\");
			window.parent.SqueezeBox.close();
			</script>";
	}


	/**
	 * Set up the Javascript for the modal button
	 */
	public static function setupModalJavascript()
	{
		HTMLHelper::_('bootstrap.modal', 'a.modal-button');
	}


	/**
	 * Close the modal window and reload the parent
	 */
	public static function closeModal()
	{
		echo '<script>var myparent = window.parent; window.parent.SqueezeBox.close(); myparent.location.reload();</script>';
	}

}
