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

namespace JMCameron\Component\Attachments\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

/**
 * A class for attachments javascript functions
 *
 * @package Attachments
 */
class AttachmentsJavascript
{
	/**
	 * Set up the appropriate Javascript framework
	 */
	public static function setupJavascript($add_refresh_script = true)
	{
		if ($add_refresh_script)
		{
			HTMLHelper::script('com_attachments/attachments_refresh.js', ['relative' => true]);
		}
	}


	/**
	 * Close the iframe
	 */
	public static function closeIframeRefreshAttachments($base_url, $parent_type, $parent_entity, $parent_id, $lang, $from, $refresh = true)
	{
		echo "<script type=\"text/javascript\">
			let iframe = window.parent.document.querySelector(\".modal.show iframe\");
			// Refresh iframe before closing
			if (\"$refresh\" && iframe) iframe.src += '';

			window.parent.refreshAttachments(\"$base_url\",\"$parent_type\",\"$parent_entity\",$parent_id,\"$lang\",\"$from\");
			window.parent.bootstrap.Modal.getInstance(window.parent.document.querySelector('.joomla-modal.show')).hide();
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
		echo '<script>var myparent = window.parent; myparent.bootstrap.Modal.getInstance(myparent.document.querySelector(\'.joomla-modal.show\')).hide(); myparent.location.reload();</script>';
	}

}
