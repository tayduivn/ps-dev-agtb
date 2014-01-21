<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once 'clients/base/api/ConfigModuleApi.php';

/**
 * RS4: Prepare ConfigModule Api.
 */
class RS4Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var User
     */
    protected static $admin;

    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var mixed
     */
    protected $config;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$admin = SugarTestHelper::setUp('current_user', array(true, true));
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        global $current_user;
        parent::setUp();
        $this->api = new ConfigModuleApi();
        $this->config = $this->api->config(
            SugarTestRestUtilities::getRestServiceMock(self::$admin),
            array('module' => 'Accounts')
        );
        $current_user = SugarTestUserUtilities::createAnonymousUser(true, false);

    }

    protected function tearDown()
    {
        $this->api->configSave(
            SugarTestRestUtilities::getRestServiceMock(self::$admin),
            array_merge(array('module' => 'Accounts'), $this->config)
        );
        parent::tearDown();
    }

    /**
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testNoAccess()
    {
        $this->api->configSave(
            SugarTestRestUtilities::getRestServiceMock(),
            array('module' => 'Accounts')
        );
    }

    /**
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testEmptyModule()
    {
        $this->api->config(
            SugarTestRestUtilities::getRestServiceMock(self::$admin),
            array()
        );
    }

    public function testSave()
    {
        $config = array('RS4Test_param1' => 'value1', 'RS4Test_param2' => array('RS4Test_param3' => 'value2'));
        $result = $this->api->configSave(
            SugarTestRestUtilities::getRestServiceMock(self::$admin),
            array_merge(array('module' => 'Accounts'), $config)
        );
        $this->assertArrayHasKey('RS4Test_param1', $result);
        $this->assertEquals($config['RS4Test_param1'], $result['RS4Test_param1']);
        $this->assertArrayHasKey('RS4Test_param2', $result);
        $this->assertEquals($config['RS4Test_param2'], $result['RS4Test_param2']);
    }
}
