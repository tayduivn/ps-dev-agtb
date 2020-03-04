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


use Sugarcrm\Sugarcrm\ProcessManager;
use PHPUnit\Framework\TestCase;

/**
 * Unit test class to cover ACL testing for SugarBPM Apis
 */
class PMSECasesListApiActTest extends TestCase
{
    /**
     * @var PMSECasesListApi
     */
    private $PMSECasesListApi;

    /**
     * @var RestService
     */
    private $api;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');

        $this->PMSECasesListApi = ProcessManager\Factory::getPMSEObject('PMSECasesListApi');
        $this->api = new RestService();
        $this->api->getRequest()->setRoute(array('acl' => 'adminOrDev'));
    }

    public function tearDown()
    {
        SugarTestACLUtilities::tearDown();
        SugarTestHelper::tearDown();
    }

    public function testSelectCasesList()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSECasesListApi->selectCasesList($this->api, array('module' => 'pmse_Inbox'));
    }

    public function testSelectLogLoad()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSECasesListApi->selectLogLoad($this->api, array('module' => 'pmse_Inbox'));
    }

    public function testClearLog()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSECasesListApi->clearLog($this->api, array('module' => 'pmse_Inbox'));
    }

    public function testConfigLogLoad()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSECasesListApi->configLogLoad($this->api, array('module' => 'pmse_Inbox'));
    }

    public function testConfigLogPut()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSECasesListApi->configLogPut($this->api, array('module' => 'pmse_Inbox'));
    }

    public function testReturnProcessUsersChart()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSECasesListApi->returnProcessUsersChart($this->api, array('module' => 'pmse_Inbox'));
    }

    public function testReturnProcessStatusChart()
    {
        $this->expectException(SugarApiExceptionNotAuthorized::class);
        $this->PMSECasesListApi->returnProcessStatusChart($this->api, array('module' => 'pmse_Inbox'));
    }

    /**
     * Check if valid user is allowed to pass ACL access
     */
    public function testReturnProcessUsersChartValidUser()
    {
        $GLOBALS['current_user']->is_admin = 1;

        $pmseCasesListApi = $this->getMockBuilder('PMSECasesListApi')
            ->setMethods(array('createProcessUsersChartData'))
            ->getMock();
        $pmseCasesListApi
            ->expects($this->any())
            ->method('createProcessUsersChartData')
            ->will($this->returnValue('testPassed'));

        $ret = $pmseCasesListApi->returnProcessUsersChart(
            $this->api,
            array('module' => 'pmse_Inbox', 'record' => 'dummy')
        );

        $this->assertEquals($ret, "testPassed", "ACL access test failed for returnProcessUsersChart");
    }
}
