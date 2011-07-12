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
require_once 'include/TimeDate.php';
require_once 'modules/Calendar/Calendar.php';

/**
 * @ticket 4236
 */
class Bug4236Test extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
    }

    public function testFirstDayOfWeek()
    {
        global $timedate, $current_user;

        // No FDOW selected (0 is the default). I expect Calendar Month View to render starting on Sunday
        $fdow = $timedate->get_first_day_of_week();
        $cal = new Calendar("month");
        // Expect that the first day in slices_arr is Sunday
        $this->assertEquals($fdow , 0);
        $this->assertEquals($fdow , $cal->slice_hash[$cal->slices_arr[0]]->start_time->day_of_week);

        // Set 0 (Sunday) as FDOW. I expect Calendar Month View to render starting on Sunday
        $current_user->setPreference('fdow', 0, 0, 'global');
        $fdow = $timedate->get_first_day_of_week();
        $cal = new Calendar("month");
        // Expect that the first day in slices_arr is Sunday
        $this->assertEquals($fdow , 0);
        $this->assertEquals($fdow , $cal->slice_hash[$cal->slices_arr[0]]->start_time->day_of_week);

        // Set 1 (Monday) as FDOW. I expect Calendar Month View to render starting on Monday
        $current_user->setPreference('fdow', 1, 0, 'global');
        $fdow = $timedate->get_first_day_of_week();
        $cal = new Calendar("month");
        // Expect that the first day in slices_arr is Monday
        $this->assertEquals($fdow , 1);
        $this->assertEquals($fdow , $cal->slice_hash[$cal->slices_arr[0]]->start_time->day_of_week);
    }
}
