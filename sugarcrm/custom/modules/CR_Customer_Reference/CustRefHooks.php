<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// Assign a case to a support rep automatically based on account
// account to rep mapping in -> custom/si_logic_hooks/meta/supportAccountRepMap.php

class CustRefHooks  {
	
	function updateName(&$bean, $event, $arguments) {
	  //	ini_set('display_errors', 'On');
		global $current_user;
		if ($event == "before_save") {
			//$bean->load_relationship('cr_customer_reference_accounts');
			$bean->name = $bean->cr_customer_reference_accounts_name;
		}
	}
/*
** @author: dtam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #:18968
** Description: Add logic hook to automatically set date published when status changes to published
*/

	function updatePubDate(&$bean, $event, $arguments) {
	  //ini_set('display_errors', 'On');
		global $current_user;
		global $timedate;

		$oldStatus = $bean->fetched_row['activity_status'];
		//SYSLOG(LOG_DEBUG, "dtam customer ref: {$bean->name} logic hook fired old status {$oldStatus} new status {$bean->activity_status}");
		// only update if before save... not already published and status is changing to published
		if (($event == "before_save") && ($oldStatus != 'published') && ($oldStatus != $bean->activity_status) && ($bean->activity_status=='published')) {
		  $tdate = date('Y-m-d');
		  $cleanDBDate =  $timedate->to_db_date($tdate);
		  //SYSLOG(LOG_DEBUG, "dtam customer ref: {$bean->name} logic hook fired new date {$cleanDBDate} old status {$oldStatus} new status {$bean->activity_status}");
		  $bean->cr_date_published_c = $cleanDBDate;
		}
	}
/* END SUGARINTERNAL CUSTOMIZATION */

}
