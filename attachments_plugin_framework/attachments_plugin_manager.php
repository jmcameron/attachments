<?php
/**
 * Manager for plugins for Attachments
 *
 * @package Attachments
 * @subpackage Attachments_Plugin_Framework
 *
 * @copyright Copyright (C) 2009-2012 Jonathan M. Cameron, All Rights Reserved
 * @license http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link http://joomlacode.org/gf/project/attachments/frs/
 * @author Jonathan M. Cameron
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );


/**
 * The class for the manager for attachments plugins
 *
 * AttachmentsPluginManager manages plugins for Attachments.
 * It knows how to create handlers for plugins for all
 * supported extensions.
 *
 * @package Attachments
 */
class AttachmentsPluginManager extends JObject
{
	/** A list of known parent_type names
	 */
	var $_parent_types = Array();

	/** An array of info about the installed entities.
	 *	Each item in the array is an associative array with the following entries:
	 *	  'id' - the unique name of entity as stored in the jos_attachments table (all lower case)
	 *	  'name' - the translated name of the entity
	 *	  'name_plural' - the translated plural name of the entity
	 *	  'parent_type' - the parent type for the entity
	 */
	var $_entity_info = Array();

	/** An associative array of attachment plugins
	 */
	var $_plugin = Array();


	/** Flag indicating if the language file haas been loaded
	 */
	var $_language_loaded = false;


	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->loadLanguage();
	}


	/**
	 * See if a particular plugin is installed (avaliable)
	 *
	 * @param string $parent_type the name of the parent extension (eg, com_content)
	 *
	 * @return Boolean true if the plugin is available (false if not)
	 */
	public function attachmentsPluginInstalled($parent_type)
	{
		return in_array( $parent_type, $this->_parent_types );
	}


	/**
	 * Check to see if an attachments plugin is enabled
	 *
	 * @param string $parent_type the name of the parent extension (eg, com_content)
	 *
	 * @return true if the attachment is enabled (false if disabled)
	 */
	public function attachmentsPluginEnabled($parent_type)
	{
		// Extract the component name (the part after 'com_')
		if ( strpos($parent_type, 'com_') == 0 ) {
			$name = substr($parent_type, 4);
			return JPluginHelper::isEnabled('attachments', "attachments_for_$name");
			}

		// If the parent type does not conform to the naming convention, assume it is not enabled
		return false;
	}


	/**
	 * Add a new parent type
	 */
	public function addParentType($new_parent_type)
	{
		if ( in_array( $new_parent_type, $this->_parent_types ) ) {
			return;
			}
		else {
			$this->_parent_types[] = $new_parent_type;
			}
	}

	/**
	 * Return the list of installed parent types
	 *
	 * @return an array of the installed parent types
	 */
	public function &getInstalledParentTypes()
	{
		return $this->_parent_types;
	}

	/**
	 * Return the list of installed parent entities
	 *
	 * @return array of entity info (see var $_entity_info definition above)
	 */
	public function &getInstalledEntityInfo()
	{
		if ( count($this->_entity_info) == 0 ) {

			// Add an option for each entity
			JPluginHelper::importPlugin('attachments');
			$apm = getAttachmentsPluginManager();

			// process all the parent types
			foreach ($this->_parent_types as $parent_type) {
				$parent = $apm->getAttachmentsPlugin($parent_type);
				$entities = $parent->getEntities();

				// Process each entity for this parent type
				foreach ($entities as $entity) {
					$centity = $parent->getCanonicalEntityId($entity);
					$this->_entity_info[] = array('id' => $centity,
												  'name' => JText::_('ATTACH_' . $centity),
												  'name_plural' => JText::_('ATTACH_' . $centity . 's'),
												  'parent_type' => $parent_type
												  );
					}
				}
			}

		return $this->_entity_info;
	}


	/**
	 * Load the langauge for this parent type
	 *
	 * @return true of the language was loaded successfullly
	 */
	public function loadLanguage()
	{
		if ( $this->_language_loaded ) {
			return true;
			}

		$lang = JFactory::getLanguage();

		$this->_language_loaded = $lang->load('plg_attachments_attachments_plugin_framework', dirname(__FILE__));

		return $this->_language_loaded;
	}


	/**
	 * Get the plugin (attachments parent handler object)
	 *
	 * @param string $parent_type the name of the parent extension (eg, com_content)
	 *
	 * @return the parent handler object
	 */
	public function getAttachmentsPlugin($parent_type)
	{
		// Make sure the parent type is valid
		if ( !in_array( $parent_type, $this->_parent_types ) ) {
			$errmsg = JText::sprintf('ATTACH_ERROR_UNKNOWN_PARENT_TYPE_S', $parent_type) . ' (ERR 303)';
			JError::raiseWarning(406, $errmsg);
			}

		// Instantiate the plugin object, if we have not already done it
		if ( !array_key_exists( $parent_type, $this->_plugin ) ) {
			$this->_installPlugin($parent_type);
			}

		return $this->_plugin[$parent_type];
	}


	/**
	 * Install the specified plugin
	 *
	 * @param string $parent_type the name of the parent extension (eg, com_content)
	 *
	 * @return true if successful (false if not)
	 */
	private function _installPlugin($parent_type)
	{
		// Do nothing if the plugin is already installed
		if ( array_key_exists( $parent_type, $this->_plugin ) ) {
			return true;
			}

		// Install the plugin
		$dispatcher	= JDispatcher::getInstance();
		$className = 'AttachmentsPlugin_' . $parent_type;
		$this->_plugin[$parent_type] = new $className($dispatcher);

		return is_object($this->_plugin[$parent_type]);
	}

}
