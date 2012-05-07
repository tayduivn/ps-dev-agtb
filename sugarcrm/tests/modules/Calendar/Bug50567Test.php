<?php
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

require_once("modules/Calendar/Calendar.php");

class Bug50567Test extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    public static function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
    }

    /**
     * providerCorrectNextMonth
     *
     */
    public function providerCorrectNextMonth()
    {
        return array(
            array('2012-01-31', 'next', '&year=2012&month=2&day=1'),
            array('2012-02-29', 'next', '&year=2012&month=3&day=1'), //Check leap year
            array('2011-02-28', 'next', '&year=2011&month=3&day=1'), //Check non-leap year
            array('2012-12-31', 'next', '&year=2013&month=1&day=1'), //Check new year

            array('2012-01-31', 'previous', '&year=2011&month=12&day=1'),
            array('2012-12-31', 'previous', '&year=2012&month=11&day=1'),
            array('2012-02-29', 'previous', '&year=2012&month=1&day=1'), //Check leap year
            array('2011-02-28', 'previous', '&year=2011&month=1&day=1'), //Check non-leap year
        );
    }


    /**
     * @dataProvider providerCorrectNextMonth
     *
     */
    public function testCorrectNextMonth($testDate, $direction, $expectedString)
    {
        global $timedate;
        $timedate = TimeDate::getInstance();
        $this->calendar = new Calendar('month');
        $this->calendar->date_time = $timedate->fromString($testDate);
        $uri = $this->calendar->get_neighbor_date_str($direction);
        $this->assertContains($expectedString, $uri, "Failed to get {$direction} expected URL: {$expectedString} from date: {$testDate}");

    }
}