<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 38
 * Update PO, attach to order
*/

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once( 'custom/si_custom_files/MoofCartHelper.php' );
class checkOrder {
	/*
	* This is the logic hook
	*/	
	function check(&$bean, $event, $arguments) {
		if($event != "after_save") return false;

		$order = reset($bean->get_linked_beans('orders_contracts','Orders',array(),0,-1,0));
		
		if(empty($order) || empty($order->fetched_row)) {
			$msg = "Contract doesn't have an order associated with it";
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);		
			return false;
		}
		
		$order->status = MoofCartHelper::determineOrderStatus($order);
		$order->save();
		
		
		/**
		 * @author Jim Bartek
		 * @project moofcart
		 * @tasknum 47
		 * After save logic hook for sync'ing to NetSuite if the SalesOps changed the order
		*/
		if($order->status == 'pending_salesops') {
			$opp = reset($order->get_linked_beans('orders_opportunities','Opportunity',array(),0,-1,0));
		
			if($opp->sales_stage='Sales Ops Closed') {
			    return MoofCartHelper::syncNetSuiteOrder($bean, $order);				
			}
		}
	}
}
?>
