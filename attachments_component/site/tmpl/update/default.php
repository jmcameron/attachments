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

use JMCameron\Component\Attachments\Site\Helper\AttachmentsJavascript;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects


// Add the plugins stylesheet to style the list of attachments
/** @var \Joomla\CMS\Application\CMSApplication $app */
$app = Factory::getApplication();
$user = $app->getIdentity();
$document = $app->getDocument();
$uri = Uri::getInstance();

$lang = $app->getLanguage();

// For convenience
$attachment = $this->attachment;
$params = $this->params;
$update = $this->update;

$parent_id = $attachment->parent_id;
if ($parent_id === null) {
    $parent_id = 0;
}

// set up URL redisplay in case of errors
$old_url = '';
if ($this->error_msg && ($update == 'url')) {
    $old_url = $attachment->url;
}

// Decide what type of update to do
if ($update == 'file') {
    $enctype = "enctype=\"multipart/form-data\"";
} else {
    $enctype = '';
}

// Prepare for error displays
$update_id = 'upload';
$filename = $attachment->filename;
if ($this->error) {
    switch ($this->error) {
        case 'no_file':
            $update_id = 'upload_warning';
            $filename = '';
            break;

        case 'file_too_big':
            $upload_id = 'upload_warning';
            break;

        case 'file_already_on_server':
            $upload_id = 'upload_warning';
            break;
    }
}

// Format modified date
$tz = new DateTimeZone($user->getParam('timezone', $app->get('offset')));
$mdate = Factory::getDate($attachment->modified);
$mdate->setTimezone($tz);
$date_format = $params->get('date_format', 'Y-m-d H:i');
$last_modified = $mdate->format($date_format, true);

// If this is an error re-display, display the CSS links directly
$echo_css = $this->error;

// Add the stylesheets
$uri = Uri::getInstance();

AttachmentsJavascript::setupJavascript();

if ($attachment->uri_type == 'file') {
    $header_msg = Text::sprintf('ATTACH_UPDATE_ATTACHMENT_FILE_S', $filename);
} else {
    $header_msg = Text::sprintf('ATTACH_UPDATE_ATTACHMENT_URL_S', $attachment->url);
}

// If this is an error re-display, display the CSS links directly
if ($this->error) {
    echo $this->startHTML();
}

?>
<div id="uploadAttachmentsPage">
<h1><?php echo $header_msg; ?></h1>
<form class="attachments" <?php echo $enctype ?> name="upload_form"
      action="<?php echo $this->save_url; ?>" method="post">
    <fieldset>
        <legend><?php echo Text::sprintf(
            'ATTACH_UPDATE_ATTACHMENT_FOR_PARENT_S_COLON_S',
            $attachment->parent_entity_name,
            $attachment->parent_title
        ); ?></legend>
        <?php if ($this->error_msg) : ?>
        <div class="formWarning" id="formWarning"><?php echo $this->error_msg; ?></div>
        <?php endif; ?>
<?php if ($update == 'file') : ?>
<p><label for="<?php echo $update_id; ?>"><?php
   echo Text::_('ATTACH_SELECT_NEW_FILE_IF_YOU_WANT_TO_UPDATE_ATTACHMENT_FILE') ?></label>
        <a class="changeButton" href="<?php echo $this->normal_update_url ?>"
           title="<?php echo Text::_('ATTACH_NORMAL_UPDATE_TOOLTIP'); ?>"
           ><?php echo Text::_('ATTACH_NORMAL_UPDATE') ?></a> <br />
        <input type="file" name="upload" id="<?php echo $update_id; ?>"
               size="78" maxlength="1024" />
        </p>
<?php elseif ($update == 'url') : ?>
        <p><label for="<?php echo $update_id; ?>"><?php echo Text::_('ATTACH_ENTER_URL') ?></label>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <label for="verify_url"><?php echo Text::_('ATTACH_VERIFY_URL_EXISTENCE') ?></label>
        <input type="checkbox" name="verify_url" value="verify" <?php echo $this->verify_url_checked ?> 
               title="<?php echo Text::_('ATTACH_VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
        &nbsp;&nbsp;&nbsp;&nbsp;
        <label for="relative_url"><?php echo Text::_('ATTACH_RELATIVE_URL') ?></label>
        <input type="checkbox" name="relative_url" value="relative" <?php echo $this->relative_url_checked ?>
               title="<?php echo Text::_('ATTACH_RELATIVE_URL_TOOLTIP'); ?>" />
        <a class="changeButton" href="<?php echo $this->normal_update_url ?>"
           title="<?php echo Text::_('ATTACH_NORMAL_UPDATE_TOOLTIP'); ?>"
           ><?php echo Text::_('ATTACH_NORMAL_UPDATE') ?></a> <br />
        <input type="text" name="url" id="<?php echo $update_id; ?>"
               size="80" maxlength="255" title="<?php echo Text::_('ATTACH_ENTER_URL_TOOLTIP'); ?>"
               value="<?php echo $old_url; ?>" /><br /><?php
                echo Text::_('ATTACH_NOTE_ENTER_URL_WITH_HTTP'); ?>
        </p>
<?php else : ?>
    <?php if ($attachment->uri_type == 'file') : ?>
        <p><label><?php echo Text::_('ATTACH_FILENAME_COLON'); ?></label> <?php echo $filename; ?>
        <a class="changeButton" href="<?php echo $this->change_file_url ?>"
           title="<?php echo Text::_('ATTACH_CHANGE_FILE_TOOLTIP'); ?>"
           ><?php echo Text::_('ATTACH_CHANGE_FILE') ?></a>
        <a class="changeButton" href="<?php echo $this->change_url_url ?>"
           title="<?php echo Text::_('ATTACH_CHANGE_TO_URL_TOOLTIP'); ?>"
           ><?php echo Text::_('ATTACH_CHANGE_TO_URL') ?></a>
        </p>
    <?php elseif ($attachment->uri_type == 'url') : ?>
<p><label for="<?php echo $update_id; ?>"><?php echo Text::_('ATTACH_ENTER_NEW_URL_COLON') ?></label>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <label for="verify_url"><?php echo Text::_('ATTACH_VERIFY_URL_EXISTENCE') ?></label>
        <input type="checkbox" name="verify_url" value="verify" <?php echo $this->verify_url_checked ?>
               title="<?php echo Text::_('ATTACH_VERIFY_URL_EXISTENCE_TOOLTIP'); ?>" />
        &nbsp;&nbsp;&nbsp;&nbsp;
        <label for="relative_url"><?php echo Text::_('ATTACH_RELATIVE_URL') ?></label>
        <input type="checkbox" name="relative_url" value="relative" <?php echo $this->relative_url_checked ?>
               title="<?php echo Text::_('ATTACH_RELATIVE_URL_TOOLTIP'); ?>" />
        <a class="changeButton" href="<?php echo $this->change_file_url ?>"
           title="<?php echo Text::_('ATTACH_CHANGE_TO_FILE_TOOLTIP'); ?>"
           ><?php echo Text::_('ATTACH_CHANGE_TO_FILE') ?></a> </p>
<p><label for="url_valid"><?php echo Text::_('ATTACH_URL_IS_VALID') ?></label>
            <?php echo $this->lists['url_valid']; ?>
</p>
<p>
        <input type="text" name="url" id="<?php echo $update_id ?>"
               size="80" maxlength="255" title="<?php echo Text::_('ATTACH_ENTER_URL_TOOLTIP'); ?>"
               value="<?php echo $attachment->url; ?>" /><br /><?php
                echo Text::_('ATTACH_NOTE_ENTER_URL_WITH_HTTP'); ?>
        <input type="hidden" name="old_url" value="<?php echo $old_url; ?>" />
</p>
    <?php endif; ?>
<?php endif; ?>
<?php if ((($attachment->uri_type == 'file') and ($update == '') ) or ($update == 'file')) : ?>
<p class="display_name"><label for="display_name"
          title="<?php echo Text::_('ATTACH_DISPLAY_FILENAME_TOOLTIP'); ?>"
          ><?php echo Text::_('ATTACH_DISPLAY_FILENAME_OPTIONAL_COLON'); ?></label>
   <input type="text" name="display_name" id="display_name"
          size="70" maxlength="80"
          title="<?php echo Text::_('ATTACH_DISPLAY_FILENAME_TOOLTIP'); ?>"
          value="<?php echo $attachment->display_name; ?>" />
   <input type="hidden" name="old_display_name" value="<?php echo $attachment->display_name; ?>" />
</p>
<?php elseif ((($attachment->uri_type == 'url') and ($update == '')) or ($update == 'url')) : ?>
<p class="display_name"><label for="display_name"
          title="<?php echo Text::_('ATTACH_DISPLAY_URL_TOOLTIP'); ?>"
          ><?php echo Text::_('ATTACH_DISPLAY_URL_COLON'); ?></label>
   <input type="text" name="display_name" id="display_name"
          size="70" maxlength="80"
          title="<?php echo Text::_('ATTACH_DISPLAY_URL_TOOLTIP'); ?>"
          value="<?php echo $attachment->display_name; ?>" />
   <input type="hidden" name="old_display_name" value="<?php echo $attachment->display_name; ?>" />
</p>
<?php endif; ?>
        <p><label for="description"><?php echo Text::_('ATTACH_DESCRIPTION_COLON'); ?></label>
           <input type="text" name="description" id="description"
                  size="70" maxlength="255" value="<?php echo stripslashes($attachment->description) ?>" /></p>
<?php if ($this->may_publish) : ?>
        <div class="at_control">
            <label>
                <?php echo Text::_('ATTACH_PUBLISHED'); ?>
            </label>
            <?php echo $this->lists['published']; ?>
        </div>
<?php endif; ?>
<?php if ($params->get('allow_frontend_access_editing', false)) : ?>
        &nbsp;&nbsp;&nbsp;&nbsp;
        <div class="at_control">
            <label for="access" title="<?php echo $this->access_level_tooltip; ?>">
                <?php echo Text::_('ATTACH_ACCESS_COLON'); ?>
            </label>
            <?php echo $this->access_level; ?>
        </div>
<?php endif; ?>
        <?php if ($params->get('user_field_1_name')) : ?>
        <p><label for="user_field_1"><?php echo $params->get('user_field_1_name'); ?>:</label>
           <input type="text" name="user_field_1" id="user_field_1" size="70" maxlength="100"
                  value="<?php echo stripslashes($attachment->user_field_1); ?>" /></p>
        <?php endif; ?>
        <?php if ($params->get('user_field_2_name')) : ?>
        <p><label for="user_field_2"><?php echo $params->get('user_field_2_name'); ?>:</label>
           <input type="text" name="user_field_2" id="user_field_2" size="70" maxlength="100"
                  value="<?php echo stripslashes($attachment->user_field_2); ?>" /></p>
        <?php endif; ?>
        <?php if ($params->get('user_field_3_name')) : ?>
        <p><label for="user_field_3"><?php echo $params->get('user_field_3_name'); ?>:</label>
           <input type="text" name="user_field_3" id="user_field_3" size="70" maxlength="100"
                  value="<?php echo stripslashes($attachment->user_field_3); ?>" /></p>
        <?php endif; ?>
        <p><?php echo Text::sprintf(
            'ATTACH_LAST_MODIFIED_ON_D_BY_S',
            $last_modified,
            $attachment->modifier_name
        ); ?></p>
    </fieldset>
    <input type="hidden" name="MAX_FILE_SIZE" value="524288" />
    <input type="hidden" name="submitted" value="TRUE" />
    <input type="hidden" name="id" value="<?php echo $attachment->id; ?>" />
    <input type="hidden" name="save_type" value="update" />
    <input type="hidden" name="update" value="<?php echo $update; ?>" />
    <input type="hidden" name="uri_type" value="<?php echo $attachment->uri_type; ?>" />
    <input type="hidden" name="new_parent" value="<?php echo $this->new_parent; ?>" />
    <input type="hidden" name="parent_id" value="<?php echo $parent_id; ?>" />
    <input type="hidden" name="parent_type" value="<?php echo $attachment->parent_type; ?>" />
    <input type="hidden" name="parent_entity" value="<?php echo $attachment->parent_entity; ?>" />
    <input type="hidden" name="from" value="<?php echo $this->from; ?>" />
    <input type="hidden" name="Itemid" value="<?php echo $this->Itemid; ?>" />
    <?php echo HTMLHelper::_('form.token'); ?>
    <div class="form_buttons">
        <input type="submit" name="submit" value="<?php echo Text::_('ATTACH_UPDATE'); ?>" />
        <span class="right">
          <input type="button" name="cancel" value="<?php echo Text::_('ATTACH_CANCEL'); ?>"
                 onClick="window.parent.bootstrap.Modal.getInstance(
                            window.parent.document.querySelector('.joomla-modal.show')).hide();" />
        </span>
    </div>
</form>

<?php

// Generate the list of existing attachments
if (($update == 'file') || ($attachment->uri_type == 'file')) {
    /** @var \Joomla\CMS\MVC\Factory\MVCFactory $mvc */
    $mvc = $app->bootComponent('com_attachments')
        ->getMVCFactory();
    /** @var \JMCameron\Component\Attachments\Site\Controller\AttachmentsController $controller */
    $controller = $mvc->createController('Attachments', 'Site', [], $app, $app->getInput());
    $controller->display(
        $parent_id,
        $attachment->parent_type,
        $attachment->parent_entity,
        'ATTACH_EXISTING_ATTACHMENTS',
        false,
        false,
        true,
        $this->from
    );
}

echo '</div>';

if ($this->error) {
    echo $this->endHTML();
}
