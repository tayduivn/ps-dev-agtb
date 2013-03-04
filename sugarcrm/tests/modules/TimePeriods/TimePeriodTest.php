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
    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public function tearDown()
    {
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
        $currentDate = $timedate->getNow();
        $currentYear = $currentDate->format('Y');
        $currentMonth = $currentDate->format('n');
        $currentDay = $currentDate->format('j');
        if($currentMonth < 10 || ($currentMonth == 10 && $currentDay < 4)) {
            $currentYear = $currentYear - 1;
        }

        $forecastConfigSettings = array (
                'timeperiod_type' => 'chronological',
                'timeperiod_interval' => TimePeriod::ANNUAL_TYPE,
                'timeperiod_leaf_interval' => TimePeriod::QUARTER_TYPE,
                'timeperiod_start_date' => ($currentYear-2) . '-10-04',
                'timeperiod_shown_forward' => '1',
                'timeperiod_shown_backward' => '1',
        );

        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod($currentYear-2 . '-10-04', $currentYear-1 . '-10-03');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod($currentYear-1 . '-10-04', $currentYear . '-10-03');
        $tp3 = SugarTestTimePeriodUtilities::createTimePeriod($currentYear . '-10-04', $currentYear+1 . '-10-03');

        $seed = BeanFactory::getBean("TimePeriods");

        $timeperiods = $seed->createTimePeriodsForUpgrade($forecastConfigSettings, $currentDate);

        foreach($timeperiods as $t) {
            SugarTestTimePeriodUtilities::addCreatedTimePeriod($t);
        }

        $currentTimePeriod = TimePeriod::getCurrentTimePeriod(TimePeriod::ANNUAL_TYPE);

        $currentLeaves = $currentTimePeriod->getLeaves();

        $this->assertEquals(4, count($currentLeaves), "Upgrade failed to create the correct number of leaves for the current time period");
        $this->assertFalse(BeanFactory::getBean("TimePeriods", $tp3->id), "Upgrade failed to delete the current time period set prior to upgrade.");
        $this->assertNotEquals(false, BeanFactory::getBean("TimePeriods", $tp2->id), "Upgrade failed to save a historical time period for record keeping");

        $currentTimePeriod = $currentTimePeriod->getNextTimePeriod();
        $this->assertNotNull($currentTimePeriod);
        $this->assertEquals($currentYear+1 . '-10-04', $currentTimePeriod->start_date, "Upgrade failed to create a future time period with the correct start date");
        $this->assertEquals($currentYear+2 . '-10-03', $currentTimePeriod->end_date, "Upgrade failed to create a future time period with the correct start end");
        $currentLeaves = $currentTimePeriod->getLeaves();
        $this->assertEquals(4, count($currentLeaves), "Upgrade failed to create the correct number of leaves for the future time period");
    }
}