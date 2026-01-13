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

namespace Tests\Unit\Helper;

use Tests\AttachmentsTestCase;

/**
 * Tests for Attachments component installation/uninstallation
 *
 * @package Attachments
 * @subpackage Tests
 */
class AttachmentsInstallTest extends AttachmentsTestCase
{
    /**
     * Test the installAttachmentsPermissions method exists and is callable
     */
    public function testInstallAttachmentsPermissionsMethodExists()
    {
        $reflection = new \ReflectionMethod(
            'JMCameron\Component\Attachments\Administrator\Helper\AttachmentsUpdate',
            'installAttachmentsPermissions'
        );
        $this->assertTrue($reflection->isPublic());
        $this->assertTrue($reflection->isStatic());
    }
}