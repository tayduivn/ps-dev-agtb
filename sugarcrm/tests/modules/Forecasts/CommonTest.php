<?php
//FILE SUGARCRM flav=pro ONLY
//TODO: fix this up for when expected opps is added back in 6.8 - https://sugarcrm.atlassian.net/browse/SFA-255
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
require_once('modules/Forecasts/Common.php');

class CommonTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Common
     */
    protected static $common_obj;

    /**
     * The Time period we are working with
     * @var Timeperiod
     */
    protected $timeperiod;

    /**
     * Manager
     * @var User
     */
    protected $manager;

    /**
     * Sales Rep
     * @var User
     */
    protected $rep;

    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        self::$common_obj = new Common();
    }

    public static function tearDownAfterClass()
    {
        self::$common_obj = null;
    }

    public function setUp()
    {
        $this->manager = SugarTestUserUtilities::createAnonymousUser();

        $this->rep = SugarTestUserUtilities::createAnonymousUser();
        $this->rep->reports_to_id = $this->manager->id;
        $this->rep->save();

        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep2->reports_to_id = $this->manager->id;
        $rep2->save();

        $this->timeperiod = SugarTestTimePeriodUtilities::createTimePeriod();

        SugarTestForecastUtilities::createForecast($this->timeperiod, $this->manager);

        SugarTestForecastUtilities::createForecast($this->timeperiod, $this->rep);

        // todo-sfa: Fix for 6.8
        //SugarTestForecastScheduleUtilities::createForecastSchedule($this->timeperiod, $this->rep);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        //SugarTestForecastScheduleUtilities::removeAllCreatedForecastSchedules();
    }

    /**
     * Only one record should be returned since we only created the forecast for the first user and not the second user
     *
     * @group forecasts
     */
    public function testGetReporteesWithForecastsReturnsOneRecord()
    {
        $return = self::$common_obj->getReporteesWithForecasts($this->manager->id, $this->timeperiod->id);

        $this->assertSame(1, count($return));
    }

    /**
     * @group forecasts
     */
    public function testGetReporteesWithForecastsReturnsEmptyWithInvalidTimePeriod()
    {
        $return = self::$common_obj->getReporteesWithForecasts($this->manager->id, 'invalid time period');

        $this->assertEmpty($return);
    }

    /**
     * @group forecasts
     */
    public function testGetReporteesWithForecastsReturnsEmptyWithInvalidUserId()
    {
        $return = self::$common_obj->getReporteesWithForecasts('Invalid Manager Id', $this->timeperiod->id);

        $this->assertEmpty($return);
    }

}
