<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 38
 * Update PO, attach to order
*/

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once( 'custom/si_custom_files/MoofCartHelper.php' );
class updatePO {
	/*
	* This is the logic hook
	*/	
	function update(&$bean, $event, $arguments) {
		if($event != 'after_relationship_add') return false;
		// no need to run it

		if($arguments['related_module'] != 'Orders') return false;

		if($bean->category_id != 'po') {
			return false;
		}


                //$order = reset($bean->get_linked_beans('orders_beanuments','Orders',array(),0,-1,0));

		$order = new $arguments['related_module'];
		$order->retrieve($arguments['related_id']);


		$GLOBALS['log']->info('jbartek --TRYING TO SET DOCUMENT JAZZ');
	
		$bean->status_id = 'Under Review';
			
		if(!empty($order)) {	
			$bean->document_name = "PO for Order #{$order->order_id} ({$bean->filename})";
			$order->status = MoofCartHelper::determineOrderStatus($order);
			$order->save();
		}
		else {
			$bean->document_name = "PO for Order ({$bean->filename})";
		}

		$GLOBALS['log']->info('jbartek --Saving Document--');
		//$bean->save();
		/**
			 * @author Jim Bartek
			 * @project moofcart
			 * @tasknum 47
			 * Before save logic hook for sync'ing to NetSuite if the SalesOps changed the order
			*/
			
			
			if(!empty($order) && $order->status == 'pending_salesops') {
				$opp = reset($order->get_linked_beans('orders_opportunities','Opportunity',array(),0,-1,0));
			
				if($opp->sales_stage='Sales Ops Closed') {
				    return MoofCartHelper::syncNetSuiteOrder($bean, $order);				
				}
			}			
			
		return true;
	}
}
?>
