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

/**
 * @coversDefaultClass ForecastWorksheet
 */

require_once 'tests/SugarTestDatabaseMock.php';

class ForecastWorksheetTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var SugarTestDatabaseMock
     */
    protected $db;

    public function setUp()
    {

        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        SugarTestForecastUtilities::setUpForecastConfig();
        // this is needed to preload vardefs & ACLs so DB mocking won't mess with them
        BeanFactory::getBean('ForecastWorksheets');
        BeanFactory::getBean('Accounts');

        $this->db = SugarTestHelper::setUp('mock_db');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestForecastUtilities::tearDownForecastConfig();
    }

    public function testGetRelatedNameReturnsEmpty()
    {
        $this->db->addQuerySpy(
            'accountQuery',
            '/my_test_id/',
            array(
                array(
                    'name' => 'My Test Account'
                ),
            )
        );

        $forecast_worksheet = BeanFactory::getBean('ForecastWorksheets');
        $return = SugarTestReflection::callProtectedMethod(
            $forecast_worksheet,
            'getRelatedName',
            array('Accounts', 'test_id')
        );
        $this->assertEmpty($return);
    }

    public function testGetRelatedNameReturnsName()
    {
        $acc_name = 'My Test Account';
        $acc_id = 'my_test_id';
        $this->db->addQuerySpy(
            'accountQuery',
            '/' . $acc_id . '/',
            array(
                array(
                    'name' => $acc_name
                ),
            )
        );

        $forecast_worksheet = BeanFactory::getBean('ForecastWorksheets');
        $return = SugarTestReflection::callProtectedMethod(
            $forecast_worksheet,
            'getRelatedName',
            array('Accounts', $acc_id)
        );
        $this->assertEquals($acc_name, $return);
    }

    /**
     * @dataProvider removeReassignedItemsProvider
     * @covers ::removeReassignedItems
     */
    public function testRemoveReassignedItems($settings, $expected, $tp_id, $execute_count, $beans)
    {
        $fw = $this->getMock('ForecastWorksheet', array('getForecastSettings',
                                                        'getBean',
                                                        'getSugarQuery',
                                                        'processRemoveChunk',
                                                        'createRemoveReassignedJob'));
        $tp = $this->getMock('TimePeriods');

        $sq = $this->getMock('SugarQuery', array('execute'));

        $tp->id = $tp_id;

        $sql = "SELECT  fw.id id FROM forecast_worksheets fw inner join forecast_worksheets fw2 on fw2.parent_id = fw.parent_id and fw2.assigned_user_id <> fw.assigned_user_id  WHERE fw.deleted = 0 AND fw.draft = 0 AND (fw.assigned_user_id = 'foo')";

        $sq->expects($this->exactly($execute_count))
            ->method('execute')
            ->will($this->returnValue($beans));

        $fw->expects($this->once())
            ->method('getForecastSettings')
            ->will($this->returnValue($settings));

        $fw->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($tp));

        $fw->expects($this->exactly($execute_count))
            ->method('getSugarQuery')
            ->will($this->returnValue($sq));

        $fw->expects($this->atLeast(((count($beans) == 0) || ($execute_count == 0))? 0:1))
            ->method('processRemoveChunk');

        if (count($beans) > 50) {
            $fw->expects($this->atleastonce())
                ->method('createRemoveReassignedJob');
        }

        $result = $fw->removeReassignedItems('foo', $tp_id);
        $this->assertEquals($expected, $result);

        if ($execute_count > 0) {
            $this->assertEquals($sql, $sq->compileSql());
        }
    }

    public function removeReassignedItemsProvider()
    {
        $fw = array('id' => 'foo');
        $fwArray = array();

        //generate 51 items to test going past the standard chunk size
        for ($i = 0; $i <= 50; $i++) {
            array_push($fwArray, $fw);
        }

        return array(
            array(
                array('is_setup' => true),
                true,
                'tpid',
                1,
                array($fw)
            ),

            array(
                array('is_setup' => true),
                true,
                'tpid',
                1,
                $fwArray
            ),

            array(array('is_setup' => false),
                false,
                'tpid',
                0,
                array($fw)
            ),

            array(array('is_setup' => true),
                false,
                '',
                0,
                array($fw)
            ),
            array(
                array('is_setup' => true),
                false,
                'tpid',
                1,
                array()
            )
        );
    }

    /**
     * @covers ::processRemoveChunk
     */
    public function testProcessRemoveChunk()
    {
        $fw = $this->getMock('ForecastWorksheet', array('mark_deleted'));
        $bean = array('id' => 'foo');

        $fw->expects($this->once())
            ->method('mark_deleted');

        $fw->processRemoveChunk(array($bean));
    }

    /**
     * @covers ::createRemoveReassignedJob
     */
    public function testCreateRemoveReassignedJob()
    {
        $data = array("foo");
        $user_id = "bar";
        $sj = $this->getMock('SchedulersJobs');
        $jq = $this->getMock('SugarJobQueue', array('submitJob'));
        $fw = $this->getMock('ForecastWorksheet', array('getBean',
                                                        'getJobQueue'));

        $jq->expects($this->once())
            ->method('submitJob')
            ->with($sj);

        $fw->expects($this->once())
            ->method('getBean')
            ->will($this->returnValue($sj));

        $fw->expects($this->once())
            ->method('getJobQueue')
            ->will($this->returnValue($jq));

        $fw->createRemoveReassignedJob($data, $user_id);

        $this->assertEquals('Remove Reassigned Items', $sj->name);
        $this->assertEquals('class::SugarJobRemoveReassignedItems', $sj->target);
        $this->assertEquals(json_encode($data), $sj->data);
        $this->assertEquals(0, $sj->retry_count);
        $this->assertEquals($user_id, $sj->assigned_user_id);

    }
}
