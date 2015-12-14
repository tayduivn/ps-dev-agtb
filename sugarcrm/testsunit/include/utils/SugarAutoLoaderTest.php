<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTestUnit\inc\utils;

/**
 *
 * SugarAutoLoader unit tests
 * @coversDefaultClass \SugarAutoLoader
 *
 */
class SugarAutoLoaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::validateFilePath
     * @dataProvider providerTestValidFilePath
     */
    public function testValidFilePath($file)
    {
        $result = \SugarAutoLoader::validateFilePath($file);
        $this->assertSame($result, $file);
    }

    public function providerTestValidFilePath()
    {
        return array(
            array(SUGAR_BASE_DIR . '/modules/Accounts/Account.php'),
        );
    }

    /**
     * @covers ::validateFilePath
     * @dataProvider providerTestInvalidFilePath
     */
    public function testInvalidFilePath($file, $msg)
    {
        $this->setExpectedException('\Exception', $msg);
        \SugarAutoLoader::validateFilePath($file);
    }

    public function providerTestInvalidFilePath()
    {
        return array(
            array(
                '/etc/passwd',
                'File name violation: file outside basedir'
            ),
            array(
                '/etc/passwd' . chr(0),
                'File name violation: null bytes detected'
            ),
            array(
                SUGAR_BASE_DIR . '/modules/Accounts/FooBar.php',
                'File name violation: file not found'
            ),
            array(
                SUGAR_BASE_DIR . '/modules/../modules/Accounts/Account.php',
                'File name violation: directory traversal detected'
            ),
        );
    }
}
