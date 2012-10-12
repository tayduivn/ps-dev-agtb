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
    private $mockMailerConfig;

    public function setUp() {
        $GLOBALS["current_user"] = SugarTestUserUtilities::createAnonymousUser();

        $this->mockMailerConfig = self::getMock(
            "OutboundSmtpEmailConfiguration"
        );
    }

    public function tearDown() {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS["current_user"]);
    }

    /**
     * @group mailer
     */
    public function testGetMailerForUser_UserHasAMailConfiguration_ReturnsSmtpMailer() {
        $expected = "SmtpMailer";
        $actual   = MailerFactory::getMailerForUser($GLOBALS["current_user"]);
        self::assertInstanceOf($expected, $actual, "The mailer should have been a {$expected}");
    }

    /**
     * @group mailer
     */
    public function testGetMailerForUser_UserHasNoMailConfigurations_ThrowsMailerException() {
        self::markTestIncomplete("Not yet implemented; requires ability to remove all possible mail configurations for the user, including system configurations, or ability to mock them out");
    }

    /**
     * @group mailer
     * @group functional
     */
    public function testGetMailer_ConfigSenderEmailIsInvalid_ThrowsMailerException() {
        $mailConfig               = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $mailConfig->sender_email = 1234; // an invalid From email address

        self::setExpectedException("MailerException");
        $actual = MailerFactory::getMailer($mailConfig); // hopefully nothing is actually returned
    }

    /**
     * @group mailer
     */
    public function testGetMailer_NoMode_ReturnsSmtpMailer() {
        $mailConfig                   = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $mailConfig->sender_email     = "foo@bar.com";
        $mailConfig->mailerConfigData = $this->mockMailerConfig;

        $expected = "SmtpMailer";
        $actual   = MailerFactory::getMailer($mailConfig);
        self::assertInstanceOf($expected, $actual, "The mailer should have been a {$expected}");
    }

    /**
     * @group mailer
     */
    public function testGetMailer_ModeIsAllCaps_ReturnsSmtpMailer() {
        $mailConfig                   = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $mailConfig->mode             = strtoupper(MailConfigurationPeer::MODE_SMTP); // use a valid mode in all caps
        $mailConfig->sender_email     = "foo@bar.com";
        $mailConfig->mailerConfigData = $this->mockMailerConfig;

        $expected = "SmtpMailer";
        $actual   = MailerFactory::getMailer($mailConfig);
        self::assertInstanceOf($expected, $actual, "The mailer should have been a {$expected}");
    }

    /**
     * @group mailer
     */
    public function testGetMailer_ModeIsInvalid_ThrowsException() {
        $mailConfig                   = new OutboundEmailConfiguration($GLOBALS["current_user"]);
        $mailConfig->mode             = "asdf"; // some asinine value that wouldn't actually be used
        $mailConfig->sender_email     = "foo@bar.com";
        $mailConfig->mailerConfigData = $this->mockMailerConfig;

        self::setExpectedException("MailerException");
        $actual = MailerFactory::getMailer($mailConfig); // hopefully nothing is actually returned
    }
}
