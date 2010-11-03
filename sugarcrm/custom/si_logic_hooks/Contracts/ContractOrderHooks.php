<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once( 'custom/si_custom_files/MoofCartHelper.php' );
class ContractOrderHooks {

	// THIS IS A BEFORE_SAVE HOOK ON THE DOCUMENTS MODULE
	function handleOrderRelationship(&$bean, $event, $arguments) {

		if($event != 'before_save') return false;

		$run_determineOrderStatusOn = array(
		    // the GUID (or GUIDs) of the Order(s) go here
		);


		// the internal field name for the relationship between Documents and Orders ... can be stolen from the HTML source of the Documents EditView
		$contract_to_order_field_name = 'orders_con055dsorders_ida';

		if(empty($bean->$contract_to_order_field_name) && !empty($_REQUEST['relate_id']) && $_REQUEST['relate_to'] == 'orders_contracts') {
			$bean->$contract_to_order_field_name = $_REQUEST['relate_id'];
		}


		// okay, they've related this Document to an Order...
		if (!empty($bean->$contract_to_order_field_name)) {


			if (empty($bean->rel_fields_before_value) || empty($bean->rel_fields_before_value[$contract_to_order_field_name])) {
				// this is the first time relating the Document to the Order-- let's run our code!
				// MoofCart magic goes here...
				// The Order ID is in $bean->$doc_to_order_field_name
				$order = new Orders;
				$order->retrieve($bean->$contract_to_order_field_name);
				
				$run_determineOrderStatusOn[] = $bean->$contract_to_order_field_name;
			}

			// they just CHANGED the Order that this Document is related to... handle it!
			elseif (!empty($bean->rel_fields_before_value) && $bean->rel_fields_before_value[$contract_to_order_field_name] != $bean->$contract_to_order_field_name) {
				// In this case, they just CHANGED which Order the Document was related to... so we need to recalculate the Order Status of both of them!
			
				// old Order ID (that doesn't have the document anymore): $bean->rel_fields_before_value[$doc_to_order_field_name]
				// new Order ID (that now has the Document): $bean->$doc_to_order_field_name
				$run_determineOrderStatusOn[] = $bean->$contract_to_order_field_name;
				$run_determineOrderStatusOn[] = $bean->rel_fields_before_value[$contract_to_order_field_name];
			}


			else {
				// they were already related... do nothing (just providing this else case for context)
			}

		}

		// the relationship field for Orders is empty... but maybe they just removed the relationship?
		else {

			if (!empty($bean->rel_fields_before_value) && !empty($bean->rel_fields_before_value[$contract_to_order_field_name])) {
				// They just removed the relationship with the Order... so we should redetermine the Order status

				// Order ID is ... $bean->rel_fields_before_value[$doc_to_order_field_name]
				$run_determineOrderStatusOn[] = $bean->rel_fields_before_value[$contract_to_order_field_name];
			}
			else {
				// no action required -- the relationship was empty before, and is empty
			}

		}
		
		if(!empty($run_determineOrderStatusOn)) {
			$bean->run_determineorderstatuson_c = implode(',',$run_determineOrderStatusOn);
		}
	}
	
	// THIS IS A after_save HOOK ON THE DOCUMENTS MODULE
	function handleOrderStatus(&$bean, $event, $arguments) {

		if($event != 'after_save') return false;
		
		
		if(empty($bean->run_determineorderstatuson_c)) {
			// nothing to do
			return false;
		}

		// get an array of the order id's
		$run_determineOrderStatusOn = explode(',', $bean->run_determineorderstatuson_c);

		// loop over each
		foreach($run_determineOrderStatusOn as $order_id) {
			// create the orders object
			$o = new Orders();
			$o->retrieve($order_id);
			// pass in the orders object to the determinOrderStatus [returns the status it should be]
			$o->status = MoofCartHelper::determineOrderStatus($o);
			$o->save();
		}
		
		// SQL Query so the After_save hook doesn't start all over again.
	    $GLOBALS['db']->query("UPDATE contracts_cstm SET run_determineorderstatuson_c = '' WHERE id_c = '{$bean->id}'");	
		
	}
	
}