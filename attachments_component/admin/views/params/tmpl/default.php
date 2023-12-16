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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;


// Load the tooltip behavior.
//JHtml::_('behavior.tooltip');
HTMLHelper::_('bootstrap.tooltip');
if (version_compare(JVERSION, '3.0', 'lt'))
{
	JHtml::_('behavior.formvalidation');
}
if (version_compare(JVERSION, '3.0', 'ge'))
{
	JHtml::_('formbehavior.chosen', 'select');
}

if ($this->fieldsets)
{
	HTMLHelper::_('bootstrap.framework');
}

$xml = $this->form->getXml();


?>
<script type="text/javascript">
	Joomla.submitbutton = function(task)
	{
		/*if (document.formvalidator.isValid(document.id('component-form')))
		{*/
			Joomla.submitform(task, document.getElementById('component-form'));
		/*}*/
	}
</script>
<?php

if (version_compare(JVERSION, '3.0', 'ge'))
{
?>
<form action="<?php echo JRoute::_('index.php?option=com_config');?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate form-horizontal">
	<div class="row-fluid">
		<!-- Begin Content -->
		<div class="col-md-12" id="config">
			<?php if ($this->fieldsets) : ?>
				<?php $opentab = 0; ?>

				<?php echo HTMLHelper::_('uitab.startTabSet', 'configTabs'); ?>

				<?php foreach ($this->fieldsets as $name => $fieldSet) : ?>
					<?php
					$hasChildren = $xml->xpath('//fieldset[@name="' . $name . '"]/fieldset');
					$hasParent = $xml->xpath('//fieldset/fieldset[@name="' . $name . '"]');
					$isGrandchild = $xml->xpath('//fieldset/fieldset/fieldset[@name="' . $name . '"]');
					?>

					<?php $dataShowOn = ''; ?>
					<?php if (!empty($fieldSet->showon)) : ?>
						<?php $wa->useScript('showon'); ?>
						<?php $dataShowOn = ' data-showon=\'' . json_encode(FormHelper::parseShowOnConditions($fieldSet->showon, $this->formControl)) . '\''; ?>
					<?php endif; ?>

					<?php $label = empty($fieldSet->label) ? 'XMAP_FIELDSET_' . strtoupper($name) : $fieldSet->label; ?>

					<?php if (!$isGrandchild && $hasParent) : ?>
						<fieldset id="fieldset-<?php echo $this->escape($name); ?>" class="options-menu options-form">
							<legend><?php echo Text::_($fieldSet->label); ?></legend>
							<div>
					<?php elseif (!$hasParent) : ?>
						<?php if ($opentab) : ?>

							<?php if ($opentab > 1) : ?>
								</div>
								</fieldset>
							<?php endif; ?>

							<?php echo HTMLHelper::_('uitab.endTab'); ?>

						<?php endif; ?>

						<?php echo HTMLHelper::_('uitab.addTab', 'configTabs', $name, Text::_($label)); ?>

						<?php $opentab = 1; ?>

						<?php if (!$hasChildren) : ?>

						<fieldset id="fieldset-<?php echo $this->escape($name); ?>" class="options-menu options-form">
							<legend><?php echo Text::_($fieldSet->label); ?></legend>
							<div>
						<?php $opentab = 2; ?>
						<?php endif; ?>
					<?php endif; ?>

					<?php if (!empty($fieldSet->description)) : ?>
						<div class="tab-description alert alert-info">
							<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
							<?php echo Text::_($fieldSet->description); ?>
						</div>
					<?php endif; ?>

					<?php if (!$hasChildren) : ?>
						<?php echo $this->form->renderFieldset($name, $name === 'permissions' ? ['hiddenLabel' => true, 'class' => 'revert-controls'] : []); ?>
					<?php endif; ?>

					<?php if (!$isGrandchild && $hasParent) : ?>
						</div>
					</fieldset>
					<?php endif; ?>
				<?php endforeach; ?>

				<?php if ($opentab) : ?>

					<?php if ($opentab > 1) : ?>
						</div>
						</fieldset>
					<?php endif; ?>
					<?php echo HTMLHelper::_('uitab.endTab'); ?>
				<?php endif; ?>

			<?php echo HTMLHelper::_('uitab.endTabSet'); ?>
			<?php endif; ?>
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
<form action="<?php echo JRoute::_('index.php?option=com_config');?>" id="component-form" method="post" name="adminForm" autocomplete="off" class="form-validate">
	<?php
	echo JHtml::_('tabs.start','config-tabs-'.$this->component->option.'_configuration', array('useCookie'=>1));
		$fieldSets = $this->form->getFieldsets();
		foreach ($fieldSets as $name => $fieldSet) :
			$label = empty($fieldSet->label) ? 'COM_CONFIG_'.$name.'_FIELDSET_LABEL' : $fieldSet->label;
			echo JHtml::_('tabs.panel',JText::_($label), 'publishing-details');
			if (isset($fieldSet->description) && !empty($fieldSet->description)) :
				echo '<p class="tab-description">'.JText::_($fieldSet->description).'</p>';
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
	echo JHtml::_('tabs.end');
	?>
	<div>
		<input type="hidden" name="id" value="<?php echo $this->component->id;?>" />
		<input type="hidden" name="option" value="com_attachments" />
		<input type="hidden" name="component" value="com_attachments" />
		<input type="hidden" name="old_secure" value="<?php echo $this->params->get('secure'); ?>" />
		<input type="hidden" name="task" value="params.edit" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
<?php
}
