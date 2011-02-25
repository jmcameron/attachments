<?php
/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2011 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

global $option;

// Add the plugins stylesheet to style the list of attachments
$document =&  JFactory::getDocument();
$uri = JFactory::getURI();
$document->addStyleSheet( $uri->root(true) . '/components/com_attachments/attachments.css', 
						  'text/css', null, array() );

$lang =& JFactory::getLanguage(); 

if ( $lang->isRTL() ) {
	$document->addStyleSheet( $uri->root(true) . '/plugins/content/attachments/attachments_rtl.css', 
							  'text/css', null, array() );
	}

?>
	<form action="index.php" method="post" name="adminForm">

		<fieldset class="attachments">
			<legend>
				<?php echo JText::_( 'CONFIGURATION' ); ?>
			</legend>
			<?php echo $this->params->render(); ?>
		</fieldset>

		<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
		<input type="hidden" name="option" value="com_attachments" />
		<input type="hidden" name="old_secure" value="<?php echo $this->params->get('secure'); ?>" />
		<input type="hidden" name="old_upload_dir" value="<?php echo $this->params->get('attachments_subdir', 'attachments'); ?>" />
		<input type="hidden" name="task" value="editParams" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
<?php

?>
