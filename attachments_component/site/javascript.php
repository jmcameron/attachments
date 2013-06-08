<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2013 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

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
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtml::_('behavior.framework');
		}
		else
		{
			// up the style sheet (to get the visual for the button working)
			JHtml::_('behavior.mootools');
		}
		if ($add_refresh_script)
		{
			JHtml::script('com_attachments/attachments_refresh.js', false, true);
		}
	}


	/**
	 * Close the iframe
	 */
	public static function closeIframeRefreshAttachments($base_url, $parent_type, $parent_entity, $parent_id, $from)
	{
		echo "<script type=\"text/javascript\">
			window.parent.refreshAttachments(\"$base_url\",\"$parent_type\",\"$parent_entity\",$parent_id,\"$from\");
			window.parent.SqueezeBox.close();
			</script>";
	}


	/**
	 * Set up the Javascript for the modal button
	 */
	public static function setupModalJavascript()
	{
		if (version_compare(JVERSION, '3.0', 'ge'))
		{
			JHtml::_('behavior.modal', 'a.modal-button');
		}
		else
		{
			JHtml::_('behavior.modal', 'a.modal-button');
		}
	}


	/**
	 * Close the modal window and reload the parent
	 */
	public static function closeModal()
	{
		echo '<script>var myparent = window.parent; window.parent.SqueezeBox.close(); myparent.location.reload();</script>';
	}

}
