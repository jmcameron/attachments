<?php
/**
 */

class Com_AttachmentsInstallerScript {

	var $plugins = Array('plg_content_attachments',
						 'plg_search_attachments',
						 'plg_attachments_plugin_framework',
						 'plg_attachments_for_content',
						 'plg_editors-xtd_add_attachment_btn',
						 'plg_editors-xtd_insert_attachments_token_btn',
						 'plg_system_show_attachments_in_editor');


	function install($parent) {
		echo JText::_('ATTACHMENTS_COMPONENT_SUCCESFULLY_INSTALLED');
	}

	function uninstall($parent) {
	}

	function update($parent) {
	}

	function preflight($type, $parent) {
	}

	function postflight($type, $parent) {

		$app = JFactory::getApplication('administrator');
		$db =& JFactory::getDBO();

		// Make sure the translations are available
		$lang =&  JFactory::getLanguage();
		$lang->load('com_attachments', JPATH_ADMINISTRATOR);

		// Enable all the plugins
		foreach ($this->plugins as $plugin_name)
		{
			// Make the query to enable the plugin
			$plugin_title = JText::_($plugin_name);
			$query	= $db->getQuery(true);
			$query->update('#__extensions');
			$query->set("enabled = 1");
			$query->where("type = 'plugin'");
			$query->where("name = '" . $plugin_name . "'");
			$db->setQuery($query);
			$db->query();

			// Complain if there was an error
			if ( $db->getErrorNum() ) {
				$errmsg = JText::sprintf('WARNING_FAILED_ENABLING_PLUGIN_S', $plugin_title);
				$errmsg .= $db->getErrorMsg();
				$app->enqueueMessage($errmsg, 'error');
				return false;
				}
			$app->enqueueMessage(JText::sprintf('ENABLED_ATTACHMENTS_PLUGIN_S', $plugin_title), 'message');
		}
		$app->enqueueMessage('<br/>', 'message');
		$app->enqueueMessage(JText::_('ALL_ATTACHMENTS_PLUGINS_ENABLED'), 'message');
		$app->enqueueMessage('<br/>', 'message');
	}
}
