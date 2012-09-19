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

require_once "modules/Mailer/MailerConfiguration.php";

class MailerConfigurationTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @group mailer
     */
    public function testLoadDefaultConfigs_CharsetIsReset_WordwrapIsInitialized() {
        $mailerConfig = new MailerConfiguration();

        // change the default configs in order to show that loadDefaultConfigs will reset them
        // this effectively tests setConfig as well
        $mailerConfig->setConfig("charset", "asdf"); // some asinine value that wouldn't actually be used

        // test that the charset has been changed from its default
        $expected = "asdf";
        $actual   = $mailerConfig->getConfig("charset");
        self::assertEquals($expected, $actual, "The charset should have been reset to {$expected}");

        $mailerConfig->loadDefaultConfigs();

        // test that the charset has been returned to its default
        $expected = "utf-8";
        $actual   = $mailerConfig->getConfig("charset");
        self::assertEquals($expected, $actual, "The charset should have been reset to {$expected}");

        // test that the wordwrap has been initialized correctly
        $expected = 996;
        $actual   = $mailerConfig->getConfig("wordwrap");
        self::assertEquals($expected, $actual, "The wordwrap should have been initialized to {$expected}");
    }

    /**
     * @group mailer
     */
    public function testMergeConfigs_NewConfigAddedToDefaultConfigs() {
        $mailerConfig = new MailerConfiguration();

        $additionalConfigs = array(
            "foo" => "bar",
        );
        $mailerConfig->mergeConfigs($additionalConfigs);

        $expected = "utf-8";
        $actual   = $mailerConfig->getConfig("charset");
        self::assertEquals($expected, $actual, "The charset should have been {$expected}");

        $expected = "bar";
        $actual   = $mailerConfig->getConfig("foo");
        self::assertEquals($expected, $actual, "The foo should have been {$expected}");
    }

    /**
     * @group mailer
     */
    public function testMergeConfigs_OverwriteExistingConfig() {
        $mailerConfig = new MailerConfiguration();

        $expected          = "iso-8559-1";
        $additionalConfigs = array(
            "charset" => $expected,
        );
        $mailerConfig->mergeConfigs($additionalConfigs);

        $actual = $mailerConfig->getConfig("charset");
        self::assertEquals($expected, $actual, "The charset should have been {$expected}");
    }

    /**
     * @group mailer
     */
    public function testSetConfigs_ReplaceDefaultConfigsWithNewConfigs() {
        $mailerConfiguration = new MailerConfiguration();

        $newConfigs = array(
            "foo" => "bar",
        );
        $mailerConfiguration->setConfigs($newConfigs);

        $expected = "bar";
        $actual   = $mailerConfiguration->getConfig("foo");
        self::assertEquals($expected, $actual, "The foo should have been {$expected}");

        self::setExpectedException("MailerException");
        $actual = $mailerConfiguration->getConfig("charset"); // hopefully this default no longer exists
    }
}
