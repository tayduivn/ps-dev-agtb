<?php

class ibm_WinPlanGenericLogicHooks {

	// BEGIN jvink customization
	// set approval date to today if approved by is set
	
	function setApprovalDate(&$focus, $event, $arguments) {

		if(empty($focus->fetched_row['user_id1_c']) && $focus->user_id1_c) {
			$focus->date_approved_c = date('Y-m-d');
		}

	}

	// END jvink customization

}
