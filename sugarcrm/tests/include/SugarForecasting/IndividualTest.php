<?php

require_once('include/SugarForecasting/Individual.php');
class SugarForecasting_IndividualTest extends Sugar_PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected static $args = array();

    /**
     * @var array
     */
    protected static $user;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        $timeperiod = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        self::$args['timeperiod_id'] = $timeperiod->id;

        SugarTestForecastUtilities::setTimePeriod($timeperiod);

        self::$user = SugarTestForecastUtilities::createForecastUser(array('timeperiod_id' => $timeperiod->id));
        self::$args['user_id'] = self::$user['user']->id;
    }

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setup('mod_strings', array('Forecasts'));
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
        SugarTestForecastUtilities::cleanUpCreatedForecastUsers();
        parent::tearDown();
    }

    public function testWorksheetContainsCorrectNumberOfRows()
    {
        $obj = new MockSugarForecasting_Individual(self::$args);
        $obj->loadWorksheet();
        $dataArray = $obj->getDataArray();

        $this->assertEquals(count(self::$user['opportunities']), count($dataArray));
    }
}

class MockSugarForecasting_Individual extends SugarForecasting_Individual
{
    public function loadWorksheet()
    {
        parent::loadWorksheet();
    }
}