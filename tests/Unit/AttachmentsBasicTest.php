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
        $sourceFile = __DIR__ . '/../../attachments_component';
        
        // Basic file checks
        $this->assertDirectoryExists($sourceFile, 'Attachments component directory should exist');
        
        // Check namespace existence
        $this->assertTrue(class_exists('JMCameron\Component\Attachments\Site\Helper\AttachmentsHelper'), 'AttachmentsHelper class should exist');
    }
    
    #[Test]
    public function testBasicAssertions(): void
    {
        $this->assertTrue(true, 'Basic true assertion should pass');
        $this->assertIsString("test", 'String assertion should pass');
        $this->assertNotEmpty(array("item"), 'Array should not be empty');
    }
}
