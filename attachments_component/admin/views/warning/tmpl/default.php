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

$template = JFactory::getApplication()->getTemplate();

// Load the tooltip behavior.
JHtml::_('behavior.tooltip');

$uri = JFactory::getURI();
$document = JFactory::getDocument();

// Hide the vertical scrollbar using javascript
$hide_scrollbar = "window.addEvent('domready', function() {
	   document.documentElement.style.overflow = \"hidden\";
	   document.body.scroll = \"no\";});";
$document->addScriptDeclaration($hide_scrollbar);

$document->addStyleSheet( $uri->base(true) . '/components/com_attachments/media/attachments.css',
						  'text/css', null, array() );

$lang = JFactory::getLanguage();
if ( $lang->isRTL() ) {
	$document->addStyleSheet( $uri->root(true) . '/components/com_attachments/media/attachments_rtl.css',
							  'text/css', null, array() );
	}

?>
<div class="attachmentsWarning">
	 <h1><?php echo $this->warning_title; ?></h1>
	 <h2 id="warning_msg"><?php echo $this->warning_question ?></h2>
  <form action="<?php echo $this->action_url; ?>" name="warning_form" method="post">
	<div align="center">
	   <span class="left">&nbsp;</span>
	   <input type="submit" name="submit" value="<?php echo $this->action_button_label ?>" />
	   <span class="right">
		  <input type="button" name="cancel" value="<?php echo JText::_('ATTACH_CANCEL'); ?>"
				 onClick="window.parent.SqueezeBox.close();" />
	   </span>
	</div>
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="from" value="<?php echo $this->from;?>" />

	<?php echo JHTML::_( 'form.token' ); ?>
  </form>
 </div>
