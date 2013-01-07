<?php
//FILE SUGARCRM flav=pro ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once('modules/TimePeriods/TimePeriod.php');
require_once('include/SugarForecasting/Filter/TimePeriodFilter.php');

class ForecastsTimePeriodTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $preTestIds = array();
    private static $configDateFormat;

    //These are the default forecast configuration settings we will use to test
    private static $forecastConfigSettings = array (
        array('name' => 'timeperiod_type', 'value' => 'chronological', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_interval', 'value' => TimePeriod::ANNUAL_TYPE, 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_leaf_interval', 'value' => TimePeriod::QUARTER_TYPE, 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_start_date', 'value' => '2013-01-01', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_shown_forward', 'value' => '2', 'platform' => 'base', 'category' => 'Forecasts'),
        array('name' => 'timeperiod_shown_backward', 'value' => '2', 'platform' => 'base', 'category' => 'Forecasts')
    );

    /**
     * Setup global variables
     */
    public static function setUpBeforeClass()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
        self::$configDateFormat = $GLOBALS['sugar_config']['datef'];
    }

    /**
     * Call SugarTestHelper to teardown initialization in setUpBeforeClass
     */
    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        $GLOBALS['sugar_config']['datef'] = self::$configDateFormat;
    }

    public function setUp()
    {
        $this->preTestIds = TimePeriod::get_timeperiods_dom();

        $db = DBManagerFactory::getInstance();

        $db->query('UPDATE timeperiods set deleted = 1');

        $admin = BeanFactory::getBean('Administration');

        self::$forecastConfigSettings[3]['timeperiod_start_date']['value'] = TimeDate::getInstance()->getNow()->modify('first day of january')->asDbDate();
        foreach(self::$forecastConfigSettings as $config)
        {
            $admin->saveSetting($config['category'], $config['name'], $config['value'], $config['platform']);
        }

        //Run rebuildForecastingTimePeriods which takes care of creating the TimePeriods based on the configuration data
        $timePeriod = TimePeriod::getByType(TimePeriod::ANNUAL_TYPE);

        $currentForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $timePeriod->rebuildForecastingTimePeriods(array(), $currentForecastSettings);

        //add all of the newly created timePeriods to the test utils
        $result = $db->query('SELECT id, start_date, end_date, type FROM timeperiods WHERE deleted = 0');
        $createdTimePeriods = array();

        while($row = $db->fetchByAssoc($result))
        {
            $createdTimePeriods[] = TimePeriod::getBean($row['id']);
        }

        SugarTestTimePeriodUtilities::setCreatedTimePeriods($createdTimePeriods);
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();

        $db->query("UPDATE timeperiods set deleted = 1");

        //Clean up anything else left in timeperiods table that was not deleted
        $db->query("UPDATE timeperiods SET deleted = 0 WHERE id IN ('" . implode("', '", array_keys($this->preTestIds))  . "')");

        $db->query("DELETE FROM timeperiods WHERE deleted = 1");
    }

    /**
     * testTimePeriodDeleteTimePeriodsWithSamePreviousSettings
     *
     * This test will check
     * 1) That the count of the the timeperiods in the database will be the same before and after the deleteTimePeriods call
     * 2) That the count of the deleted timeperiods will remain the same before and after the deleteTimePeriods calls
     * @group timeperiods
     * @group forecasts
     *
     */
    public function testTimePeriodDeleteTimePeriodsWithSamePreviousSettings()
    {
        $admin = BeanFactory::newBean('Administration');
        $prior_forecasts_settings = $admin->getConfigForModule('Forecasts', 'base');

        $timePeriod = BeanFactory::newBean('TimePeriods');
        $this->assertTrue($timePeriod->isSettingIdentical($prior_forecasts_settings, $prior_forecasts_settings));
    }


    /**
     * getShownDifferenceProvider
     *
     * This is the data provider function for getShownDifferenceProvider
     */
    public function getShownDifferenceProvider()
    {
        return array(
           array(1, 2, 'timeperiod_shown_forward', 1),
           array(2, 2, 'timeperiod_shown_forward', 0),
           array(2, 1, 'timeperiod_shown_forward', -1),
           array(1, 2, 'timeperiod_shown_backward', 1),
           array(2, 2, 'timeperiod_shown_backward', 0),
           array(2, 1, 'timeperiod_shown_backward', -1)
        );
    }


    /**
     * This function tests the getShownDifference method in TimePeriod
     *
     * @group timeperiods
     * @group forecasts
     * @dataProvider getShownDifferenceProvider
     */
    public function testGetShownDifference($previous, $current, $key, $expected)
    {
        $timePeriod = BeanFactory::getBean('TimePeriods');

        $admin = BeanFactory::newBean('Administration');
        $priorForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $priorForecastSettings[$key] = $previous;

        $newConfigSettings = $priorForecastSettings;
        $newConfigSettings[$key] = $current;

        $this->assertEquals($expected, $timePeriod->getShownDifference($priorForecastSettings, $newConfigSettings, $key), sprintf("Failed asserting that %s difference was not %d", $key, $expected));
    }

    /**
     * testIsTargetDateDifferentFromPrevious
     *
     * This test will check the accuracy of the timedate->isTargetDateDifferentFromPrevious method
     *
     * @group timeperiods
     * @group forecasts
     */
    public function testIsTargetDateDifferentFromPrevious()
    {
        $timedate = TimeDate::getInstance();
        $timeperiod = BeanFactory::getBean('TimePeriods');

        //First let's check what happens when we pass the same start month and day
        $targetStartDate = $timedate->getNow();
        $targetStartDate->setDate($targetStartDate->format('Y'), 1, 1);

        $admin = BeanFactory::newBean('Administration');
        $priorForecastSettings = $admin->getConfigForModule('Forecasts', 'base');

        $this->assertFalse($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, $priorForecastSettings), sprintf("Failed asserting that %s is not different target start date", $timedate->asDbDate($targetStartDate)));

        //Check if the start_date is different
        $priorForecastSettings['timeperiod_start_date'] = '2012-02-02';
        $this->assertTrue($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, $priorForecastSettings), sprintf("Failed asserting that %s is different target start date", $timedate->asDbDate($targetStartDate)));

        //Check if the targetStartDate is one year back
        $targetStartDate->modify('-1 year');
        $priorForecastSettings['timeperiod_start_date'] = '2012-01-01';
        $this->assertFalse($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, $priorForecastSettings), sprintf("Failed asserting that %s is different target start date", $timedate->asDbDate($targetStartDate)));

        //Check if the targetStartDate is one year back
        $targetStartDate->modify('+2 year');
        $this->assertFalse($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, $priorForecastSettings), sprintf("Failed asserting that %s is different target start date", $timedate->asDbDate($targetStartDate)));

        //Check if there were no previous settings
        $this->assertTrue($timeperiod->isTargetDateDifferentFromPrevious($targetStartDate, array()), sprintf("Failed asserting that %s is different target start date", $timedate->asDbDate($targetStartDate)));
    }


    /**
     * testIsTargetIntervalDifferent
     *
     * @group timeperiods
     * @group forecasts
     */
    public function testIsTargetIntervalDifferent()
    {
        $timeperiod = BeanFactory::getBean('TimePeriods');
        $admin = BeanFactory::newBean('Administration');
        $priorForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $currentForecastSettings = $priorForecastSettings;

        //Check if they're the same
        $this->assertFalse($timeperiod->isTargetIntervalDifferent($priorForecastSettings, $currentForecastSettings));

        //Check if prior settings are empty
        $this->assertTrue($timeperiod->isTargetIntervalDifferent(array(), $currentForecastSettings));

        //Check if timeperiod_interval chagnes
        $currentForecastSettings['timeperiod_interval'] = TimePeriod::QUARTER_TYPE;
        $this->assertTrue($timeperiod->isTargetIntervalDifferent($priorForecastSettings, $currentForecastSettings));

        //Check if timeperiod_leaf_interval chagnes
        $currentForecastSettings['timeperiod_interval'] = TimePeriod::QUARTER_TYPE;
        $currentForecastSettings['timeperiod_leaf_interval'] = TimePeriod::MONTH_TYPE;
        $this->assertTrue($timeperiod->isTargetIntervalDifferent($priorForecastSettings, $currentForecastSettings));
    }


    /**
     * getByTypeDataProvider
     *
     * This is the data provider function for the testGetByType function
     * @group timeperiods
     * @group forecasts
     */
    public function getByTypeDataProvider()
    {
        return array(
            array(TimePeriod::ANNUAL_TYPE),
            array(TimePeriod::QUARTER_TYPE),
            array(TimePeriod::MONTH_TYPE)
        );
    }

    /**
     * testGetByType
     *
     * @group timeperiod
     * @group forecasts
     *
     * This is a test to check that the TimePeriod::getByType function returns the appropriate TimePeriod bean instance
     * @dataProvider getByTypeDataProvider
     */
    public function testGetByType($type)
    {
        $bean = TimePeriod::getByType($type);
        $this->assertEquals($type, $bean->type);
    }


    /**
     * getTimePeriodNameProvider
     *
     * This is the data provider function for the testTimePeriodName function
     */
    public function getTimePeriodNameProvider()
    {
        return array(
            array('m/d/Y', TimePeriod::ANNUAL_TYPE, '2012-07-01', 1, 'Year 2012'),
            array('m/d/Y', TimePeriod::ANNUAL_TYPE, '2012-12-31', 2, 'Year 2012'),
            array('m/d/Y', TimePeriod::ANNUAL_TYPE, '2013-01-01', 1, 'Year 2013'),
            array('m/d/Y', TimePeriod::QUARTER_TYPE, '2012-07-01', 1, 'Q1 (07/01/2012 - 09/30/2012)'),
            array('m/d/Y', TimePeriod::QUARTER_TYPE, '2012-10-01', 2, 'Q2 (10/01/2012 - 12/31/2012)'),
            array('m/d/Y', TimePeriod::QUARTER_TYPE, '2013-01-01', 3, 'Q3 (01/01/2013 - 03/31/2013)'),
            array('m/d/Y', TimePeriod::QUARTER_TYPE, '2013-04-01', 4, 'Q4 (04/01/2013 - 06/30/2013)'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-07-01', 1, '07/01/2012 - 07/31/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-08-01', 2, '08/01/2012 - 08/31/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-09-01', 3, '09/01/2012 - 09/30/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-10-01', 4, '10/01/2012 - 10/31/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-11-01', 5, '11/01/2012 - 11/30/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-12-01', 6, '12/01/2012 - 12/31/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-01-01', 7, '01/01/2012 - 01/31/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-02-01', 8, '02/01/2012 - 02/29/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-03-01', 9, '03/01/2012 - 03/31/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-04-01', 10, '04/01/2012 - 04/30/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-05-01', 11, '05/01/2012 - 05/31/2012'),
            array('m/d/Y', TimePeriod::MONTH_TYPE, '2012-06-01', 12, '06/01/2012 - 06/30/2012'),

            //Test with a different date format
            array('m.d.Y', TimePeriod::ANNUAL_TYPE, '2012-07-01', 1, 'Year 2012'),
            array('m.d.Y', TimePeriod::ANNUAL_TYPE, '2012-12-31', 2, 'Year 2012'),
            array('m.d.Y', TimePeriod::ANNUAL_TYPE, '2013-01-01', 1, 'Year 2013'),
            array('m.d.Y', TimePeriod::QUARTER_TYPE, '2012-07-01', 1, 'Q1 (07.01.2012 - 09.30.2012)'),
            array('m.d.Y', TimePeriod::QUARTER_TYPE, '2012-10-01', 2, 'Q2 (10.01.2012 - 12.31.2012)'),
            array('m.d.Y', TimePeriod::QUARTER_TYPE, '2013-01-01', 3, 'Q3 (01.01.2013 - 03.31.2013)'),
            array('m.d.Y', TimePeriod::QUARTER_TYPE, '2013-04-01', 4, 'Q4 (04.01.2013 - 06.30.2013)'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-07-01', 1, '07.01.2012 - 07.31.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-08-01', 2, '08.01.2012 - 08.31.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-09-01', 3, '09.01.2012 - 09.30.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-10-01', 4, '10.01.2012 - 10.31.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-11-01', 5, '11.01.2012 - 11.30.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-12-01', 6, '12.01.2012 - 12.31.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-01-01', 7, '01.01.2012 - 01.31.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-02-01', 8, '02.01.2012 - 02.29.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-03-01', 9, '03.01.2012 - 03.31.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-04-01', 10, '04.01.2012 - 04.30.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-05-01', 11, '05.01.2012 - 05.31.2012'),
            array('m.d.Y', TimePeriod::MONTH_TYPE, '2012-06-01', 12, '06.01.2012 - 06.30.2012')
        );
    }

    /**
     * testGetTimePeriodName
     *
     * This is a test to check that the getTimePeriodName function returns the appropriate names based on the TimePeriod bean instance
     *
     * @group forecasts
     * @group timeperiods
     * @dataProvider getTimePeriodNameProvider
     */
    public function testGetTimePeriodName($datef, $type, $startDate, $count, $expectedName)
    {
        $GLOBALS['sugar_config']['datef'] = $datef;
        $timePeriod = TimePeriod::getByType($type);
        $timePeriod->setStartDate($startDate);
        $this->assertEquals($expectedName, $timePeriod->getTimePeriodName($count));
    }


    /**
     * testGetLatest
     * This is a test for TimePeriod::getLatest function
     *
     * @group forecasts
     * @group timeperiods
     */
    public function testGetLatest()
    {
        $db = DBManagerFactory::getInstance();
        //Mark all created test timeperiods as deleted so that they do not interfere with the test
        $db->query('UPDATE timeperiods SET deleted = 1');

        //Create 3 timeperiods.  The latest should be the last one
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2000-01-01', '2000-03-31');
        $tp1->type = TimePeriod::ANNUAL_TYPE;
        $tp1->save();

        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2001-01-01', '2001-03-31');
        $tp2->type = TimePeriod::ANNUAL_TYPE;
        $tp2->save();

        $tp3 = SugarTestTimePeriodUtilities::createTimePeriod('2002-01-01', '2002-03-31');
        $tp3->type = TimePeriod::ANNUAL_TYPE;
        $tp3->save();
        $timePeriod = TimePeriod::getLatest(TimePeriod::ANNUAL_TYPE);

        $this->assertEquals($tp3->id, $timePeriod->id);
    }


    /**
     * testGetEarliest
     * This is a test for the TimePeriod::getEarliest function
     *
     * @group forecasts
     * @group timeperiods
     */
    public function testGetEarliest()
    {
        $db = DBManagerFactory::getInstance();
        //Mark all created test timeperiods as deleted so that they do not interfere with the test
        $db->query('UPDATE timeperiods SET deleted = 1');

        //Create three timeperiods.  The earliest should be $tp1
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('1980-01-01', '1980-03-31');
        $tp1->type = TimePeriod::ANNUAL_TYPE;
        $tp1->save();

        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('1981-01-01', '1981-03-31');
        $tp2->type = TimePeriod::ANNUAL_TYPE;
        $tp2->save();

        $tp3 = SugarTestTimePeriodUtilities::createTimePeriod('1982-01-01', '1982-03-31');
        $tp3->type = TimePeriod::ANNUAL_TYPE;
        $tp3->save();

        $timePeriod = TimePeriod::getEarliest(TimePeriod::ANNUAL_TYPE);
        $this->assertEquals($tp1->id, $timePeriod->id);
    }


    /**
     * buildTimePeriodsProvider
     *
     * This is the data provider for the the testBuildTimePeriodsProvider function
     *
     * The arguments are as follows
     * 1) The is_upgrade setting to use in simulating the call to rebuildForecastingTimePeriods
     * 2) The prior timeperiod_shown_backward argument
     * 3) The current timeperiod_shown_backward argument
     * 4) The parent TimePeriod type to create
     * 5) The leaf TimePeriod type to create
     * 6) The timeperiod_start_month argument
     * 7) The timeperiod_start_day argument
     * 8) The expected number of parent TimePeriod instances to create
     * 9) The expected number of leaf TimePeriod instances to create
     * 10) Direction
     * 11) The expected month of the parent TimePeriod based on direction
     * 12) The expected day of the parent TimePeriod based on direction
     * 13) The expected month of the leaf TimePeriod based on direction
     * 14) The expected day of the leaf TimePeriod based on direction
     */
    public function buildTimePeriodsProvider()
    {
        return array
        (
            //Going from 2 to 4 creates 2 additional annual timeperiods backwards (2 annual, 8 quarters)

            array(0, 2, 4, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, 1, 1, '-2 year', 2, 8, 'backward'),

            array(0, 2, 4, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, 7, 1, '-2 year', 2, 8, 'backward'),

            //Going from 4 to 6 creates 2 annual timeperiods backwards (2 annual, 8 quarters)
            array(0, 4, 6, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, 1, 1, '-2 year', 2, 8, 'backward'),

            //Going from 6 to 2 should not create anything
            array(0, 6, 2, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, 1, 1, '0 year', 0, 0, 'backward'),

            //Going from 2 to 4 creates 2 annual timeperiods forward (2 annual, 8 quarters)
            array(0, 2, 4, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, 1, 1, '2 year', 2, 8, 'forward', 1, 1, 10, 1),

            //Going from 2 to 4 creates 2 annual timeperiods forward (2 annual, 8 quarters)
            array(0, 2, 4, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, 7, 1, '2 year', 2, 8, 'forward', 1, 1, 10, 1),

            //Going from 4 to 6 creates 2 annual timeperiods forward (2 annual, 8 quarters)
            array(0, 4, 6, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, 1, 1, '2 year', 2, 8, 'forward', 1, 1, 10, 1),

            //Going from 6 to 2 should not create anything
            array(0, 6, 2, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, 1, 1, '0 year', 0, 0, 'forward', 1, 1, 10, 1),

            //Create 4 quarters going backward.  Earliest quarter and month should be -1 year from timeperiod
            array(0, 0, 4, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, 1, 1, '-1 year', 4, 12, 'backward'),

            //Create 8 quarters going backward.  Earliest quarter and month should be -2 years from timeperiod
            array(0, 4, 12, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, 1, 1, '-2 year', 8, 24, 'backward'),

            //Going from 12 to 6 should not create anything
            array(0, 12, 6, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, 1, 1, '0 year', 0, 0, 'backward'),

            array(0, 0, 4, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, 1, 1, '1 year', 4, 12, 'forward', 10, 1, 12, 1),
            array(0, 4, 12, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, 1, 1, '2 year', 8, 24, 'forward', 10, 1, 12, 1),
            array(0, 12, 6, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, 1, 1, '0 year', 0, 0, 'forward', 10, 1, 12, 1),

            //Forward TimePeriods will be created
            array(1, 2, 2, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, 1, 1, '2 year', 2, 8, 'forward'),
            array(1, 2, 4, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, 1, 1, '1 year', 4, 12, 'forward'),

        );
    }

    /**
     * This is a test for checking the creation of time periods based on various scenarios
     *
     * @group forecasts
     * @group timeperiods
     * @dataProvider buildTimePeriodsProvider
     */
    public function testBuildTimePeriods (
            $isUpgrade,
            $previous,
            $current,
            $parentType,
            $leafType,
            $startMonth,
            $startDay,
            $dateModifier,
            $expectedParents,
            $expectedLeaves,
            $direction,
            $expectedMonth = 1,
            $expectedDay = 1,
            $expectedLeafMonth = 1,
            $expectedLeafDay = 1
    ) {
        $timedate = TimeDate::getInstance();

        $admin = BeanFactory::newBean('Administration');

        $priorForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $priorForecastSettings["timeperiod_shown_{$direction}"] = $previous;
        $priorForecastSettings['timeperiod_interval'] = $parentType;
        $priorForecastSettings['timeperiod_leaf_interval'] = $leafType;
        $priorForecastSettings['is_upgrade'] = $isUpgrade;

        $currentForecastSettings = $priorForecastSettings;
        $currentForecastSettings["timeperiod_shown_{$direction}"] = $current;
        $currentForecastSettings['timeperiod_interval'] = $parentType;
        $currentForecastSettings['timeperiod_leaf_interval'] = $leafType;
        $currentForecastSettings['is_upgrade'] = $isUpgrade;

        $db = DBManagerFactory::getInstance();

        //If it's not annual type we need to re-seed with Quarter/Monthly intervals
        if($parentType != TimePeriod::ANNUAL_TYPE)
        {
           $admin->saveSetting('Forecasts', 'timeperiod_interval', $parentType, 'base');
           $admin->saveSetting('Forecasts', 'timeperiod_leaf_interval', $leafType, 'base');
           $db->query("UPDATE timeperiods SET deleted = 0");
           $priorForecastSettings["timeperiod_shown_backward"] = 8;
           $priorForecastSettings["timeperiod_shown_forward"] = 8;
           $timePeriod = TimePeriod::getByType($parentType);
           $timePeriod->rebuildForecastingTimePeriods(array(), $priorForecastSettings);
           $priorForecastSettings["timeperiod_shown_{$direction}"] = $previous;
        }

        $expectedSeed = ($direction == 'backward') ? TimePeriod::getEarliest($parentType) :  TimePeriod::getLatest($parentType);
        $expectedSeedLeaf = ($direction == 'backward') ? TimePeriod::getEarliest($leafType) :  TimePeriod::getLatest($leafType);

        $timePeriod = TimePeriod::getByType($parentType);
        $timePeriod->rebuildForecastingTimePeriods($priorForecastSettings, $currentForecastSettings);

        $expectedDate = $timedate->getNow()->setDate($timedate->fromDbDate($expectedSeed->start_date)->modify($dateModifier)->format('Y'), $expectedMonth, $expectedDay);

        if($isUpgrade && $direction == 'forward') {
            $start_date = $db->getOne("SELECT max(start_date) FROM timeperiods WHERE type = '{$parentType}' AND deleted = 0");
            $expectedDate = $timedate->fromDbDate(substr($start_date, 0, 10));
        }

        $tp = $direction == 'backward' ? TimePeriod::getEarliest($parentType) : TimePeriod::getLatest($parentType);

        $this->assertEquals($expectedDate->asDbDate(), $tp->start_date, "Failed creating {$expectedParents} new {$direction} timeperiods");

        //If this is an upgrade the expectedDate should be forward from what the current time period is
        if($isUpgrade && $direction == 'forward') {
            $tp = TimePeriod::getLatest($leafType);
            $start_date = $db->getOne("SELECT max(start_date) FROM timeperiods WHERE type = '{$leafType}' AND deleted = 0");
            $expectedDate = $timedate->fromDbDate(substr($start_date, 0, 10));
            $this->assertEquals($expectedDate->asDbDate(), $tp->start_date, "Failed creating {$expectedLeaves} leaf timeperiods");
        }

    }

    /**
     * This is the data provider to simulate arguments we pass to the testCreateTimePeriodsForUpgrade test
     *
     */
    public function testCreateTimePeriodsForUpgradeProvider() {

        return array(
            //This data set simulates case where the start date specified is the same as current date

            array(1, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, TimeDate::getInstance()->getNow()->modify('first day of january'), 2, TimeDate::getInstance()->getNow()->modify('first day of january'), 15, TimeDate::getInstance()->getNow()->modify('first day of october'), TimeDate::getInstance()->getNow()->modify('last day of december')),
            array(1, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, TimeDate::getInstance()->getNow()->modify('first day of january'), 4, TimeDate::getInstance()->getNow()->modify('first day of january'), 25, TimeDate::getInstance()->getNow()->modify('first day of october'), TimeDate::getInstance()->getNow()->modify('last day of december')),
            array(1, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->getNow()->modify('first day of january'), 2, TimeDate::getInstance()->getNow()->modify('first day of january'), 10, TimeDate::getInstance()->getNow()->modify('first day of january'), TimeDate::getInstance()->getNow()->modify('last day of march')),
            array(1, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->getNow()->modify('first day of january'), 4, TimeDate::getInstance()->getNow()->modify('first day of january'), 18, TimeDate::getInstance()->getNow()->modify('first day of january'), TimeDate::getInstance()->getNow()->modify('last day of march')),
            array(9, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->getNow()->modify('first day of january'), 2, TimeDate::getInstance()->getNow()->modify('first day of january'), 10, TimeDate::getInstance()->getNow()->modify('first day of september'), TimeDate::getInstance()->getNow()->modify('last day of september')),
            array(17, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->getNow()->modify('first day of january'), 4, TimeDate::getInstance()->getNow()->modify('first day of january'), 18, TimeDate::getInstance()->getNow()->modify('+1 year')->modify('first day of march'), TimeDate::getInstance()->getNow()->modify('+1 year')->modify('last day of march')),

            //This data set simulates case where the start date specified is before the current date
            array(1, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, TimeDate::getInstance()->getNow()->modify('first day of february'), 2, TimeDate::getInstance()->getNow()->modify('first day of march'), 15, TimeDate::getInstance()->getNow()->modify('first day of november'), TimeDate::getInstance()->getNow()->modify('+1 year')->modify('last day of january')),

            array(1, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, TimeDate::getInstance()->getNow()->modify('first day of february'), 4, TimeDate::getInstance()->getNow()->modify('first day of march'), 25, TimeDate::getInstance()->getNow()->modify('first day of november'), TimeDate::getInstance()->getNow()->modify('+1 year')->modify('last day of january')),
            array(1, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->getNow()->modify('first day of february'), 2, TimeDate::getInstance()->getNow()->modify('first day of march'), 11, TimeDate::getInstance()->getNow()->modify('first day of april'), TimeDate::getInstance()->getNow()->modify('last day of april')),
            array(1, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->getNow()->modify('first day of february'), 4, TimeDate::getInstance()->getNow()->modify('first day of march'), 19, TimeDate::getInstance()->getNow()->modify('first day of april'), TimeDate::getInstance()->getNow()->modify('last day of april')),
            array(14, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, TimeDate::getInstance()->getNow()->modify('first day of february'), 2, TimeDate::getInstance()->getNow()->modify('first day of march'), 15, TimeDate::getInstance()->getNow()->modify('+2 year')->modify('first day of november'), TimeDate::getInstance()->getNow()->modify('+3 year')->modify('last day of january')),
            array(24, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, TimeDate::getInstance()->getNow()->modify('first day of february'), 4, TimeDate::getInstance()->getNow()->modify('first day of march'), 25, TimeDate::getInstance()->getNow()->modify('+4 year')->modify('first day of november'), TimeDate::getInstance()->getNow()->modify('+5 year')->modify('last day of january')),

            //This data set simulates case where the start date specified is after the current date
            array(1, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, TimeDate::getInstance()->getNow()->modify('first day of march'), 2, TimeDate::getInstance()->getNow()->modify('first day of february'), 15, TimeDate::getInstance()->getNow()->modify('first day of december'), TimeDate::getInstance()->getNow()->modify('+1 year')->modify('last day of february')),
            array(1, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, TimeDate::getInstance()->getNow()->modify('first day of march'), 4, TimeDate::getInstance()->getNow()->modify('first day of february'), 25, TimeDate::getInstance()->getNow()->modify('first day of december'), TimeDate::getInstance()->getNow()->modify('+1 year')->modify('last day of february')),
            array(1, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->getNow()->modify('first day of march'), 2, TimeDate::getInstance()->getNow()->modify('first day of february'), 12, TimeDate::getInstance()->getNow()->modify('first day of may'), TimeDate::getInstance()->getNow()->modify('last day of may')),
            array(1, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->getNow()->modify('first day of march'), 4, TimeDate::getInstance()->getNow()->modify('first day of february'), 20, TimeDate::getInstance()->getNow()->modify('first day of may'), TimeDate::getInstance()->getNow()->modify('last day of may')),
            array(11, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->getNow()->modify('first day of march'), 2, TimeDate::getInstance()->getNow()->modify('first day of february'), 12, TimeDate::getInstance()->getNow()->modify('first day of november'), TimeDate::getInstance()->getNow()->modify('last day of november')),
            array(19, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->getNow()->modify('first day of march'), 4, TimeDate::getInstance()->getNow()->modify('first day of february'), 20, TimeDate::getInstance()->getNow()->modify('+1 year')->modify('first day of may'), TimeDate::getInstance()->getNow()->modify('+1 year')->modify('last day of may')),

            //This data set simulates case where the start date specified is before current date and there are no existing current TimePeriods for the current date
            array(1, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, TimeDate::getInstance()->getNow()->modify('+2 year')->modify('first day of january'), 2, TimeDate::getInstance()->getNow()->modify('+2 year')->modify('first day of january')->modify('+1 day'), 15, TimeDate::getInstance()->getNow()->modify('+2 year')->modify('first day of october'), TimeDate::getInstance()->getNow()->modify('+2 year')->modify('last day of december')),

            //This data set simulates upgrades using variable TimePeriods so that we are not bound to the TimePeriods created in the setUp method
            array(1, TimePeriod::ANNUAL_TYPE, TimePeriod::QUARTER_TYPE, TimeDate::getInstance()->fromDbDate('2013-01-01'), 2, TimeDate::getInstance()->fromDbDate('2013-01-02'), 15, TimeDate::getInstance()->fromDbDate('2013-10-01'), TimeDate::getInstance()->fromDbDate('2013-12-31'),
                array("INSERT into timeperiods (id, name, start_date, end_date, parent_id, deleted) values ('abc1', 'Q4 2013', '2013-10-01', '2013-12-31', 'abc5', 0)",
                      "INSERT into timeperiods (id, name, start_date, end_date, parent_id, deleted) values ('abc2', 'Q3 2013', '2013-07-01', '2013-09-31', 'abc5', 0)",
                      "INSERT into timeperiods (id, name, start_date, end_date, parent_id, deleted) values ('abc3', 'Q2 2013', '2013-04-01', '2013-06-31', 'abc5', 0)",
                      "INSERT into timeperiods (id, name, start_date, end_date, deleted) values ('abc5', 'Year 2013', '2013-10-01', '2013-12-31', 0)",
                      "INSERT into timeperiods (id, name, start_date, end_date, parent_id, deleted) values ('abc4', 'Q1 2013', '2013-01-01', '2013-03-31', 'abc5', 0)"
                )
            ),

            array(1, TimePeriod::QUARTER_TYPE, TimePeriod::MONTH_TYPE, TimeDate::getInstance()->fromDbDate('2013-01-01'), 2, TimeDate::getInstance()->fromDbDate('2013-01-02'), 10, TimeDate::getInstance()->fromDbDate('2013-01-01'), TimeDate::getInstance()->fromDbDate('2013-03-31'),
                array("INSERT into timeperiods (id, name, start_date, end_date, parent_id, deleted) values ('abc1', 'Q4 2013', '2013-10-01', '2013-12-31', 'abc5', 0)",
                      "INSERT into timeperiods (id, name, start_date, end_date, parent_id, deleted) values ('abc2', 'Q3 2013', '2013-07-01', '2013-09-31', 'abc5', 0)",
                      "INSERT into timeperiods (id, name, start_date, end_date, parent_id, deleted) values ('abc3', 'Q2 2013', '2013-04-01', '2013-06-31', 'abc5', 0)",
                      "INSERT into timeperiods (id, name, start_date, end_date, deleted) values ('abc5', 'Year 2013', '2013-10-01', '2013-12-31', 0)",
                      "INSERT into timeperiods (id, name, start_date, end_date, parent_id, deleted) values ('abc4', 'Q1 2013', '2013-01-01', '2013-03-31', 'abc5', 0)"
                )
            ),

        );

    }

    /**
     * This is a test for the createTimePeriodsForUpgrade method
     *
     * @group forecasts
     * @group timeperiods
     * @dataProvider testCreateTimePeriodsForUpgradeProvider
     *
     * @param $createdTimePeriodToCheck int value of the created TimePeriod index to check
     * @param $interval The TimePeriod interval type
     * @param $leafInterval The TimePeriod leaf interval type
     * @param $startDate TimeDate instance of chosen start date for the TimePeriod interval
     * @param $shownForward The number of forward TimePeriod intervals to create
     * @param $currentDate TimeDate instance of the current date
     * @param $expectedTimePeriods int value of the expected TimePeriods created
     * @param $startDateFirstCreated TimeDate instance of the start date of created TimePeriod interval to test
     * @param $endDateFirstCreated TimeDate instance of the end date of created TimePeriod interval to test
     * @param $overrideEntries
     *
     * @outputBuffering disabled
     */
    public function testCreateTimePeriodsForUpgrade(
        $createdTimePeriodToCheck,
        $interval,
        $leafInterval,
        $startDate,
        $shownForward,
        $currentDate,
        $expectedTimePeriods,
        $startDateFirstCreated,
        $endDateFirstCreated,
        $overrideEntries = array())
    {

        if(!empty($overrideEntries)) {
            $db = DBManagerFactory::getInstance();
            //Get rid of all non-deleted timeperiods
            $db->query("DELETE FROM timeperiods WHERE deleted = 0");
            foreach($overrideEntries as $entry) {
                $db->query($entry);
            }
        }

        $currentSettings = array();
        $currentSettings['timeperiod_interval'] = $interval;
        $currentSettings['timeperiod_leaf_interval'] = $leafInterval;
        $currentSettings['timeperiod_start_date'] = $startDate->asDbDate();
        $currentSettings['timeperiod_shown_forward'] = $shownForward;

        //Save the altered admin settings
        $admin = BeanFactory::getBean('Administration');
        foreach($currentSettings as $key=>$value) {
            $admin->saveSetting('Forecasts', $key, $value, 'base');
        }

        $timePeriod = TimePeriod::getByType($interval);
        $created = $timePeriod->createTimePeriodsForUpgrade($currentSettings, $currentDate);

        $this->assertEquals($expectedTimePeriods, count($created));
        $firstTimePeriod = $created[$createdTimePeriodToCheck];
        $this->assertEquals($startDateFirstCreated->asDbDate(), $firstTimePeriod->start_date, 'Failed asserting that the start date of first backward timeperiod is ' . $startDateFirstCreated);
        $this->assertEquals($endDateFirstCreated->asDbDate(), $firstTimePeriod->end_date, 'Failed asserting that the end date of first backward timeperiod is ' . $firstTimePeriod->end_date);

        $klass = new SugarForecasting_Filter_TimePeriodFilter(array());
        $timePeriods = $klass->process();
        $this->assertNotEmpty($timePeriods);
    }

    /**
     * This is a test for TimePeriod::getCurrentTimePeriod
     *
     * @group forecasts
     * @group timeperiods
     */
    public function testGetCurrentTimePeriod() {
        global $app_strings;
        global $sugar_config;
        $timedate = TimeDate::getInstance();
        $queryDate = $timedate->getNow()->format('Y');
        $currentAnnualTimePeriod = TimePeriod::getCurrentTimePeriod(TimePeriod::ANNUAL_TYPE);
        $expectedAnnualTimePeriodName = string_format($app_strings['LBL_ANNUAL_TIMEPERIOD_FORMAT'], array($queryDate));
        $this->assertEquals($expectedAnnualTimePeriodName, $currentAnnualTimePeriod->name);

        $month = $timedate->getNow()->format('m');
        $year = $timedate->getNow()->format('Y');
        $currentId = 1;
        $startMonth = '01-01';

        switch($month) {
            case 4:
            case 5:
            case 6:
                $currentId = 2;
                $startMonth = '04-01';
                break;
            case 7:
            case 8:
            case 9:
                $currentId = 3;
                $startMonth = '07-01';
                break;
            case 10:
            case 11:
            case 12:
                $currentId = 4;
                $startMonth = '10-01';
                break;
        }

        $startMonth = $year . '-' . $startMonth;
        $currentQuarterTimePeriod = TimePeriod::getCurrentTimePeriod(TimePeriod::QUARTER_TYPE);
        $start = $timedate->fromDbDate($startMonth)->format($sugar_config['datef']);
        $end = $timedate->fromDbDate($startMonth)->modify($currentQuarterTimePeriod->next_date_modifier)->modify('-1 day')->format($sugar_config['datef']);
        $expectedQuarterTimePeriodName = string_format($app_strings['LBL_QUARTER_TIMEPERIOD_FORMAT'], array($currentId, $start, $end));
        $this->assertEquals($expectedQuarterTimePeriodName, $currentQuarterTimePeriod->name);

        //Test without passing any arguments
        $admin = BeanFactory::getBean('Administration');
        $config = $admin->getConfigForModule('Forecasts', 'base');
        $type = $config['timeperiod_leaf_interval'];
        $currentTimePeriod = TimePeriod::getCurrentTimePeriod($type);
        $this->assertNotNull($currentTimePeriod);
    }



    /*
     * This is a test to see how the TimePeriod code handles specifying corner cases like a leap year as the starting forecasting date
     *
     * @group timeperiods
     * @group forecasts
    public function testCreateLeapYearTimePeriods() {
        $db = DBManagerFactory::getInstance();
        $db->query("UPDATE timeperiods SET deleted = 1");

        $settings['timeperiod_start_date'] = "2012-02-29";
        $settings['timeperiod_interval'] = TimePeriod::ANNUAL_TYPE;
        $settings['timeperiod_leaf_interval'] = TimePeriod::QUARTER_TYPE;
        $settings['timeperiod_shown_backward'] = 4;
        $settings['timeperiod_shown_forward'] = 4;

        $timePeriod = TimePeriod::getByType(TimePeriod::ANNUAL_TYPE);
        $timePeriod->setStartDate('2012-02-29');
        $timePeriod->rebuildForecastingTimePeriods(array(), $settings);

        $timePeriods = TimePeriod::get_not_fiscal_timeperiods_dom();

        //We are basically asserting that for 8 years of timeperiods created, we should have two leaf timeperiods
        $leapYearFoundCount = 0;
        foreach($timePeriods as $id=>$name) {
            $timePeriod = TimePeriod::getByType(TimePeriod::QUARTER_TYPE, $id);
            if(preg_match('/\d{4}\-02-29/', $timePeriod->start_date)) {
                $leapYearFoundCount++;
            }
        }
        $this->assertTrue($leapYearFoundCount >= 2, "Failed to find at least 2 leap year leaf timeperiods for 8 years of timeperiods");
    }
    */


    /**
     * This is a test for the getChartLabels function
     *
     * @group timeperiods
     * @group forecasts
     */
    public function testGetChartLabels() {
        $timePeriod = new MonthTimePeriod();
        $timePeriod->setStartDate('2012-01-01');
        $timePeriod->save();

        SugarTestTimePeriodUtilities::$_createdTimePeriods[] = $timePeriod;
    }


    /**
     * This is a test for checking the edge time periods and crossed timeperiods
     *
     * @group forecasts
     * @group timeperiods
     */
    public function testCurrentTimePeriodAcrossTimeZones () {
        //store the current global user
        $user = $GLOBALS['current_user'];
        $GLOBALS['disable_date_format'] = 0;
        //create my anonymous users
        $userA = SugarTestUserUtilities::createAnonymousUser(true);
        $userB = SugarTestUserUtilities::createAnonymousUser(true);
        //get timeDate instance and disable timedate chaching
        $timedate = TimeDate::getInstance();
        $timedate->allow_cache = false;

        //get timezones to find
        $timeZones = DateTimeZone::listIdentifiers();
        //need to find two timezones that cross dates of each other
        $timeZoneA = new DateTimeZone($timeZones[0]);
        $timeZoneANow = new SugarDateTime("now", $timeZoneA);
        $timeZoneADay = $timeZoneANow->format("j");
        foreach($timeZones as $tz) {
            $timeZoneB = new DateTimeZone($tz);
            $timeZoneBNow = new SugarDateTime("now", $timeZoneB);
            $timeZoneBDay = $timeZoneBNow->format("j");
            if($timeZoneBDay != $timeZoneADay)
            {
                //check if they are in reverse order, we want A to come before B
                if($timeZoneBDay < $timeZoneADay)
                {
                    $timeZoneB = new DateTimeZone($timeZones[0]);
                    $timeZoneA = new DateTimeZone($tz);
                    $timeZoneANow = new SugarDateTime("now", $timeZoneA);
                    $timeZoneBNow = new SugarDateTime("now", $timeZoneB);
                }
                break;
            }
        }

        //set users to be in different timezones
        $userA->setPreference('timezone', $timeZoneA->getName());
        $userA->savePreferencesToDB();
        $userB->setPreference('timezone', $timeZoneB->getName());
        $userB->savePreferencesToDB();

        //destroy existing time periods created by setup
        $db = DBManagerFactory::getInstance();

        $db->query("UPDATE timeperiods set deleted = 1");

        $admin = BeanFactory::newBean('Administration');

        //change settings as needed to reset dates
        $currentForecastSettings = $admin->getConfigForModule('Forecasts', 'base');
        $currentForecastSettings['is_upgrade'] = 0;

        //set start date to be today by the later time zone standards, which may be today or tomorrow
        $currentForecastSettings['timeperiod_start_date'] = $timeZoneBNow->asDbDate(false);

        //rebuild time periods
        $timePeriod = TimePeriod::getByType(TimePeriod::ANNUAL_TYPE);
        $timePeriod->rebuildForecastingTimePeriods(array(), $currentForecastSettings);

        //add all of the newly created timePeriods to the test utils
        $result = $db->query('SELECT id, start_date, end_date, type FROM timeperiods WHERE deleted = 0');
        $createdTimePeriods = array();

        while($row = $db->fetchByAssoc($result))
        {
            $createdTimePeriods[] = TimePeriod::getBean($row['id']);
        }

        SugarTestTimePeriodUtilities::setCreatedTimePeriods($createdTimePeriods);

        //reset current user to use the later time zone
        $GLOBALS['current_user'] = $userB;
        //update timedate to pertain to userb
        $timedate->setUser($userB);
        $timedate->setNow($timeZoneBNow);

        $timeZoneBCurrentTimePeriod = TimePeriod::getCurrentTimePeriod();

        //now get timeperiods for UserA
        $GLOBALS['current_user'] = $userA;
        $timedate->setUser($userA);
        $timedate->setNow($timeZoneANow);

        //get timeperiod per userA's timezone
        $timeZoneACurrentTimePeriod = TimePeriod::getCurrentTimePeriod();

        //make assertions, Users should have timeperiods based on timezones
        $this->assertNotEquals($timeZoneACurrentTimePeriod->id, $timeZoneBCurrentTimePeriod->id, "time periods were equal, users were in same time zone");

        //check that today for user a matches the timeperiod end date
        $this->assertEquals($timeZoneANow->asDbDate(false), $timeZoneACurrentTimePeriod->end_date, "User in Time Zone A current date should have matched the timeperiod end date, but it didn't");

        //check that today for user b matches the timeperiod start date
        $this->assertEquals($timeZoneBNow->asDbDate(false), $timeZoneBCurrentTimePeriod->start_date, "User in Time Zone B current date should have matched the timeperiod start date, but it didn't");

        //reset current user back to the original user
        $GLOBALS['current_user'] = $user;
    }
}