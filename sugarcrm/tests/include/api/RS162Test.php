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
 * Copyright (C) 2004-2014 SugarCRM Inc. All rights reserved.
 */

require_once 'include/api/SugarListApi.php';

/**
 *  RS-162: Prepare SugarList Api.
 */
class RS162Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, false));
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function testParseArgs()
    {
        $api = $this->getMockForAbstractClass('SugarListApi');
        $rest = SugarTestRestUtilities::getRestServiceMock();
        $result = $api->parseArguments(
            $rest,
            array()
        );
        $this->assertArrayHasKey('limit', $result);
        $this->assertArrayHasKey('offset', $result);
        $this->assertArrayHasKey('fields', $result);
        $this->assertArrayHasKey('orderBy', $result);
    }

    public function testConvertOrderByToSql()
    {
        $api = $this->getMockForAbstractClass('SugarListApi');
        $result = $api->convertOrderByToSql(array('date' => 'ASC', 'name' => 'DESC'));
        $this->assertEquals('date ASC,name DESC', $result);
    }
}
