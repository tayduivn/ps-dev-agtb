<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 68
 * Before Save Logic Hook for orders to upgrade subs when the opportunity is Additional or Renewal and the order is completed
*/

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once( 'modules/Subscriptions/Subscription.php' );
require_once( 'custom/si_custom_files/MoofCartHelper.php' );
class updateSubscriptions {
	/*
	* This is the logic hook
	*/
	function update(&$bean, $event, $arguments) {
		if($event != "before_save") return false;

		$db = &DBManagerFactory::getInstance();

		// This is a check for existing order
	        if(isset($bean->order_id)) {
			// Get the original status
			$orig_status = $bean->fetched_row['status'];			

			if($orig_status != 'Completed' && $bean->status == 'Completed') {
				$opp = $this->checkOpportunity($bean,$db);
			}
			else {
				// its not completed so return
				return true;
			}
		}
		// new order
		else {
			if($bean->status == 'Completed') {
				$opp = $this->checkOpportunity($bean,$db);
			}
			else {
				// its not completed so retrun
				return true;
			}
		}
		// if the opp isn't additional or renewal false is returned
		if($opp === false) {
			// its not a opp to do stuff with so return
			return true;
		}
		// get the number of users attached to the product(s)
                $quantities = $this->getNumberOfUsers($bean,$db);

                if( empty( $quantities ) ) {
			// if there aren't any products of type subscription return
                        return true;
                }
		// get the account bean
		$acc = $this->getAccount($bean);
		// GET SUBSCRIPTION
		$sub = $this->getSubscription($db,$acc->id);

		// check if it is an upgrade and set the new users quantity if necessary
                $this->checkUpgradeStatus($bean,$sub,$opp, $quantities);
		// if its of type renewal
		if($opp->Revenue_Type_c == 'Renewal') {
			// enable it if its disabled
			$sub->status = ($sub->status == 'Disabled') ? 'Enabled' : 'Enabled';
			$sub->save();
			// set the new expiration
			$this->setSubscriptionExpiration($sub,$db,$opp->Term_c);
		}
                // return something for the heck of it
		return true;
	}
	/*
	* this is a function with similar functionality to the SoapCustomFunctions::auto_close_opp_completed_order in ./custom/si_custom_files/SoapCustomFunctions.php
	* it takes the order bean the sub, the opp and the quantities of users for the products
	*/
	function checkUpgradeStatus(&$bean, &$sub, &$opp, $quantities) {
		// get the distgroups associated to the subscription
                $distgroups = $sub->get_linked_beans('distgroups', 'DistGroup', array(), 0, -1, 0);
		// if it needs upgraded
                if (in_array($opp->opportunity_type, MoofCartHelper::$converge_opportunity_types)) {
       	        	$distgroup = $distgroups[0];
			// loop over teh quantities [in case there are more than 1 update]
			foreach($quantities AS $seats_purchased ) {
	                if (in_array($distgroup->id, array_keys(MoofCartHelper::$distgroup_converge_map))) {
                        		$new_distgroup = MoofCartHelper::$distgroup_converge_map[$distgroup->id];
                        		$new_distgroup_bean = new DistGroup();
                        		$new_distgroup_bean->retrieve($new_distgroup);
					// if its renewal set the quantity to the seats purchased and upgrade it to the new converge group
					if( $opp->Revenue_Type_c == 'Renewal' ) {
                        			$sub->distgroups->delete($sub->id, $distgroup->id);
                        			$sub->distgroups->add($new_distgroup, array('quantity' => $seats_purchased));
					}
					// if its additional set add the seats_purchased to the current quantity and upgrade it to the new convergence group
					elseif( $opp->Revenue_type_c == 'Additional' ) {
						$current_quantity = $distgroup->quantity;
						$sub->distgroups->delete( $sub->id, $distgroup->id );
						$sub->distgroups->add($new_distgroup, array('quantity' => $current_quantity + $seats_purchased));
					}
					unset( $new_distgroup, $new_distgroup_bean );
                        }
             
                        else {
                                        $distgroup = $distgroups[0];
					// if the quantity is different
                                        if ($distgroup->quantity != $seats_purchased) {
						// if its renewal set to the current seats_purchased
						if( $opp->Revenue_Type_c == 'Renewal' ) {
                                                	$sub->distgroups->add($distgroup->id, array('quantity' => $seats_purchased));
						}
						// if its additional add the current quantity to the new seats_purchased
						elseif( $opp->Revenue_Type_c == 'Additional' ) {
							$sub->distgroups->add($distgroup->id, array('quantity' => $distgroup->quantity + $seats_purchased));
						}
                                        }
                        }
			}
		}
	}

	/*
	* set the subscription expiration for a renewal
	* it takes the sub bean, the db and the opp term_c
	*/
	function setSubscriptionExpiration(&$sub,&$db,$term) {
		switch($term) {
			case 'Annual':
				$expiration_date = "1 YEAR";
				break;
			case 'Quarterly':
                                $expiration_date = "3 MONTH";
				break;
			case 'Semi-Annual':
                                $expiration_date = "6 MONTH";
				break;
			case 'Two Year':
                                $expiration_date = "2 YEAR";
				break;
			case 'Three Year':
                                $expiration_date = "3 YEAR";
				break;
			case 'Four Year':
                                $expiration_date = "4 YEAR";
				break;
			case 'Five Year':
                                $expiration_date = "5 YEAR";
				break;
		}
		// do the update of the expiration_date
		$update_query = "update subscriptions set ignore_expiration_date = 0, expiration_date = adddate(expiration_date, INTERVAL $expiration_date ) where id = '{$sub->id}'";
                $db->query($update_query);
		// set the audit for tracking purposes
                $audit_query = "insert into subscriptions_audit set id = '".create_guid()."', parent_id = '{$subscription_data['id']}', date_created = NOW(), created_by = '1', ".
                     "field_name = 'expiration_date', data_type = 'date', before_value_string = '{$subscription_data['expiration_date']}', after_value_string = adddate('{$subscription_data['expiration_date']}', INTERVAL $interval )";
                $db->query($audit_query);
	}


	function getNumberOfUsers(&$bean,&$db) {
		$return = array();
		// GET PRODUCTS
		$result = $db->query("	SELECT products.id, products.quantity 
                                        FROM products, orders_products_c, product_categories 
                                        WHERE   products.id = orders_products_c.orders_pro2902roducts_idb AND 
                                                products.category_id = product_categories.id AND 
                                                orders_products_c.orders_prob569sorders_ida = '{$bean->id}' AND
                                                product_categories.name = 'Subscriptions' AND
                                                products.deleted = 0"
					);

		// GET PRODUCT CATEGORY (SUBSCRIPTION)
		while($row=$db->fetchByAssoc($result)) {
			$return[$row['id']]=$row['quantity'];
		}

		// GET # OF USERS
		// RETURN IT	
		return $return;
	}

	/*
	* get the subscription bean by the account and return it
	*/
	function getSubscription(&$db,$account_id) {
		$result = $db->query("SELECT id FROM subscriptions WHERE account_id = '{$account_id}' AND deleted = 0");
		while($row = $db->fetchByAssoc($result)) {
			$subscription_id = $row['id'];
		}
		$sub = new Subscription();
		return $sub->retrieve($subscription_id);	
	}
	/*
	* get the opportunity and check if its additional or renewal return false if its not one of those types or return the bean
	*/
	function checkOpportunity(&$bean) {
		$opp_id = $bean->orders_opp02e0unities_idb;
		$opp_bean = new Opportunity();
		$opp_bean->retrieve($opp_id);
		if( $opp_bean->Revenue_Type_c == 'Additional' || $opp_bean->Revenue_Type_c == 'Renewal' ) {
			return $opp_bean;
		}
		return false;
	}

	/*
	* get the account bean and return it
	*/
	function getAccount(&$bean) {
		$acc_id = $bean->accounts_od749ccounts_ida;
		$acc_bean = new Account();
		$acc_bean->retrieve($acc_id);
		return $acc_bean;
	}
}
