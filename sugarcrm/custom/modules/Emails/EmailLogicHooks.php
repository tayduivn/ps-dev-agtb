<?php

class EmailLogicHooks {

	// BEGIN jvink customization
	//

	function lastInteraction(&$focus, $event, $arguments) {

		global $timedate;
		require_once('modules/Calendar/DateTimeUtil.php');

		// only update when the call is actually held
		if($focus->status == 'sent') {
			// search for linked account, as the same procedure is used by multiple logic hooks
			// this logic has been put in the IBMHelper to be extendable and reuseable 
			if($account_id = IBMHelper::searchRelatedAccount($focus)) {

				$date_sent = DateTimeUtil::get_time_start($focus->date_sent);

				// call helper function to update the last interaction datetime
				IBMHelper::updateLastInteractionDate($account_id, $timedate->to_display_date_time(gmdate("Y-m-d H:i:s", $date_sent->ts),true));

			} else {
				$GLOBALS['log']->debug('EmailLogicHooks-lastInteraction: couldn\'t find related account to update last Interaction date');
			}
		}

	}

	// END jvink customization

}
