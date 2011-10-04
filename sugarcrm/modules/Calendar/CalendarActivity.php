<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
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
 
require_once('include/utils/activity_utils.php');

class CalendarActivity {
	var $sugar_bean;
	var $start_time;
	var $end_time;

	function CalendarActivity($args){
	    // if we've passed in an array, then this is a free/busy slot
	    // and does not have a sugarbean associated to it
		global $timedate;

		if ( is_array ( $args )){
		   $this->start_time = clone $args[0];
		   $this->end_time = clone $args[1];
		   $this->sugar_bean = null;
		   $timedate->tzGMT($this->start_time);
		   $timedate->tzGMT($this->end_time);
		   return;
		}

	    // else do regular constructor..

	    	$sugar_bean = $args;
			$this->sugar_bean = $sugar_bean;


			if ($sugar_bean->object_name == 'Task')
			{
			    $this->start_time = $timedate->fromUser($this->sugar_bean->date_due);
				if ( empty($this->start_time))
				{
					return null;
				}

				$this->end_time = $timedate->fromUser($this->sugar_bean->date_due);
			}
			else
			{
		    $this->start_time = $timedate->fromUser($this->sugar_bean->date_start);
				if ( empty($this->start_time))
				{
				    return null;
				}
				$hours = $this->sugar_bean->duration_hours;
				if(empty($hours)) {
				    $hours = 0;
				}
				$mins = $this->sugar_bean->duration_minutes;
				if(empty($mins)) {
				    $mins = 0;
				}
				$this->end_time = $this->start_time->get("+$hours hours $mins minutes");
			}
		// Convert it back to database time so we can properly manage it for getting the proper start and end dates
		$timedate->tzGMT($this->start_time);
		$timedate->tzGMT($this->end_time);
	}

	function get_occurs_within_where_clause($table_name, $rel_table, $start_ts_obj, $end_ts_obj, $field_name='date_start', $view){
		global $timedate;
        // ensure we're working with user TZ
		$start_ts_obj = $timedate->tzUser($start_ts_obj);
		$end_ts_obj = $timedate->tzUser($end_ts_obj);
		switch ($view) {
			case 'month':
                //C.L. For the start date, go back 6 days since 99 hours is the max duration (6 days)
                $start = $start_ts_obj->get("-6 days")->get_day_begin();
				$end = $end_ts_obj->get("first day of next month")->get_day_begin();
				break;
            case 'freebusy':    //bug: 44586, for freebusy, don't modify the start/end dates
                $start = $start_ts_obj;
                $end = $end_ts_obj;
                break;
			default:
				// Date for the past 5 days as that is the maximum duration of a single activity
				$start = $start_ts_obj->get("-5 days")->get_day_begin();
				$end =  $start_ts_obj->get("+5 days")->get_day_end();
				break;
		}

		$field_date = $table_name.'.'.$field_name;
        $start_day = $GLOBALS['db']->convert("'{$start->asDb()}'",'datetime');
        $end_day = $GLOBALS['db']->convert("'{$end->asDb()}'",'datetime');

		$where = "($field_date >= $start_day AND $field_date < $end_day";
        if($rel_table != '') {
            $where .= " AND $rel_table.accept_status != 'decline'";
        }

		$where .= ")";
		return $where;
	}

	function get_freebusy_activities($user_focus, $start_date_time, $end_date_time){
		$act_list = array();
		$vcal_focus = new vCal();
		$vcal_str = $vcal_focus->get_vcal_freebusy($user_focus);

		$lines = explode("\n",$vcal_str);
		$utc = new DateTimeZone("UTC");
	 	foreach ($lines as $line){
			if ( preg_match('/^FREEBUSY.*?:([^\/]+)\/([^\/]+)/i',$line,$matches)){
			  $dates_arr = array(SugarDateTime::createFromFormat(vCal::UTC_FORMAT, $matches[1], $utc),
				              SugarDateTime::createFromFormat(vCal::UTC_FORMAT, $matches[2], $utc));
			  $act_list[] = new CalendarActivity($dates_arr);
			}
		}
		usort($act_list,'sort_func_by_act_date');
		return $act_list;
	}

 	function get_activities($user_id, $params, $view_start_time, $view_end_time, $view){
		global $current_user;
		$act_list = array();
		$seen_ids = array();


		// get all upcoming meetings, tasks due, and calls for a user
		if(ACLController::checkAccess('Meetings', 'list', $current_user->id == $user_id)) {
			$meeting = new Meeting();

			if($current_user->id  == $user_id) {
				$meeting->disable_row_level_security = true;
			}

			$where = CalendarActivity::get_occurs_within_where_clause($meeting->table_name, $meeting->rel_users_table, $view_start_time, $view_end_time, 'date_start', $view);
			$focus_meetings_list = build_related_list_by_user_id($meeting,$user_id,$where);
			foreach($focus_meetings_list as $meeting) {
				if(isset($seen_ids[$meeting->id])) {
					continue;
				}

				$seen_ids[$meeting->id] = 1;
				$act = new CalendarActivity($meeting);

				if(!empty($act)) {
					$act_list[] = $act;
				}
			}
		}
		
		if($params['show_calls']){
			if(ACLController::checkAccess('Calls', 'list',$current_user->id  == $user_id)) {
				$call = new Call();

				if($current_user->id  == $user_id) {
					$call->disable_row_level_security = true;
				}

				$where = CalendarActivity::get_occurs_within_where_clause($call->table_name, $call->rel_users_table, $view_start_time, $view_end_time, 'date_start', $view);
				$focus_calls_list = build_related_list_by_user_id($call,$user_id,$where);

				foreach($focus_calls_list as $call) {
					if(isset($seen_ids[$call->id])) {
						continue;
					}
					$seen_ids[$call->id] = 1;

					$act = new CalendarActivity($call);
					if(!empty($act)) {
						$act_list[] = $act;
					}
				}
			}
		}


		if($params['show_tasks']){
			if(ACLController::checkAccess('Tasks', 'list',$current_user->id == $user_id)) {
				$task = new Task();

				$where = CalendarActivity::get_occurs_within_where_clause('tasks', '', $view_start_time, $view_end_time, 'date_due', $view);
				$where .= " AND tasks.assigned_user_id='$user_id' ";

				$focus_tasks_list = $task->get_full_list("", $where,true);

				if(!isset($focus_tasks_list)) {
					$focus_tasks_list = array();
				}

				foreach($focus_tasks_list as $task) {
					$act = new CalendarActivity($task);
					if(!empty($act)) {
						$act_list[] = $act;
					}
				}
			}
		}
		return $act_list;
	}
}

?>
