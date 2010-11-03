<?php

chdir('../..');
define('sugarEntry', true);

require_once('include/entryPoint.php');
require_once('modules/Opportunities/Opportunity.php');
require_once('modules/Contacts/Contact.php');
require_once('modules/Accounts/Account.php');
require_once('modules/Tasks/Task.php');
require_once('custom/modules/Opportunities/RRController.php');

global $current_user;
global $app_list_strings;
$current_user = new User();
$current_user->getSystemUser();

global $current_language;

/* if the language is not set yet, then set it to the default language. */
if (isset($_SESSION['authenticated_user_language']) && $_SESSION['authenticated_user_language'] != '') {
    $current_language = $_SESSION['authenticated_user_language'];
} else {
    $current_language = $sugar_config['default_language'];
}

/* set module and application string arrays based upon selected language */
$app_strings = return_application_language($current_language);
if (!isset($modListHeader)) {
    if (isset($current_user)) {
        $modListHeader = query_module_access_list($current_user);
    }
}

/*
** untouched 60 min opp functionality
** author asandberg
** updated by dee 10/13/2009
*/
	global $timedate;
	$rr = new RRController();

	// ITREQUEST 12298
        // get all round robin user ids
        global $region_to_user_mapping;
        $GLOBALS['round_robin_ids'] = array();
        foreach($region_to_user_mapping['USA'] as $region) {
                foreach($region as $key=>$value) {
                        $round_robin_ids[] = $value['user_id'];
                }
        }
        // END ITREQUEST 12298

        $GLOBALS['log']->info('----->Scheduler fired job of type handleSixtyMinuteOpps()');
        $start_time = time();

        $now = gmdate("Y-m-d H:i:s");
        $t_now = strtotime($now);

        $db = DBManagerFactory::getInstance();

        //For our query, grab all opps so we can process both use cases where not modified > 60 min and > 120 min.
        $query = "SELECT id,date_entered, date_modified FROM opportunities o, opportunities_cstm oc WHERE o.id= oc.id_c AND o.deleted='0' AND oc.sixtymin_opp_c='1'";
        $rs = $db->query($query);

        while($row = $db->fetchByAssoc($rs))
        {

                $opp_id = $row['id'];
		echo $opp_id." - ";
		
		$opp = new Opportunity();
                $opp->disable_row_level_security = TRUE; //Sometimes scheduler doesn't have access to a particular opp.
               	$opp->retrieve($opp_id);
                
		echo $opp->assigned_user_id." - ".$opp->sixtymin_opp_c." - ".$opp->sixtymin_opp_pass_c."<br />";
		
		if(!isset($opp->sixtymin_opp_pass_c) || empty($opp->sixtymin_opp_pass_c)) {
			$GLOBALS['log']->fatal("Error OPPQ101: Sixty Min Opp Pass value is not set. Cannot round robin this opportunity {$opp->id}");
                      	continue;
		}

		$min_time = $opp->sixtymin_opp_pass_c * 60;
		$max_time = $min_time + 60;

		$date_created = $row['date_entered'];
		$t_date_created = strtotime($date_created);
		$diff_in_seconds = $t_now - $t_date_created;
                $diff_in_minutes = $diff_in_seconds / 60; //Convert to minutes
		echo "Diff: ".$diff_in_minutes." - ". $min_time . " - ". $max_time ."<br />";

		if($diff_in_minutes >= $min_time && $diff_in_minutes < $max_time) {
			$body = "Opportunity: http://sugarinternal.sugarondemand.com/index.php?module=Opportunities&action=DetailView&record=".$opp->id;
                        $body .= "\nBefore Pass values: \n";
                        $body .= $opp->sixtymin_opp_c."-".$opp->sixtymin_opp_pass_c." - ".$opp->assigned_user_id;
			$GLOBALS['log']->debug("Scheduler found Sixty Min opp that is over 60 minutes old (less than 120 min) - Opp ID: $opp_id ");
                        //Update number of times assigned
                        $new_assigned_user = $rr->getNextAssignedUserFromOpportunityOjbect($opp);
                        if($new_assigned_user === FALSE)
                        {
                                $GLOBALS['log']->fatal("Unable to get new assignable user id from handleSixtyMinuteOpps method scheduler");
                                continue;
                        }
			else
                        {
                                $GLOBALS['log']->fatal("Reassigning Opp ID: {$opp->id} to user: $new_assigned_user");
                               	if(!is_int($opp->sixtymin_opp_pass_c)) {
					$opp->sixtymin_opp_pass_c = $opp->sixtymin_opp_pass_c + 1;
				}
				else {
					$opp->sixtymin_opp_pass_c = $opp->sixtymin_opp_pass_c++;
                                }
				$opp->assigned_user_id = $new_assigned_user;
                                $opp->save();
                                $acc = new Account();
                                $acc->retrieve($opp->account_id);
                                $acc->load_relationship('contacts');
                                foreach ($acc->build_related_list($acc->contacts->getQuery(), new Contact) as $contact) {
                                        $contact->assigned_user_id = $new_assigned_user;
                                        $contact->save();
                                }
                                $acc->assigned_user_id = $new_assigned_user;
                                $acc->save();
                        }
                        $body .= "\nAfter Pass Values\n";
                        $body .= "Round robin pass - ".$opp->sixtymin_opp_c."-".$opp->sixtymin_opp_pass_c." - ".$opp->assigned_user_id."\n";
                	echo $body."<br />";
			if($opp->sixtymin_opp_pass_c == 3) {
				//Create new Task
				$vince_randazzo_user_id = '912da741-09eb-bcf8-9329-45d9f7520350';
                        	$task = new Task();
                        	$task->parent_type = 'Opportunities';
                        	$task->parent_id = $opp_id;
                        	$task->name = "Sixty Minute Opp Task";
                        	$task->assigned_user_id = $vince_randazzo_user_id;
                        	$task->priority = "High";
                        	$task->status = "Not Started";

                        	$now = gmdate("Y-m-d H:i:s");
                        	$task->date_start = $timedate->to_display_date_time($now);

                        	//Set date due in an hour.
                        	$future_now = strtotime($now) + (60 *60); //Second * minutes = 1 hr.
                        	$future_now = date("Y-m-d H:i:s",$future_now);
                        	$task->date_due = $timedate->to_display_date_time($future_now);
				$task->save(TRUE);
			}
		}	

        }

        $end_time = time();
        $GLOBALS['log']->info('----->Scheduler handleSixtyMinuteOpps finished, took '.($end_time-$start_time).' seconds');
?>
