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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

$app = Factory::getApplication();
$template = $app->getTemplate();

// Load the tooltip behavior.
HTMLHelper::_('bootstrap.tooltip');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
$wa->useScript('form.validate');


if (version_compare(JVERSION, '3.0', 'ge'))
{
	HTMLHelper::_('formbehavior.chosen', 'select');
}

$document = $app->getDocument();
$uri = Uri::getInstance();

?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		if (document.formvalidator.isValid(document.getElementById('component-form')))
		{
			Joomla.submitform(task, document.getElementById('component-form'));
		}
	}
</script>
<?php

if (version_compare(JVERSION, '3.0', 'ge'))
{
?>
<form action="<?php echo Route::_('index.php?option=com_config');?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate form-horizontal">
	<div class="row-fluid">
		<div class="span10">
			<ul class="nav nav-tabs" id="configTabs">
				<?php
					$fieldSets = $this->form->getFieldsets();
					foreach ($fieldSets as $name => $fieldSet) :
						$label = empty($fieldSet->label) ? 'COM_CONFIG_'.$name.'_FIELDSET_LABEL' : $fieldSet->label;
				?>
					<li><a href="#<?php echo $name;?>" data-toggle="tab"><?php echo	 Text::_($label);?></a></li>
				<?php
					endforeach;
				?>
			</ul>
			<div class="tab-content">
				<?php
					$fieldSets = $this->form->getFieldsets();
					foreach ($fieldSets as $name => $fieldSet) :
				?>
					<div class="tab-pane" id="<?php echo $name;?>">
						<?php
							if (isset($fieldSet->description) && !empty($fieldSet->description)) :
								echo '<p class="tab-description">'.Text::_($fieldSet->description).'</p>';
							endif;
							foreach ($this->form->getFieldset($name) as $field):
						?>
							<div class="control-group">
						<?php if (!$field->hidden && $name != "permissions") : ?>
								<div class="control-label">
									<?php echo $field->label; ?>
								</div>
						<?php endif; ?>
						<div class="<?php if ($name != "permissions") : ?>controls<?php endif; ?>">
							<?php echo $field->input; ?>
						</div>
					</div>
				<?php
					endforeach;
				?>
				</div>
				<?php
				endforeach;
				?>
			</div>
		</div>
	</div>
	<div>
		<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
		<input type="hidden" name="option" value="com_attachments" />
		<input type="hidden" name="component" value="com_attachments" />
		<input type="hidden" name="old_secure" value="<?php echo $this->params->get('secure'); ?>" />
		<input type="hidden" name="task" value="params.edit" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
<script type="text/javascript">
		jQuery('#configTabs a:first').tab('show'); // Select first tab
</script>
<?php
}
else
{
?>
<form action="<?php echo Route::_('index.php?option=com_config');?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate">
	<?php
	echo HTMLHelper::_('tabs.start','config-tabs-'.$this->component->option.'_configuration', array('useCookie'=>1));
		$fieldSets = $this->form->getFieldsets();
		foreach ($fieldSets as $name => $fieldSet) :
			$label = empty($fieldSet->label) ? 'COM_CONFIG_'.$name.'_FIELDSET_LABEL' : $fieldSet->label;
			echo HTMLHelper::_('tabs.panel',Text::_($label), 'publishing-details');
			if (isset($fieldSet->description) && !empty($fieldSet->description)) :
				echo '<p class="tab-description">'.Text::_($fieldSet->description).'</p>';
			endif;
	?>
			<ul class="config-option-list" id="attachments-options">
			<?php
			foreach ($this->form->getFieldset($name) as $field):
			?>
				<li>
				<?php if (!$field->hidden) : ?>
				<?php echo $field->label; ?>
				<?php endif; ?>
				<?php echo $field->input; ?>
				</li>
			<?php
			endforeach;
			?>
			</ul>


	<div class="clr"></div>
	<?php
		endforeach;
	echo HTMLHelper::_('tabs.end');
	?>
	<div>
		<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
		<input type="hidden" name="option" value="com_attachments" />
		<input type="hidden" name="component" value="com_attachments" />
		<input type="hidden" name="old_secure" value="<?php echo $this->params->get('secure'); ?>" />
		<input type="hidden" name="task" value="params.edit" />
		<?php echo HTMLHelper::_('form.token'); ?>
	</div>
</form>
<?php
}
