<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once( 'custom/si_custom_files/MoofCartHelper.php' );
class DocumentOrderHooks {

	// THIS IS A BEFORE_SAVE HOOK ON THE DOCUMENTS MODULE
	function handleOrderRelationship(&$bean, $event, $arguments) {

		if($event != 'before_save') return false;

		if($bean->category_id != 'po') return false;

		$run_determineOrderStatusOn = array(
		    // the GUID (or GUIDs) of the Order(s) go here
		);


		// the internal field name for the relationship between Documents and Orders ... can be stolen from the HTML source of the Documents EditView
		$doc_to_order_field_name = 'orders_docd099sorders_ida';

		if(empty($bean->$doc_to_order_field_name) && !empty($_REQUEST['relate_id']) && $_REQUEST['relate_to'] == 'orders_documents') {
			$bean->$doc_to_order_field_name = $_REQUEST['relate_id'];
		}
		
		// okay, they've related this Document to an Order...
		if (!empty($bean->$doc_to_order_field_name)) {
//			exit('a');
			if (empty($bean->rel_fields_before_value) || empty($bean->rel_fields_before_value[$doc_to_order_field_name])) {
//				exit('b');
				// this is the first time relating the Document to the Order-- let's run our code!
				// MoofCart magic goes here...
				// The Order ID is in $bean->$doc_to_order_field_name
				$order = new Orders;
				$order->retrieve($bean->$doc_to_order_field_name);
				
				$run_determineOrderStatusOn[] = $bean->$doc_to_order_field_name;
				
				// If you want to modify the Document name (to put the Order Number in it), you should be able to just set $bean->document_name -- and no need to call $bean->save() because this is a before_save logic hook
			
//				echo "PO for Order #{$order->order_id} ({$bean->filename})";
//				exit();
				
				$bean->document_name = "PO for Order #{$order->order_id} ({$bean->filename})";
			}

			// they just CHANGED the Order that this Document is related to... handle it!
			elseif (!empty($bean->rel_fields_before_value) && $bean->rel_fields_before_value[$doc_to_order_field_name] != $bean->$doc_to_order_field_name) {
//				exit('c');
				// In this case, they just CHANGED which Order the Document was related to... so we need to recalculate the Order Status of both of them!
			
				// old Order ID (that doesn't have the document anymore): $bean->rel_fields_before_value[$doc_to_order_field_name]
				// new Order ID (that now has the Document): $bean->$doc_to_order_field_name
				$run_determineOrderStatusOn[] = $bean->$doc_to_order_field_name;
				$run_determineOrderStatusOn[] = $bean->rel_fields_before_value[$doc_to_order_field_name];
								
				$order = new Orders;
				$order->retrieve($bean->$doc_to_order_field_name);
				// If you want to modify the Document name (to put the Order Number in it), you should be able to just set $bean->document_name -- and no need to call $bean->save() because this is a before_save logic hook
				$bean->document_name = "PO for Order #{$order->order_id} ({$bean->filename})";
			}


			else {
//				exit('d');
				// they were already related... do nothing (just providing this else case for context)
			}

		}

		// the relationship field for Orders is empty... but maybe they just removed the relationship?
		else {
//			exit('e');
			if (!empty($bean->rel_fields_before_value) && !empty($bean->rel_fields_before_value[$doc_to_order_field_name])) {
//				exit('f');
				// They just removed the relationship with the Order... so we should redetermine the Order status

				// Order ID is ... $bean->rel_fields_before_value[$doc_to_order_field_name]
				$run_determineOrderStatusOn[] = $bean->rel_fields_before_value[$doc_to_order_field_name];

				// resetting the document name to the filename of the document
				$bean->document_name = $bean->filename;

			}
			else {
//				exit('g');
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
	    $GLOBALS['db']->query("UPDATE documents_cstm SET run_determineorderstatuson_c = '' WHERE id_c = '{$bean->id}'");	
		
	}
	
}