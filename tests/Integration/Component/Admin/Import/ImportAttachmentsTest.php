<?php

namespace Tests\Integration\Component\Admin\Import;

/**
 * Attachments component
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

use JMCameron\Component\Attachments\Administrator\Helper\AttachmentsImport;
use JMCameron\Plugin\AttachmentsPluginFramework\AttachmentsPluginManager;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactory;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\MVC\Factory\MVCFactory;
use Joomla\CMS\Table\Table;
use Joomla\Language\Parser\IniParser;
use Joomla\Registry\Registry;
use Tests\AttachmentsDatabaseTestCase;
use Tests\Utils\CsvFileIterator;

/**
 * Tests for ACL action permissions for various users
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 */
class ImportAttachmentsTest extends AttachmentsDatabaseTestCase
{
    protected static bool $first_run = true;

    /**
     * Sets up the fixture
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::setUpBeforeClass();

        if (static::$first_run) {
            static::$first_run = false;
            // Possibly not needed, remember to remove later
            $this->populateViewLevels();

            // Populate users table
            $this->populateUsers();

            // Create attachments table
            $this->createAttachmentsTable();
            // var_dump($this->populateViewLevels());
            // $db = $this->getDatabaseManager()->getConnection();
            // $query = $db->getQuery(true);
            // $query->select('*')->from('#__viewlevels');
            // $db->setQuery($query);
            // $db->execute();
            // var_dump($db->loadObjectList());

            $this->mockApp->method('getConfig')
                ->willReturn(new Registry());
        }
        // Force loading the component language
        /** @var \Joomla\CMS\Application\WebApplication $app */
        $app = Factory::getApplication();
        $app->loadLanguage();
        $lang =  Factory::getApplication()->getLanguage();
        $lang->load('com_attachments', JPATH_BASE . '/attachments_component/admin', 'en-GB', true);

        // It is only used in the plugin framework to load the language files
        // and it looks at the wrong path anyway as it assumes being in site context
        // so we define the constant here just to avoid errors
        if (!defined('JPATH_PLUGINS')) {
            define('JPATH_PLUGINS', JPATH_ROOT . '/plugins');
        }

        if (!defined('JPATH_COMPONENT')) {
            define('JPATH_COMPONENT', JPATH_BASE . '/attachments_component/admin/src');
        }

        Table::addIncludePath(JPATH_BASE . '/attachments_component/admin/src/Table');

        // Set up mock functions to avoid further db queries and dependencies
        $this->mockUser->method('getAuthorisedViewLevels')
            ->willReturn([1, 2, 3]);
        
        $namespace = "JMCameron\\Component\\Attachments";
        $mvcFactory = new MVCFactory($namespace);
        $dispatcher = new ComponentDispatcherFactory($namespace, $mvcFactory);
        $mvcComponent = new \Joomla\CMS\Extension\MVCComponent($dispatcher);
        $mvcComponent->setMVCFactory($mvcFactory);
        
        $this->mockApp->method('bootComponent')
            ->willReturnMap([
                ['com_attachments', $mvcComponent],
            ]);
        $this->mockApp->method('getInput')
            ->willReturn(Factory::getApplication()->input);

        // Add com_content as a known parent type
        $apm = AttachmentsPluginManager::getAttachmentsPluginManager();
        $apm->addParentType('com_content');

        // Inject a lightweight stub plugin to avoid installPlugin() trying to load real plugin classes
        $ref = new \ReflectionClass($apm);
        $prop = $ref->getProperty('plugin');
        $prop->setAccessible(true);
        $plugins = $prop->getValue($apm) ?: [];

        $plugins['com_content'] = new class {
            // implement the minimal methods AttachmentsImport/getInstalledEntityInfo expect
            public function getEntities(): array
            {
                return ['article']; // adjust to match entities used in your CSV/testdata
            }
            public function getCanonicalEntityId(string $entity): string
            {
                return strtolower($entity);
            }

            public function parentExists($parent_id, $parent_entity): bool
            {
                if ($parent_id === 1) {
                    return true;
                }

                return false;
            }

            public function getTitle($parent_id, $parent_entity): string
            {
                if ($parent_id === 1) {
                    return "Welcome";
                }

                return "Test Article Title";
            }
        };

        $prop->setValue($apm, $plugins);
    }

    /**
     *
     *
     * @dataProvider provider
     *
     */
    public function testImportAttachmentsFromCSVFile($test_filename, $expected_result, $update, $dry_run)
    {
        $path = dirname(__FILE__) . '/' . $test_filename;

        // Open the CSV file
        $result = AttachmentsImport::importAttachmentsFromCSVFile($path, true, $update, $dry_run);
        if (is_numeric($expected_result) && is_numeric($result)) {
            $this->assertEquals((int)$expected_result, (int)$result);
        } elseif (is_array($result)) {
            $this->assertEquals((int)$expected_result, count($result));
        } else {
            // Cut off the error number for comparison
            $errmsg = substr($result, 0, strpos($result, ' (ERR'));

            // Replace "  [LINE: x] " with empty string for comparison
            $errmsg = preg_replace('/  \[LINE: \d+\] /', '', $errmsg);

            // Replace %base_path% with actual path in the expected result
            $expected_result = str_replace("%base_path%", dirname(__FILE__), $expected_result);

            $this->assertEquals($expected_result, $errmsg);
        }

        // Delete the attachments
        if (!$update) {
            $db = Factory::getContainer()->get('DatabaseDriver');
            if (is_array($result)) {
                $query = $db->getQuery(true);
                $ids = implode(',', $result);
                $query->delete('#__attachments')->where("id IN ( $ids )");
                $db->setQuery($query);
                if (!$db->execute()) {
                    $this->assertTrue(false, 'ERROR deleting new test attachments' . $db->getErrorMsg());
                }
            }
        }
    }


    /**
     * Get the test data from CSV file
     */
    public static function provider()
    {
        return new CsvFileIterator(dirname(__FILE__) . '/testImportAttachmentsData.csv');
    }

    protected function createAttachmentsTable()
    {
        $db = $this->getDatabaseManager()->getConnection();
        
        try {
            // Create the attachments table if it doesn't exist using raw SQL
            $createTableSQL = "CREATE TABLE IF NOT EXISTS " . $db->quoteName('#__attachments') . " (
                " . $db->quoteName('id') . " INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
                " . $db->quoteName('filename') . " TEXT NOT NULL DEFAULT NULL,
                " . $db->quoteName('filename_sys') . " TEXT NOT NULL DEFAULT NULL,
                " . $db->quoteName('file_type') . " TEXT NOT NULL DEFAULT NULL,
                " . $db->quoteName('file_size') . " INTEGER NOT NULL DEFAULT NULL,
                " . $db->quoteName('url') . " TEXT NOT NULL,
                " . $db->quoteName('url_valid') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('url_relative') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('url_verify') . " INTEGER NOT NULL DEFAULT 1,
                " . $db->quoteName('display_name') . " TEXT NOT NULL,
                " . $db->quoteName('description') . " TEXT NOT NULL,
                " . $db->quoteName('icon_filename') . " TEXT NOT NULL DEFAULT NULL,
                " . $db->quoteName('access') . " INTEGER NOT NULL DEFAULT 1,
                " . $db->quoteName('state') . " INTEGER NOT NULL DEFAULT 0,
                " . $db->quoteName('user_field_1') . " TEXT NOT NULL,
                " . $db->quoteName('user_field_2') . " TEXT NOT NULL,
                " . $db->quoteName('user_field_3') . " TEXT NOT NULL,
                " . $db->quoteName('parent_type') . " TEXT NOT NULL DEFAULT 'com_content',
                " . $db->quoteName('parent_entity') . " TEXT NOT NULL DEFAULT 'article',
                " . $db->quoteName('parent_id') . " INTEGER DEFAULT NULL,
                " . $db->quoteName('created') . " NUMERIC DEFAULT NULL,
                " . $db->quoteName('created_by') . " INTEGER NOT NULL DEFAULT NULL,
                " . $db->quoteName('modified') . " NUMERIC DEFAULT NULL,
                " . $db->quoteName('modified_by') . " INTEGER NOT NULL DEFAULT NULL,
                " . $db->quoteName('download_count') . " INTEGER DEFAULT 0
            )";
            
            $db->setQuery($createTableSQL);
            $db->execute();
            
            return true;
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }

    protected function tearDown(): void
    {
        // Reset the AttachmentsPluginManager singleton
        $ref = new \ReflectionClass(AttachmentsPluginManager::class);
        if ($ref->hasProperty('instance')) {
            $prop = $ref->getProperty('instance');
            $prop->setAccessible(true);
            $prop->setValue(null, null);
        }
        parent::tearDown();
    }
}
