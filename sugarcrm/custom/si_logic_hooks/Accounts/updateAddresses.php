<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 47
 * Before save logic hook for sync'ing to NetSuite if the SalesOps changed the order
*/

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('custom/si_custom_files/MoofCartHelper.php');
class updateAddresses {
    /*
    * This is the logic hook
    */
    function update(&$bean, $event, $arguments) {
		global $current_user;
	
	    if($event != "before_save") return false;
		
		$check_addresses = array(
									'billing_address_street',
									'billing_address_city',
									'billing_address_state',
									'billing_address_postalcode',
									'billing_address_country',
									'shipping_address_street',
									'shipping_address_city',
									'shipping_address_state',
									'shipping_address_postalcode',
									'shipping_address_country',
								);
		
		$changes = false;
		foreach($check_addresses AS $field) {
			if($bean->$field != $bean->fetched_row[$field]) {
				$changes = true;
			}
		}
		
		if($changes === false) {
			// nothing changed
			return false;
		}
		
		$opps = $bean->get_linked_beans('opportunities_accounts','Opportunity',array(),0,-1,0);
		
		foreach($opps AS $opp) {
			if($opp->sales_stage == 'Sales Ops Closed') {
				// get order
				$order = reset($opp->get_linked_beans('orders_opportunities','Orders',array(),0,-1,0));
			    return MoofCartHelper::syncNetSuiteOrder($bean, $order);				
			}
		}
	}
}
?>