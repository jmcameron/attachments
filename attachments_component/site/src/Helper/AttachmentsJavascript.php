<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace JMCameron\Component\Attachments\Site\Helper;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


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
        if ($add_refresh_script) {
            HTMLHelper::script('com_attachments/attachments_refresh.js', ['relative' => true]);
        }
    }


    /**
     * Close the iframe
     */
    public static function closeIframeRefreshAttachments(
        $base_url,
        $parent_type,
        $parent_entity,
        $parent_id,
        $lang,
        $from,
        $refresh = true
    ) {
        echo "<script type=\"text/javascript\">
			let iframe = window.parent.document.querySelector(\".modal.show iframe\");
			// Refresh iframe before closing
			if (\"$refresh\" && iframe) iframe.src += '';

			window.parent.refreshAttachments(\"$base_url\",\"$parent_type\",
                                              \"$parent_entity\",$parent_id,\"$lang\",\"$from\");
			window.parent.bootstrap.Modal.getInstance(
                        window.parent.document.querySelector('.joomla-modal.show')).hide();
			</script>";
    }


    /**
     * Set up the Javascript for the modal button selector is the selector of the modal
     */
    public static function setupModalJavascript()
    {
        HTMLHelper::_('bootstrap.modal', '.joomla-modal');
    }




    /**
     * Close the modal window and reload the parent
     */
    public static function closeModal()
    {
        echo '<script>
                var myparent = window.parent; myparent.bootstrap.Modal.getInstance(
                    myparent.document.querySelector(\'.joomla-modal.show\')).hide();
                myparent.location.reload();
               </script>';
    }

    public static function modifyLinksForDesktop(): void
    {
        echo '<script type="text/javascript">
            document.addEventListener("DOMContentLoaded", function() {
                if (!/mobi|android|webos|iphone|ipad|ipod|blackberry|opera mini/i.test(navigator.userAgent)) {
                    const links = document.querySelectorAll("a.attachment.modal-button");
                    links.forEach(function(link) {
                        link.removeAttribute("href");
                        link.setAttribute("type", "button");
                        link.setAttribute("data-bs-toggle", "modal");
                    });
                }
            });
            </script>';
    }
}
