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

require_once("modules/Forecasts/clients/base/api/ForecastsProgressApi.php");

class ForecastsProgressApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var TimePeriod
     */
    protected static $timeperiod;

    /**
     * @var ForecastsProgressApi
     */
    protected $api;

    /**
     * @var RestService
     */
    protected $service;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');

        self::$timeperiod = SugarTestForecastUtilities::getCreatedTimePeriod();
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    public function setUp()
    {
        $this->service = SugarTestRestUtilities::getRestServiceMock();
        $this->api = new ForecastsProgressApi();
    }

    public function tearDown()
    {
        unset($this->service);
        unset($this->api);
    }

    /**
     * Test method progressRep
     */
    public function testProgressRep()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();

        $result = $this->api->progressRep($this->service, array(
            'user_id' => $user->id,
            'timeperiod_id' => self::$timeperiod->id,
        ));
        $this->assertNotEmpty($result);

        $this->assertArrayHasKey('quota_amount', $result);
        $this->assertArrayHasKey('amount', $result);
        $this->assertArrayHasKey('best_case', $result);
        $this->assertArrayHasKey('worst_case', $result);
        $this->assertArrayHasKey('overall_amount', $result);
        $this->assertArrayHasKey('overall_best', $result);
        $this->assertArrayHasKey('overall_worst', $result);
        $this->assertArrayHasKey('overall_worst', $result);
        $this->assertArrayHasKey('timeperiod_id', $result);
        $this->assertArrayHasKey('lost_count', $result);
        $this->assertArrayHasKey('lost_amount', $result);
        $this->assertArrayHasKey('lost_best', $result);
        $this->assertArrayHasKey('lost_worst', $result);
        $this->assertArrayHasKey('won_count', $result);
        $this->assertArrayHasKey('won_amount', $result);
        $this->assertArrayHasKey('won_best', $result);
        $this->assertArrayHasKey('won_worst', $result);
        $this->assertArrayHasKey('included_opp_count', $result);
        $this->assertArrayHasKey('total_opp_count', $result);
        $this->assertArrayHasKey('includedClosedCount', $result);
        $this->assertArrayHasKey('includedClosedAmount', $result);
        $this->assertArrayHasKey('includedClosedBest', $result);
        $this->assertArrayHasKey('includedClosedWorst', $result);
        $this->assertArrayHasKey('includedIdsInLikelyTotal', $result);
        $this->assertArrayHasKey('user_id', $result);

        $this->assertEquals($user->id, $result['user_id']);
        $this->assertEquals(self::$timeperiod->id, $result['timeperiod_id']);
    }

    /**
     * Test method progressManager
     */
    public function testProgressManager()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();

        $result = $this->api->progressManager($this->service, array(
            'user_id' => $user->id,
            'timeperiod_id' => self::$timeperiod->id,
        ));
        $this->assertNotEmpty($result);

        $this->assertArrayHasKey('best_case', $result);
        $this->assertArrayHasKey('best_adjusted', $result);
        $this->assertArrayHasKey('likely_case', $result);
        $this->assertArrayHasKey('likely_adjusted', $result);
        $this->assertArrayHasKey('timeperiod_id', $result);
        $this->assertArrayHasKey('user_id', $result);
        $this->assertArrayHasKey('worst_case', $result);
        $this->assertArrayHasKey('worst_adjusted', $result);
        $this->assertArrayHasKey('closed_amount', $result);
        $this->assertArrayHasKey('quota_amount', $result);

        $this->assertEquals($user->id, $result['user_id']);
        $this->assertEquals(self::$timeperiod->id, $result['timeperiod_id']);
    }
}
