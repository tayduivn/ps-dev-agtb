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

require_once 'clients/base/api/RecordListApi.php';

/**
 * RecordList Api Test
 */
class RecordListApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var RecordListApi
     */
    protected $recordListApi;

    /**
     * @var RestService
     */
    protected $serviceMock;

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user', array(true, true));

        $this->recordListApi = new RecordListApi();
        $this->serviceMock = SugarTestRestUtilities::getRestServiceMock();
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * Data provider for testRecordListCreate
     *
     * @return array
     */
    public function recordListCreateDataProvider()
    {
        return array(
            array(
                array(
                    'records' => array(1, 2, 3),
                    'module' => 'Accounts',
                ),
                'Accounts',
                array(1, 2, 3),
            ),
            array(
                array(
                    'records' => array(),
                    'module' => 'Contacts',
                ),
                'Contacts',
                array(),
            ),
            array(
                array(
                    'records' => array(3, 2, 1),
                    'module' => 'Contacts',
                ),
                'Contacts',
                array(3, 2, 1),
            ),
        );
    }

    /**
     * Test asserts behavior of recordListCreate
     *
     * @dataProvider recordListCreateDataProvider
     */
    public function testRecordListCreate($args, $moduleName, $records)
    {
        $result = $this->recordListApi->recordListCreate($this->serviceMock, $args);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('module_name', $result);
        $this->assertArrayHasKey('records', $result);

        $this->assertNotEmpty($result['id']);
        $this->assertEquals($moduleName, $result['module_name']);

        $this->assertEquals($records, $result['records']);
    }

    /**
     * Data provider for testRecordListDelete
     *
     * @return array
     */
    public function recordListDeleteDataProvider()
    {
        return array(
            array(
                'Accounts',
                array(1, 2, 3),
            ),
            array(
                'Accounts',
                array(),
            ),
            array(
                'Contacts',
                array(3, 2, 1),
            ),
        );
    }

    /**
     * Test asserts behavior of recordListDelete
     *
     * @dataProvider recordListDeleteDataProvider
     */
    public function testRecordListDelete($moduleName, array $records)
    {
        $recordListId = RecordListFactory::saveRecordList($records, $moduleName);

        $result = $this->recordListApi->recordListDelete($this->serviceMock, array(
            'module' => $moduleName,
            'record_list_id' => $recordListId,
        ));

        $this->assertNotEmpty($result);
        $this->assertTrue($result);
    }

    /**
     * Data provider for testRecordListGet
     *
     * @return array
     */
    public function recordListGetDataProvider()
    {
        return array(
            array(
                'Accounts',
                array(1, 2, 3),
            ),
            array(
                'Accounts',
                array(),
            ),
            array(
                'Contacts',
                array(3, 2, 1),
            ),
        );
    }

    /**
     * Test asserts behavior of recordListGet
     *
     * @dataProvider recordListGetDataProvider
     */
    public function testRecordListGet($moduleName, array $records)
    {
        $recordListId = RecordListFactory::saveRecordList($records, $moduleName);
        $result = $this->recordListApi->recordListGet($this->serviceMock, array(
            'module' => $moduleName,
            'record_list_id' => $recordListId,
        ));

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('module_name', $result);
        $this->assertArrayHasKey('records', $result);

        $this->assertNotEmpty($result['id']);
        $this->assertEquals($moduleName, $result['module_name']);
        $this->assertEquals($records, $result['records']);
    }
}
