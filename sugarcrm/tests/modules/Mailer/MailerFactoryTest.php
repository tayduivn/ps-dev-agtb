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

class MailerFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() {
        $GLOBALS["current_user"] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown() {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS["current_user"]);
    }

    /**
     * @group email
     * @group mailer
     */
    public function testGetMailerForUser_UserHasAMailConfiguration_ReturnsSmtpMailer() {
        $outboundSmtpEmailConfiguration = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $outboundSmtpEmailConfiguration->setFrom("foo@bar.com", "Foo Bar");
        $outboundSmtpEmailConfiguration->setMode("smtp");

        $mockMailerFactory = self::getMockClass("MailerFactory", array("getOutboundEmailConfiguration"));
        $mockMailerFactory::staticExpects(self::any())
            ->method("getOutboundEmailConfiguration")
            ->will(self::returnValue($outboundSmtpEmailConfiguration));

        $expected = "SmtpMailer";
        $actual   = $mockMailerFactory::getMailerForUser($GLOBALS["current_user"]);
        self::assertInstanceOf($expected, $actual, "The mailer should have been a {$expected}");
    }

    /**
     * @group bug59513
     * @group email
     * @group mailer
     */
    public function testGetMailerForUser_UserHasNoMailConfigurations_ThrowsMailerException() {
        // Bug #59513
        // This test case requires mocking a static method, which requires use of late static binding. Late static
        // binding is available as of PHP 5.3, which is the minimum supported version for SugarCRM v7.0 and above.
        // However, test environments are still running PHP 5.2. Even the code the this case tests is specific to 7.0+,
        // until PHP 5.3 is standard on test environments, this test must be skipped.
        // Reference about mocking static methods in PHP Unit:
        // http://sebastian-bergmann.de/archives/883-Stubbing-and-Mocking-Static-Methods.html
        self::markTestSkipped("This is a 7.0+ test only, which requires ");
        $mockMailerFactory = self::getMockClass("MailerFactory", array("getOutboundEmailConfiguration"));
        $mockMailerFactory::staticExpects(self::any())
            ->method("getOutboundEmailConfiguration")
            ->will(self::throwException(new MailerException()));

        self::setExpectedException("MailerException");
        $actual = $mockMailerFactory::getMailerForUser($GLOBALS["current_user"]); // hopefully nothing is actually returned
    }

    /**
     * @group email
     * @group mailer
     */
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
}
