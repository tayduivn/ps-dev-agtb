<?php

class ibm_RevenueLineItemRoadmapLogicHooks {
	
	// START jvink customization --> moved to SyncHelper
	/*function setOpptyDecisionDate(&$focus, $event, $arguments) {
			
		// figure out related opportunity
		require_once('modules/ibm_revenueLineItems/ibm_revenueLineItems.php');
        $lineItem = new ibm_revenueLineItems();
        $lineItem->retrieve($focus->revenuelineitem_id_c);
        $lineItem->load_relationship('ibm_revenuelineitems_opportunities');
                
        // get opportunity owner
		if($opp_owner_id = IBMHelper::getOpptyOwner($lineItem->ibm_revenud375unities_ida)) {
		
			// update oppty decision date if this roadmap belongs to oppty owner
			if($opp_owner_id == $focus->assigned_user_id) {
				require_once('modules/Opportunities/Opportunity.php');
				$opp = new Opportunity();
				$opp->retrieve($lineItem->ibm_revenud375unities_ida);
				if($focus->bill_date > $opp->date_closed) {
					$opp->date_closed = $focus->bill_date;
					$opp->save();
				}
			}
		}		
	}*/
	// END jvink
}
