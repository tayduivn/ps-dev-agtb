<?php
//FILE SUGARCRM flav=pro ONLY
/**
 * Created by JetBrains PhpStorm.
 * User: jwhitcraft
 * Date: 8/14/12
 * Time: 10:18 PM
 * To change this template use File | Settings | File Templates.
 */

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
        self::$common_obj = new Common();
    }

    public static function tearDownAfterClass()
    {
        self::$common_obj = null;
    }

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
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

        SugarTestForecastScheduleUtilities::createForecastSchedule($this->timeperiod, $this->rep);
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::removeAllCreatedForecasts();
        SugarTestForecastScheduleUtilities::removeAllCreatedForecastSchedules();
    }

    /**
     * Only one record should be returned since we only created the forecast for the first user and not the second user
     */
    public function testGetReporteesWithForecastsReturnsOneRecord()
    {
        $return = self::$common_obj->getReporteesWithForecasts($this->manager->id, $this->timeperiod->id);

        $this->assertSame(1, count($return));
    }

    public function testGetReporteesWithForecastsReturnsEmptyWithInvalidTimePeriod()
    {
        $return = self::$common_obj->getReporteesWithForecasts($this->manager->id, 'invalid time period');

        $this->assertEmpty($return);
    }

    public function testGetReporteesWithForecastsReturnsEmptyWithInvalidUserId()
    {
        $return = self::$common_obj->getReporteesWithForecasts('Invalid Manager Id', $this->timeperiod->id);

        $this->assertEmpty($return);
    }

    /*
     * check my_timeriods has current timeperiod for current user
     */
    public function testGetMyTimeperiods()
    {
        self::$common_obj->current_user = $this->rep->id;
        self::$common_obj->get_my_timeperiods();

        $this->assertEquals(array($this->timeperiod->id => $this->timeperiod->name), self::$common_obj->my_timeperiods, 'my_timeperiods is empty');
    }
}
