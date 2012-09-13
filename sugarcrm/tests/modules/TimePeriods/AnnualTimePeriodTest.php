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

class TimePeriodTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected static $tp;

    public static function setUpBeforeClass() {
        self::$tp = SugarTestTimePeriodUtilities::createAnnualTimePeriod();
        parent::setUpBeforeClass();
    }


    public function setUp()
    {
        SugarTestHelper::setUp('app_strings');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();


    }

    public static function tearDownAfterClass() {
        //SugarTestTimePeriodUtilities::removeAllCreatedTimePeriods();

        parent::tearDownAfterClass();
    }

    /**
     *
     */
    function testCreateNextTimePeriod()
    {
        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$tp;

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
     *
     */
    function testGetNextPeriod()
    {

        global $app_strings;

        $timedate = TimeDate::getInstance();
        //get current timeperiod
        $baseTimePeriod = self::$tp;

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

    //TODO: add tests to check inclusive/exclusivity of first day last day etc.

}