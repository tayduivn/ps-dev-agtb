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
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
        SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();
    }

    /**
     *
     */
    function testGetLastCurrentNextIds()
    {
        global $app_strings;

        $current = SugarTestTimePeriodUtilities::createTimePeriod();
        $timedate = TimeDate::getInstance();
        $endDate = $timedate->fromDbDate($current->start_date)->modify('-1 day')->asDbDate();
        $last = SugarTestTimePeriodUtilities::createTimePeriod('2011-01-01', $endDate);
        $startDate = $timedate->fromDbDate($current->end_date)->modify('+1 day')->asDbDate();
        $next = SugarTestTimePeriodUtilities::createTimePeriod($startDate, '2020-01-01');

        $timeperiods = array();
        $timeperiods[$current->id] = $app_strings['LBL_CURRENT_TIMEPERIOD'];
        $timeperiods[$last->id] = $app_strings['LBL_PREVIOUS_TIMEPERIOD'];
        $timeperiods[$next->id] = $app_strings['LBL_NEXT_TIMEPERIOD'];

        $result = TimePeriod::getLastCurrentNextIds();
        $this->assertSame($timeperiods, $result);

        $result = TimePeriod::getLastCurrentNextIds(TimeDate::getInstance());
        $this->assertSame($timeperiods, $result);
    }

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
}