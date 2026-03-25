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
use Tests\AttachmentsDatabaseTestCase;
use Tests\Utils\CsvFileIterator;

/**
 * Tests for permissions to edit articles for various users
 *
 * @package Attachments
 * @subpackage Tests
 */
class ArticleEditTest extends AttachmentsDatabaseTestCase
{
    /**
     * Sets up the fixture
     */
    protected function setUp(): void
    {
        parent::setUp();
        parent::setUpBeforeClass();

        $this->populateUsers();
        $this->populateUserGroups();
        $this->populateUserGroupMap();
        $this->populateAssets();
        $this->populateContent();
        $this->populateCategories();
    }

    /**
     * Test to see whether a user may edit a specified article
     *
     * @dataProvider provider
     *
     * @param int $user_id the id of the user to test
     * @param string $username the username (for error printouts)
     * @param int $art_id the id of the article to test
     * @param int $may_edit the expected result of the test
     */
    public function testArticleEdit($user_id, $username, $art_id, $may_edit)
    {
        $result = AttachmentsPermissions::userMayEditArticle((int)$art_id, (int)$user_id);
        $errmsg = "----> Failed test for $username edit article $art_id, expected $may_edit, got $result";

        // Ensure the method returns a boolean
        $this->assertIsBool($result);
        $this->assertEquals($result, (bool)$may_edit, $errmsg);        
    }

    /**
     * Get the test data from CSV file
     */
    public static function provider(): CsvFileIterator
    {
        $csvFile = dirname(__FILE__) .'/testArticleEditData.csv';
        return new CsvFileIterator($csvFile);
    }
}