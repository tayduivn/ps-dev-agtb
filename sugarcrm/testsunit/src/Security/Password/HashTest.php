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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Password;

use Sugarcrm\Sugarcrm\Security\Password\Hash;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Password\Hash
 *
 */
class HashTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::verify
     * @covers ::verifyMd5
     * @covers ::isLegacyHash
     * @covers ::verifyLegacy
     * @covers ::setAllowLegacy
     * @dataProvider providerTestVerifyMd5
     */
    public function testVerify($legacy, $password, $hash, $expected)
    {
        $backend = $this->getMock('Sugarcrm\Sugarcrm\Security\Password\BackendInterface');
        $backend->expects($this->any())
            ->method('verify')
            ->with($this->equalTo(md5($password)), $this->equalTo($hash))
            ->will($this->returnValue(null));

        $sut = new Hash($backend);
        $sut->setAllowLegacy($legacy);

        $this->assertSame($expected, $sut->verify($password, $hash));
    }

    public function providerTestVerifyMd5()
    {
        return array(
            // invalid md5 hash, hits backend
            array(
                true,
                'password',
                'invalidmd5',
                null,
            ),
            // valid md5 hash and matching password
            array(
                true,
                'passwordgoeshere',
                '061ed5c2fdbe73d1420ec470f2c3e210',
                true,
            ),
            // valid md5 hash with wrong password
            array(
                true,
                'wrongpassword',
                '061ed5c2fdbe73d1420ec470f2c3e210',
                false,
            ),
            // valid md5 hash and matching password, but not allowed
            array(
                false,
                'passwordgoeshere',
                '061ed5c2fdbe73d1420ec470f2c3e210',
                null,
            ),
        );
    }
}
