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

/** Define the legacy classes, if necessary */
require_once(JPATH_SITE.'/components/com_attachments/legacy/view.php');

/** Load the Attachments defines */
require_once(JPATH_SITE.'/components/com_attachments/defines.php');


/**
 * View for the utils controller
 * 
 * @package Attachments
 */
class AttachmentsViewHelp extends JViewLegacy
{
	/**
	 * Data about each of the document section headers
	 */
	protected $sections = null;
	
	/**
	 * Display the view
	 *
	 * @param   string  $tpl  A template file to load. [optional]
	 *
	 */
	public function display($tpl = null)
	{
		$this->version = AttachmentsDefines::$ATTACHMENTS_VERSION;
		$this->date = AttachmentsDefines::$ATTACHMENTS_VERSION_DATE;

		parent::display($tpl);
	}



	function sectionLink($snum)
	{
		$id = $this->sections[$snum]['id'];
		$title = $this->sections[$snum]['title'];
		return "<a class=\"reference internal\" href=\"#$id\">$title</a>";
	}


	function replace($html, $replacements)
	{
		if ( is_array($replacements) )
		{
			foreach ($replacements as $tag => $replace)
			{
				$html = str_replace($tag, $replace, $html);
			}
		}

		return $html;
	}


	function sectionTOC($snum)
	{
		$sect_data = $this->sections[$snum];
		$sid = $sect_data['id'];
		$stitle = $sect_data['title'];
		echo "   <li><a class=\"reference internal\" href=\"#$sid\" id=\"id$snum\">$stitle</a></li>\n";
	}


	function startSection($snum)
	{
		$sect_data = $this->sections[$snum];
		$sid = $sect_data['id'];
		$text_code = $sect_data['code'];
		$stitle = $sect_data['title'];
		$tcid = "<span class=\"text_code\">[$text_code]</span>";
		$hclass = 'class="toc-backref"';
		$html =  "<div class=\"section\" id=\"$sid\">\n";
		$html .= "<h1><a $hclass href=\"#id$snum\">$stitle$tcid</a></h1>\n";
		echo $html;
	}

	function endSection($snum)
	{
		echo "</div><?-- end of section $snum -->\n";
	}

	function startSubSection($sect_data)
	{
		$sid = $sect_data['id'];
		$stitle = $sect_data['title'];
		$html =  "<div class=\"section\" id=\"$sid\">\n";
		$html .= "<h2>$stitle</h2>\n";
		echo $html;
	}

	function endSubSection($title)
	{
		echo "</div><?-- end of subsection $title -->\n";
	}

	function addAdmonition($type, $type_code, $text_codes, $replacements = null, $terminate = true)
	{
		$title = JText::_($type_code);
		if (!is_array($text_codes))
		{
			$text_codes = Array($text_codes);
		}

		$html  = "<div class=\"$type\">\n";
		$html .= "   <p class=\"first admonition-title\">$title</p>\n";
		foreach ($text_codes as $text_code)
		{
			$tcid = "<span class=\"text_code\">[$text_code]</span>";
			$text = $this->replace(JText::_($text_code), $replacements);
			$html .= "   <p class=\"last\">" . $text . $tcid . "</p>\n";
		}
		if ( $terminate )
		{
			$html .= "</div>\n";
		}

		echo $html;
	}

	function endAdmonition()
	{
		echo "</div>\n";
	}


	function addHint($texts, $replacements = null, $terminate = true)
	{
		echo $this->addAdmonition('hint', 'ATTACH_HELP_HINT', $texts, $replacements, $terminate);
	}

	function addImportant($texts, $replacements = null, $terminate = true)
	{
		echo $this->addAdmonition('important', 'ATTACH_HELP_IMPORTANT', $texts, $replacements, $terminate);
	}

	function addNote($texts, $replacements = null, $terminate = true)
	{
		echo $this->addAdmonition('note', 'ATTACH_HELP_NOTE', $texts, $replacements, $terminate);
	}

	function addWarning($texts, $replacements = null, $terminate = true)
	{
		echo $this->addAdmonition('warning', 'ATTACH_HELP_WARNING', $texts, $replacements, $terminate);
	}


	function addParagraph($text_codes, $replacements = null, $pclass = null)
	{
		if (!is_array($text_codes))
		{
			$text_codes = Array($text_codes);
		}
		$html = '';
		foreach ($text_codes as $text_code)
		{
			$tcid = "<span class=\"text_code\">[$text_code]</span>";
			$text = $this->replace(JText::_($text_code), $replacements) . $tcid;
			if ($pclass)
			{
				$html .= "<p class=\"$pclass\">" . $text . "</p>\n";
			}
			else
			{
				$html .= '<p>' . $text . "</p>\n";
			}
		}
    
		echo $html;
	}

	function addPreBlock($text, $class='literal-block')
	{
		$html = "<pre class=\"$class\">\n";
		$html .= $text . "\n";
		$html .= "</pre>\n";
		echo $html;
	}

	function startList($type = 'ul', $class='simple')
	{
		echo "<$type class=\"$class\">\n";
	}

	function addListElement($text_codes, $replacements = null, $terminate = true)
	{
		if (!is_array($text_codes))
		{
			$text_codes = Array($text_codes);
		}

		$html = '<li>';

		foreach ($text_codes as $text_code)
		{
			$tcid = "<span class=\"text_code\">[$text_code]</span>";
			$text = $this->replace(JText::_($text_code), $replacements);
			$html .= "<p>" . $text . $tcid . "</p>\n";
		}

		if ($terminate)
		{
			$html .= "</li>\n";
		}

		echo $html;
	}

	function addListElementLink($url, $text_code)
	{
		$tcid = "<span class=\"text_code\">[$text_code]</span>";
		$text = $this->replace(JText::_($text_code), Array('{LINK}' => $url));
		echo "<li><a class=\"reference external\" href=\"$url\">$text$tcid</a></li>\n";
	}

	function endListElement()
	{
		echo "</li>\n";
	}

	function addListElementHtml($html)
	{
		echo "<li>$html</li>\n";
	}

	function endList($type = 'ul')
	{
		echo "</$type>\n";
	}

	function addLineBreak()
	{
		echo "<br/>\n";
	}

	function addFigure($filename, $alt_code, $caption_code = null, $dclass = 'figure')
	{
		$html = "<div class=\"$dclass\">\n";
		$html .= $this->image($filename, JText::_($alt_code)) . "\n";
		if ( $caption_code )
		{
			$html .= '<p class="caption">' . JText::_($caption_code) . "</p>\n";
		}
		$html .= '</div>';
		echo $html;
	}

	function startPermissionsTable($col1, $col2, $col3)
	{
		echo "<table id=\"permissions\"class=\"permissions docutils\">\n";
		echo "<colgroup>\n";
		echo "  <col class=\"col_perm_name\"/>\n";
		echo "  <col class=\"col_perm_note\"/>\n";
		echo "  <col class=\"col_perm_action\"/>\n";
		echo "</colgroup>\n";
		echo "<thead>\n";
		echo "  <tr>\n";
		echo "     <th class=\"head\">".JText::_($col1)."</th>\n";
		echo "     <th class=\"head\">".JText::_($col2)."</th>\n";
		echo "     <th class=\"head\">".JText::_($col3)."</th>\n";
		echo "  </tr>\n";
		echo "</thead>\n";
		echo "<tbody>\n";
	}

	function addPermissionsTableRow($col1, $col2, $col3)
	{
		echo "  <tr>\n";
		echo "     <td>".JText::_($col1)."</td>\n";
		echo "     <td>".JText::_($col2)."</td>\n";
		echo "     <td>".JText::_($col3)."</td>\n";
		echo "  </tr>\n";
	}

	function endPermissionsTable()
	{
		echo "</table>\n";
	}

	function textCodeSpan($text_code)
	{
		return "<span class=\"text_code\">[$text_code]</span>";
	}
		


	/**
	 * Return an image URL if the file is found
	 *
	 * @return string image URL (or null if the image was not found)
	 */
	function image($filename, $alt, $attribs = Array())
	{
		$lcode = $this->lang->getDefault();

		// First try the current language
		$img = JHtml::image('com_attachments/help/' . $lcode . '/' . $filename, $alt, $attribs, true);

		if ($img)
		{
			return $img;
		}

		// If that fails, return the English/en-GB image
		if ( $lcode != 'en-GB' )
		{
			return JHtml::image('com_attachments/help/en-GB/' . $filename, $alt, $attribs, true);
		}

		// The image was not found for either language so return nothing
		return null;
	}
	
}
