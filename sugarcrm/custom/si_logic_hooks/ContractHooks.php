<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class ContractHooks  {

	// See ITRequest #12543
	// Make sure the Contracts 'Assigned To' value always matches the 'Assigned To' value of the related Account
	function overrideAssignedTo(&$bean, $event, $arguments) {
		if (!empty($bean->account_id) && (!isset($bean->disable_reassignment_hook) || $bean->disable_reassignment_hook === FALSE)) {
			require_once('modules/Accounts/Account.php');

			$acc = new Account();
			$acc->retrieve($bean->account_id);

			$bean->assigned_user_id = $acc->assigned_user_id;
		}
	}

}
