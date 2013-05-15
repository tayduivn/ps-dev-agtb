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

require_once "modules/Mailer/MailerFactory.php"; // imports all of the Mailer classes that are needed

/**
 * @group email
 * @group mailer
 */
class MailerFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() {
        SugarTestHelper::setUp("files");
        SugarTestHelper::setUp("current_user");
    }

    public function tearDown() {
        SugarTestHelper::tearDown();
    }

    /**
     * @group bug59513
     */
    public function testGetMailerForUser_UserHasAMailConfiguration_ReturnsSmtpMailerWithExpectedFromEmailAddress() {
        $expected = "foo@bar.com";

        $outboundSmtpEmailConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $outboundSmtpEmailConfiguration->setFrom($expected, "Foo Bar");

        $mockMailerFactory = self::getMockClass("MailerFactory", array("getOutboundEmailConfiguration"));
        $mockMailerFactory::staticExpects(self::any())
            ->method("getOutboundEmailConfiguration")
            ->will(self::returnValue($outboundSmtpEmailConfiguration));

        $mailer = $mockMailerFactory::getMailerForUser($GLOBALS["current_user"]);
        $from   = $mailer->getHeader(EmailHeaders::From);
        $actual = $from->getEmail();
        self::assertEquals(
            $expected,
            $actual,
            "The mailer should have been an SmtpMailer instance with '{$expected}' as the From email address");
    }

    /**
     * @group bug59513
     * @group functional
     */
    public function testGetMailerForUser_UsesACustomSendingStrategy_MailConfigurationExists_ReturnsCustomMailer() {
        SugarTestHelper::ensureDir("custom/modules/Mailer");

        $expected = "FooMailer_" . ((int)microtime(true)); // the name of the custom strategy that is expected

        $outboundSmtpEmailConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $outboundSmtpEmailConfiguration->setFrom("foo@bar.com", "Foo Bar");

        $strategies = array(
            "smtp" => $expected,
        );

        $mockMailerFactory = self::getMockClass(
            "MailerFactory",
            array(
                "getOutboundEmailConfiguration",
                "getStrategies",
            )
        );
        $mockMailerFactory::staticExpects(self::any())
            ->method("getOutboundEmailConfiguration")
            ->will(self::returnValue($outboundSmtpEmailConfiguration));
        $mockMailerFactory::staticExpects(self::any())
            ->method("getStrategies")
            ->will(self::returnValue($strategies));

        $file = "custom/modules/Mailer/{$expected}.php";
        SugarTestHelper::saveFile($file);

        $customMailer = <<<PHP
<?php
require_once "modules/Mailer/BaseMailer.php";

class {$expected} extends BaseMailer
{
    public function send() {}
}

PHP;
        SugarAutoLoader::put($file, $customMailer, true);

        $actual = $mockMailerFactory::getMailerForUser($GLOBALS["current_user"]);
        self::assertInstanceOf($expected, $actual, "The mailer should have been a {$expected}");
    }

    /**
     * @group bug59513
     */
    public function testGetMailerForUser_UserHasNoMailConfigurations_ThrowsMailerException() {
        // Bug #59513
        // This test case requires mocking a static method, which requires use of late static binding. Late static
        // binding is available as of PHP 5.3, which is the minimum supported version for SugarCRM v7.0 and above.
        // However, test environments are still running PHP 5.2. Even the code the this case tests is specific to 7.0+,
        // until PHP 5.3 is standard on test environments, this test must be skipped.
        // Reference about mocking static methods in PHP Unit:
        // http://sebastian-bergmann.de/archives/883-Stubbing-and-Mocking-Static-Methods.html
        $mockMailerFactory = self::getMockClass("MailerFactory", array("getOutboundEmailConfiguration"));
        $mockMailerFactory::staticExpects(self::any())
            ->method("getOutboundEmailConfiguration")
            ->will(self::throwException(new MailerException()));

        self::setExpectedException("MailerException");
        $actual = $mockMailerFactory::getMailerForUser($GLOBALS["current_user"]); // hopefully nothing is actually returned
    }

    public function testGetMailer_ModeIsInvalid_ThrowsException() {
        $mockOutboundEmailConfiguration = self::getMock(
            "OutboundEmailConfiguration",
            array("getMode"),
            array($GLOBALS["current_user"])
        );

        $mockOutboundEmailConfiguration->expects(self::any())
            ->method("getMode")
            ->will(self::returnValue("asdf")); // some asinine value that wouldn't actually be used

        $mockOutboundEmailConfiguration->setFrom("foo@bar.com");

        self::setExpectedException("MailerException");
        $actual = MailerFactory::getMailer($mockOutboundEmailConfiguration); // hopefully nothing is actually returned
    }

    /**
     * There is no currently no concept of a non-SMTP mailer. When the mode is default, then the configuration is
     * incapable of sending email even though the record exists. The record may exist because it was an SMTP
     * configuration at one time, but has since changed for some reason. Or, it may exist for legacy reasons or have
     * been mistakenly left around by a unit test. Regardless of the reason, there is no Mailer strategy that matches
     * the "default" mode, so we don't want to support the notion that such a strategy exists.
     */
    public function testGetMailer_ModeIsDefault_ThrowsException() {
        $mockOutboundEmailConfiguration = self::getMock(
            "OutboundEmailConfiguration",
            array("getMode"),
            array($GLOBALS["current_user"])
        );

        $mockOutboundEmailConfiguration->expects(self::any())
            ->method("getMode")
            ->will(self::returnValue("default"));

        $mockOutboundEmailConfiguration->setFrom("foo@bar.com");

        self::setExpectedException("MailerException");
        $actual = MailerFactory::getMailer($mockOutboundEmailConfiguration); // hopefully nothing is actually returned
    }
}
