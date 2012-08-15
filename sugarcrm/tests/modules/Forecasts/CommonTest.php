<?php
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
     *
     * @var string
     */
    protected $timeperiod_id;

    /**
     * User Id of the Manager
     *
     * @var string
     */
    protected $manager_id;

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
        $user = SugarTestUserUtilities::createAnonymousUser();

        $this->manager_id = $user->id;

        $rep = SugarTestUserUtilities::createAnonymousUser();
        $rep->reports_to_id = $user->id;
        $rep->save();

        $rep2 = SugarTestUserUtilities::createAnonymousUser();
        $rep2->reports_to_id = $user->id;
        $rep2->save();

        $timeperiod = new TimePeriod();
        $timeperiod->start_date = "2012-01-01";
        $timeperiod->end_date = "2012-03-31";
        $timeperiod->name = "Test";
        $timeperiod->save();

        $this->timeperiod_id = $timeperiod->id;

        $managerForecast = new Forecast();
        $managerForecast->user_id = $user->id;
        $managerForecast->best_case = 1500;
        $managerForecast->likely_case = 1200;
        $managerForecast->worst_case = 900;
        $managerForecast->timeperiod_id = $timeperiod->id;
        $managerForecast->forecast_type = "Direct";
        $managerForecast->team_set_id = 1;
        $managerForecast->save();

        $repForecast = new Forecast();
        $repForecast->user_id = $rep->id;
        $repForecast->best_case = 1100;
        $repForecast->likely_case = 900;
        $repForecast->worst_case = 700;
        $repForecast->timeperiod_id = $timeperiod->id;
        $repForecast->forecast_type = "Direct";
        $repForecast->team_set_id = 1;
        $repForecast->save();
    }

    public function tearDown()
    {
        $userIds = SugarTestUserUtilities::getCreatedUserIds();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['db']->query('DELETE FROM timeperiods WHERE id ="' . $this->timeperiod_id . '";');
        $GLOBALS['db']->query('DELETE FROM forecasts WHERE user_id IN (\'' . implode("', '", $userIds) . '\')');
    }

    /**
     * Only one record should be returned since we only created the forecast for the first user and not the second user
     */
    public function testGetReporteesWithForecastsReturnsOneRecord()
    {
        $return = self::$common_obj->getReporteesWithForecasts($this->manager_id, $this->timeperiod_id);

        $this->assertSame(1, count($return));
    }

    public function testGetReporteesWithForecastsReturnsEmptyWithInvalidTimePeriod()
    {
        $return = self::$common_obj->getReporteesWithForecasts($this->manager_id, 'invalid time period');

        $this->assertEmpty($return);
    }

    public function testGetReporteesWithForecastsReturnsEmptyWithInvalidUserId()
    {
        $return = self::$common_obj->getReporteesWithForecasts('Invalid Manager Id', $this->timeperiod_id);

        $this->assertEmpty($return);
    }
}
