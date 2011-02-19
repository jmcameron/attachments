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

jimport( 'joomla.application.component.view' );

/**
 * View for the special controller
 * (adapted from administrator/components/com_config/views/component/view.php) 
 *
 * @package Attachments
 */
class AttachmentsViewSpecial extends JView
{
	/**
	 * Display the special view
	 */
	function display()
	{
	   $model		 =& $this->getModel();
	   $params		 =& $model->getParams();

?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
	<html>
	<head>
	   <title>Edit Attachments Parameters</title>
	   <link href="templates/khepri/css/general.css" rel="stylesheet" type="text/css" />
	   <link href="templates/khepri/css/component.css" rel="stylesheet" type="text/css" />
	</head>
	<body>
	<h1>Edit Attachments Parameters</h1>
	<form action="index.php" method="post" name="adminForm">
		<div align="center">
			<input type="submit" name="submit" value="Submit" />
		</div>
		<fieldset>
			<legend>
				<?php echo JText::_( 'CONFIGURATION' ); ?>
			</legend>
			<?php echo $params->render();?>
		</fieldset>

		<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
		<input type="hidden" name="option" value="com_config" />
		<input type="hidden" name="component" value="com_attachments" />
		<input type="hidden" name="controller" value="component" />
		<input type="hidden" name="tmpl" value="component" />
		<input type="hidden" name="task" value="save" />
		<?php echo JHTML::_( 'form.token' ); ?>
	</form>
	</body>
	</html>
<?php
	exit();
	}
}