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

// Access check.
if (!JFactory::getUser()->authorise('core.admin', 'com_attachments')) {
	return JError::raiseError(404, JText::_('JERROR_ALERTNOAUTHOR') . ' (ERR 171)');
}

jimport( 'joomla.application.component.view' );

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');


/**
 * View for the special controller
 * (adapted from administrator/components/com_config/views/component/view.php)
 *
 * @package Attachments
 */
class AttachmentsViewAdminUtils extends JViewLegacy
{
	/**
	 * Display the view
	 */
	public function display($tpl = null)
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$uri = JFactory::getURI();

		// Add the style sheets
		JHtml::stylesheet('com_attachments/attachments_admin_utils.css', Array(), true);
		$lang = JFactory::getLanguage();
		if ( $lang->isRTL() ) {
			JHtml::stylesheet('com_attachments/attachments_admin_form_utils.css', Array(), true);
			}

		// Hide the vertical scrollbar using javascript
		$hide_scrollbar = "window.addEvent('domready', function() {
			   document.documentElement.style.overflow = \"hidden\";
			   document.body.scroll = \"no\";});";
		$document->addScriptDeclaration($hide_scrollbar);

?>
<div class="attachmentsAdmin" id="utilsList">
  <h1><?php echo JText::_('ATTACH_ATTACHMENTS_ADMINISTRATIVE_UTILITY_COMMANDS'); ?></h1>
  <ul>
<?php foreach ($this->entries as $link_html) {
		  echo "	  <li><h2>$link_html</h2></li>\n";
	}
?>
  </ul>
</div>
<?php
	}
}
