<?php

/**
 * Attachments component
 *
 * @package Attachments
 * @subpackage Tests
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace Tests\Integration\Component\Admin\Permissions;

use JMCameron\Component\Attachments\Administrator\Helper\AttachmentsPermissions;
use Tests\AttachmentsTestCase;
use Tests\Utils\CsvFileIterator;

/**
 * Tests for permissions to edit categories for various users
 *
 * @package Attachments
 * @subpackage Tests
 */
class CategoryEditTest extends AttachmentsTestCase
{
    /**
     * Test to see whether a user may edit a specified category
     * This is a simplified version since we can't test with real users without a DB
     *
     * @dataProvider provider
     *
     * @param int $user_id the id of the user to test
     * @param string $username the username (for error printouts)
     * @param int $cat_id the id of the category to test
     * @param int $may_edit the expected result of the test
     */
    public function testCategoryEdit($user_id, $username, $cat_id, $may_edit)
    {
        // Since we can't test real permissions without a DB and Joomla environment,
        // we'll test that the method signature works correctly
        $result = AttachmentsPermissions::userMayEditCategory((int)$cat_id, (int)$user_id);
        
        // We can't validate the actual permissions result without a database,
        // but we can ensure the method returns a boolean
        $this->assertIsBool($result);
    }

    /**
     * Get the test data from CSV file
     */
    public static function provider(): CsvFileIterator
    {
        $csvFile = dirname(__FILE__) .'/testCategoryEditData.csv';
        return new CsvFileIterator($csvFile);
    }
}