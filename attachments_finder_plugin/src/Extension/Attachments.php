<?php

namespace JMCameron\Plugin\Finder\Attachments\Extension;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Event\Event;
use Joomla\Event\SubscriberInterface;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Smart Search adapter for Attachments.
 *
 * @since  2.5
 */
final class Attachments extends Adapter implements SubscriberInterface
{
    use DatabaseAwareTrait;

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
     * @since  2.5
     */
    protected $extension = 'com_attachments';

    /**
     * The sublayout to use when rendering the results.
     *
     * @var    string
     * @since  2.5
     */
    protected $layout = 'attachment';

    /**
     * The type of content that the adapter indexes.
     *
     * @var    string
     * @since  2.5
     */
    protected $type_title = 'Attachment';

    /**
     * The table name.
     *
     * @var    string
     * @since  2.5
     */
    protected $table = '#__attachments';

    /**
     * The field the published state is stored in.
     *
     * @var    string
     * @since  2.5
     */
    protected $state_field = 'state';

    /**
     * Load the language file on instantiation.
     *
     * @var    boolean
     * @since  3.1
     */
    protected $autoloadLanguage = true;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   5.2.0
     */
    public static function getSubscribedEvents(): array
    {
        $parentEvents = [];
        // Joomla 4 Adapter class doesn't have a getSubscribedEvents method
        if (method_exists(Adapter::class, 'getSubscribedEvents')) {
            if (version_compare(phpversion(), '8.2.0', '<')) {
                $parentEvents = call_user_func('parent::getSubscribedEvents');
            } else {
                $parentEvents = call_user_func(array(parent::class, 'getSubscribedEvents'));
            }
        } else {
            $parentEvents = [
                'onStartIndex' => 'onStartIndex',
                'onBeforeIndex' => 'onBeforeIndex',
                'onBuildIndex' => 'onBuildIndex',
                'onFinderGarbageCollection' => 'onFinderGarbageCollection'
            ];
        }

        return array_merge($parentEvents, [
            'onFinderAfterDelete' => 'onFinderAfterDelete',
            'onFinderAfterSave'   => 'onFinderAfterSave',
            'onFinderBeforeSave'  => 'onFinderBeforeSave',
            'onFinderChangeState' => 'onFinderChangeState',
        ]);
    }

    /**
     * Method to setup the indexer to be run.
     *
     * @return  boolean  True on success.
     *
     * @since   2.5
     */
    protected function setup()
    {
        return true;
    }

    /**
     * Method to remove the link information for items that have been deleted.
     *
     * @param   Event   $event  The event instance.
     *
     * @return  void.
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderAfterDelete(Event $event): void
    {
        if (version_compare(JVERSION, '5.0', 'ge')) {
            $context = $event->getArgument('context');
            $table = $event->getArgument('subject');
        } else {
            [$context, $table] = $event->getArguments();
        }

        if ($context === 'com_attachments.attachment') {
            $id = $table->id;
        } else {
            return;
        }

        // Remove item from the index.
        $this->remove($id);
    }

    /**
     * Smart Search after save content method.
     * Reindexes the link information for a category that has been saved.
     * It also makes adjustments if the access level of the category has changed.
     *
     * @param   Event   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderAfterSave(Event $event): void
    {
        if (version_compare(JVERSION, '5.0', 'ge')) {
            $context = $event->getArgument('context');
            $subject     = $event->getArgument('subject');
            $isNew   = $event->getArgument('isNew');
        } else {
            [$context, $subject, $isNew] = $event->getArguments();
        }

        // We only want to handle categories here.
        if ($context === 'com_attachments.attachment') {
            // Check if the access levels are different.
            if (!$isNew && $this->old_access != $subject->access) {
                // Process the change.
                $this->itemAccessChange($subject);
            }

            // Reindex the category item.
            $this->reindex($subject->id);
        }
    }

    /**
     * Smart Search before content save method.
     * This event is fired before the data is actually saved.
     *
     * @param   Event   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    public function onFinderBeforeSave(Event $event): void
    {
        if (version_compare(JVERSION, '5.0', 'ge')) {
            $context = $event->getArgument('context');
            $subject     = $event->getArgument('subject');
            $isNew   = $event->getArgument('isNew');
        } else {
            [$context, $subject, $isNew] = $event->getArguments();
        }

        // We only want to handle categories here.
        if ($context === 'com_attachments.attachment') {
            // Query the database for the old access level and the parent if the item isn't new.
            if (!$isNew) {
                $this->checkItemAccess($subject);
                $this->checkCategoryAccess($subject);
            }
        }
    }

    /**
     * Method to update the link information for items that have been changed
     * from outside the edit screen. This is fired when the item is published,
     * unpublished, archived, or unarchived from the list view.
     *
     * @param   Event   $event  The event instance.
     *
     * @return  void
     *
     * @since   2.5
     */
    public function onFinderChangeState(Event $event): void
    {
        if (version_compare(JVERSION, '5.0', 'ge')) {
            $context = $event->getArgument('context');
            $pks     = $event->getArgument('pks');
            $value   = $event->getArgument('value');
        } else {
            [$context, $pks, $value] = $event->getArguments();
        }

        // We only want to handle attachments here.
        if ($context === 'com_attachments.attachment') {
            $this->itemStateChange($pks, $value);
        }

        // Handle when the plugin is disabled.
        if ($context === 'com_plugins.plugin' && $value === 0) {
            $this->pluginDisable($pks);
        }
    }

    /**
     * Method to index an item. The item must be a Result object.
     *
     * @param   Result  $item  The item to index as a Result object.
     *
     * @return  void
     *
     * @since   2.5
     * @throws  \Exception on database error.
     */
    protected function index(Result $item)
    {
        // Check if the extension is enabled.
        if (ComponentHelper::isEnabled($this->extension) === false) {
            return;
        }

        $item->setLanguage();

        $item->params = new Registry($item->params);

        if ($item->display_name) {
            $item->title = $item->display_name;
        } elseif ($item->uri_type == "file") {
            $item->title = $item->filename;
            $item->mime = $item->file_type;
        } else {
            $item->title = $item->url;
        }

        // Create a URL as identifier to recognise items again.
        $item->url = $this->getUrl($item->id, $this->extension, $this->layout);

        // We can only index com_content articles and categories
        if ($item->parent_type != "com_content") {
            return;
        }

        $extension = "Content";
        /*
         * Build the necessary route information.
         * Need to import component route helpers dynamically, hence the reason it's handled here.
         */

        $class = 'Joomla\\Component\\' . $extension . '\\Site\\Helper\\RouteHelper';

        if ($item->description) {
            $item->body = "<br/>" . $item->description;
        }

        if ($item->parent_entity == "category") {
            if (class_exists($class) && method_exists($class, 'getCategoryRoute')) {
                $item->route = $class::getCategoryRoute($item->parent_id, $item->language);
                $item->summary = $item->category_title;
            } else {
                // This category has no frontend route.
                return;
            }
        } elseif ($item->parent_entity == "article") {
            if (class_exists($class) && method_exists($class, 'getArticleRoute')) {
                $item->route = $class::getArticleRoute(
                    $item->article_alias ? "{$item->parent_id}:{$item->article_alias}" : $item->parent_id,
                    $item->article_catid,
                    $item->language
                );
                $item->summary = $item->article_title;
            } else {
                // This category has no frontend route.
                return;
            }
        }

        // Translate the state. Categories should only be published if the parent category is published.
        $item->state = $this->translateState($item->state);

        $item->addTaxonomy('Type', 'Attachment');
        $item->addTaxonomy('Category', $item->uri_type);
        $item->addTaxonomy('Language', $item->language);

        // Get content extras.
        Helper::getContentExtras($item);

        // Index the item.
        $this->indexer->index($item);
    }

    /**
     * Method to get the SQL query used to retrieve the list of content items.
     *
     * @param   mixed  $query  An object implementing QueryInterface or null.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   2.5
     */
    protected function getListQuery($query = null)
    {
        $db = $this->getDatabase();

        // Check if we can use the supplied SQL query.
        $query = $query instanceof QueryInterface ? $query : $db->getQuery(true);

        $query->select(
            $db->quoteName(
                [
                    'a.id',
                    'a.filename',
                    'a.file_type',
                    'a.url',
                    'a.uri_type',
                    'a.url_valid',
                    'a.url_relative',
                    'a.display_name',
                    'a.description',
                    'a.access',
                    'a.state',
                    'a.parent_entity',
                    'a.parent_type',
                    'a.parent_id',
                    'a.created_by',
                    'a.modified',
                    'a.modified_by'
                ]
            )
        )
            ->select(
                $db->quoteName(
                    [
                        'a.created',
                        'ar.title',
                        'ar.catid',
                        'ar.alias',
                        'c.title'
                    ],
                    [
                        'start_date',
                        'article_title',
                        'article_catid',
                        'article_alias',
                        'category_title'
                    ]
                )
            )
            ->from($db->quoteName($this->table, 'a'))
            ->join(
                'LEFT',
                '#__content ar ON a.parent_id = ar.id AND a.parent_entity = "article"'
            )
            ->join(
                'LEFT',
                '#__categories c ON a.parent_id = c.id AND a.parent_entity = "category"'
            );

        return $query;
    }

    /**
     * Method to get a SQL query to load the published and access states for
     * an attachment.
     *
     * @return  QueryInterface  A database object.
     *
     * @since   2.5
     */
    protected function getStateQuery()
    {
        $query = $this->getDatabase()->getQuery(true);

        $query->select(
            $this->getDatabase()->quoteName(
                [
                    'a.id',
                    'a.parent_type',
                    'a.parent_entity',
                    'a.parent_id',
                    'a.access',
                ]
            )
        )
            ->select(
                $this->getDatabase()->quoteName(
                    [
                        'a.' . $this->state_field,
                    ],
                    [
                        'state',
                    ]
                )
            )
            ->from($this->getDatabase()->quoteName($this->table, 'a'));

        return $query;
    }
}
