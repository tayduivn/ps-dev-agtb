<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class ProjectTaskHooks  {

	// BEGIN jostrow customization
	// if the ProjectTask "Resource" value doesn't match the "Assigned To" value upon save through the Grid View, "Assigned To" will be overwritten
	// we want to prevent this for the MoofCart project so we can utilize the "Under Review" status
	// (e.g. the Resource is one person, but the ProjectTask is assigned to somebody else)

	function reassignProjectTask(&$bean, $event, $arguments) {
		if ($_REQUEST['action'] == 'SaveGrid' && $bean->assigned_user_id != $bean->fetched_row['assigned_user_id']) {
			$bean->assigned_user_id = $bean->fetched_row['assigned_user_id'];
		}
	}

	// END jostrow customization

}
