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

require_once 'modules/Filters/clients/base/api/PreviouslyUsedFiltersApi.php';

/**
 *  RS175: Prepare PreviouslyUsedFilters Api.
 */
class RS175Test extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var PreviouslyUsedFiltersApi
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
        SugarTestFilterUtilities::removeAllCreatedFilters();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->api = new PreviouslyUsedFiltersApi();
    }

    protected function tearDown()
    {
        $this->api->setUsed(self::$rest, array('module_name' => 'Accounts', 'filters' => array()));
        parent::tearDown();
    }

    public function testApi()
    {
        global $current_user;
        $result = $this->api->setUsed(
            self::$rest,
            array('module_name' => 'Accounts', 'filters' => array())
        );
        $this->assertEmpty($result);

        $filter1 = SugarTestFilterUtilities::createUserFilter(
            $current_user->id,
            'RS189Filter1',
            json_encode(array('module' => 'Accounts', 'name' => 'RS189Name1'))
        );
        $result = $this->api->setUsed(
            self::$rest,
            array('module_name' => 'Accounts', 'filters' => array($filter1->id))
        );
        $this->assertCount(1, $result);
        $result = array_shift($result);
        $this->assertEquals($filter1->id, $result['id']);

        $result = $this->api->getUsed(self::$rest, array('module_name' => 'Accounts'));
        $this->assertCount(1, $result);
        $result = array_shift($result);
        $this->assertEquals($filter1->id, $result['id']);


        $filter2 = SugarTestFilterUtilities::createUserFilter(
            $current_user->id,
            'RS189Filter2',
            json_encode(array('module' => 'Accounts', 'name' => 'RS189Name2'))
        );
        $this->api->setUsed(
            self::$rest,
            array('module_name' => 'Accounts', 'filters' => array($filter1->id, $filter2->id))
        );

        $result = $this->api->deleteUsed(
            self::$rest,
            array('module_name' => 'Accounts', 'record' => $filter1->id)
        );
        $this->assertCount(1, $result);

        $result = $this->api->deleteUsed(
            self::$rest,
            array('module_name' => 'Accounts')
        );
        $this->assertEmpty($result);
    }
}
