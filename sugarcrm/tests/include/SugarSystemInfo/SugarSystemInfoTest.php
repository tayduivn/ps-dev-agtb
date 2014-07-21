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

require_once 'include/SugarSystemInfo/SugarSystemInfo.php';

/**
 * Class SugarSystemInfoTest
 * @group BR-1722
 */
class SugarSystemInfoTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var SugarSystemInfo
     */
    protected $sysInfo;

    protected function setUp()
    {
        SugarTestTrackerUtility::setup();
        $bean = new Account();
        $user = SugarTestUserUtilities::createAnonymousUser(true, true);
        $user->updateLastLogin();
        SugarTestTrackerUtility::insertTrackerEntry($bean, 'editview');
        $this->sysInfo = SugarSystemInfo::getInstance();
    }

    protected function tearDown()
    {
        SugarTestTrackerUtility::restore();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTrackerUtility::removeAllTrackerEntries();
    }

    /**
     * @return SugarSystemInfo
     */
    public function testGetInstance()
    {
       $this->assertInstanceOf('SugarSystemInfo', SugarSystemInfo::getInstance());
    }


    public function testGetInfo()
    {
        $this->assertArrayHasKey('sugar_flavor', $this->sysInfo->getInfo());
    }

    public function testGetBaseInfo()
    {
        $this->assertArrayHasKey('auth_level', $this->sysInfo->getBaseInfo());
    }

    public function testGetUsersInfo()
    {
        $this->assertArrayHasKey('users', $this->sysInfo->getUsersInfo());
        $this->assertArrayHasKey('registered_users', $this->sysInfo->getUsersInfo());
        $this->assertArrayHasKey('admin_users', $this->sysInfo->getUsersInfo());
        $this->assertArrayHasKey('users_active_30_days', $this->sysInfo->getUsersInfo());
    }

    public function testGetActiveUsersXDaysCount()
    {
        $this->assertGreaterThan(0, $this->sysInfo->getActiveUsersXDaysCount(30));
    }

    public function testGetAdminCount()
    {
        $this->assertGreaterThan(0, $this->sysInfo->getAdminCount());
    }

    public function testGetUsersCount()
    {
        $this->assertGreaterThan(0, $this->sysInfo->getUsersCount());
    }

    public function testGetActiveUsersCount()
    {
        $this->assertGreaterThan(0, $this->sysInfo->getActiveUsersCount());
    }

    public function testGetSystemName()
    {
        $sql = "SELECT value FROM config WHERE category = 'system' AND name = 'name'";
        $systemName = $GLOBALS['db']->getOne($sql);
        $this->assertNotEmpty($systemName);
        $this->assertEquals($systemName, $this->sysInfo->getSystemName());
    }

    public function testGetSystemNameInfo()
    {
        $this->assertArrayHasKey('system_name', $this->sysInfo->getSystemNameInfo());
    }

    public function testGetLicenseInfo()
    {
        $this->assertArrayHasKey('license_users', $this->sysInfo->getLicenseInfo());
    }

    public function testGetClientInfo()
    {
        if(!file_exists('modules/Administration/System.php')) {
            $this->markTaskSkipped('This test relies on System.php bean');
        }
        $this->assertArrayHasKey('oc_br_all', $this->sysInfo->getClientInfo());
    }

    public function testGetLicensePortalInfo()
    {
        $info = $this->sysInfo->getLicensePortalInfo();
        $this->assertArrayHasKey('license_portal_max', $info);
        $this->assertGreaterThan(-1, $info['license_portal_max']);
    }

    public function testGetEnvInfo()
    {
        $this->assertArrayHasKey('php_version', $this->sysInfo->getEnvInfo());
    }

    public function testGetAppInfo()
    {
        $this->assertArrayHasKey('auth_level', $this->sysInfo->getAppInfo());
    }

    public function testGetApplicationKeyInfo()
    {
        $this->assertArrayHasKey('application_key', $this->sysInfo->getApplicationKeyInfo());
    }

    public function testGetLatestTrackerIdInfo()
    {
        $info = $this->sysInfo->getLatestTrackerIdInfo();
        $this->assertArrayHasKey('latest_tracker_id', $info);
        $this->assertGreaterThan(0, $info['latest_tracker_id']);
    }
}
