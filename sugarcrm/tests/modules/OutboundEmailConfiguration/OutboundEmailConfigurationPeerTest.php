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

class OutboundEmailConfigurationPeerTest extends Sugar_PHPUnit_Framework_TestCase
{
    public $systemConfig;

    public function setUp() {
        parent::setUp();

        $GLOBALS["current_user"] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS["db"]->commit(); // call a commit for transactional dbs
        SugarTestHelper::setUp("app_list_strings");
        SugarTestHelper::setUp("app_strings");
        SugarTestHelper::setUp("beanFiles");
        SugarTestHelper::setUp("beanList");

        OutboundEmailConfigurationTestHelper::backupExistingConfigurations();

        $config             = OutboundEmailConfigurationTestHelper::createOutboundEmailConfig(
            $name = "Name", $GLOBALS["current_user"]->id,  $type = "system-override",
            $fromEmail = "system@unit.net", $fromName = "From Name"
        );
        $this->systemConfig = OutboundEmailConfigurationTestHelper::createOutboundEmail($config);
    }

    public function tearDown() {
        OutboundEmailConfigurationTestHelper::restoreExistingConfigurations();

        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        parent::tearDown();
    }

    /**
     * @group email
     * @group outboundemailconfiguration
     */
    public function testListMailConfigurations_All_Success() {
        $seedConfigs = OutboundEmailConfigurationTestHelper::createSeedConfigurations(2);

        $expectedEmailConfigurations = array(
            $seedConfigs[0]["outbound"]->id => $seedConfigs[0]["outbound"]->name,
            $seedConfigs[1]["outbound"]->id => $seedConfigs[1]["outbound"]->name,
            $this->systemConfig->id         => $this->systemConfig->name
        );

        $configs = OutboundEmailConfigurationPeer::listMailConfigurations($GLOBALS["current_user"]);
        $actualEmailConfigurations = array();

        if (is_array($configs)) {
            foreach ($configs AS $config) {
                $actualEmailConfigurations[$config->getConfigId()] = $config->getConfigName();
            }
        }

        $this->assertEquals(
            $expectedEmailConfigurations,
            $actualEmailConfigurations,
            "Unexpected list for 'ALL' MailConfigurations"
        );
    }

    /**
     * @group email
     * @group outboundemailconfiguration
     */
    public function testListMailConfigurations_SystemOnly_Success() {
        $seedConfigs = OutboundEmailConfigurationTestHelper::createSeedConfigurations(2);

        $config = OutboundEmailConfigurationPeer::getSystemMailConfiguration($GLOBALS["current_user"]);

        $this->assertNotEmpty($config, "SYSTEM OutboundEmailConfiguration Not Found");
        $this->assertEquals(
            $config->getConfigId(),
            $this->systemConfig->id,
            "Unexpected 'SYSTEM' OutboundEmailConfiguration"
        );
    }

    /**
     * @group email
     * @group outboundemailconfiguration
     */
    public function testValidSystemMailConfigurationExists_SystemConfigExistsAndIsValid_ReturnsTrue() {
        $actual = OutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertTrue($actual, "A system mail configuration should exist");
    }

    /**
     * @group email
     * @group outboundemailconfiguration
     */
    public function testValidSystemMailConfigurationExists_NoSystemConfigExists_ReturnsFalse() {
        // delete the system configuration
        $this->systemConfig->delete();

        $actual = OutboundEmailConfigurationPeer::validSystemMailConfigurationExists($GLOBALS["current_user"]);
        self::assertFalse($actual, "No system mail configuration should be found");

        // restore the system configuration
        $this->systemConfig->new_with_id = true;
        $this->systemConfig->save();
    }
}
