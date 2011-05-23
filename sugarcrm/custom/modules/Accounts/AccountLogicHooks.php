<?php

class AccountLogicHooks {

	// BEGIN jostrow customization
	// sets the ListView icon depending on what type of Account this is

	function setListViewIcon(&$focus, $event, $arguments) {
		if (IBMHelper::isCMR($focus)) {
			$GLOBALS['log']->debug(__FUNCTION__ . " :: setting CMR icon for id={$focus->id}");

			$focus->listview_icon = 'themes/custom_images/cmr_icon.png';
		}
		elseif (IBMHelper::isClient($focus)) {
			$GLOBALS['log']->debug(__FUNCTION__ . " :: setting Client icon for id={$focus->id}");

			$focus->listview_icon = 'themes/custom_images/client_icon.png';
		}
	}

	// END jostrow customization

	// BEGIN jostrow customization
	// for newly-created records, sets a Client ID or CMR Number

	function setCMROrClientNumber(&$focus, $event, $arguments) {

		// verify that this is a new record being saved
		if (empty($focus->fetched_row)) {

			$GLOBALS['log']->debug(__FUNCTION__ . " :: new Account creation detected (id={$focus->id})");

			// Depending on what type of new Account is being saved, set the CMR Number or Client ID field
			if (IBMHelper::isCMR($focus)) {
				$GLOBALS['log']->debug(__FUNCTION__ . " :: CMR number set (id={$focus->id})");

				$focus->cmr_number = IBMHelper::generateCMRNumber();
			}
			elseif (IBMHelper::isClient($focus)) {
				$GLOBALS['log']->debug(__FUNCTION__ . " :: Client ID set (id={$focus->id})");

				$focus->client_id = IBMHelper::generateClientId();
			}

		}
		else {
			$GLOBALS['log']->debug(__FUNCTION__ . " :: fetched_row not empty, this must not be a new Account (id={$focus->id})");
		}


	}

	// END jostrow customization

	// BEGIN jostrow customization
	// update the tags associated with this record

	function saveTags(&$focus, $event, $arguments) {

		if (isset($_REQUEST['tags'])) {
			IBMHelper::saveTags($focus->module_dir, $focus->id, $_REQUEST['tags']);
		}

	}

	// END jostrow customization

	// BEGIN jostrow customization
	// get tags associated with this record

	function getTags(&$focus, $event, $arguments) {

		$tags = IBMHelper::getRecordTags($focus);

		// encode as a Multienum so the EditView can process it correctly
		$focus->tags = encodeMultienumValue($tags);

	}

	// END jostrow customization

	// BEGIN jvink customization
	// sync Client_ID from parent Account to current bean
	
	function clientIdFromParent(&$focus, $event, $arguments) {
		
		if($focus->parent_id) {
			
			// load the parent account
			$parent = new Account();
			$parent->retrieve($focus->parent_id);
			
			// set client id
			$focus->client_id = $parent->client_id;
		}
		
	}
	
	// END jvink customization
	
	// START jvink customization
	// add assigned_to_user to the accounts_users table
	
	function setAssignedUserRelationship(&$focus, $event, $arguments) {
		// Before save, collect the information necessary for the after_save
		if($event == 'before_save'){
			$focus->before_save_assigned_user_id = '';
			if(!empty($focus->fetched_row['assigned_user_id'])){
				$focus->before_save_assigned_user_id = $focus->fetched_row['assigned_user_id'];
			}
		}
		
		// After save, update the data
		if($event == 'after_save'){
			if(!isset($focus->assigned_user_id)){
				$focus->assigned_user_id = '';
			}
			
			// If the asigned user is not equal to the previous value, we remove the previous value
			if(!empty($focus->before_save_assigned_user_id) && $focus->before_save_assigned_user_id != $focus->assigned_user_id){
				$focus->load_relationship('users');
				$focus->users->delete($focus->id, $focus->before_save_assigned_user_id);
			}
			
			// If we have a value, we add it to the contacts subpanel on the opportunity
			if(!empty($focus->assigned_user_id)){
				$focus->load_relationship('users');
				$rel_users = $focus->users->add($focus->assigned_user_id, array('user_role' => 'Client Rep'));
			}
		}
	}
	
	
	// END jvink customization

	// BEGIN sadek - SIMILAR OPPORTUNITY CALCULATOR
	function addOppsToSimCalcQueue(&$focus, $event, $arguments) {
		if($event == 'before_save'){
			if(!empty($focus->fetched_row['id']) &&
				($focus->fetched_row['industry'] != $focus->industry || $focus->fetched_row['billing_address_country'] != $focus->billing_address_country)
			){
				require_once('custom/IBMSimilarOpportunities.php');
				$query = "SELECT opportunity_id FROM accounts_opportunities WHERE account_id = '{$focus->id}' AND deleted = 0";
				$res = $GLOBALS['db']->query($query);
				while($row = $GLOBALS['db']->fetchByAssoc($res)){
					$GLOBALS['log']->info("AccountLogicHooks::addOppsToSimCalcQueue() adding opp with id '{$row['opportunity_id']}' to similar opp calc queue");
					IBMSimilarOpportunities::addToQueue($row['opportunity_id']);
				}
			}
		}
	}
	// END sadek - SIMILAR OPPORTUNITY CALCULATOR
}
