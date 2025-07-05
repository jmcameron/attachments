<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Attachments_Component
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


/** @var \Joomla\CMS\Application\CMSApplication $app */
$app = Factory::getApplication();
$template = $app->getTemplate();

// Load the tooltip behavior.
HTMLHelper::_('bootstrap.tooltip');

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $app->getDocument()->getWebAssetManager();
$wa->useScript('form.validate');

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

<form action="<?php echo Route::_('index.php?option=com_config');?>" 
      id="component-form" method="post" name="adminForm" autocomplete="off" 
      class="form-validate form-horizontal">
    <div class="row-fluid">
        <div class="span10">
            <?php echo HTMLHelper::_("uitab.startTabSet", "configTabs", ["active" => "basic"]);
                    $fieldSets = $this->form->getFieldsets();
            foreach ($fieldSets as $name => $fieldSet) :
                $label = empty($fieldSet->label) ? 'COM_CONFIG_' . $name . '_FIELDSET_LABEL' : $fieldSet->label;
                echo HTMLHelper::_("uitab.addTab", "configTabs", $name, Text::_($label));
                if (isset($fieldSet->description) && !empty($fieldSet->description)) :
                    echo '<p class="tab-description">' . Text::_($fieldSet->description) . '</p>';
                endif;
                $options = [];
                if ($name == "permissions") {
                    $options['hiddenLabel'] = true;
                }
                echo $this->form->renderFieldset($name, $options);

                echo HTMLHelper::_("uitab.endTab");
            endforeach;
                echo HTMLHelper::_("form.token");
                echo HTMLHelper::_("uitab.endTabSet"); ?>
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

