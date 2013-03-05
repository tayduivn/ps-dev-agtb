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

class TimePeriodTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $preTestIds = array();

    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        $this->preTestIds = TimePeriod::get_timeperiods_dom();

        $db = DBManagerFactory::getInstance();

        $db->query('UPDATE timeperiods set deleted = 1');
    }

    public function tearDown()
    {
        $db = DBManagerFactory::getInstance();

        $db->query("UPDATE timeperiods set deleted = 1");

        //Clean up anything else left in timeperiods table that was not deleted
        $db->query("UPDATE timeperiods SET deleted = 0 WHERE id IN ('" . implode("', '", array_keys($this->preTestIds))  . "')");

        SugarTestHelper::tearDown();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    /**
     * @group timeperiods
     */
    public function testGetTimePeriodFromDbDateWithValidDate()
    {
        // get time period within 2009-02-15
        $expected = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');

        $result = TimePeriod::retrieveFromDate('2009-02-15');
        $this->assertEquals($expected->id, $result->id);

        $result = TimePeriod::retrieveFromDate('2009-01-01');
        $this->assertEquals($expected->id, $result->id);

        $result = TimePeriod::retrieveFromDate('2009-03-31');
        $this->assertEquals($expected->id, $result->id);

    }

    /**
     * check that the timestamps are generated correctly
     * @group timeperiods
     */
    public function testTimePeriodTimeStamps()
    {
        // create a time period
        $tp = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        $timedate = TimeDate::getInstance();

        $start_date_timestamp = $timedate->fromDbDate('2009-01-01');
        $start_date_timestamp->setTime(0,0,0);
        $start_date_timestamp = $start_date_timestamp->getTimestamp();

        $end_date_timestamp = $timedate->fromDbDate('2009-03-31');
        $end_date_timestamp->setTime(23,59,59);
        $end_date_timestamp = $end_date_timestamp->getTimestamp();

        $this->assertEquals($start_date_timestamp, $tp->start_date_timestamp, "start time stamps do not match");
        $this->assertEquals($end_date_timestamp, $tp->end_date_timestamp, "end time stamps do not match");
    }

    /**
     * @group timeperiods
     */
    public function testUpgradeLegacyTimePeriodsUpgradesTimePeriodsWithOutDateStamps()
    {
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2009-01-01', '2009-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2009-04-01', '2009-06-30');

        // create a third just to make sure that only two are really updated
        SugarTestTimePeriodUtilities::createTimePeriod('2009-07-01', '2009-09-30');

        $sql = "UPDATE timeperiods
                SET start_date_timestamp = null, end_date_timestamp = null
                WHERE id in ('".$tp1->id."','".$tp2->id."')";
        $db = DBManagerFactory::getInstance();
        $db->query($sql);

        $updated = $tp1->upgradeLegacyTimePeriods();

        $this->assertEquals(2, $updated);
    }

    /**
     * Test is meant to test what happens with an upgrade where timeperiods existed previously.
     * Historical Timeperiods should remain in the database, but anything current and future should be deleted
     *
     * @ticket 61489
     * @group timeperiods
     * @group forecasts
     */
    public function testCreateTimePeriodsForUpgradeCreates4Quarters()
    {
        $timedate = TimeDate::getInstance();
        $timedate->setNow($timedate->getNow()->setDate(2013, 3, 1));

        $forecastConfigSettings = array (
                'timeperiod_type' => 'chronological',
                'timeperiod_interval' => TimePeriod::ANNUAL_TYPE,
                'timeperiod_leaf_interval' => TimePeriod::QUARTER_TYPE,
                'timeperiod_start_date' => '2010-10-04',
                'timeperiod_shown_forward' => '1',
                'timeperiod_shown_backward' => '1',
        );

        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2010-10-04', '2011-10-03');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2011-10-04', '2012-10-03');
        $tp3 = SugarTestTimePeriodUtilities::createTimePeriod('2012-10-04', '2013-10-03');

        $seed = BeanFactory::getBean("TimePeriods");

        $timeperiods = $seed->createTimePeriodsForUpgrade($forecastConfigSettings, $timedate->getNow());

        foreach($timeperiods as $t) {
            SugarTestTimePeriodUtilities::addCreatedTimePeriod($t);
        }

        $currentTimePeriod = TimePeriod::getCurrentTimePeriod(TimePeriod::ANNUAL_TYPE);

        $currentLeaves = $currentTimePeriod->getLeaves();

        $this->assertEquals(3, count($currentLeaves), "Upgrade failed to create the correct number of leaves for the current time period");
        $this->assertNotEquals(false, BeanFactory::getBean("TimePeriods")->retrieve($tp2->id), "Upgrade failed to save a historical time period for record keeping");

        $currentTimePeriod = $currentTimePeriod->getNextTimePeriod();
        $this->assertNotNull($currentTimePeriod);
        $this->assertEquals('2013-10-04', $currentTimePeriod->start_date, "Upgrade failed to create a future time period with the correct start date");
        $this->assertEquals('2014-10-03', $currentTimePeriod->end_date, "Upgrade failed to create a future time period with the correct start end");
        $currentLeaves = $currentTimePeriod->getLeaves();
        $this->assertEquals(4, count($currentLeaves), "Upgrade failed to create the correct number of leaves for the future time period");
    }

    /**
     * Test is meant to test what happens with an upgrade where timeperiods existed previously.
     * Historical Timeperiods should remain in the database, but anything current and future should be deleted
     *
     * @ticket 60606
     * @group timeperiods
     * @group forecasts
     */
    public function testCreateTimePeriodsForUpgradeCreatesFuturePeriods()
    {
        //reset now to be static point in time to make sure that this test will continue passing with the progression of time
        $timedate = TimeDate::getInstance();
        $timedate->setNow($timedate->getNow()->setDate(2013, 3, 5));

        $forecastConfigSettings = array (
                'timeperiod_type' => 'chronological',
                'timeperiod_interval' => TimePeriod::ANNUAL_TYPE,
                'timeperiod_leaf_interval' => TimePeriod::QUARTER_TYPE,
                'timeperiod_start_date' => '2013-01-01',
                'timeperiod_shown_forward' => '1',
                'timeperiod_shown_backward' => '2',
        );

        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2012-08-06', '2012-11-04');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2012-08-13', '2012-11-11');
        $tp3 = SugarTestTimePeriodUtilities::createTimePeriod('2012-08-20', '2012-11-18');
        $tp4 = SugarTestTimePeriodUtilities::createTimePeriod('2012-08-27', '2012-11-25');
        $tp5 = SugarTestTimePeriodUtilities::createTimePeriod('2012-09-03', '2012-12-02');
        $tp6 = SugarTestTimePeriodUtilities::createTimePeriod('2012-09-10', '2012-12-09');
        $tp7 = SugarTestTimePeriodUtilities::createTimePeriod('2012-09-17', '2012-12-16');
        $tp8 = SugarTestTimePeriodUtilities::createTimePeriod('2012-09-24', '2012-12-23');

        $seed = BeanFactory::getBean("TimePeriods");

        $timeperiods = $seed->createTimePeriodsForUpgrade($forecastConfigSettings, $timedate->getNow());

        foreach($timeperiods as $t) {
            SugarTestTimePeriodUtilities::addCreatedTimePeriod($t);
        }

        $currentTimePeriod = TimePeriod::getCurrentTimePeriod(TimePeriod::ANNUAL_TYPE);

        $currentLeaves = $currentTimePeriod->getLeaves();

        $this->assertEquals(3, count($currentLeaves), "Upgrade failed to create the correct number of leaves for the current time period");
        //$this->assertNotEquals(false, BeanFactory::getBean("TimePeriods")->retrieve($tp2->id), "Upgrade failed to save a historical time period for record keeping");

        $currentTimePeriod = $currentTimePeriod->getNextTimePeriod();
        $this->assertNotNull($currentTimePeriod);
        $this->assertEquals('2014-01-01', $currentTimePeriod->start_date, "Upgrade failed to create a future time period with the correct start date");
        $this->assertEquals('2014-12-31', $currentTimePeriod->end_date, "Upgrade failed to create a future time period with the correct start end");
        $currentLeaves = $currentTimePeriod->getLeaves();
        $this->assertEquals(4, count($currentLeaves), "Upgrade failed to create the correct number of leaves for the future time period");
    }
}