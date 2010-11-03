<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


class NoteAssignmentForCasePortal {

function assignNotes(&$bean, $event, $arguments) {

	global $current_user;

	if($event == "before_save"){
		if($bean->created_by == '1d72284d-6496-c5cb-4312-47433f21629b') {
			if (($bean->parent_type == "Cases") && !empty($bean->parent_id)) {
				require_once($beanFiles[$beanList[$bean->parent_type]]);
				$parent_bean = new $beanList[$bean->parent_type]();
				$parent_bean->disable_row_level_security = TRUE;
				$parent_bean->retrieve($bean->parent_id);

				$bean->assigned_user_id = $parent_bean->assigned_user_id;

				}
			}
		}	
	}
}
?>

