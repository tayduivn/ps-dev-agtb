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

namespace Sugarcrm\SugarcrmTestsUnit\Security\Crypto;

use Sugarcrm\Sugarcrm\Security\Crypto\CSPRNG;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;

/**
 *
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Security\Crypto\CSPRNG
 *
 */
class CSPRNGTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Platform suported generator
     *
     * @covers ::generate
     * @dataProvider providerTestGenerate
     */
    public function testGenerate($size, $encode)
    {
        $csprng = new CSPRNG();
        $random = $csprng->generate($size, $encode);
        $actual = TestReflection::callProtectedMethod($csprng, 'binaryStrLen', array($random));
        $this->assertEquals($size, $actual);
    }

    public function providerTestGenerate()
    {
        return array(
            array(10, false),
            array(256, false),
            array(512, false),
            array(1024, false),
            array(10, true),
            array(256, true),
            array(512, true),
            array(1024, true),
        );
    }

    /**
     * Generator specific testing
     *
     * @covers ::genUrandom
     * @covers ::genMcrypt
     * @covers ::genOpenssl
     *
     * @dataProvider providerTestGenerators
     */
    public function testGenerators($method, $size)
    {
        $csprng = new CSPRNG();

        // make sure platform suports the generator
        if (!in_array($method, TestReflection::getProtectedValue($csprng, 'generators'))) {
            $this->markTestSkipped("Unsupported generator $method");
        }

        $random = TestReflection::callProtectedMethod($csprng, $method, array($size));
        $actualSize = TestReflection::callProtectedMethod($csprng, 'binaryStrLen', array($random));
        $this->assertEquals($size, $actualSize);
    }

    public function providerTestGenerators()
    {
        return array(
            array('genUrandom', 8),
            array('genUrandom', 16),
            array('genUrandom', 22),
            array('genUrandom', 32),
            array('genUrandom', 64),
            array('genMcrypt', 8),
            array('genMcrypt', 16),
            array('genMcrypt', 22),
            array('genMcrypt', 32),
            array('genMcrypt', 64),
            array('genOpenssl', 8),
            array('genOpenssl', 16),
            array('genOpenssl', 22),
            array('genOpenssl', 32),
            array('genOpenssl', 64),
        );
    }

    /**
     * @covers ::binaryEncode
     * @dataProvider providerTestBinaryEncode
     */
    public function testBinaryEncode($binary, $size, $expected)
    {
        $csprng = new CSPRNG();
        $encoded = TestReflection::callProtectedMethod($csprng, 'binaryEncode', array($binary, $size));
        $this->assertRegExp('#^[A-Za-z0-9+/]+$#D', $encoded);
        $this->assertEquals($expected, $encoded);
    }

    public function providerTestBinaryEncode()
    {
        return array(
            // already encoded format
            array(
                'abcdefghij',
                5,
                'YWJjZ',
            ),
            // real binary string
            array(
                0xCC . 0xA9 . 0xF2 . 0x42 . 0x8C . 0x39 . 0xBA . 0xDD,
                4,
                'MjA0',
            ),
        );
    }

    /**
     * @covers ::binaryStrLen
     * @covers ::binarySubstr
     * @dataProvider providerTestBinaryStr
     */
    public function testBinaryStr($string, $strLen, $start, $length, $subStr)
    {
        $csprng = new CSPRNG();
        $actualStrLen = TestReflection::callProtectedMethod($csprng, 'binaryStrLen', array($string));
        $actualSubStr = TestReflection::callProtectedMethod($csprng, 'binarySubStr', array($string, $start, $length));
        $this->assertEquals($strLen, $actualStrLen);
        $this->assertEquals($subStr, $actualSubStr);
    }

    public function providerTestBinaryStr()
    {
        return array(
            array('xyz', 3, 0, 1, 'x'),
            array('我abc', 6, 3, 2, 'ab'),
        );
    }
}
