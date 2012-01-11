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


class Bug37953Test extends Sugar_PHPUnit_Framework_TestCase
{
    var $call;

    public function setUp()
    {
        global $current_user;
        $current_user = SugarTestUserUtilities::createAnonymousUser();
        $this->call = SugarTestCallUtilities::createCall();
        $this->useOutputBuffering = false;
	}

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestCallUtilities::removeAllCreatedCalls();
    }

    public function testCallAppearsWithinMonthView()
    {
        $this->markTestIncomplete('Skipping for now.  Communicated with Yuri about properly resolving this since eCalendar moves out the get_occurs_within_where clause to CalendarActivity.php');
        global $timedate,$sugar_config,$DO_USER_TIME_OFFSET , $current_user;

        $DO_USER_TIME_OFFSET = true;
        $timedate = TimeDate::getInstance();
        $format = $current_user->getUserDateTimePreferences();
        $name = 'Bug37953Test' . $timedate->nowDb();
        $this->call->name = $name;
        $this->call->date_start = $timedate->swap_formats("2011-09-29 11:00pm" , 'Y-m-d h:ia', $format['date'].' '.$format['time']);
        $this->call->time_start = "";
        $this->call->object_name = "Call";
        $this->call->duration_hours = 99;
        
        $ca = new CalendarActivity($this->call);
        $where = $ca->get_occurs_within_where_clause($this->call->table_name, $this->call->rel_users_table, $ca->start_time, $ca->end_time, 'date_start', 'month');

        $this->assertRegExp('/2011\-09\-23 00:00:00/', $where, 'Assert that we go back 6 days from the date_start value');
        $this->assertRegExp('/2011\-11\-01 00:00:00/', $where, 'Assert that we go to the end of next month');
    }
}