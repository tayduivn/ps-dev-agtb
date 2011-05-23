<?php

class ibm_WinPlanSTGLogicHooks {

	// BEGIN jvink customization
	// set approval date to today if approved by is set
	
	function setApprovalDate(&$focus, $event, $arguments) {

		// STG approval date --> pay attention, field id3 !!! (link for approver_c field)
		if(empty($focus->fetched_row['user_id3_c']) && $focus->user_id3_c) {
			$focus->date_approved_c = date('Y-m-d');
		}

		// S&D approval date
		if(empty($focus->fetched_row['user_id2_c']) && $focus->user_id2_c) {
			$focus->sd_date_approved = date('Y-m-d');
		}
		
		
	}

	// END jvink customization

}
