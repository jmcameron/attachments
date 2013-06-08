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

// No direct access
defined('_JEXEC') or die();

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');

/**
 * View for the uploads
 *
 * @package Attachments
 */
class AttachmentsFormView extends JViewLegacy
{

	/**
	 * Return the starting HTML for the page
	 *
	 * Note: When displaying a View directly from user code (not a conroller),
	 *       it does not automatically create the HTML <html>, <body> and
	 *       <head> tags.  This code fixes that.
	 *
	 *       There is probably a better way to do this!
	 */
	protected function startHTML()
	{
		jimport('joomla.filesystem.file');

		require_once JPATH_BASE.'/libraries/joomla/document/html/renderer/head.php';
		$document = JFactory::getDocument();
		$this->assignRef('document', $document);

		$app = JFactory::getApplication();
		$this->template = $app->getTemplate(true)->template;
		$template_dir = $this->baseurl.'/templates/'.$this->template;

		$file ='/templates/system/css/system.css';
		if (JFile::exists(JPATH_SITE.$file)) {
			$document->addStyleSheet($this->baseurl.$file);
			}

		// Try to add the typical template stylesheets
		$files = Array('template.css', 'position.css', 'layout.css', 'general.css');
        foreach($files as $file) {
			$path = JPATH_SITE.'/templates/'.$this->template.'/css/'.$file;
			if (JFile::exists($path)) {
				$document->addStyleSheet($this->baseurl.'/templates/'.$this->template.'/css/'.$file);
				}
			}

		// Add the CSS for the attachments list (whether we need it or not)
		JHtml::stylesheet('com_attachments/attachments_list.css', array(), true);

		$head_renderer = new JDocumentRendererHead($document);

		$html = '';
		$html .= "<html>\n";
		$html .= "<head>\n";
		$html .= $head_renderer->fetchHead($document);
		$html .= "</head>\n";
		$html .= "<body id=\"attachments_iframe\">\n";

		return $html;
	}


	/**
	 * Return the ending HTML tags for the page
	 */
	protected function endHTML()
	{
		$html = "\n";
		$html .= "</body>\n";
		$html .= "</html>\n";

		return $html;
	}
}
