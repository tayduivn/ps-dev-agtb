<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class BugHooks  {
	function assignmentRules(&$bean, $event, $arguments) {
		global $current_user;
		
		// If the user is a member of the product management role, we don't force assignment
		if($current_user->check_role_membership('Product Management')){
			return;
		}
		
		if($event == "before_save"){
			if (empty($bean->fetched_row)) { // For new records only
				if(isset($bean->type) && $bean->type == 'Feature'){
					// Assign it to the ProductManagement user
					$bean->assigned_user_id = '657e94d6-25ec-f7f0-5eff-47e197ddf83f';
				}
			}
		}
	}
}
