<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use PHPUnit\Framework\TestCase;

require_once 'include/utils/file_utils.php';

/**
 * Can't use 'covers' on functions.
 * covers isValidCopyPath
 */
class IsValidCopyPathTest extends TestCase
{
    /**
     * @dataProvider isValidCopyPathSuccessProvider
     */
    public function testIsValidCopyPathSuccess($path)
    {
        $actual = isValidCopyPath($path);
        $this->assertTrue($actual);
    }

    public static function isValidCopyPathSuccessProvider()
    {
        return array(
            'unix-path' => array('a/b/c'),
            'windows-path' => array('a\\b\\c'),
            'directory-name-contains-dots' => array('a/b..c/d'),
        );
    }

    /**
     * @dataProvider isValidCopyPathFailureProvider
     */
    public function testIsValidCopyPathFailure($path)
    {
        $actual = isValidCopyPath($path);
        $this->assertFalse($actual);
    }

    public static function isValidCopyPathFailureProvider()
    {
        return array(
            'absolute-unix' => array('/etc/passwd'),
            'absolute-windows' => array('\\Windows\\Whatever'),
            'parent-directory-beginning' => array('../some/other/instance'),
            'parent-directory-middle' => array('some/../other/instance'),
            'parent-directory-end' => array('some/other/instance/..'),
            'empty' => array(''),
        );
    }

    /**
     * @dataProvider isValidCopyPathFailureOnWindowsProvider
     *
     * @requires OSFAMILY Windows
     */
    public function testIsValidCopyPathFailureOnWindows($path)
    {
        $actual = isValidCopyPath($path);
        $this->assertFalse($actual);
    }

    public static function isValidCopyPathFailureOnWindowsProvider()
    {
        return array(
            'drive-letter' => array('C:\\Windows\\Whatever'),
        );
    }
}
