<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 37
 * After save check account / contact with order
*/
ini_set('display_errors',1);
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once( 'custom/si_custom_files/MoofCartHelper.php' );
class accountContactCheck {

	/*
	* This is the logic hook
	*/
	
	// account fields that need checked
	private $verify_account_fields = array(	'billing_address_street'		=>	'billing_address',
											'billing_address_city'			=>	'billing_city',
											'billing_address_state'			=>	'billing_state',
											'billing_address_postalcode'	=>	'billing_zip_code',
											'billing_address_country'		=>	'billing_country',
											'shipping_address_street'		=>	'shipping_address',
											'shipping_address_city'			=>	'shipping_city',
											'shipping_address_state'		=>	'shipping_state',
											'shipping_address_postalcode'	=>	'shipping_zip_code',
											'shipping_address_country'		=>	'shipping_country',
											'Support_Service_Level_c'		=>	false,
										);
	// contact fields that need checked
	private $verify_contact_fields = array(	'primary_address_street'		=>	'billing_address',
											'primary_address_city'			=>	'billing_city',
											'primary_address_state'			=>	'billing_state',
											'primary_address_postalcode'	=>	'billing_zip_code',
											'primary_address_country'		=>	'billing_country',
											'alt_address_street'			=>	'shipping_address',
											'alt_address_city'				=>	'shipping_city',
											'alt_address_state'				=>	'shipping_state',
											'alt_address_postalcode'		=>	'shipping_zip_code',
											'alt_address_country'			=>	'shipping_country',
										);
	// set if the account type may need changed
	private $account_type = false;
	
	// the logic hook
	function check(&$bean, $event, $arguments) {
		if($event != "after_save") return false;

		// get the products
		$products = $bean->get_linked_beans('orders_products','Product',array(),0,-1,0);
		// does support need to change?
		$change_support = false;
		// go through the products
		foreach($products AS $pbean) {
			// check if support needs to change
			if(isset(MoofCartHelper::$productToSupportUsers[$pbean->product_template_id])) {
				$this->verify_account_fields['Support_Service_Level_c'] = MoofCartHelper::$productToSupportUsers[$pbean->product_template_id];
				$change_support = true;
			}
			// check if account type needs to change
			if(isset(MoofCartHelper::$productToAccountType[$pbean->product_template_id])) {
				$this->account_type = $productToAccountType[$pbean->product_template_id]; 
			}
		}
		// if it doesn't need to change it doesn't need to be in the array
		if($change_support===false) {
			unset($this->verify_account_fields['Support_Service_Level_c']);
		}
		// get the account
		$account = reset($bean->get_linked_beans('accounts_orders', 'Account', array(), 0, 1));
		// get the contact
		$contact = reset($bean->get_linked_beans('contacts_orders', 'Contact', array(), 0, 1));
		// if account is empty create it
		if(empty($account)) {
			// create account
			if(!$account = $this->createAccount($bean)) {
				$GLOBALS['log']->fatal(__FILE__ . '::' . __FUNCTION__."- Failed to create the account");
				return false;
			}
		}
		// verify the account with the order
		else {
			if(!$this->verifyAccount($bean,$account)) {
				$GLOBALS['log']->fatal(__FILE__ . '::' . __FUNCTION__."- Failed to verify/update the account");
				return false;
			}
		}
		// if contact is empty create it
		if(empty($contact)) {
			// create contact and link to account
			if(!$contact = $this->createContact($bean,$account)) {
				$GLOBALS['log']->fatal(__FILE__ . '::' . __FUNCTION__."- Failed to create the contact");
				return false;
			}
		}
		// verify contact with the order
		else {
			if(!$this->verifyContact($bean,$contact)) {
				$GLOBALS['log']->fatal(__FILE__ . '::' . __FUNCTION__."- Failed to verify/update the contact");
				return false;
			}
		}
		return true;
	}
	
	// create new account
	function createAccount(&$bean) {
		$acc = new Account();
		foreach($this->verify_account_fields AS $acc_field => $order_field) {
			$acc->$acc_field = $bean->$order_field;
		}
		
		$acc->name = $bean->company_name;
		
		if($this->account_type !== false) {
			$acc->account_type = $this->account_type;
		}
		
		if(!$acc->save(FALSE)) {
			return false;
		}
		// link to order
		$bean->get_linked_beans('accounts_orders','Account',array(),0,-1,0);
		if(!$bean->accounts_orders->add($acc->id)) {
			$GLOBALS['log']->fatal(__FILE__ . '::' . __FUNCTION__."- Failed to add order to the account");
			return false;
		}
		
		return $acc;
	}
	// create contact
	function createContact(&$bean,&$account) {

		$contacts = $account->get_linked_beans('contacts','Contact',array(),0,-1,0);
		
		$first_contact = false;
		
		if(empty($contacts)) {
			$first_contact = true;
		}
	
		$con = new Contact();
		foreach($this->verify_contact_fields AS $con_field => $order_field) {
			$con->$con_field = $bean->$order_field;
		}
		
		if($con->portal_active == 0) {
			$con->portal_active = 1;
		}
		
		if(empty($con->portal_name)) {
			$con->portal_name = $bean->email;
		}
		
		$con->first_name = $bean->first_name;
		$con->last_name = $bean->last_name;
		
		if($first_contact===true) {
			$con->manage_employees_c=1;
			$con->download_software_c=1;
			$con->support_authorized_c=1;
		}
		
		if(!$con->save(FALSE)) {
			return false;
		}
		// link to account
		$account->load_relationship('contacts');
		if(!$account->contacts->add($contact->id)) {
			$GLOBALS['log']->fatal(__FILE__ . '::' . __FUNCTION__."- Failed to add contact to the account");
			return false;
		}
		// link to order
		$con->load_relationship('orders');
		if(!$con->orders->add($bean->id)) {
			$GLOBALS['log']->fatal(__FILE__ . '::' . __FUNCTION__."- Failed to add order to contact");
			return false;
		}
		return $con;
	}

	// verify the account
	function verifyAccount(&$bean,&$acc) {
		// loop through and see if things are different
		foreach($this->verify_account_fields AS $acc_field => $order_field) {
			if((!isset($acc->$acc_field) && !empty($order_field)) || $acc->$acc_field != $bean->$order_field) {
				$acc->$acc_field = $bean->$order_field;
			}
		}

		if($this->account_type !== false) {
			$acc->account_type = $this->account_type;
		}
		
		// save the account
		if(!$acc->save(FALSE)){
			return false;
		}
		return true;
	}
	
	// verify contact
	function verifyContact(&$bean,&$con) {
		// loop thorugh and see if things are different
		foreach($this->verify_contact_fields AS $contact_field => $order_field) {
			if((!isset($con->$contact_field) && !empty($order_field)) || $con->$contact_field != $bean->$order_field) {
				$con->$contact_field = $bean->$order_field;
			}
		}

		$contacts = $account->get_linked_beans('contacts','Contact',array(),0,-1,0);
		
		$first_contact = false;
		// this is the only contact
		if(count($contacts)==1) {
			$first_contact = true;
		}
		
		// activate portal if its inactive
		if($con->portal_active == 0) {
			$con->portal_active = 1;
		}
		
		if($first_contact===true) {
			$con->manage_employees_c=1;
			$con->download_software_c=1;
			$con->support_authorized_c=1;
		}
		
		// set portal name if its empty to the email address of the contact creating the order
		if(empty($con->portal_name)) {
			$con->portal_name = $bean->email;
		}
		
		if(!$con->save(FALSE)) {
			return false;
		}
		return true;
	}
	
}
