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

require_once 'clients/base/api/MassUpdateApi.php';

/**
 *  Prepare MassUpdate Api.
 */
class RS189Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected static $rest;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
        self::$rest = SugarTestRestUtilities::getRestServiceMock();
    }

    public static function tearDownAfterClass()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->api = new MassUpdateApi();
    }

    /**
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testDeleteException()
    {
        $this->api->massDelete(self::$rest, array());
    }

    public function testEmptyDelete()
    {
        $result = $this->api->massDelete(
            self::$rest,
            array('massupdate_params' => array(), 'module' => 'Accounts')
        );
        $this->assertEquals(array('status' => 'done'), $result);
    }

    public function testDelete()
    {
        $id = create_guid();
        $account = SugarTestAccountUtilities::createAccount($id);
        $result = $this->api->massDelete(
            self::$rest,
            array(
                'massupdate_params' => array('uid' => array($id)),
                'module' => 'Accounts'
            )
        );
        $this->assertEquals(array('status' => 'done'), $result);
        $account = BeanFactory::getBean('Accounts');
        $account->retrieve($id, true, false);
        $this->assertEquals(1, $account->deleted);
    }

    public function testMassUpdate()
    {
        $account = SugarTestAccountUtilities::createAccount();
        $result = $this->api->massUpdate(
            self::$rest,
            array(
                'massupdate_params' => array('uid' => array($account->id), 'name' => 'RS189Test'),
                'module' => 'Accounts'
            )
        );
        $this->assertEquals(array('status' => 'done'), $result);
        $account = BeanFactory::getBean('Accounts', $account->id);
        $this->assertEquals('RS189Test', $account->name);
    }
}
