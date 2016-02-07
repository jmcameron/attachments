<?php

defined('JPATH_BASE') or die;

jimport('joomla.application.component.helper');

// Load the base adapter.
require_once JPATH_ADMINISTRATOR . '/components/com_finder/helpers/indexer/adapter.php';

// jimport('joomla.log.log');

/**
 * Finder para eventos
 *
 * @package     Joomla.Plugin
 * @subpackage  Finder.Eventos
 * @since       2.5
 */
class plgFinderAttachments extends FinderIndexerAdapter {

  /**
   * The plugin identifier.
   *
   * @var    string
   * @since  2.5
   */
  protected $context = 'Attachments';

  /**
   * The extension name.
   *
   * @var    string
   * @since  2.5l
   */
  protected $extension = 'com_attachments';

  /**
   * The sublayout to use when rendering the results.
   *
   * @var    string
   * @since  2.5
   */
  protected $layout = 'download';

  /**
   * The type of content that the adapter indexes.
   *
   * @var    string
   * @since  2.5
   */
  protected $type_title = 'Attachments';

  /**
   * The table name.
   *
   * @var    string
   * @since  2.5
   */
  protected $table = '#__attachments';

  public function __construct(&$subject, $config) {

    parent::__construct($subject, $config);
    $this->loadLanguage();
  }

  protected function setup() {

// Load dependent classes
    include_once JPATH_SITE . '/components/com_content/helpers/route.php';
// get attachments Itemid	
    /* 	$db		  = JFactory::getDBO();
      $sqlitemid = "SELECT id FROM ".$db->quoteName( '#__menu')." WHERE link LIKE '%com_attachments%' AND level = '1' AND published = '1'";
      $db->setQuery( $sqlitemid);
      $this->item_id = $db->loadResult(); */

    return true;
  }

  protected function getURL($id, $extension, $view) {

    return 'index.php?option=' . $extension . '&task=' . $view . '&id=' . $id;
  }

  protected function index(FinderIndexerResult $item, $format = 'html') {
    if (JComponentHelper::isEnabled($this->extension) == false) {
      return;
    }

    $registry = new JRegistry;
    $registry->loadString($item->params);
    $item->params = JComponentHelper::getParams('com_attachments', true);
    $item->params->merge($registry);

    $registry = new JRegistry;
    $registry->loadString($item->metadata);
    $item->metadata = $registry;

    $item->summary = FinderIndexerHelper::prepareContent($item->summary, $item->params);
    $item->url = $this->getUrl($item->id, $this->extension, $this->layout);

// check if thread starter (OP) or reply				
//if ( $item->id == $item->asset_id) { // op
//index.php?option=com_attachments&task=download&id=5
    $item->route = //'index.php?option=' . $this->extension . '&task=download'. 
//	'&id='.$item->id;
            ContentHelperRoute::getArticleRoute($item->slug, $item->catslug, $item->language);

//}


    $item->path = FinderIndexerHelper::getContentPath($item->route);

// Add the meta-author.
    $item->metaauthor = $item->metadata->get('author');

// Translate the state. Articles should only be published if the category is published.
    $item->state = $this->translateState($item->state, $item->cat_state);

// Add the type taxonomy data.
    $item->addTaxonomy('Type', 'Attachments');

// Add the category taxonomy data.
    $item->addTaxonomy('Category', $item->category, $item->cat_state, $item->cat_access);

// Get content extras.
    FinderIndexerHelper::getContentExtras($item);
// Index the item.
    $this->indexer->index($item);
// Index the item.
//FinderIndexer::index($item);
  }

  protected function getListQuery($sql = null) {

    $db = JFactory::getDbo();


    $sql = $sql instanceof JDatabaseQuery ? $sql : $db->getQuery(true);

    $sql->select('a.id,  a.display_name AS title');
    $sql->select('a.state AS state, ja.catid, a.created_by, 1 AS access');
    $sql->select('a.created AS start_date, a.modified, a.modified_by');
    $sql->select('a.created  AS publish_start_date');
    $sql->select('jc.title AS category, jc.alias AS cat_alias, jc.published AS cat_state');



    $sql->select('u.name AS author');

// Handle the alias CASE WHEN portion of the query
    $case_when_item_alias = ' CASE WHEN ';
    $case_when_item_alias .= $sql->charLength('ja.alias');
    $case_when_item_alias .= ' THEN ';
    $a_id = $sql->castAsChar('ja.id');
    $case_when_item_alias .= $sql->concatenate(array($a_id, 'ja.alias'), ':');
    $case_when_item_alias .= ' ELSE ';
    $case_when_item_alias .= $a_id . ' END as slug';
    $sql->select($case_when_item_alias);

    $case_when_category_alias = ' CASE WHEN ';
    $case_when_category_alias .= $sql->charLength('jc.alias');
    $case_when_category_alias .= ' THEN ';
    $c_id = $sql->castAsChar('jc.id');
    $case_when_category_alias .= $sql->concatenate(array($c_id, 'jc.alias'), ':');
    $case_when_category_alias .= ' ELSE ';
    $case_when_category_alias .= $c_id . ' END as catslug';
    $sql->select($case_when_category_alias);


    $sql->from('#__attachments AS a');
    $sql->join('LEFT', '#__content AS ja ON a.parent_id = ja.id');
    $sql->join('LEFT', '#__categories AS jc ON jc.id = ja.catid');
    $sql->join('LEFT', '#__users AS u ON u.id = a.created_by');
    $sql->where($db->quoteName('a.parent_type') . " = 'com_content'");
    $sql->where($db->quoteName('a.parent_entity') . " = 'article'");
// $sql->where($db->quoteName('a.state') . ' = 1');
//$sql->where($db->quoteName('ja.state') . ' = 1');
// $sql->where($db->quoteName('jc.published') . ' = 1');

    return $sql;
  }

  public function onFinderAfterDelete($context, $table) {

    if ($context == 'com_attachments') {
      $id = $table->id;
      if ($table->parent_type == 'com_content' && $table->parent_entity == 'article')
        return $this->remove($id);
      return true;
    } else {

      return true;
    }
  }

  public function onFinderAfterSave($context, $row, $isNew) {
// We only want to handle attachements here
    if ($context == $this->extension) {
// Check if the access levels are different
//if (!$isNew && $this->old_access != $row->access)
//{
// Process the change.
//	$this->itemAccessChange($row);
//}
// Reindex the item
      if ($row->parent_type == 'com_content' && $row->parent_entity == 'article')
        $this->reindex($row->id);
    }

// Check for access changes in the category
    if ($context == 'com_categories.category') {
// Check if the access levels are different
      if (!$isNew && $this->old_cataccess != $row->access) {
        $this->categoryAccessChange($row);
      }
    }

    return true;
  }

  /**
   * Method to update the link information for items that have been changed
   * from outside the edit screen. This is fired when the item is published,
   * unpublished, archived, or unarchived from the list view.
   *
   * @param   string   $context  The context for the content passed to the plugin.
   * @param   array    $pks      An array of primary key ids of the content that has changed state.
   * @param   integer  $value    The value of the state that the content has been changed to.
   *
   * @return  void
   *
   * @since   3.2
   */
  public function onFinderChangeState($context, $pks, $value) {
// We only want to handle attachments here.
    if ($context == $this->extension) {
      foreach ($pks as $pk) {
        $this->reindex($pk);
      }
      //can not use itemchangestate becouse attachments dont have categories.
//$this->itemStateChange($pks, $value);
    }

// Handle when the plugin is disabled.
    if ($context == 'com_plugins.plugin' && $value === 0) {
      $this->pluginDisable($pks);
    }
  }

}
