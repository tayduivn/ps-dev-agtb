<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 38
 * Update contract
*/
//ini_set('display_error',1);
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once( 'custom/si_custom_files/MoofCartHelper.php' );
class updateContract {
	/*
	* This is the logic hook
	*/	
	function update(&$bean, $event, $arguments) {
		if($event != "after_save") return false;
		// agreement_type placeholder
		
		$acc = reset($bean->get_linked_beans('accounts_orders', 'Account', array(), 0, -1, 0));

		$opp = reset($bean->get_linked_beans('orders_opportunities', 'Opportunity',array(),0,-1,0));

		// these are place holders for the agreement and contracts
		$agreement_type = $_SESSION['agreement_type'];
		$contracts = $_SESSION['contracts'];
		
		if(empty($contracts)) {
			// no contracts
			return true;
		}
		
		// contract array placeholder
		foreach($contracts AS $c) {
			
			$fields = array();
			$get_doc = false;
			switch($agreement_type) {
				case 'clickthru':
					$fields['name'] = 'Clickthrough Agreement'; 
					$fields['agreement_type_c'] = 'MSA';
					$fields['execution_status_c'] = 'Fully Executed';
					$fields['order_id'] = $bean->id;
					$fields['start_date'] = date('Y-m-d');
					$fields['end_date'] = date('Y-m-d', strtotime('+' . $c['term'])); // ADD TERM
					$fields['customer_signed_date'] = date('Y-m-d');
					$fields['contact_term_c'] = $c['term']; // ADD TERM
					$fields['opportunity_id'] = $opp->id;
					$fields['account_id'] = $acc->id;
					$fields['assigned_to'] = $bean->assigned_user_id;
					$get_doc = true;
					break;
				case 'echosign':
					$fields['name'] = $c['filename']; // GET File name
					$fields['agreement_type_c'] = 'MSA';
					$fields['execution_status_c'] = 'Customer Executed';
					$fields['order_id'] = $bean->id;
					$fields['start_date'] = date('Y-m-d');
					$fields['end_date'] = date('Y-m-d', strtotime('+' . $c['term'])); // ADD TERM
					$fields['customer_signed_date'] = date('Y-m-d');
					$fields['contact_term_c'] = $c['term']; // ADD TERM
					$fields['opportunity_id'] = $opp->id;
					$fields['account_id'] = $acc->id;
					$fields['assigned_to'] = $bean->assigned_user_id;
					$get_doc = true;
					break;
				default:
					break;
			}
			// create contract
			$contract = new Contract();
			foreach($fields AS $key=>$val) {
				$contract->$key=$val;
			}
			$contract->save(FALSE);

			// create document record
			if($get_doc === true) {
				//create document
				$d = new Document();
				$d->document_name = $c['filename'];
				$d->status_id = 'Active';
				$d->active_date = date('Y-m-d');
				$d->team_set_id = 1;
				$d->team_id = 1;
				$d->created_by = $bean->created_by;
				$d->category_id = 'agreements';

				//get new document id
				$d_id = $d->save(FALSE);
				$d->retrieve($d_id);
		
				// create revision
				$dr = new DocumentRevision();
				$dr->revision = '1.0';
				$dr->filename = $c['filename'];
				$dr->document_id = $d_id;
				$dr->created_by = $bean->created_by;
				$file = explode('.', $c['filename']);
				$dr->file_ext = end($file);
				$dr->file_mime_type = 'application/x-' . end($file);
				$dr->deleted = 0;
				$dr->date_entered = date('Y-m-d H:i:s');
				$dr->date_modified = date('Y-m-d H:i:s');

				// get new document revision id
				$dr_id = $dr->save(FALSE);	


				// update document with new revision id
				$d->document_revision_id = $dr->id;
				$d->save(FALSE);
				
				$docs = $contract->get_linked_beans('contracts_documents','Document',array(),0,-1,0);
				
				$contract->contracts_documents->add($d->id);
				
				$contract->save(FALSE);
				
			}
			// attach purchasers contact to contract
			$contacts = $contract->get_linked_beans('contacts', 'Contact', array(), 0, -1, 0);
			$c = new Contact();
			$c->retrieve($bean->created_by);
			$contract->contacts->add($c->id);
			// products
			$contract->get_linked_beans('products','Product',array(),0,-1,0);
			$products = $bean->get_linked_beans('orders_products','Product',array(),0,-1,0);
			foreach($products AS $product) {
				$p = new Product();
				$p->retrieve($product->id);
				$contract->products->add($p->id);
			}
			$contract->save(FALSE);
		}
	}
}
?>