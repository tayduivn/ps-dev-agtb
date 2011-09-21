<?php

$DO_USER_TIME_OFFSET = false;

class CalendarActivity {

	var $sugar_bean;
	var $start_time;
	var $end_time;

	function CalendarActivity($args){
		// if we've passed in an array, then this is a free/busy slot
		// and does not have a sugarbean associated to it
		global $DO_USER_TIME_OFFSET;
		global $timedate;

		if(is_array($args)){
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


		if($sugar_bean->object_name == 'Task'){
			$this->start_time = $timedate->fromUser($this->sugar_bean->date_due);
			if(empty($this->start_time)){
				return null;
			}
			$this->end_time = $timedate->fromUser($this->sugar_bean->date_due);
		}else{
			$this->start_time = $timedate->fromUser($this->sugar_bean->date_start);
			if(empty($this->start_time)){
				return null;
			}
			$hours = $this->sugar_bean->duration_hours;
			if(empty($hours)) {
			    $hours = 0;
			}
			$mins = $this->sugar_bean->duration_minutes;
			if(empty($mins)){
				$mins = 0;
			}
				$this->end_time = $this->start_time->get("+$hours hours $mins minutes");
		}
		// Convert it back to database time so we can properly manage it for getting the proper start and end dates
		$timedate->tzGMT($this->start_time);
		$timedate->tzGMT($this->end_time);
	}
	

	function get_occurs_within_where_clause($table_name, $rel_table, $start_ts_obj, $end_ts_obj, $field_name='date_start', $view) {
		global $timedate;
		$dtUtilArr = array();
		switch ($view) {
			case 'month':
				$start_ts = $start_ts_obj->get_first_day_of_this_month();
				$end_ts = $end_ts_obj->get_first_day_of_next_month();
				break;
			default:
				// Date for the past 5 days as that is the maximum duration of a single activity
				$dtUtilArr['ts'] = $start_ts_obj->ts - (86400*5);
				$start_ts = new DateTimeUtil($dtUtilArr, false);
				// Date for the next 5 days as that is the maximum duration of a single activity
				$dtUtilArr['ts'] = $end_ts_obj->ts + (86400*5);
				$end_ts = new DateTimeUtil($dtUtilArr, false);
				break;
		}
		$start_mysql_date = explode('-', $start_ts->get_mysql_date());
		$start_mysql_date_time = explode(' ',$timedate->handle_offset(date($GLOBALS['timedate']->get_db_date_time_format(), $start_ts->ts), $timedate->dbDayFormat, true));

		$end_mysql_date = explode('-', $end_ts->get_mysql_date());
		$end_mysql_date_time = explode(' ',$timedate->handle_offset(date($GLOBALS['timedate']->get_db_date_time_format(), $end_ts->ts), $timedate->dbDayFormat, true));
			
		$where =  "(". db_convert($table_name.'.'.$field_name,'date_format',array("'%Y-%m-%d'"),array("'YYYY-MM-DD'")) ." >= '{$start_mysql_date_time[0]}' AND ";
		$where .= db_convert($table_name.'.'.$field_name,'date_format',array("'%Y-%m-%d'"),array("'YYYY-MM-DD'")) ." <= '{$end_mysql_date_time[0]}')";
			
		if($rel_table != '') {
			$where .= ' AND '.$rel_table.'.accept_status != \'decline\'';
		} 
		return $where;
	}

	function get_freebusy_activities(&$user_focus,&$start_date_time,&$end_date_time){

		$act_list = array();
		$vcal_focus = new vCal();
		$vcal_str = $vcal_focus->get_vcal_freebusy($user_focus);
		$lines = explode("\n",$vcal_str);

		foreach ($lines as $line){
			$dates_arr = array();
			if ( preg_match('/^FREEBUSY.*?:([^\/]+)\/([^\/]+)/i',$line,$matches)){
				$dates_arr[] =DateTimeUtil::parse_utc_date_time($matches[1]);
				$dates_arr[] =DateTimeUtil::parse_utc_date_time($matches[2]);
				$act_list[] = new CalendarActivity($dates_arr); 
			}
		}
		usort($act_list,'sort_func_by_act_date2');
		return $act_list;
	}


 	function get_activities($user_id, $params, &$view_start_time, &$view_end_time, $view){
 		
		global $current_user;
		$act_list = array();
		$seen_ids = array();
		
		if(!is_array($params))
			$params = array();		
		if(!isset($params['show_calls']))
			$params['show_calls'] = true;
		if(!isset($params['show_tasks']))
			$params['show_tasks'] = false;		
				
		
		// get all upcoming meetings, tasks due, and calls for a user
		if(ACLController::checkAccess('Meetings', 'list', $current_user->id == $user_id)){
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
			if(ACLController::checkAccess('Calls', 'list',$current_user->id  == $user_id)){
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
			if(ACLController::checkAccess('Tasks', 'list',$current_user->id == $user_id)){
				$task = new Task();
	
				$where = CalendarActivity::get_occurs_within_where_clause('tasks', '', $view_start_time, $view_end_time, 'date_due', $view);
				$where .= " AND tasks.assigned_user_id='$user_id' ";
	
				$focus_tasks_list = $task->get_full_list("", $where,true);
	
				if(!isset($focus_tasks_list)) {
					$focus_tasks_list = array();
				}
	
				foreach($focus_tasks_list as $task) {
					$act = new CalendarActivity($task);
					if(!empty($act)){
						$act_list[] = $act;
					}
				}
			}
		}

		usort($act_list,'sort_func_by_act_date2');				
		return $act_list;
	}
}

function sort_func_by_act_date2($act0,$act1){
	if ($act0->start_time->ts == $act1->start_time->ts){
		return 0;
	}
	return ($act0->start_time->ts < $act1->start_time->ts) ? -1 : 1;
}

?>
