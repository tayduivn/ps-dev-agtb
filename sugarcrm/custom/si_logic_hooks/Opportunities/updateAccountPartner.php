<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 93 
 * Before save logic hook for updating account partner_assigned_to_new_c if different
*/

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
class updateAccountPartner {
        /*
        * This is the logic hook
        */
        function update(&$bean, $event, $arguments) {
                if($event != "before_save") return false;
                if(is_array($bean->fetched_row)) {
                        // Get the original sales stage
                        $orig_sales_stage = $bean->fetched_row['sales_stage'];

                        if($orig_sales_stage != 'Finance Closed' && $bean->sales_stage == 'Finance Closed' && !empty($bean->partner_assigned_to_new_c) ) {
                                $this->checkPartnerAccount($bean);
                        }
                        else {
                                // its not completed so return
                                return true;
                        }
                }
                // new order
                else {
                        if($bean->sales_stage == 'Finance Closed' && !empty($bean->partner_assigned_to_new_c)) {
                                $this->checkPartnerAccount($bean);
                        }
                        else {
                                // its not completed so retrun
                                return true;
                        }
                }
		return true;
	}

	function checkPartnerAccount(&$bean) {
		// GET ACCOUNT ATTACHED TO THIS OPP
		$acc = new Account();
		$acc->retrieve($bean->account_id);
		// If the partner_assigned_to's don't match	
		if($acc->account_id_c != $bean->account_id_c) {
			// set the opp's partner to be the account's partner
			$acc->account_id_c = $bean->account_id_c;
			// save that sucka
			$acc->save();
		}

	}


}
