<?php

/**
 * Integration test for Attachments component installation
 *
 * @package Attachments
 * @subpackage Tests
 *
 * @copyright Copyright (C) 2007-2025 Jonathan M. Cameron, All Rights Reserved
 * @license https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link https://github.com/jmcameron/attachments
 * @author Jonathan M. Cameron
 */

namespace Tests\Unit\Helper;

use Tests\AttachmentsTestCase;

/**
 * Test class for Attachments component installation
 *
 * @package Attachments
 * @subpackage Tests
 */
class AttachmentsUpdateTest extends AttachmentsTestCase
{
    /**
     * Test the installAttachmentsPermissions method can be called
     */
    public function testInstallAttachmentsPermissionsMethod()
    {
        // Since we can't run the actual installation in test environment,
        // we just verify the method exists and is callable
        $method = new \ReflectionMethod(
            'JMCameron\Component\Attachments\Administrator\Helper\AttachmentsUpdate',
            'installAttachmentsPermissions'
        );
        
        $this->assertTrue($method->isPublic());
        $this->assertTrue($method->isStatic());
        $this->assertEquals(1, $method->getNumberOfParameters());
    }
}