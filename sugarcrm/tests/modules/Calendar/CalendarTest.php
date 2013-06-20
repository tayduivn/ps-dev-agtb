<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/en/msa/master_subscription_agreement_11_April_2011.pdf
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
 * by SugarCRM are Copyright (C) 2004-2011 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/



require_once "modules/Calendar/Calendar.php";
require_once "modules/Calendar/CalendarUtils.php";
require_once('modules/Meetings/Meeting.php');

class CalendarTest extends Sugar_PHPUnit_Framework_TestCase {

   	/**
	 * @var TimeDate
	 */
	protected $time_date;

	protected $meeting_id = null;

	public function setUp()
	{
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('current_user');
        $this->time_date = new TimeDate();
		$_REQUEST['module'] = 'Calendar';
		$_REQUEST['year'] = '2012';
		$_REQUEST['month'] = '01';
		$_REQUEST['day'] = '02';
	}

	public function tearDown(){
		unset($_REQUEST['module']);
		unset($_REQUEST['year']);
		unset($_REQUEST['month']);
		unset($_REQUEST['day']);

		if(isset($this->meeting_id)){
			$GLOBALS['db']->query("DELETE FROM meetings WHERE id = '{$this->meeting_id}'");
			$GLOBALS['db']->query("DELETE FROM meetings_users WHERE meeting_id = '{$this->meeting_id}'");
			unset($this->meeting_id);
		}
        SugarTestHelper::tearDown();
	}

	public function testCalendarDate(){
		$cal = new Calendar('week');
		$this->assertEquals('2012',$cal->date_time->year);
	}

	public function testCalendarAddActivity(){
		$cal = new Calendar('week');
		$cal->add_activities($GLOBALS['current_user']);
		$count1 = count($cal->acts_arr[$GLOBALS['current_user']->id]);
		$cal->acts_arr = array();

		$this->meeting_id = uniqid();

        $db = $GLOBALS['db'];
        $db->query("INSERT INTO meetings (id,date_start,assigned_user_id) VALUES(".
                                            $db->quoted($this->meeting_id) . ", " .
                                            $db->convert($db->quoted("2012-01-02 00:00:00"), 'datetime') . ", " .
                                            $db->quoted($GLOBALS['current_user']->id) .")");

		$db->query("INSERT INTO meetings_users (id,meeting_id,user_id) VALUES (".
                                                $db->quoted(uniqid()).", ".
                                                $db->quoted($this->meeting_id).", ".
                                                $db->quoted($GLOBALS['current_user']->id).")");

		$cal->add_activities($GLOBALS['current_user']);
		$count2 = count($cal->acts_arr[$GLOBALS['current_user']->id]);

		$this->assertEquals($count1 + 1, $count2, "Count of records should be one more after meeting added");
	}

	public function testCalendarLoadActivities(){
		$cal = new Calendar('month');
		$cal->add_activities($GLOBALS['current_user']);
		$format = $GLOBALS['current_user']->getUserDateTimePreferences();
		$meeting = new Meeting();
		$meeting->meeting_id = uniqid();
		$meeting->date_start = $this->time_date->swap_formats("2012-01-01 11:00pm" , 'Y-m-d h:ia', $format['date'].' '.$format['time']);
		$meeting->name = "test";
		$cal->acts_arr = array();
		$cal->acts_arr[$GLOBALS['current_user']->id] = array();
		$cal->acts_arr[$GLOBALS['current_user']->id][] = new CalendarActivity($meeting);
		$cal->load_activities();

		$this->assertEquals($cal->items[0]['time_start'],$this->time_date->swap_formats("2012-01-01 11:00pm" , 'Y-m-d h:ia', $format['time']),"Time should remain the same after load_activities");
	}

	public function testHandleOffset(){
		$gmt_today =  $this->time_date->nowDb();
		$date1 = $this->time_date->handle_offset($gmt_today, $GLOBALS['timedate']->get_db_date_time_format());
		$date2 = $this->time_date->nowDb();
		$this->assertEquals($date1, $date2, "HandleOffset should be equaivalent to nowDb");
	}

	public function testUserDateFormat(){
		$gmt_default_date_start = $this->time_date->get_gmt_db_datetime();
		$date1 = $this->time_date->handle_offset($gmt_default_date_start, $GLOBALS['timedate']->get_date_time_format());
		$date2 = $this->time_date->asUser($this->time_date->getNow());
		$this->assertEquals($date1, $date2, "HandleOffset should be equaivalent to nowDb");
	}



}
