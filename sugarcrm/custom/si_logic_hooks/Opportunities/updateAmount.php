<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 47
 * Before save logic hook for sync'ing to NetSuite if the SalesOps changed the order
*/

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('custom/si_custom_files/MoofCartHelper.php');
class updateAmount {
    /*
    * This is the logic hook
    */
    function update(&$bean, $event, $arguments) {
		global $current_user;
	
	    if($event != "before_save") return false;

		// not sales ops don't run
		if(!$current_user->check_role_membership("Sales Operations") && !$current_user->check_role_membership("Sales Operations Opportunity Admin")) {
			return false;
		}

	    $order = reset($bean->get_linked_beans('orders_opportunities','Orders',array(),0,-1,0));

		//no order do nothing
		if(empty($order)) {
			return false;
		}

		// not the correct sales stage do nothing
		if($bean->sales_stage != 'Sales Ops Closed') {
			return false;
		}

		// amount didn't changed don't run
		if($bean->amount == $bean->fetched_row['amount']) {
			return false;
		}
		// log that its calling Moofy-goodness
	    return MoofCartHelper::syncNetSuiteOrder($bean, $order);
	}
}
?>