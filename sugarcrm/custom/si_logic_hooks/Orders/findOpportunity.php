<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 36
 * After save find an opportunity or create one
 */
if (!defined('sugarEntry') || !sugarEntry){ die('Not A Valid Entry Point'); }
require_once('custom/si_custom_files/MoofCartHelper.php');
class findOpportunity
{
    /*
     * This is the logic hook
     */
    function find(&$bean, $event, $arguments)
    {
    
        if ($event != "after_save") return false;

		// ORDER IS DONE SEND THE RECEIPT
		$GLOBALS['log']->info(__FILE__.'::'.__FUNCTION__. ' - Calling sendOrderReceipt');		
		MoofCartHelper::sendOrderReceipt($bean);


		if(empty($bean->fetched_row)) {
			//mail("jbartek@sugarcrm.com","Order Empty",print_r($bean,true),"From: Jim <jbartek@sugarcrm.com>");
			$msg = "Order Empty";
			$GLOBALS['log']->fatal(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
			return false;
		}


		//mail("jbartek@sugarcrm.com",":: Orders ::",print_r($bean,true),"From: Jim <jbartek@sugarcrm.com>");

		if(!empty($bean->orders_opp02e0unities_idb)) {
			$msg = "Order: {$bean->id} already has an opportunity: {$bean->orders_opp02e0unities_idb}";
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);			
			return true;
		}

        // get the products
        $products = $bean->get_linked_beans('orders_products', 'Product', array(), 0, -1, 0);

        // get the account
        $acc = $this->getAccount($bean);


		if(empty($acc)) {
			//mail("jbartek@sugarcrm.com","Account Empty",print_r($acc,true),"From: Jim <jbartek@sugarcrm.com>");
			$msg = "Account Empty for Order: {$bean->id}";
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
			return false;
		}




	if($bean->blue_bird_c == 1 || empty($acc->Support_Service_Level_c)) {
		$acc->Support_Service_Level_c = 'standard';
		foreach($products AS $product) {
			if(isset(MoofCartHelper::$productToSupportUsers[$product->product_template_id])) {
				$acc->Support_Service_Level_c = MoofCartHelper::$productToSupportUsers[$product->product_template_id];
			}
		}

		$acc->save();
	}


		if(empty($products)) {
			//mail("jbartek@sugarcrm.com","product Empty",print_r($products,true),"From: Jim <jbartek@sugarcrm.com>");
			$msg = "Products Empty for Order: {$bean->id}";
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
			return false;
		}

        // get the filters
        $filters = $this->getFilters($bean, $acc, $products);

		if($filters == false) {
			//mail("jbartek@sugarcrm.com","Filters Empty",print_r($filters,true),"From: Jim <jbartek@sugarcrm.com>");
			$msg = "Filters Empty for Order: {$bean->id}";
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
			return false;
		}

		if($bean->blue_bird_c == 1) {
			//mail("jbartek@sugarcrm.com","Blue Bird Started","Blue Bird Started","From: Jim <jbartek@sugarcrm.com>");
			$msg = "Creating Blue Bird Opp for Order: {$bean->id}";
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);		
			$this->createOpp($bean,$filters,true);
			return true;
		}

        // get all account opportunities
        $opps = $acc->get_linked_beans('opportunities_accounts', 'Opportunity', array(), 0, -1, 0);
		        
        // if there aren't any
        if (count($opps) == 0) {
	        // create new Opp using filters
	    	$new = ($bean->blue_bird_c == 1) ? true : false;
			//mail("jbartek@sugarcrm.com","Creating new opp Started","Creating new opp","From: Jim <jbartek@sugarcrm.com>");
			$msg = "No opportunities for Account: {$acc->id} - Creating a new one for Order: {$bean->id}";
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);	    	
	        $this->createOpp($bean, $filters, $new);
	        return true;
        }
            // find a valid opp
        else {
            $valid_opps = array();
            foreach ($opps AS $opp_bean) {
                if ($opp_bean->opportunity_type == $filters['opportunity_type'] && $opp_bean->Revenue_Type_c == $filters['Revenue_Type_c']) {
                    // create relationship it matches
                    $valid_opps[] = $opp_bean;
                }
            }
        }


        // there can be only one...update it
        if (count($valid_opps) == 1) {
            /**
             * @var $opp_bean Opportunity
             */
            $opp_bean = reset($valid_opps);

			$msg = "Found a valid opp for Account: {$acc->id} - Opp ID: {$opp_bean->id} - Updating It for Order : {$bean->id}";
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);	    	             

			$bean->orders_opp02e0unities_idb = $opp_bean->id;
			$bean->save();

		//	mail("jbartek@sugarcrm.com","Updating Opportunity","Updating Opportunity","From: Jim <jbartek@sugarcrm.com>");
	    	$this->updateOpportunity($bean, $opp_bean, $filters);
 
            if ($opp_bean->Revenue_Type_c == "Renewal") {
                $sublist = $bean->get_linked_beans('orders_subscriptions', 'Subscription', array(), 0, 1, 0);
                // get contract
                
                $contract = reset($bean->get_linked_beans('orders_contracts', 'Contract',array(),0,1,0));
                
                $term = $contract->end_date;
                
                /**
                 * @var $sub Subscription
                 */

                foreach ($sublist as $sub) {
                    if ($sub->status == 'disabled' && strtotime($sub->expiration_date) < strtotime('-30 days')) {
                        // has not renewed with in 30 days
                        // create a new subscription and create a new opp
                        $n_sub = new Subscription();
                        $n_sub->subscription_id = md5($acc->name . '-' . time());
                        $n_sub->status = 'enabled';
                        
                        // set expiration date to the contract term;
                        $n_sub->expiration_date = $term;
                        
                        $n_sub->account_id = $acc->id;
                        $n_sub->perpetual = $sub->perpetual;
                        $n_sub->audited = $sub->audited;
                        $n_sub->ignore_expiration_date = $sub->ignore_expiration_date;
                        $n_sub->enforce_portal_users = $sub->enforce_portal_users;
                        $n_sub->enforce_user_limit = $sub->enforce_user_limit;
                        $n_sub->portal_users = $sub->portal_users;

                        $n_id = $n_sub->save();
		
		                $distgroups = $sub->get_linked_beans('distgroups', 'DistGroup', array(), 0, -1, 0);
					
						$distgroup = $distgroups[0];
						
						$n_sub->load_relationship('distgroups');
									
                       	$n_sub->distgroups->add($distgroup->id);
					
                        $this->createOpp($bean, $filters);

                        $opp_bean->sales_stage = "Close Lost";
                        $opp_bean->closed_lost_reason_c = "Existing Customer";
                        $opp_bean->closed_lost_description = "Subscription was not renewed with in 30 days of the expiration date.  Close the found opp and created a new one.";
                        $o_id = $opp_bean->save(false);
						
                        $bean->orders_subb9eaiptions_idb = $n_sub->id;
						
						// set the old sub as deleted
                        $sub->deleted = 1;
                        $sub->save();
                    }
                }
            } 
        }
        else {
            // create new Opp using filters
            $this->createOpp($bean, $filters);        	
        }

		return true;
    }

    // update the current opportunity
    function updateOpportunity(&$bean, &$opp_bean, $filters)
    {
        // attach it to the order
        $opp_bean->orders_id = $bean->id;

        foreach ($filters AS $key => $val) {
            $opp_bean->$key = $val;
        }

        if (!$opp_bean->save(FALSE)) {
			$msg = "Update Opportunity Failed for Opp: {$opp_bean->id} for Order: {$bean->id}";
            return false;
        }
        
        
        return true;
    }

    // create opp
    function createOpp(&$bean, $filters, $new=false)
    {
        // setup the creation array based on the filters and some other stuff
        $create = $filters;
        $create['orders_id'] = $bean->id;
        
        if($new === true) {
       		$create['Revenue_Type_c'] = 'New';
        	$create['sales_stage'] = 'Closed Won';
        	
        	$bean->assigned_user_id = MoofCartHelper::$salesop_id;
			$bean->save();
		
			$create['assigned_user_id'] = MoofCartHelper::$salesop_id;
        }
		else {
			$create['assigned_user_id'] = $bean->assigned_user_id;
		}
		
	//	mail("jbartek@sugarcrm.com","Creating Opportunity",print_r($create,true),"From: Jim <jbartek@sugarcrm.com>");

        // create the new opportunity

        $opp = new Opportunity;
        foreach ($create AS $key => $val) {
            $opp->$key = $val;
        }


        if (!$opp_id = $opp->save(FALSE)) {
	//		mail("jbartek@sugarcrm.com","Products Empty","Opp Save Failed For Order:{$bean->id}","From: Jim <jbartek@sugarcrm.com>");
			$msg = "Create Opporutnity Failed for Order: {$bean->id}";
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);        
            return false;
        }

//		mail("jbartek@sugarcrm.com","New Opportunity ID","New Opportunity ID: {$opp->id}","From: Jim <jbartek@sugarcrm.com>");

		$bean->orders_opp02e0unities_idb = $opp->id;
		$bean->save();



        return true;
    }

    // get the filters
    function getFilters(&$bean, &$acc, $products = array())
    {
        // there aren't any products
        if (empty($products)) {
//			mail("jbartek@sugarcrm.com","Products Empty","Products empty","From: Jim <jbartek@sugarcrm.com>");
            return false;
        }
        // initialize the filters array
        $filters = array();

        // get the priorities and flip'em so its easier to deal with
        $priority = array_flip(MoofCartHelper::$productToOpportunityPriority);

        // initialize the current priority
        $current_priority = array();

        $data = array();

        // loop over the products
        foreach ($products AS $product) {
            if (!empty($product->product_template_id)) {
                // set the priority
                $current_priority[$product->product_template_id] = $priority[$product->product_template_id];
                $users = (int) $product->quantity;
                $data[$product->product_template_id] = array('users' => $users,
                    'Term_c' => $product->term_c,
                    'name'	 =>	$product->name,
					'product_template_id'	=>	$product->product_template_id,
                );
            }
        }
		
		if($bean->cart_action_c == 'add_users' || $bean->cart_action->c == 'add_support' || $bean->cart_action->c == 'upgrade_enterprise') {
			$filters['Revenue_Type_c'] = 'Additional';
			$filters['Term_c'] = 'Remainder of Term';
		}
		else {
	        switch ($data[key($current_priority)]['Term_c']) {
	            case 1:
	                $filters['Term_c'] = 'Annual';
	                break;
	            case 2:
	                $filters['Term_c'] = 'Two Year';
	                break;
	            case 3:
	                $filters['Term_c'] = 'Three Year';
	                break;
				case 4:
					$filters['Term_c'] = 'Four Year';
					break;
				case 5:
					$filters['Term_c'] = 'Five Year';
					break;
	        }
	        
	        if($bean->cart_action_c == 'renew') {
	        	$filters['Revenue_Type_c'] = 'Renewal';
	        }
	        else{
	        	$filters['Revenue_Type_c'] = 'New';
	        }
		}	
		
        // sort the array maintaining the keys
        asort($current_priority);

        // get the first one
        reset($current_priority);

        // get the opp type from the first ones key
        $filters['opportunity_type'] = MoofCartHelper::$productToOpportunityType[key($current_priority)];

        // get users :: this is the "Subscriptions" label, explicitly set to 0.
        $filters['users'] = (isset($data[key($current_priority)]['users'])) ? $data[key($current_priority)]['users'] : 0;



        // if partner [another placeholder field] set partner assigned to
        if(isset($bean->account_id_c)) {
            $filters['partner_assigned_to_c'] = $bean->account_id_c;
        }

        // the orders assigned user id will be set correctly by the after purchase orders logic hook, therefore we should be able to copy that one for the opp and be done.
        $filters['assigned_user_id'] = $bean->assigned_user_id;

        $filters['amount'] = $bean->total;

        $acc = reset($bean->get_linked_beans('accounts_orders', 'Account', array(), 0, -1, 0));

        $filters['account_id'] = $acc->id;

		$filters['sales_stage'] = 'Closed Won';
		
		$filters['opportunity_type'] = MoofCartHelper::$productToOpportunityType[$data[key($current_priority)]['product_template_id']];
		
		$filters['expected_close_date'] = date('Y-m-d');
		
		$filters['team_id'] = 1;
		
		$filters['team_set_id'] = 1;

		$filters['order_number'] = $bean->order_id;

		// let's let 'em know where this came from!!
		$filters['processed_by_moofcart_c'] = 1;

		$name = '';
		
		$partner_name = '';
		
		if($bean->account_id_c) {
			$partner_acc = new Account();
			$partner_acc->retrieve($bean->account_id_c);
			$partner_name = $partner_acc->name . ' - ';
		}
		
		$find_array = array(
		 'Extended Support - 90 day'=>  'Ext Sup(90)',                         
		 'Extended Support - annual'=>  'Ext Sup',     
		 'Premium support'=> 'Prem Sup',        
		 'Sugar Enterprise'=> 'Ent',                       
		 'Sugar Professional'=>   'Pro',		
		);
		
/*		Don't think this is needed anymore

		$con = reset($bean->get_linked_beans('orders_contracts','Contract',array(),0,-1,0));
		if(empty($con)) {
			mail("jbartek@sugarcrm.com","Contracts Empty","Contracts empty","From: Jim <jbartek@sugarcrm.com>");		
			$msg = "Contracts Empty for Order: {$bean->id}";
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__ . ' - ' . $msg);
			return false;
		}
*/


		global $app_list_strings;
		
		$name = $partner_name . ' ' . $acc->name . ' ' . $filters['users'] . ' ' . $find_array[$data[key($current_priority)]['name']];

		unset($data[key($current_priority)]);

		$counter = 1;
		foreach($data AS $name_info) {
			$prefix = ' ';
			if($counter>0) {
				$prefix = ' + ';
			}
			
			if(isset($find_array[$name_info['name']])) {
				$name .= $prefix . $find_array[$name_info['name']];
				$counter++;
			}
		}
		
//		$name = $filter['users'] . ' ' . $find_array[$data[key($current_priority)]['name']] . ':' . $partner_name;
		
		$filters['name'] = $name;
		
		mail("jbartek@sugarcrm.com","Filters",print_r($filters,true),"From: Jim <jbartek@sugarcrm.com>");
		
//		mail("jbartek@sugarcrm.com", "Product - Primary", print_r($data[key($current_priority)], true), "From: Jim <jbartek@sugarcrm.com>");
		
//		mail("jbartek@sugarcrm.com","Products",print_r($products,true),"From: Jim <jbartek@sugarcrm.com>");
		
		
        // return the filters
        return $filters;
    }

    /*
     * get the account bean and return it
     */
    function getAccount(&$bean)
    {
        $acc_bean = reset($bean->get_linked_beans('accounts_orders', 'Account', array(), 0, 1, 0));
//		mail("jbartek@sugarcrm.com","Account get_linked_beans",print_r($bean->get_linked_beans('accounts_orders', 'Account', array(), 0, 1, 0),true),"From: Jim <jbartek@sugarcrm.com>");
        return $acc_bean;
    }
}
