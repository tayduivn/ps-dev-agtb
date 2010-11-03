<?php

require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Tasks/Task.php');

function closeOpportunity($opp_id, $after_sales_stage, $reason){
	$after_sales_stage_display = $after_sales_stage;
	if($after_sales_stage == 'Closed Won')
		$after_sales_stage_display = 'Sales Rep Closed';
	
	$opp = new Opportunity();
	$opp->retrieve($opp_id);
	$opp->sales_stage = $after_sales_stage;
	$opp->probability = $GLOBALS['app_list_strings']['sales_probability_dom'][$after_sales_stage];
	if(!isset($opp->description)){
		$opp->description = '';
	}
	$opp->description .= "\n--\nSet to sales stage '$after_sales_stage_display' on ".date("Y-m-d H:i")." by {$GLOBALS['current_user']->user_name}";
	if(!empty($reason)){
		$opp->description .= "\nReason: $reason";
	}
	$opp->save();
	unset($opp);
}

function rejectOpportunity($opp_id, $before_sales_stage, $reason, $assign_task_to = ''){
	$before_sales_stage_display = $before_sales_stage;
	if($before_sales_stage == 'Closed Won')
		$before_sales_stage_display = 'Sales Rep Closed';
	
	$opp = new Opportunity();
	$opp->retrieve($opp_id);
	if(!isset($opp->description)){
		$opp->description = '';
	}
	$opp->sales_stage = $before_sales_stage;
	$opp->description .= "\n--\nPushed back to sales stage '$before_sales_stage_display' on ".date("Y-m-d H:i")." by {$GLOBALS['current_user']->user_name}";
	if(!empty($reason)){
		$opp->description .= "\nReason: $reason";
	}
	$opp->save();
	
	if(!empty($assign_task_to)){
		$email_reports_to = false;
		if($assign_task_to == 'opp_owner'){
			$email_reports_to = true;
			$assign_task_to = $opp->assigned_user_id;
		}
		$task = new Task();
		$task->name = "Clean Opp: {$opp->name}";
		$task->description = "This opportunity has been set back to '$before_sales_stage_display'";
		$task->status = 'Not Started';
		$task->assigned_user_id = $assign_task_to;
		$task->sales_ops_task_c = '0';
		if(!empty($reason)){
			$task->description .= "\n\nReason: $reason";
		}
		$task->save(true);
		$task->sales_ops_task_c = '0';
		$task->load_relationship("opportunities");
		$task->opportunities->add($opp->id);
		$task->save();
		
		// NEW CODE FOR ITREQUEST 2221 - Emails Reports to when the opp is rejected
		if($email_reports_to){
			$res = $GLOBALS['db']->query("select reports_to_id from users where id = '$assign_task_to'");
			$row = $GLOBALS['db']->fetchByAssoc($res);
			if(!empty($row['reports_to_id'])){
				require_once("modules/Administration/Administration.php");
				$admin = new Administration();
				$admin->retrieveSettings();
				$reports_to = new User();
				$reports_to->retrieve($row['reports_to_id']);
				if(!empty($reports_to->id)){
					$task->send_assignment_notifications($reports_to, $admin);
				}
			}
		}
		// END NEW CODE FOR ITREQUEST 2221
	}
	
	unset($opp);
	unset($task);
}
