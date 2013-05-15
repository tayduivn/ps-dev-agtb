<?php
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once "modules/OutboundEmailConfiguration/OutboundEmailConfiguration.php";

/**
 * @group email
 * @group outboundemailconfiguration
 */
class OutboundEmailConfigurationTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp("current_user");
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testLoadDefaultConfigs_CharsetIsReset_WordwrapIsInitialized()
    {
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);

        // change the default charset in order to show that loadDefaultConfigs will reset it
        $configuration->setCharset("asdf"); // some asinine value that wouldn't actually be used

        // test that the charset has been changed from its default
        $expected = "asdf";
        $actual   = $configuration->getCharset();
        self::assertEquals($expected, $actual, "The charset should have been set to {$expected}");

        $configuration->loadDefaultConfigs();

        // test that the charset has been returned to its default
        $expected = "utf-8";
        $actual   = $configuration->getCharset();
        self::assertEquals($expected, $actual, "The charset should have been reset to {$expected}");

        // test that the wordwrap has been initialized correctly
        $expected = 996;
        $actual   = $configuration->getWordwrap();
        self::assertEquals($expected, $actual, "The wordwrap should have been initialized to {$expected}");
    }

    public function testSetEncoding_PassInAValidEncoding_EncodingIsSet()
    {
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $expected      = Encoding::EightBit;

        $configuration->setEncoding($expected);
        $actual = $configuration->getEncoding();
        self::assertEquals($expected, $actual, "The encoding should have been set to {$expected}");
    }

    public function testSetEncoding_PassInAnInvalidEncoding_ThrowsException()
    {
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $encoding      = "asdf"; // some asinine value that wouldn't actually be used

        self::setExpectedException("MailerException");
        $configuration->setEncoding($encoding);
    }

    public function testSetMode_ModeIsInvalid_ThrowsException()
    {
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $invalidMode   = "asdf"; // some asinine value that wouldn't actually be used

        self::setExpectedException("MailerException");
        $configuration->setMode($invalidMode); // hopefully nothing is actually returned
    }

    public function testSetMode_NoMode_ModeBecomesDefault()
    {
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $configuration->setMode("");

        $expected = OutboundEmailConfigurationPeer::MODE_DEFAULT;
        $actual   = $configuration->getMode();
        self::assertEquals($expected, $actual, "The mode should have been a {$expected}");
    }

    public function testSetFrom_EmailIsInvalid_ThrowsMailerException()
    {
        self::setExpectedException("MailerException");
        $configuration = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $configuration->setFrom(1234); // an invalid email address
    }
}
