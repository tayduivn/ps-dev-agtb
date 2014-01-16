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

require_once 'modules/Products/clients/base/api/ProductsPipelineChartApi.php';
require_once 'SugarTestForecastUtilities.php';

/**
 * Tests ProductsPipelineChartApi.
 */
class ProductsPipelineChartApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var SugarApi
     */
    protected $api;

    /**
     * @var User
     */
    protected $current_user;

    /**
     * @var int
     */
    protected $count = 3;

    /**
     * @var int
     */
    protected $case = 100;

    /**
     * @var array
     */
    protected $user;

    public static function setUpBeforeClass()
    {
        SugarTestForecastUtilities::setUpForecastConfig(array('is_setup' => 0));
        parent::setUpBeforeClass();
    }

    protected function setUp()
    {
        parent::setUp();

        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $this->current_user = SugarTestHelper::setUp('current_user', array(true, false));

        $this->api = new ProductsPipelineChartApi();

        $this->user =  SugarTestForecastUtilities::createForecastUser(
            array(
                'user' => array(
                    'reports_to' => $this->current_user->id,
                ),
                'opportunities' => array(
                    'total' => $this->count,
                    'include_in_forecast' => $this->count,
                ),
            )
        );
        $i = 0;
        $tp = SugarTestForecastUtilities::getCreatedTimePeriod();
        $d = TimeDate::getInstance()->fromDbDate($tp->start_date);
        $dt = $d->getTimestamp();

        foreach ($this->user['opportunities'] as $opp) {
            $i++;
            $opp->revenuelineitems->resetLoaded();
            foreach ($opp->revenuelineitems->getBeans() as $rli) {
                $rli->date_closed = $d->asDbDate();
                $rli->date_closed_timestamp = $dt;
                $rli->likely_case = $this->case;
                $rli->sales_stage = "stage_{$i}";
                $rli->save();
            }
        }
    }

    protected function tearDown()
    {
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestForecastUtilities::tearDownForecastConfig();
        parent::tearDownAfterClass();
    }

    public function testUserPipeline()
    {
        $result = $this->api->pipeline(
            SugarTestRestUtilities::getRestServiceMock($this->user['user']),
            array('module' => 'RevenueLineItems', 'type' => 'user')
        );
        $this->assertCount($this->count, $result['data']);
        $this->assertEquals($this->case * $this->count, $result['properties']['total']);
    }

    public function testGroupPipeline()
    {
        $result = $this->api->pipeline(
            SugarTestRestUtilities::getRestServiceMock($this->current_user),
            array('module' => 'RevenueLineItems', 'type' => 'group')
        );
        $this->assertCount($this->count, $result['data']);
        $this->assertEquals($this->case * $this->count, $result['properties']['total']);
    }
}
