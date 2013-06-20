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
        parent::setUp();
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
        parent::tearDown();
    }

    /**
     * @group timeperiods
     */
    public function testGetTimePeriodFromDbDateWithValidDate()
    {
        $this->markTestIncomplete("Marking incomplete as it fails in strict mode. SFA team.");
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
     * @group timeperiods
     */
     public function testRetrieveFromDate()
     {
        $this->markTestIncomplete("Marking incomplete as it fails in strict mode.  SFA team.");
        $tp1 = SugarTestTimePeriodUtilities::createTimePeriod('2013-01-01', '2013-03-31');
        $tp2 = SugarTestTimePeriodUtilities::createTimePeriod('2013-04-01', '2013-06-30');
        
        //check to see if dates are in a timeperiod
        $tp3 = TimePeriod::retrieveFromDate('2013-01-30');
        $tp4 = TimePeriod::retrieveFromDate('2013-05-14');
        $tp5 = TimePeriod::retrieveFromDate('2013-07-01');
        
        $this->assertEquals($tp1->id, $tp3->id);
        $this->assertEquals($tp2->id, $tp4->id);
        $this->assertEquals(false, $tp5);
         
     }
}
