<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once "modules/OutboundEmailConfiguration/OutboundEmailConfigurationPeer.php";
require_once "OutboundEmailConfigurationTestHelper.php";

/**
 * @group email
 * @group outboundemailconfiguration
 */
class OutboundEmailConfigurationPeerTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $systemOverrideConfiguration;

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp("current_user");
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");
        OutboundEmailConfigurationTestHelper::setUp();

        $this->systemOverrideConfiguration =
            OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
                $GLOBALS["current_user"]->id
            );
    }

    public function tearDown()
    {
        OutboundEmailConfigurationTestHelper::tearDown();
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDown();
    }

    public function testListMailConfigurations_NoSystemOrSystemOverrideConfigurationsExist_SystemConfigurationIsNotAllowed_SystemOverrideConfigurationIsCreatedAndReturned()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $configuration = $mockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS["current_user"]);

        $expected = "system";
        $actual   = $configuration[0]->getConfigType();
        $this->assertEquals($expected, $actual, "The system-override configuration should be of type 'system'");

        $actual = $configuration[0]->getPersonal();
        $this->assertTrue($actual, "The system-override configuration should be a personal configuration");
    }

    public function testListMailConfigurations_NoSystemOrSystemOverrideConfigurationsExist_SystemConfigurationIsAllowed_SystemConfigurationIsCreatedAndReturned()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $configuration = $mockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS["current_user"]);

        $expected = "system";
        $actual   = $configuration[0]->getConfigType();
        $this->assertEquals($expected, $actual, "The system configuration should be of type 'system'");

        $actual = $configuration[0]->getPersonal();
        $this->assertFalse($actual, "The system configuration should not be a personal configuration");
    }

    public function testListMailConfigurations_SystemConfigurationIsNotAllowedAndUserHasUserAndSystemOverrideConfigurations_ReturnsAllExceptTheSystemConfiguration()
    {
        $userConfigurations = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(2);

        $expected = array(
            $this->systemOverrideConfiguration->id => $this->systemOverrideConfiguration->name,
            $userConfigurations[0]["outbound"]->id => $userConfigurations[0]["outbound"]->name,
            $userConfigurations[1]["outbound"]->id => $userConfigurations[1]["outbound"]->name,
        );

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $configurations = $mockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS["current_user"]);
        $actual         = array();

        foreach ($configurations AS $configuration) {
            $actual[$configuration->getConfigId()] = $configuration->getConfigName();
        }

        $this->assertEquals($expected, $actual, "The wrong configurations were returned");
    }

    public function testListMailConfigurations_SystemConfigurationIsAllowedAndUserHasUserAndSystemOverrideConfigurations_ReturnsAllExceptTheSystemOverrideConfiguration()
    {
        $userConfigurations  = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfigurations(2);
        $systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();

        $expected = array(
            $systemConfiguration->id               => $systemConfiguration->name,
            $userConfigurations[0]["outbound"]->id => $userConfigurations[0]["outbound"]->name,
            $userConfigurations[1]["outbound"]->id => $userConfigurations[1]["outbound"]->name,
        );

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $configurations = $mockOutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS["current_user"]);
        $actual         = array();

        foreach ($configurations AS $configuration) {
            $actual[$configuration->getConfigId()] = $configuration->getConfigName();
        }

        $this->assertEquals($expected, $actual, "The wrong configurations were returned");
    }

    public function testGetSystemMailConfiguration_SystemConfigurationIsNotAllowed_ReturnsTheUsersSystemOverrideConfiguration()
    {
        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $configuration = $mockOutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS["current_user"]);

        $expected = $this->systemOverrideConfiguration->id;
        $actual   = $configuration->getConfigId();
        $this->assertEquals($expected, $actual, "The user's system-override configuration should have been returned");
    }

    public function testGetSystemMailConfiguration_SystemConfigurationIsAllowed_ReturnsTheSystemConfiguration()
    {
        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $configuration = $mockOutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS["current_user"]);

        $expected = "system";
        $actual   = $configuration->getConfigType();
        $this->assertEquals($expected, $actual, "The system configuration should be of type 'system'");

        $actual = $configuration->getPersonal();
        $this->assertFalse($actual, "The system configuration should not be a personal configuration");
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsAllowedAndSystemConfigurationIsValid_ReturnsTrue()
    {
        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertTrue($actual, "There should be a system configuration and the host should not be empty");
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsAllowedAndSystemConfigurationIsInvalid_ReturnsFalse()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = array(
            "name"              => "System",
            "type"              => "system",
            "user_id"           => "1",
            "from_email"        => "foo@bar.com",
            "from_name"         => "Foo Bar",
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "foo",
            "mail_smtppass"     => "foobar",
            "mail_smtpauth_req" => "1",
            "mail_smtpssl"      => "0",
        );
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(true);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertFalse($actual, "There should be a system configuration but the host should be empty");
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsNotAllowedAndSystemOverrideConfigurationIsValid_ReturnsTrue()
    {
        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertTrue($actual, "There should be a system-override configuration and the host should not be empty");
    }

    public function testValidSystemMailConfigurationExists_SystemConfigurationIsNotAllowedAndSystemOverrideConfigurationIsInvalid_ReturnsFalse()
    {
        OutboundEmailConfigurationTestHelper::removeAllCreatedEmailRecords();

        $configuration = array(
            "name"              => "System Override",
            "type"              => "system-override",
            "user_id"           => $GLOBALS["current_user"]->id,
            "from_email"        => "foo@bar.com",
            "from_name"         => "Foo Bar",
            "mail_sendtype"     => "SMTP",
            "mail_smtptype"     => "other",
            "mail_smtpserver"   => "",
            "mail_smtpport"     => "25",
            "mail_smtpuser"     => "foo",
            "mail_smtppass"     => "foobar",
            "mail_smtpauth_req" => "1",
            "mail_smtpssl"      => "0",
        );
        OutboundEmailConfigurationTestHelper::createOutboundEmail($configuration);

        $mockOutboundEmailConfigurationPeer = $this->getMockOutboundEmailConfigurationPeer(false);

        $actual = $mockOutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertFalse($actual, "There should be a system-override configuration but the host should be empty");
    }

    private function getMockOutboundEmailConfigurationPeer($isAllowUserAccessToSystemDefaultOutbound = false)
    {
        $mockOutboundEmail = $this->getMock("OutboundEmail", array("isAllowUserAccessToSystemDefaultOutbound"));
        $mockOutboundEmail->expects($this->any())
            ->method("isAllowUserAccessToSystemDefaultOutbound")
            ->will($this->returnValue($isAllowUserAccessToSystemDefaultOutbound));

        $mockOutboundEmailConfigurationPeer = $this->getMockClass(
            "OutboundEmailConfigurationPeer",
            array("loadOutboundEmail")
        );
        $mockOutboundEmailConfigurationPeer::staticExpects($this->any())
            ->method("loadOutboundEmail")
            ->will($this->returnValue($mockOutboundEmail));

        return $mockOutboundEmailConfigurationPeer;
    }
}
