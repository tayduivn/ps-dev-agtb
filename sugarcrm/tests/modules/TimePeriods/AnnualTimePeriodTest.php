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


require_once('modules/TimePeriods/TimePeriod.php');

class AnnualTimePeriodTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $calendarTP;
    protected static $calendarLeaves;
    protected static $fiscalTP;
    protected static $fiscalLeaves;

    public static function setUpBeforeClass() {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setup('app_list_strings');
        self::$calendarTP = SugarTestTimePeriodUtilities::createITimePeriod("Annual", false);
        self::$fiscalTP = SugarTestTimePeriodUtilities::createITimePeriod("Annual", true);
        self::$calendarTP->buildLeaves('Quarter');
        self::$calendarLeaves = self::$calendarTP->getLeaves();
        self::$fiscalTP->buildLeaves('Quarter544');
        self::$fiscalLeaves = self::$fiscalTP->getLeaves();

        SugarTestTimePeriodUtilities::addTimePeriod(self::$calendarLeaves[0]);
        SugarTestTimePeriodUtilities::addTimePeriod(self::$calendarLeaves[1]);
        SugarTestTimePeriodUtilities::addTimePeriod(self::$calendarLeaves[2]);
        SugarTestTimePeriodUtilities::addTimePeriod(self::$calendarLeaves[3]);

        SugarTestTimePeriodUtilities::addTimePeriod(self::$fiscalLeaves[0]);
        SugarTestTimePeriodUtilities::addTimePeriod(self::$fiscalLeaves[1]);
        SugarTestTimePeriodUtilities::addTimePeriod(self::$fiscalLeaves[2]);
        SugarTestTimePeriodUtilities::addTimePeriod(self::$fiscalLeaves[3]);
        parent::setUpBeforeClass();
    }


    public function setUp()
    {
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();

    }

    public static function tearDownAfterClass() {
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();

        parent::tearDownAfterClass();
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function testCreateNextTimePeriod()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$calendarTP;

        $nextTimePeriod = $baseTimePeriod->createNextTimePeriod();
        $nextTimePeriod->name = "SugarTestCreatedNextAnnualTimePeriod";
        $nextTimePeriod->save();
        SugarTestTimePeriodUtilities::addTimePeriod($nextTimePeriod);
        $nextTimePeriod = BeanFactory::getBean('AnnualTimePeriods', $nextTimePeriod->id);

        //next timeperiod (1 year from today)
        $nextStartDate = $timedate->fromDBDate($baseTimePeriod->start_date);
        $nextStartDate = $nextStartDate->modify("+1 year");
        $nextEndDate = $timedate->fromDBDate($baseTimePeriod->end_date);
        $nextEndDate = $nextEndDate->modify("+1 year");

        $this->assertEquals($timedate->fromDBDate($nextTimePeriod->start_date), $nextStartDate);

        $this->assertEquals($timedate->fromDBDate($nextTimePeriod->end_date), $nextEndDate);

        $dayLength = 365;
        if(($nextEndDate->year % 4) == 0) {
            $dayLength = 366;
        }

        $this->assertEquals($dayLength, $nextTimePeriod->getLengthInDays());
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function testGetNextPeriod()
    {

        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$calendarTP;

        $nextTimePeriod = $baseTimePeriod->getNextTimePeriod();

        //next timeperiod (1 year from today)
        $nextStartDate = $timedate->fromDBDate($baseTimePeriod->start_date);
        $nextStartDate = $nextStartDate->modify("+1 year");
        $nextEndDate = $timedate->fromDBDate($baseTimePeriod->end_date);
        $nextEndDate = $nextEndDate->modify("+1 year");

        //todo make expected on the left
        $this->assertEquals($nextTimePeriod->start_date, $timedate->asDbDate($nextStartDate));
        $this->assertEquals($nextTimePeriod->end_date, $timedate->asDbDate($nextEndDate));

        $dayLength = 365;
        if(($nextEndDate->year % 4) == 0) {
            $dayLength = 366;
        }

        $this->assertEquals($dayLength, $nextTimePeriod->getLengthInDays());
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function testQuarterLeavesCreated()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$calendarTP;

        $leaves = $baseTimePeriod->getLeaves();
        $this->assertEquals(4, count(self::$calendarLeaves), "Incorrect Number Of Leaves Created");
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function test1stQuarterCalendarLeafBounds()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$calendarTP;
        $startDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $endDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $endDate = $endDate->modify("+3 month");
        $endDate = $endDate->modify("-1 day");
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);

        $this->assertEquals($startDate->getTimestamp(), self::$calendarLeaves[0]->start_date_timestamp, "1st quarter start timestamp is wrong");
        $this->assertEquals($endDate->getTimestamp(), self::$calendarLeaves[0]->end_date_timestamp, "1st quarter end timestamp is wrong");
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function test2ndQuarterCalendarLeafBounds()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$calendarTP;
        $startDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $endDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $startDate = $startDate->modify("+3 month");
        $endDate = $endDate->modify("+6 month");
        $endDate = $endDate->modify("-1 day");
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);

        $this->assertEquals($startDate->getTimestamp(), self::$calendarLeaves[1]->start_date_timestamp, "2nd quarter start timestamp is wrong");
        $this->assertEquals($endDate->getTimestamp(), self::$calendarLeaves[1]->end_date_timestamp, "2nd quarter end timestamp is wrong");
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function test3rdQuarterCalendarLeafBounds()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$calendarTP;
        $startDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $endDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $startDate = $startDate->modify("+6 month");
        $endDate = $endDate->modify("+9 month");
        $endDate = $endDate->modify("-1 day");
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);

        $this->assertEquals($startDate->getTimestamp(), self::$calendarLeaves[2]->start_date_timestamp, "3rd quarter start timestamp is wrong");
        $this->assertEquals($endDate->getTimestamp(), self::$calendarLeaves[2]->end_date_timestamp, "3rd quarter end timestamp is wrong");
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function test4thQuarterCalendarLeafBounds()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$calendarTP;
        $startDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $endDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $startDate = $startDate->modify("+9 month");
        $endDate = $endDate->modify("+12 month");
        $endDate = $endDate->modify("-1 day");
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);

        $this->assertEquals($startDate->getTimestamp(), self::$calendarLeaves[3]->start_date_timestamp, "4th quarter start timestamp is wrong");
        $this->assertEquals($endDate->getTimestamp(), self::$calendarLeaves[3]->end_date_timestamp, "4th quarter end timestamp is wrong");
    }

    ///////////// fiscal tests ////////////////

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function testCreateNextFiscalTimePeriod()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$fiscalTP;

        $nextTimePeriod = $baseTimePeriod->createNextTimePeriod();
        $nextTimePeriod->name = "SugarTestCreatedNextAnnualFiscalTimePeriod";
        $nextTimePeriod->save();
        SugarTestTimePeriodUtilities::addTimePeriod($nextTimePeriod);
        $nextTimePeriod = BeanFactory::getBean('AnnualTimePeriods', $nextTimePeriod->id);

        //next timeperiod (1 year from today)
        $nextStartDate = $timedate->fromDBDate($baseTimePeriod->start_date);
        $nextStartDate = $nextStartDate->modify("+52 week");
        $nextEndDate = $timedate->fromDBDate($baseTimePeriod->end_date);
        $nextEndDate = $nextEndDate->modify("+52 week");

        $this->assertEquals($timedate->fromDBDate($nextTimePeriod->start_date), $nextStartDate, "Fiscal Start Dates do not match");

        $this->assertEquals($timedate->fromDBDate($nextTimePeriod->end_date), $nextEndDate, "Fiscal End Dates do not match");

        $dayLength = 52 * 7;

        $this->assertEquals($dayLength, $nextTimePeriod->getLengthInDays());
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function test1stQuarterFiscalLeafBounds()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$fiscalTP;
        $startDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $endDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $endDate = $endDate->modify("+13 week");
        $endDate = $endDate->modify("-1 day");
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);

        $this->assertEquals($startDate->getTimestamp(), self::$fiscalLeaves[0]->start_date_timestamp, "1st quarter start timestamp is wrong");
        $this->assertEquals($endDate->getTimestamp(), self::$fiscalLeaves[0]->end_date_timestamp, "1st quarter end timestamp is wrong");
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function test2ndQuarterFiscalLeafBounds()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$fiscalTP;
        $startDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $endDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $startDate = $startDate->modify("+13 week");
        $endDate = $endDate->modify("+26 week");
        $endDate = $endDate->modify("-1 day");
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);

        $this->assertEquals($startDate->getTimestamp(), self::$fiscalLeaves[1]->start_date_timestamp, "2nd quarter start timestamp is wrong");
        $this->assertEquals($endDate->getTimestamp(), self::$fiscalLeaves[1]->end_date_timestamp, "2nd quarter end timestamp is wrong");
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function test3rdQuarterFiscalLeafBounds()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$fiscalTP;
        $startDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $endDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $startDate = $startDate->modify("+26 week");
        $endDate = $endDate->modify("+39 week");
        $endDate = $endDate->modify("-1 day");
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);

        $this->assertEquals($startDate->getTimestamp(), self::$fiscalLeaves[2]->start_date_timestamp, "3rd quarter start timestamp is wrong");
        $this->assertEquals($endDate->getTimestamp(), self::$fiscalLeaves[2]->end_date_timestamp, "3rd quarter end timestamp is wrong");
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function test4thQuarterFiscalLeafBounds()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$fiscalTP;
        $startDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $endDate = $timedate->fromDbDate($baseTimePeriod->start_date);
        $startDate = $startDate->modify("+39 week");
        $endDate = $endDate->modify("+52 week");
        $endDate = $endDate->modify("-1 day");
        $startDate->setTime(0,0,0);
        $endDate->setTime(23,59,59);

        $this->assertEquals($startDate->getTimestamp(), self::$fiscalLeaves[3]->start_date_timestamp, "4th quarter start timestamp is wrong");
        $this->assertEquals($endDate->getTimestamp(), self::$fiscalLeaves[3]->end_date_timestamp, "4th quarter end timestamp is wrong");
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function testBuildLeavesAlreadyExistException()
    {
        global $app_strings;
        $exceptionThrown = false;
        //get current timeperiod
        $baseTimePeriod = self::$calendarTP;
        try {
            $baseTimePeriod->buildLeaves();
        } catch (Exception $e) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

    /**
     * @group forecasts
     * @group timeperiods
     */
    public function testBuildLeavesOnLeafException()
    {
        global $app_strings;
        $exceptionThrown = false;
        //get a leaf
        $leaf = self::$calendarLeaves[0];
        try {
            $leaf->buildLeaves();
        } catch (Exception $e) {
            $exceptionThrown = true;
        }
        $this->assertTrue($exceptionThrown);
    }

}