<?php

class ibm_WinPlanLogicHooks {

	// BEGIN jvink customization
	// set type in name field for display in Opportunity WinPlans subpanel (collection list)
	
	function setType(&$focus, $event, $arguments) {

		$set_name = false;
		switch($focus->object_name) {
			case 'ibm_WinPlanGeneric':
				$set_name = 'Generic';
				break;
			case 'ibm_WinPlanSTG':
				$set_name = 'STG';
				break;
			case 'ibm_WinPlanSWG':
				$set_name = 'SWG';
				break;
		}
		if($set_name) $focus->name = $set_name;
	}

	// END jvink customization

}
