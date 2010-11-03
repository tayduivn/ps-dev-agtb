<?php

class ps_TimesheetsController extends SugarController
{
	function action_getTimesheets() {
		global $timedate, $current_user, $app_list_strings;
		require_once('include/json_config.php');
		require_once('include/formbase.php');
		
		$json = getJSONobj();
		
		if(isset($_REQUEST['month']) && !empty($_REQUEST['month'])) {
			$month = $_REQUEST['month'];
		}
		
		if(isset($_REQUEST['year']) && !empty($_REQUEST['year'])) {
			$year = $_REQUEST['year'];
		}
		
		if(isset($_REQUEST['user_id']) && !empty($_REQUEST['user_id'])) {
			$resultArray = array();
/*
** @author: DTam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #:17974
** Description: Timeshssets module enhancements
** Wiki customization page: 
*/
			$this->bean->load_relationship('ps_timesheets_tasks');
			$lhs_key = $this->bean->ps_timesheets_tasks->_relationship->join_key_lhs;
			$rhs_key = $this->bean->ps_timesheets_tasks->_relationship->join_key_rhs;
			$join_table = $this->bean->ps_timesheets_tasks->_relationship->join_table;
			$sql = "SELECT ts.id, ts.activity_date, ts.activity_type, ts.time_spent, ts.description, ta.name as task_name, ta.id as task_id ".
				"FROM ps_timesheets ts LEFT JOIN {$join_table} ta_ts ON ts.id = ta_ts.{$rhs_key} ".
				"LEFT JOIN tasks ta ON ta.id = ta_ts.{$lhs_key} WHERE ts.deleted = 0 AND ts.assigned_user_id = '{$_REQUEST['user_id']}' ";

			if(!empty($month)) $sql .= "AND MONTH(ts.activity_date) = '{$month}' ";
			if(!empty($year)) $sql .= "AND YEAR(ts.activity_date) = '{$year}' ";
			
			$sql .= "ORDER BY ts.activity_date ASC";
			
			$result = $this->bean->db->query($sql);
			while($row = $this->bean->db->fetchByAssoc($result)) {
				$resultArray[] = array(
					'id' => $row['id'],
					'activity_date' => $timedate->to_display_date($row['activity_date'], false),
					'task_name' => $row['task_name'],
					'task_id' => $row['task_id'],
					'activity_type' => $app_list_strings['activity_type_list'][$row['activity_type']],
					'time_spent' => $row['time_spent'],
					'description' => $row['description'],
				);
			}
			//var_dump($_REQUEST);
			$resultJSON = $json->encode($resultArray);
			echo $resultJSON;
		}
		else {
			echo "{}";
		}
	}
	
	function action_saveTimeEntry(){
		global $timedate, $current_user, $app_list_strings;
		require_once('include/json_config.php');
		require_once('include/formbase.php');
		
		$json = getJSONobj();

		if(isset($_REQUEST['removed_row']) && !empty($_REQUEST['removed_row'])) {
			$this->bean->mark_deleted($_REQUEST['removed_row']);
			$resultArray = array('id'=>$_REQUEST['removed_row']);
			$resultJSON = $json->encode($resultArray);
			echo $resultJSON;
			die();
		}
		
		populateFromPost('', $this->bean);
		$this->bean->assigned_user_id = $current_user->id;
		//$this->bean->name = "Timesheet entry for ".$current_user->full_name;
		$this->bean->save();
		
		/*
		if(isset($_REQUEST['account_id']) && !empty($_REQUEST['account_id'])) {
			$account_id = $_REQUEST['account_id'];
			$account_name = $_REQUEST['account_name'];
			$this->bean->load_relationship('ps_timesheets_accounts');
			$this->bean->ps_timesheets_accounts->add($_REQUEST['account_id']);
		}
		*/
		
		if(isset($_REQUEST['task']) && !empty($_REQUEST['task'])) {
			$task_id = $_REQUEST['task'];
			$this->bean->load_relationship('ps_timesheets_tasks');
			$this->bean->ps_timesheets_tasks->add($_REQUEST['task']);
			$task = new Task();
			$task->retrieve($task_id);
			$task_name = $task->name;
		}
		
		$resultArray = array(
			'id' => $this->bean->id,
			'activity_date' => $timedate->to_display_date($this->bean->activity_date, false),
			'task_name' => $task_name,
			'task_id' => $task_id,
			'activity_type' => $app_list_strings['activity_type_list'][$this->bean->activity_type],
			'time_spent' => $this->bean->time_spent,
			'description' => $this->bean->description,
		);
		/*** END SUGARINTERNAL CUSTOMIZATION ***/
		$resultJSON = $json->encode($resultArray);
		echo $resultJSON;
	}
	
}