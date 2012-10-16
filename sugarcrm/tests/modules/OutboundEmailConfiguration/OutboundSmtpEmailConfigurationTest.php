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

require_once "modules/OutboundEmailConfiguration/OutboundSmtpEmailConfiguration.php";

class OutboundSmtpEmailConfigurationTest extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp() {
        $GLOBALS["current_user"] = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown() {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS["current_user"]);
    }

    /**
     * @group outboundemailconfiguration
     */
    public function testSetSecurityProtocol_PassInAValidProtocol_SecurityProtocolIsSet() {
        $config   = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $expected = OutboundSmtpEmailConfiguration::SecurityProtocolSsl;

        $config->setSecurityProtocol($expected);
        $actual = $config->getSecurityProtocol();
        self::assertEquals($expected, $actual, "The security protocol should have been set to {$expected}");
    }

    /**
     * @group outboundemailconfiguration
     */
    public function testSetSecurityProtocol_PassInAnInvalidProtocol_ThrowsException() {
        $config           = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $securityProtocol = "asdf"; // some asinine value that wouldn't actually be used

        self::setExpectedException("MailerException");
        $config->setSecurityProtocol($securityProtocol);
    }

    /**
     * @group outboundemailconfiguration
     */
    public function testSetMode_ValidModeSmtpIsInAllCaps_ModeBecomesLowerCaseSmtp() {
        $config = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);

        $expected = OutboundEmailConfigurationPeer::MODE_SMTP;
        $config->setMode(strtoupper($expected));
        $actual = $config->getMode();
        self::assertEquals($expected, $actual, "The mode should have been a {$expected}");
    }

    /**
     * @group outboundemailconfiguration
     */
    public function testSetMode_NoMode_ModeBecomesSmtp() {
        $config = new OutboundSmtpEmailConfiguration($GLOBALS["current_user"]);
        $config->setMode("");

        $expected = OutboundEmailConfigurationPeer::MODE_SMTP;
        $actual   = $config->getMode();
        self::assertEquals($expected, $actual, "The mode should have been a {$expected}");
    }
}
