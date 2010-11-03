<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// Assign a case to a support rep automatically based on account
// account to rep mapping in -> custom/si_logic_hooks/meta/supportAccountRepMap.php

class CustRefHooks  {
	
	function updateName(&$bean, $event, $arguments) {
		ini_set('display_errors', 'On');
		global $current_user;
		if ($event == "before_save") {
			//$bean->load_relationship('cr_customer_reference_accounts');
			SYSLOG(LOG_DEBUG, "dtam customer ref: {$bean->name} logic hook fired account name {$bean->cr_customer_reference_accounts_name}");
			$bean->name = $bean->cr_customer_reference_accounts_name;
		}
	}

}
