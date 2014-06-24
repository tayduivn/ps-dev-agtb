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

require_once 'modules/RevenueLineItems/clients/base/api/RevenueLineItemsPipelineChartApi.php';
require_once 'SugarTestForecastUtilities.php';

/**
 * Tests RevenueLineItemsPipelineChartApiTest.
 */
class RevenueLineItemsPipelineChartApiTest extends Sugar_PHPUnit_Framework_TestCase
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

        $this->api = new RevenueLineItemsPipelineChartApi();

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
