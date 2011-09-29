<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-professional-eula.html
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
 * by SugarCRM are Copyright (C) 2004-2005 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once('modules/Calendar/CalendarUtils.php');
require_once('include/utils/activity_utils.php');
require_once('modules/Calendar/CalendarActivity.php');


class Calendar {
	
	var $view = 'week'; // current view
	var $dashlet = false; // whether is displayed in dashlet	
	var $date_time;
	
	var $show_tasks = true;
	var $show_calls = true;	
	var $day_start_time; // working day start time in format '12:00'
	var $day_end_time; // working day end time in format '12:00'
	
	var $gmt_today;	// GMT of today
	var $today_unix; // timestamp of today
	var $time_step = 60; // time step of each slot in minutes
		
	var $acts_arr = array(); // Array of activities objects	
	var $ActRecords = array(); // Array of activities data to be displayed	
	var $date_arr = array(); // ('year','month','day')
	var $shared_ids = array(); // ids of users for shared view
	
	var $celcount; // working day count of slots 
	var $cells_per_day; // entire 24h day count of slots 
	var $d_start_minutes; // working day start minutes 
	var $d_end_minutes; // working day end minutes
	
	function __construct($view = "day",$time_arr=array()){
		global $current_user, $timedate;	
		
		$this->view = $view;		
		$this->init();	
		
		if(empty($time_arr))
			$time_arr = $this->date_arr;	

		if($current_user->getPreference('time')){
			$time = $current_user->getPreference('time');
		}else{
			$time = $GLOBALS['sugar_config']['default_time_format'];
		}

		if(!empty($time_arr)){
			$this->date_time = $timedate->fromTimeArray($time_arr);
		}else{
		        $this->date_time = $timedate->getNow();
		}		

		$timedate->tzUser($this->date_time, $current_user);
        	$GLOBALS['log']->debug("CALENDATE: ".$this->date_time->format('r'));
	}
	
	/**
	 * initialize
	 */
	function init(){
		global $current_user,$timedate;
		
		if(!in_array($this->view,array('day','week','month','year','shared')))
			$this->view = 'week';
		
		$date_arr = array();
		if(!empty($_REQUEST['day']))
			$_REQUEST['day'] = intval($_REQUEST['day']);
		if(!empty($_REQUEST['month']))
			$_REQUEST['month'] = intval($_REQUEST['month']);

		if (!empty($_REQUEST['day']))
			$date_arr['day'] = $_REQUEST['day'];
		if (!empty($_REQUEST['month']))
			$date_arr['month'] = $_REQUEST['month'];
		if (!empty($_REQUEST['week']))
			$date_arr['week'] = $_REQUEST['week'];

		if (!empty($_REQUEST['year'])){
			if ($_REQUEST['year'] > 2037 || $_REQUEST['year'] < 1970){
				print("Sorry, calendar cannot handle the year you requested");
				print("<br>Year must be between 1970 and 2037");
				exit;
			}
			$date_arr['year'] = $_REQUEST['year'];
		}

		if(empty($_REQUEST['day']))
			$_REQUEST['day'] = "";
		if(empty($_REQUEST['week']))
			$_REQUEST['week'] = "";
		if(empty($_REQUEST['month']))
			$_REQUEST['month'] = "";
		if(empty($_REQUEST['year']))
			$_REQUEST['year'] = "";

		// if date is not set in request set current date
		if(empty($date_arr) || !isset($date_arr['year']) || !isset($date_arr['month']) || !isset($date_arr['day']) ){	
			$user_today = $timedate->nowDb();
			preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/',$user_today,$matches);
			$date_arr = array(
			      'year' => $matches[1],
			      'month' => $matches[2],
			      'day' => $matches[3],
			);
		}else{		
			$this->gmt_today = $date_arr['year'] . "-" . CalendarUtils::add_zero($date_arr['month']) . "-" . CalendarUtils::add_zero($date_arr['day']);
		}		
		
		$this->date_arr = $date_arr;
		
		$this->show_tasks = $current_user->getPreference('show_tasks');
		if(is_null($this->show_tasks))
			$this->show_tasks = SugarConfig::getInstance()->get('calendar.show_tasks_by_default',true);		
		$this->show_calls = $current_user->getPreference('show_calls');
		if(is_null($this->show_calls))
			$this->show_calls = SugarConfig::getInstance()->get('calendar.show_calls_by_default',true);
	
		$this->day_start_time = $current_user->getPreference('day_start_time');
		if(is_null($this->day_start_time))
			$this->day_start_time = SugarConfig::getInstance()->get('calendar.default_day_start',"08:00");
		$this->day_end_time = $current_user->getPreference('day_end_time');
		if(is_null($this->day_end_time))
			$this->day_end_time = SugarConfig::getInstance()->get('calendar.default_day_end',"19:00");
			
		if($this->view == "day"){
			$this->time_step = SugarConfig::getInstance()->get('calendar.day_timestep',15);
		}else if($this->view == "week" || $this->view == "shared"){
			$this->time_step = SugarConfig::getInstance()->get('calendar.week_timestep',30);
		}else if($this->view == "month"){
			$this->time_step = SugarConfig::getInstance()->get('calendar.month_timestep',60);
		}else{
			$this->time_step = 60;
		}			

		$this->today_unix = CalendarUtils::to_timestamp($this->gmt_today);
		$this->calculate_day_range();
	}
	
	/**
	 * loads activities data to array
	 */		
	function load_activities(){
		$field_list = CalendarUtils::get_fields();
		foreach($this->acts_arr as $user_id => $acts){
			foreach($acts as $act){				
					$newAct = array();
					$newAct['module_name'] = $act->sugar_bean->module_dir;
					$newAct['type'] = strtolower($act->sugar_bean->object_name);				
					$newAct['user_id'] = $user_id;
					$newAct['assigned_user_id'] = $act->sugar_bean->assigned_user_id;
					$newAct['id'] = $act->sugar_bean->id;	
					$newAct['name'] = $act->sugar_bean->name;
					$newAct['status'] = $act->sugar_bean->status;
					
					if(isset($act->sugar_bean->duration_hours)){
						$newAct['duration_hours'] = $act->sugar_bean->duration_hours;
						$newAct['duration_minutes'] = $act->sugar_bean->duration_minutes;
					}
				
					$bean = new $act->sugar_bean->object_name();
					$bean->retrieve($newAct['id']);
					 			
					$newAct['detailview'] = 0;
					$newAct['editview'] = 0;
					
					if($act->sugar_bean->ACLAccess('DetailView'))
						$newAct['detailview'] = 1;						
					if($act->sugar_bean->ACLAccess('Save'))
						$newAct['editview'] = 1;					
						
					if(empty($bean->id)){
						$newAct['detailview'] = 0;
						$newAct['editview'] = 0;
					}					
					
					if($newAct['detailview'] == 1){
						if(isset($field_list[$newAct['module_name']])){
							foreach($field_list[$newAct['module_name']] as $field){
								if(!isset($newAct[$field])){
									$newAct[$field] = $bean->$field;									
									if($act->sugar_bean->field_defs[$field]['type'] == 'text'){
										$t = $newAct[$field];				
										$t = str_replace("\r\n","<br>",$t);
										$t = str_replace("\r","<br>",$t);
										$t = str_replace("\n","<br>",$t);
										$newAct[$field] = $t;
									}										
								}
							}					
						}				
					}								

					$newAct['date_start'] = $act->sugar_bean->date_start;	
					$date_unix = CalendarUtils::to_timestamp_from_uf($act->sugar_bean->date_start);
				
					if($newAct['type'] == 'task'){
					 	$newAct['date_start'] = $act->sugar_bean->date_due;					 	
						$date_unix = CalendarUtils::to_timestamp_from_uf($newAct['date_start']);
					}
								
					$newAct['start'] = $date_unix;
					$newAct['time_start'] = CalendarUtils::timestamp_to_user_formated2($newAct['start'],$GLOBALS['timedate']->get_time_format());

					if(!isset($newAct['duration_hours']) || empty($newAct['duration_hours']))
						$newAct['duration_hours'] = 0;
					if(!isset($newAct['duration_minutes']) || empty($newAct['duration_minutes']))
						$newAct['duration_minutes'] = 0;				
			
					$this->ActRecords[] = $newAct;
			}
		}
	}
	
	/*
	 * returns javascript objects of activities to be displayed on calendar
	 * @return string
	 */
	function get_activities_js(){	
				$field_list = CalendarUtils::get_fields();
				$a_str = "";				
				$ft = true;
				foreach($this->ActRecords as $act){
					if(!$ft)
						$a_str .= ",";						
					$a_str .= "{";		
					$a_str .= '
						"type" : "'.$act["type"].'", 
						"module_name" : "'.$act["module_name"].'",  
						"record" : "'.$act["id"].'",
						"user_id" : "'.$act["user_id"].'",
						"start" : "'.$act["start"].'",
						"time_start" : "'.$act["time_start"].'",
						"record_name": "'.$act["name"].'",'.
					'';
					foreach($field_list[$act['module_name']] as $field){
						if(!isset($act[$field]))
							$act[$field] = "";
						$a_str .= '	"'. $field . '" : "'.$act[$field].'",
					'; 
					}
					$a_str .=	'
						"detailview" : "'.$act["detailview"].'",
						"editview" : "'.$act["editview"].'"
					';
					$a_str .= "}";
					$ft = false;				
				}				
				return $a_str;
	}	
	
	/**
	 * initialize ids of shared users
	 */	
	function init_shared(){
		global $current_user;
		$user_ids = $current_user->getPreference('shared_ids');
		if(!empty($user_ids) && count($user_ids) != 0 && !isset($_REQUEST['shared_ids'])) {
			$this->shared_ids = $user_ids;
		}elseif(isset($_REQUEST['shared_ids']) && count($_REQUEST['shared_ids']) > 0){
			$this->shared_ids = $_REQUEST['shared_ids'];
			$current_user->setPreference('shared_ids', $_REQUEST['shared_ids']);
		}else{
			$this->shared_ids = array($current_user->id);				
		}
	}
	
	/**
	 * calculates count of timeslots per visible day, calculates day start and day end in minutes 
	 */	
	function calculate_day_range(){	
		
		list($hour_start,$minute_start) =  explode(":",$this->day_start_time);		
		list($hour_end,$minute_end) =  explode(":",$this->day_end_time);		

		$this->d_start_minutes = $hour_start * 60 + $minute_start;
		$this->d_end_minutes = $hour_end * 60 + $minute_end;		

		$this->celcount = 0;
		for($i = $hour_start; $i < $hour_end; $i++){
				for($j = 0; $j < 60; $j += $this->time_step){
					if($i*60+$j >= $hour_end*60 + $minute_end)
						break;
					$this->celcount++;
				}
		}
		$this->cells_per_day = 24 * (60 / $this->time_step);
	}
	
	/**
	 * loads array of objects
	 * @param User $user user object
	 * @param string $type
	 */	
	function add_activities($user,$type='sugar'){
		global $timedate;
		$start_date_time = $this->date_time;
		if($this->view == 'week' || $this->view == 'shared') {
			$end_date_time = $this->date_time->get("+7 days");
		}else if($this->view == 'month'){
			$start_date_time = $this->date_time->get('first day of last month');
			$end_date_time = $this->date_time->get('first day of next month');
			$end_date_time = $end_date_time->get("+7 days");
		}else{
			$end_date_time = $this->date_time;
		}
		
		$params = array(
				'show_calls' => $this->show_calls,
				'show_tasks' => $this->show_tasks,
		);

		$acts_arr = array();
	    	if($type == 'vfb'){
				$acts_arr = CalendarActivity::get_freebusy_activities($user, $start_date_time, $end_date_time);
	    	}else{
				$acts_arr = CalendarActivity::get_activities($user->id, $params, $start_date_time, $end_date_time, $this->view);
	    	}
	    	
	    	$this->acts_arr[$user->id] = $acts_arr;
	}


	function get_previous_date_str(){
		if ($this->view == 'month'){
		    $day = $this->date_time->get("-1 month")->get_day_begin(1);
		}else if ($this->view == 'week' || $this->view == 'shared'){
			// first day last week
			$day = $this->date_time->get("-7 days")->get_day_by_index_this_week(0)->get_day_begin();
		}else if ($this->view == 'day'){
			$day = $this->date_time->get("yesterday")->get_day_begin();
		}else if ($this->view == 'year'){
            		$day = $this->date_time->get("-1 year")->get_day_begin();
		}else{
			return "get_previous_date_str: notdefined for this view";
		}
		return $day->get_date_str();
	}

	function get_next_date_str(){
		if ($this->view == 'month'){
			$day = $this->date_time->get("+1 month")->get_day_begin(1);
		}else if ($this->view == 'week' || $this->view == 'shared' ){
			$day = $this->date_time->get("+7 days")->get_day_by_index_this_week(0)->get_day_begin();
		}else if ($this->view == 'day'){
			$day = $this->date_time->get("tomorrow")->get_day_begin();
		}else if ($this->view == 'year'){
			$day = $this->date_time->get("+1 year")->get_day_begin();
		}else{
			sugar_die("get_next_date_str: not defined for view");
		}
		return $day->get_date_str();
	}


}

?>
