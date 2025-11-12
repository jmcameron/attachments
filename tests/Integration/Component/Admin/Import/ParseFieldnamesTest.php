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
use Tests\AttachmentsDatabaseTestCase;
use Tests\Utils\CsvFileIterator;

/**
 * Tests for ACL action permissions for various users
 *
 * @package Attachments_test
 * @subpackage Attachments_permissions
 */
class ParseFieldnamesTest extends AttachmentsDatabaseTestCase
{
    /**
     * Sets up the fixture
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::setUpBeforeClass();

        // Force loading the component language
        /** @var \Joomla\CMS\Application\WebApplication $app */
        $app = Factory::getApplication();
        $app->loadLanguage();
        $lang =  Factory::getApplication()->getLanguage();
        $lang->load('com_attachments', JPATH_BASE . '/attachments_component/admin', 'en-GB', true);
    }


    /**
     * Gets the data set to be loaded into the database during setup
     *
     * @return xml dataset
     */
    protected function getDataSet()
    {
        return $this->createXMLDataSet(JPATH_TESTS . '/joomla_db.xml');
    }


    /**
     *
     *
     * @dataProvider provider
     *
     */
    public function testParseFieldnames($test_filename, $result)
    {
        // Open the CSV file
        $path = dirname(__FILE__) . '/' . $test_filename;
        $this->assertFileExists($path);
        $f = fopen($path, 'r');
        $this->assertNotEquals(false, $f);

        // parse the first line
        $field = AttachmentsImport2::parseFieldNames($f);
        if (is_array($field)) {
            // verify that all fieldnames were found
            $this->assertEquals(array_diff(array_keys($field), AttachmentsImport::$field_names), array());
        } else {
            // Cut off the error number for comparison
            $errmsg = substr($field, 0, strpos($field, ' (ERR'));
            $this->assertEquals($errmsg, $result);
        }
    }


    /**
     * Get the test data from CSV file
     */
    public static function provider()
    {
        return new CsvFileIterator(dirname(__FILE__) . '/testParseFieldnamesData.csv');
    }
}
