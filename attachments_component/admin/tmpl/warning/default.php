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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

/** @var \Joomla\CMS\Application\CMSApplication $app */
$app = Factory::getApplication();
$template = $app->getTemplate();

// Load the tooltip behavior.
HTMLHelper::_('bootstrap.tooltip');

$uri = Uri::getInstance();
$document = $app->getDocument();

// Hide the vertical scrollbar using javascript
$hide_scrollbar = "document.addEventListener('DOMContentLoaded', (event) => {
	   document.documentElement.style.overflow = \"hidden\";
	   document.body.scroll = \"no\";});";
$document->addScriptDeclaration($hide_scrollbar);

?>
<div class="attachmentsWarning">
	 <h1><?php echo $this->warning_title; ?></h1>
	 <h2 id="warning_msg"><?php echo $this->warning_question ?></h2>
  <form action="<?php echo $this->action_url; ?>" name="warning_form" method="post">
	<div class="form_buttons" align="center">
	   <span class="left">&nbsp;</span>
	   <input type="submit" name="submit" value="<?php echo $this->action_button_label ?>" />
	   <span class="right">
		  <input type="button" name="cancel" value="<?php echo Text::_('ATTACH_CANCEL'); ?>"
				 onClick="window.parent.bootstrap.Modal.getInstance(window.parent.document.querySelector('.joomla-modal.show')).hide();" />
	   </span>
	</div>
	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="from" value="<?php echo $this->from;?>" />

	<?php echo HTMLHelper::_( 'form.token' ); ?>
  </form>
 </div>
