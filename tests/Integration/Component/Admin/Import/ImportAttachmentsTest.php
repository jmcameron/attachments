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
use Joomla\Test\DatabaseTestCase;
use Tests\Utils\CsvFileIterator;

/**
 * Tests for ACL action permissions for various users
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 */
class ImportAttachmentsTest extends DatabaseTestCase
{
    /**
     * Sets up the fixture
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::setUpBeforeClass();

        // Force loading the component language
        $lang =  Factory::getApplication()->getLanguage();
        $lang->load('com_attachments', JPATH_BASE . '/administrator/components/com_attachments');
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
