<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

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
