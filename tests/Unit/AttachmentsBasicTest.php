<?php

/**
 * Basic functionality test for Attachments component
 *
 * @package Attachments
 * @subpackage Tests
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * Basic tests for Attachments component structure
 *
 * @package Attachments
 * @subpackage Tests
 */
class AttachmentsBasicTest extends TestCase
{
    #[Test]
    public function testBasicStructure(): void
    {
        // Test that the source file exists and has correct structure
        $sourceFile = __DIR__ . '/../../attachments_component/admin/src/Helper/AttachmentsPermissions.php';
        
        // Basic file checks
        $this->assertFileExists($sourceFile, 'AttachmentsPermissions.php should exist');
        $content = file_get_contents($sourceFile);
        $this->assertNotEmpty($content, 'AttachmentsPermissions.php should not be empty');
        
        // Check basic file structure
        $this->assertStringContainsString('namespace JMCameron\\Component\\Attachments\\Administrator\\Helper;', $content, 'File should have correct namespace');
        $this->assertStringContainsString('class AttachmentsPermissions', $content, 'File should define AttachmentsPermissions class');
    }
    
    #[Test]
    public function testBasicAssertions(): void
    {
        $this->assertTrue(true, 'Basic true assertion should pass');
        $this->assertIsString("test", 'String assertion should pass');
        $this->assertNotEmpty(array("item"), 'Array should not be empty');
    }
}
