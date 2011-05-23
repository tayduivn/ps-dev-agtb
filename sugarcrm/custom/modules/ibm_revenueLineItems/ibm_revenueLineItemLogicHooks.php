<?php

class ibm_revenueLineItemLogicHooks {
	
	// START jvink customization
	// Add "line items specialist" to the opportunity teams list if user is not already present
	// In case the user is already present with a different role, we do nothing
	function setLineItemSpecialist(&$focus, $event, $arguments) {

		// only trigger this function if a line item specialist is assigned 
		if(isset($focus->assigned_user_id)) {
		
			// fetch opportunity relationship		
			$opp_list = $focus->get_linked_beans('ibm_revenuelineitems_opportunities', 'Opportunity');
			if(isset($opp_list[0])) {
				
				// fetch related users to this opportunity
				$opp = $opp_list[0];
				$user_list = $opp->get_linked_beans('users','User');

				$opp_users = array();
				foreach($user_list as $user) {
					$opp_users[$user->id] = $user;
				}		 
				
				// add line item specialst to opp_users relationship if user not found
				if(! array_key_exists($focus->assigned_user_id, $opp_users)) {
					//$opp->load_relationship('contacts');
					$opp->users->add($focus->assigned_user_id, array('user_role' => '6'));
				}
			}

		}

	}
	// END jvink customization
	
	// START jvink customization -- moved to SyncHelper
	// set average probability on related opportunity
	/*function setAverageProbability(&$focus, $event, $arguments) {
		
		// fetch opportunity relationship		
		$opp_list = $focus->get_linked_beans('ibm_revenuelineitems_opportunities', 'Opportunity');
		if(isset($opp_list[0]->id)) {
			
			$sql = 'SELECT AVG(rev.probability) AS prob_avg
					FROM ibm_revenuepportunities_c rel
					INNER JOIN ibm_revenuelineitems rev
						ON rev.id = rel.ibm_revenu04e3neitems_idb
						AND rev.deleted = 0
					WHERE rel.ibm_revenud375unities_ida = "'.$opp_list[0]->id.'"
						AND rel.deleted = 0';
			$q_avg = $GLOBALS['db']->query($sql);
			$avg = $GLOBALS['db']->fetchByAssoc($q_avg);
			if(isset($avg['prob_avg'])) {
				$update = 'UPDATE opportunities SET probability = "'.$avg['prob_avg'].'"
					WHERE id = "'.$opp_list[0]->id.'"';
				$GLOBALS['db']->query($update);
			}
		}
		
	}*/
	// END jvink

	// BEGIN sadek - SIMILAR OPPORTUNITY CALCULATOR
	function addOppsToSimCalcQueue(&$focus, $event, $arguments) {
		if($event == 'before_save'){
			// if this is a new record OR it's not a new record AND (the brand code changed(level20) OR the product information(level30) changed)
			if(empty($focus->fetched_row['id']) ||
				($focus->fetched_row['brand_code'] != $focus->brand_code || $focus->fetched_row['product_information'] != $focus->product_information)
			){
				require_once('custom/IBMSimilarOpportunities.php');
				$query = "SELECT ibm_revenud375unities_ida opp_id FROM ibm_revenuepportunities_c WHERE ibm_revenu04e3neitems_idb = '{$focus->id}' AND deleted = 0";
				$res = $GLOBALS['db']->query($query);
				$found_one = false;
				while($row = $GLOBALS['db']->fetchByAssoc($res)){
					$found_one = true;
					$GLOBALS['log']->info("ibm_revenueLineItemLogicHooks::addOppsToSimCalcQueue() a) adding opp with id '{$row['opp_id']}' to similar opp calc queue");
					IBMSimilarOpportunities::addToQueue($row['opp_id']);
				}
				
				if(!$found_one && !empty($_REQUEST['relate_id'])){
					$GLOBALS['log']->info("ibm_revenueLineItemLogicHooks::addOppsToSimCalcQueue() b) adding opp with id '{$row['opp_id']}' to similar opp calc queue");
					IBMSimilarOpportunities::addToQueue($_REQUEST['relate_id']);
				}
			}
		}
	}
	// END sadek - SIMILAR OPPORTUNITY CALCULATOR
	
	// BEGIN sadek - SugarAlerts logic hooks
	function sugarAlerts(&$focus, $event, $arguments){
		if($event == 'before_save'){
			// Sugar Alert for new rev line items. We set a flag if this is a new ibm rev line item and call it after the data has been saved (after_save hook)
			if(empty($focus->fetched_row['id'])){
				$focus->SugarAlertsNewRli = true;
			}
		}
		if($event == 'after_save'){
			if(isset($focus->SugarAlertsNewRli) && $focus->SugarAlertsNewRli == true){
				require_once('custom/include/SugarAlerts/Alerts/SugarAlertsNewRevLineItem.php');
				$sa = new SugarAlertsNewRevLineItem();
				$sa->handleAlert($focus);
			}
		}
	}
	// END sadek - SugarAlerts logic hooks
}
