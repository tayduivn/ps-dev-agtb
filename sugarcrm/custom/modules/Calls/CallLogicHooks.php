<?php

class CallLogicHooks {

	// BEGIN jvink customization
	//

	function lastInteraction(&$focus, $event, $arguments) {

		global $timedate;
		require_once('modules/Calendar/DateTimeUtil.php');

		// only update when the call is actually held
		if($focus->status == 'Held') {
			// search for linked account, as the same procedure is used by multiple logic hooks
			// this logic has been put in the IBMHelper to be extendable and reuseable 
			if($account_id = IBMHelper::searchRelatedAccount($focus)) {

				// calculate datetime end
				$date_time_start = DateTimeUtil::get_time_start($focus->date_start);
				$date_time_end = DateTimeUtil::get_time_end($date_time_start, $focus->duration_hours, $focus->duration_minutes);

				// call helper function to update the last interaction datetime
				IBMHelper::updateLastInteractionDate($account_id, $timedate->to_display_date_time(gmdate("Y-m-d H:i:s", $date_time_end->ts),true));

			} else {
				$GLOBALS['log']->debug('CallLogicHooks-lastInteraction: couldn\'t find related account to update last Interaction date');
			}
		}

	}

	// END jvink customization

}
