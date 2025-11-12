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
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\Language\Parser\IniParser;
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
            // var_dump($this->populateViewLevels());
            // $db = $this->getDatabaseManager()->getConnection();
            // $query = $db->getQuery(true);
            // $query->select('*')->from('#__viewlevels');
            // $db->setQuery($query);
            // $db->execute();
            // var_dump($db->loadObjectList());
        }
        // Force loading the component language
        /** @var \Joomla\CMS\Application\WebApplication $app */
        $app = Factory::getApplication();
        $app->loadLanguage();
        $lang =  Factory::getApplication()->getLanguage();
        $lang->load('com_attachments', JPATH_BASE . '/attachments_component/admin', 'en-GB', true);

        // Set up mock functions to avoid further db queries and dependencies
        $this->mockUser->method('getAuthorisedViewLevels')
            ->willReturn([1, 2, 3]);
        
        // It is only used in the plugin framework to load the language files
        // and it looks at the wrong path anyway as it assumes being in site context
        // so we define the constant here just to avoid errors
        if (!defined('JPATH_PLUGINS')) {
            define('JPATH_PLUGINS', JPATH_ROOT . '/plugins');
        }
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
        $result = AttachmentsImport::importAttachmentsFromCSVFile($path, $verify_parent = true, $update, $dry_run);
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
}
