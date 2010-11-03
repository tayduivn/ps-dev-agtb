<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// updates an Opportunity's probability based on sales stage
// sales stage -> probability mapping stored in custom/si_custom_files/meta/OpportunitiesSalesStageConfig.php

global $default_sales_stage;
$default_sales_stage = 'Interested_Prospect';

class OpportunityHooks  {
/*
** @author: dtam
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 18588
** Description: update prospect status based on opp sales stage
** Wiki customization page: internalwiki.sjc.sugarcrm.pvt/index.php/PushToPardot
*/
	function pushToPardot(&$bean, $event, $arguments) {
		if($event == "before_save" && isset($bean->sales_stage) && isset($bean->fetched_row['sales_stage']) && $bean->sales_stage != $bean->fetched_row['sales_stage']) {
		  require_once('modules/Accounts/Account.php');
		  require_once('modules/Contacts/Contact.php');
  
		  SYSLOG(LOG_DEBUG, "dtam pardot push initiated rev1244");	  
		  // Get contacts emails for opp
		  $opp_acc = new Account();
		  $opp_acc->retrieve($bean->account_id);
		  SYSLOG(LOG_DEBUG, "dtam pardot push opp acc name" . $opp_acc->name);
		  
		  //Get all contacts associated to this opportunity, both from this email bean and account
		  require_once('modules/P1_Partners/P1_PartnersUtils.php');
		  // Get contacts only gets contacts with email1 set
		  $contact_arr = P1_PartnerUtils::getContacts($bean);
		  if(empty($contact_arr) || !isset($contact_arr)) {
		    $GLOBALS['log']->fatal("--OpportunityHooks.php--pushToPardot()--No contacts returned for OPP:{$bean->id}");
		    return false;
		  }
		  $reqParams = array(); // initialize api request paramenters
		  $oldSalesStage = $bean->fetched_row['sales_stage'];
		  // is sales stage is set to to Sales Rep Closed (98%), Sales Ops Closed(99%) or Finance Closed(100%)
		  if (($bean->sales_stage == 'Closed Won') || ($bean->sales_stage == 'Sales Ops Closed') || ($bean->sales_stage == 'Finance Closed')) {
		    $reqParams['status'] = 'Customer'; 
		  } elseif (($oldSalesStage == 'Finance Closed') && ($bean->sales_stage == 'Closed Lost')) {
		    $reqParams['status'] = 'Lost_Customer'; 
		  } elseif (($oldSalesStage != 'Finance Closed') && ($bean->sales_stage == 'Closed Lost')) {
		    $reqParams['status'] = 'Lost_Prospect'; 
		   }
		  if ($bean->sales_stage == 'Closed Lost') {
		    $reqParams['closed_lost_reason_c']=$bean->closed_lost_reason_c;
		  }
		  
		  // set request prospect sales stage
		  $newSalesStage = str_replace(' ','_',$bean->sales_stage); // pardot drop down values dont have space so replace " " with "_"
		  $reqParams['sales_stage'] = $newSalesStage; 
		  SYSLOG(LOG_DEBUG, "dtam formatted pardot sales stage " . $newSalesStage);
		  $emails = array();
		  // loop over each contact
		  foreach($contact_arr as $contact_id) {
		    $contact = new Contact();
		    $contact->retrieve($contact_id);
		    $emails[]=$contact->email1;
		    SYSLOG(LOG_DEBUG, "dtam adding to update list email: " . $contact->email1);
		  }
		  $origWorkLoad = array('reqParams'=>$reqParams,'emails'=>$emails);
		  $workload = serialize($origWorkLoad);
		  require_once('custom/si_custom_files/MoofCartHelper.php');
		  $server = MoofCartHelper::getGearmanServers();
		  
		  $client = new GearmanClient();
		  $client->addServers($server);
		  $client->doBackground('PardotUpdateSalesStage', $workload);
		}
	}
	/* END SUGARINTERNAL CUSTOMIZATION */


	//DEE CUSTOMIZATION 12.16.2008 - ITREQUEST 5041
	function setCloseDate(&$bean, $event, $arguments) {
		if($event == "before_save") {
			global $current_user;
		
			if(!$current_user->check_role_membership('Sales Manager')) {
                               if(isset($bean->fetched_row['top20deal_c']) && $bean->fetched_row['top20deal_c'] == '1')
                                 $bean->top20deal_c = $bean->fetched_row['top20deal_c'];
                        }		
	
/*
** @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 15158
** Description: added following to condition:   && $bean->fetched_row['sales_stage'] !='Closed Won' 
** the extra check makes sure sales stage is not modified if current value is already set to closed won
*/
			if(isset($bean->sales_stage) && $bean->sales_stage == 'Closed Won' && $bean->fetched_row['sales_stage'] !='Closed Won') {
				//Check to see if the sales stage has been set to 'Closed Won' before. Checking the opportunities_audit table
				$audit_query = "SELECT id FROM opportunities_audit WHERE parent_id = '{$bean->id}' AND field_name = 'sales_stage' and after_value_string = 'Closed Won'";
				$audit_result = $GLOBALS['db']->query($audit_query);	
				$audit_id = array();
				while($audit_row = $GLOBALS['db']->fetchByAssoc($audit_result)){
					$audit_id[] = $audit_row['id'];
				}
				//if sales stage has been set to Closed Won before then return else set the expected close date to today's date
				if(!empty($audit_id) && isset($audit_id))
					return;
				else
                                	$bean->date_closed = date("Y-m-d");
			}
		}
	}
	//END DEE

	function verifyCloseDate(&$bean, $event, $arguments) {
		if(isset($bean->allow_override_date_closed) && $bean->allow_override_date_closed == true){
			return;
		}
		global $current_user;
		if($event == "before_save"){
			require_once('custom/si_custom_files/custom_functions.php');

	                /*
	                @author: EDDY
	                ** SUGARINTERNAL CUSTOMIZATION
	                ** ITRequest #: 15411 :: Closed lost reason of invalid data reverting back to no response
	                ** Description: set closed date in past to current date and allow record to be edited if sales stage is being set to closed lost
	                ** customization includes this new block of code, and checks for $updateTime parameter throughout code
	                */
	                // if the stages have been changed from some value to closed lost, and the closed date is in the past then:
	                // 1. allow modification of opportunity,   2. change closed date in past to present date
			// Default to the value the user entered
	                $updateTime = false;
			$final_insert = $bean->date_closed;
			/*
			** @author: dtam
			** SUGARINTERNAL CUSTOMIZATION
			** ITRequest #: 18765
			** Description: fix problem where opps cant be closed past 4pm because its using gmdate to set date
			** Wiki customization page: internalwiki.sjc.sugarcrm.pvt/index.php/View.opportunitywizardsave.php
			*/
			global $timedate;
			global $current_user;
			$curUserTZ = $current_user->getPreference('timezone');
			if(empty($curUserTZ)) {
			  $curr_date = gmdate('Y-m-d',time());
			} else {
			  $curr_date = $timedate->handle_offset(gmdate('Y-m-d',time()),'Y-m-d',true, $current_user, $curUserTZ);

			}
			/* END SUGARINTERNAL CUSTOMIZATION */
			$curr_time_stamp = strtotime($curr_date);
			$notify = false;

	                //check to see if sales stage is being set from a previous value to closed lost
	                if(!empty($bean->fetched_row['sales_stage']) && $bean->sales_stage == 'Closed Lost' && $bean->fetched_row['sales_stage'] != 'Closed Lost'){
	                    $updateTime = true;
	                    
	                    //check to see if date is a day in the past
	                     if(strtotime($bean->date_closed) < $curr_time_stamp)
	                     {
	                     	$userpref = $current_user->getUserDateTimePreferences();
				$TimeDate = new TimeDate();
							
	                        //reset date value on bean and set updated time to true
	                        $final_insert = gmdate("Y-m-d", $curr_time_stamp);
	                        $bean->date_closed = $final_insert;
	                    	$updateTime = true;
	                	}
			}
        	        /*END CUSTOMIZATION*/

			// This block prevents the non Finance members from changing the close date to prior to this month
			if(!$current_user->check_role_membership('Finance') && !$current_user->check_role_membership('Sales Operations')){
				$closed_sales_stages = getSugarInternalClosedStages('array');
				
				$newrecord = false;
				if(empty($bean->fetched_row['id'])){
					$newrecord = true;
				}

				
				// The message in the email that gets sent out
				$msg = '';
				$previous = '';
				$prv_arr = array();
				
			if(!$newrecord ){
	             		// Get the previous value in standard format
	 		    	// If the record is in the past, don't allow the users to change the Closed Date, Sales Stage, or Amount
			  if(((strtotime($bean->date_closed) < $curr_time_stamp)) && !$updateTime){
					$audit_fields = array(
						/*
						array(
							'display' => 'Expected Close Date',
							'field' => 'date_closed',
						),
						array(
							'display' => 'Sales Stage',
							'field' => 'sales_stage',
						),
						*/
						array(
							'display' => 'Amount',
							'field' => 'amount',
						),
					);
					$additional_msg = '';
					foreach($audit_fields as $f){
						if($bean->fetched_row[$f['field']] != $bean->$f['field']){
							$additional_msg .= "* The {$f['display']} cannot be changed, and has been left as {$bean->fetched_row[$f['field']]}\n";
							$bean->$f['field'] = $bean->fetched_row[$f['field']];
							$notify = true;
						}
					}
					if($notify){
						//make sure that the probability field is also reverted to match the sales stage
						$bean->probability = $bean->fetched_row['probability'];
						$msg .= "\nThis opportunity was closed with an incorrect date.\n$additional_msg";
					}
				}
			}
					
			// If the entered year is before this year or the entered year is equal and the month is prior, fail
			if( 
				$bean->date_closed != $bean->fetched_row['date_closed'] && 
				( strtotime($bean->date_closed) < $curr_time_stamp)
		    	){
				// if it's a new record, default to the current date, else don't change it
				if($newrecord || $updateTime){
					$userpref = $current_user->getUserDateTimePreferences();
					$TimeDate = new TimeDate();						
					$final_insert = date("Y-m-d", $curr_time_stamp);
					$final_insert = $TimeDate->swap_formats($final_insert, "Y-m-d", $userpref['date']);
					$msg .= "The Expected Close Date was set to a date in the past.  The system has set the date to today's date.\n";
				}
				else{
					$final_insert = $bean->fetched_row['date_closed'];
					$msg .= "The Expected Close Date was set to a date in the past. The system has left the value unchanged.\n";
				}
				$notify = true;

			}
			else if( !$newrecord && in_array($bean->fetched_row['sales_stage'], $closed_sales_stages) && $bean->date_closed != $bean->fetched_row['date_closed'] && 
						 (strtotime($bean->date_closed) > $curr_time_stamp)
			){
				$final_insert = $bean->fetched_row['date_closed'];
				$msg .= "You moved the Expected Close Date on a closed opportunity from a previous date to a current or future date. The system has left the value unchanged.\n";
				$notify = true;
			}
			if($notify && !$updateTime){
					global $sugar_config;
					$body = "This is to notify you that you have incorrectly modified a value on an Opportunity. ";
					$body .= $msg."\n";
					$body .= $sugar_config['site_url']."/index.php?module=Opportunities&action=DetailView&record={$bean->id}\n\n";
					$body .= "Please email accounting@sugarcrm.com if you have any questions.\n\nThanks,\nSugar Internal";
					$subject = "Opportunity '{$bean->name}': Bad Expected Close Date";
					$from = array('from_address' => 'accounting@sugarcrm.com', 'from_name' => 'Accounting');
					$this->sendOpportunityNotification($current_user, $subject, $body, '', $from);
			}
					
			$bean->date_closed = $final_insert;
			}
		}
	}
	
	function validateOpportunity(&$bean, $event, $arguments){
		global $current_user;

		if($event == "before_save"){
			$this->hardEnforcements($bean);

			// If the user is a member of the Finance team, we don't validate
			if(!$current_user->check_role_membership('Finance') && !$current_user->check_role_membership('Sales Operations Opportunity Admin')){
				require_once('custom/si_custom_files/checkRecordValidity.php');
				$checker = new checkRecordValidity();
				$checker->checkValidity($bean, 'custom/si_custom_files/oppCheckMeta.php', false);
				$warningString = '';
				if(!empty($checker->warningFields)){
					$fieldDisplay = array();
					foreach($checker->warningFields as $fieldArr){
						$fieldDisplay[] = $fieldArr['display'];
						if(!empty($bean->fetched_row)){
							$bean->$fieldArr['field'] = $bean->fetched_row[$fieldArr['field']];
						}
					}
					foreach($checker->warningArray as $string){
						$warningString .= "$string\n";
					}
				
					global $sugar_config;
					$body = "";
					if(!empty($bean->fetched_row)){
						$body .= "The following fields have been reverted back to their values before you saved the record:\n";
						$body .= "'" . implode("', '", $fieldDisplay) . "'\n\n";
						$body .= "The reason they were reverted back was due to the following rules:\n";
					}
					else{
						$body .= "Please resolve the following issues before this Opportunity can be closed:\n";
					}
					$body .= $warningString."\n";
					$body .= $sugar_config['site_url']."/index.php?module=Opportunities&action=DetailView&record={$bean->id}\n\n";
					$body .= "Please contact sales-ops@sugarcrm.com if you have any questions.\n\nThanks,\nSugar Internal";
					$subject = "Opportunity Validity Check: '{$bean->name}'";
					$from = array('from_name' => 'Sales Ops', 'from_address' => 'sales-ops@sugarcrm.com');
					$cc_user = '';
					if(isset($bean->assigned_user_id) && $bean->assigned_user_id != $current_user->id){
						$cc_user = new User();
						$cc_user->retrieve($bean->assigned_user_id);
					}
					$this->sendOpportunityNotification($current_user, $subject, $body, $cc_user, $from);
				}
			}
		}
	}

	// BEGIN jostrow customization
	// See ITRequest #5086
	// If someone hacked the UI to Delete an Opportunity that's in Sales Op Closed/Finance Closed (and does not have access), stop them
	// This is a 'before_delete' logic hook
	function checkDeletePermissions(&$bean, $event, $arguments) {
		global $current_user;

                if(isset($bean->sales_stage) && $bean->sales_stage == "Sales Ops Closed" && isset($bean->fetched_row) && $bean->fetched_row['deleted'] == 0 && !$current_user->check_role_membership('Sales Operations Opportunity Admin') && !$current_user->check_role_membership('Finance')){
			$GLOBALS['log']->fatal("User {$current_user->user_name}) attempted to Delete the Opportunity {$bean->id} which is in Sales Stage {$bean->sales_stage}");

			sugar_die("<i>This Opportunity has been set to 'Sales Ops Closed'. Please contact the Sales Ops or Finance departments if you'd like to make any changes.</i>");
                }
                if(isset($bean->sales_stage) && $bean->sales_stage == "Finance Closed" && isset($bean->fetched_row) && $bean->fetched_row['deleted'] == 0 && !$current_user->check_role_membership('Finance')){
			$GLOBALS['log']->fatal("User {$current_user->user_name}) attempted to Delete the Opportunity {$bean->id} which is in Sales Stage {$bean->sales_stage}");

			sugar_die("<i>This Opportunity has been set to 'Finance Closed'. Please contact the Finance department if you'd like to make any changes.</i>");
                }

	}
	// END jostrow customization

	function hardEnforcements(&$bean){
		global $current_user, $default_sales_stage;

		// If someone hacked the UI and set the Sales Stage to Finance Closed we revert back to what it was
		if(!$current_user->check_role_membership('Finance') && isset($bean->sales_stage) && $bean->sales_stage == 'Finance Closed'){
			if(!empty($bean->fetched_row) && $bean->fetched_row['sales_stage'] != 'Finance Closed'){
				$bean->sales_stage = $bean->fetched_row['sales_stage'];
			}
			else if(empty($bean->fetched_row)){
				$bean->sales_stage = $default_sales_stage;
			}
			$GLOBALS['log']->fatal("User {$current_user->user_name} attempted to set the Sales Stage value to 'Finance Closed' for Opportunity '{$bean->name}'");
		}
		// If someone hacked the UI and set the Sales Stage to Sales Ops Closed we revert back to what it was
		if(!($current_user->check_role_membership('Sales Operations') || $current_user->check_role_membership('Sales Operations') || $current_user->check_role_membership('Finance')) && isset($bean->sales_stage) && $bean->sales_stage == 'Sales Ops Closed'){
				$bean->sales_stage = $bean->fetched_row['sales_stage'];
			if(!empty($bean->fetched_row) && $bean->fetched_row['sales_stage'] != 'Sales Ops Closed'){
				$bean->sales_stage = $bean->fetched_row['sales_stage'];
			}
			else if(empty($bean->fetched_row)){
				$bean->sales_stage = $default_sales_stage;
			}
			$GLOBALS['log']->fatal("User {$current_user->user_name} attempted to set the Sales Stage value to 'Sales Ops Closed' for Opportunity '{$bean->name}'");
		}
		
		// DEE CUSTOMIZATION - 11333 - OppQ: Opportunity at 10% and with wrong team - REMOVE THIS AS WAS RESETTING OPPS TO 10%
		/*if(isset($bean->sales_stage) && $bean->sales_stage == 'Initial_Opportunity'){
			if(!empty($bean->fetched_row) && $bean->fetched_row['sales_stage'] != 'Initial_Opportunity'){
				$bean->sales_stage = $bean->fetched_row['sales_stage'];
			}
			else if(empty($bean->fetched_row)){
				if(!isset($_REQUEST['action']) || $_REQUEST['action'] != 'ConvertLeadSave'){
					$bean->sales_stage = $default_sales_stage;
				}
			}
		}*/
		// end DEE CUSTOMIZATION - 11333 - OppQ: Opportunity at 10% and with wrong team - REMOVE THIS AS WAS RESETTING OPPS TO 10%

                // BEGIN jostrow customization
                // See ITRequest #7156: Need to modify the Opportunities screen so SLC field is only editable by Sales Ops/Finance

                if (!$current_user->check_role_membership('Finance') && !$current_user->check_role_membership('Sales Operations')) {
                        if (empty($bean->fetched_row) && !empty($bean->additional_training_credits_c)) {
                                $bean->additional_training_credits_c = 0;
                        }
                        elseif (!empty($bean->fetched_row) && $bean->fetched_row['additional_training_credits_c'] != $bean->additional_training_credits_c) {
                                $bean->additional_training_credits_c = $bean->fetched_row['additional_training_credits_c'];
                        }
                }

                // END jostrow customization

                // BEGIN jostrow customization
                // See ITRequest #7123: restrict edit access to "order type" in opportunities module

                if (!$current_user->check_role_membership('Finance')) {
                        if (empty($bean->fetched_row) && !empty($bean->order_type_c)) {
                                $bean->order_type_c = '';
                        }
                        elseif (!empty($bean->fetched_row) && $bean->fetched_row['order_type_c'] != $bean->order_type_c) {
                                $bean->order_type_c = $bean->fetched_row['order_type_c'];
                        }
                }

                // END jostrow customization
	}
	
	function sendOpportunityNotification($notify_user, $subject, $body, $cc_user = '', $from = array()){
		global $sugar_config;
		require_once("include/SugarPHPMailer.php");
		require_once("modules/Administration/Administration.php");
		
		$admin = new Administration();
		$admin->retrieveSettings();
		$notify_mail = new SugarPHPMailer();
		$body = from_html($body);
		$notify_mail->Body = $body;

		$subject = from_html($subject);
		$notify_mail->Subject = $subject;

		$notify_address = (empty($notify_user->email1)) ? from_html($notify_user->email2) : from_html($notify_user->email1);
		$notify_name = (empty($notify_user->first_name)) ? from_html($notify_user->user_name) : from_html($notify_user->first_name . " " . $notify_user->last_name);
		
		$notify_mail->AddAddress($notify_address, $notify_name);

		if(!empty($cc_user)){
			$notify_cc_address = (empty($cc_user->email1)) ? from_html($cc_user->email2) : from_html($cc_user->email1);
			$notify_cc_name = (empty($cc_user->first_name)) ? from_html($cc_user->user_name) : from_html($cc_user->first_name . " " . $cc_user->last_name);
			$notify_mail->AddCC($notify_cc_address, $notify_cc_name);
		}

		if ($admin->settings['mail_sendtype'] == "SMTP")
		{
			$notify_mail->Mailer = "smtp";
			$notify_mail->Host = $admin->settings['mail_smtpserver'];
			$notify_mail->Port = $admin->settings['mail_smtpport'];
			if ($admin->settings['mail_smtpauth_req'])
			{
				$notify_mail->SMTPAuth = TRUE;
				$notify_mail->Username = $admin->settings['mail_smtpuser'];
				$notify_mail->Password = $admin->settings['mail_smtppass'];
			}
		}
		
		$notify_mail->From = $admin->settings['notify_fromaddress'];
		$notify_mail->FromName = (empty($admin->settings['notify_fromname'])) ? "" : $admin->settings['notify_fromname'];
		if(!empty($from) && !empty($from['from_address'])){
			$notify_mail->From = $from['from_address'];
		}
		if(!empty($from) && !empty($from['from_name'])){
			$notify_mail->FromName = $from['from_name'];
		}
		
		if(!$notify_mail->Send())
		{
			$GLOBALS['log']->fatal("Opportunity Notification Logic Hook: error sending e-mail (method: {$notify_mail->Mailer}), (error: {$notify_mail->ErrorInfo})");
		}
		else
		{
			$GLOBALS['log']->debug("Opportunity Notification Logic Hook: e-mail successfully sent - user {$GLOBALS['current_user']->user_name}");
		}
	}
	
	//DEE CUSTOMIZATION SUGAR EXPRESS
	function applySugarExpress(&$bean, $event, $arguments){ // Sugar EXPRESS Support Cases customization (CE-OnDemand)
                if($event == 'before_save'){
                        require_once("custom/si_custom_files/SugarExpressFunctions.php");
                        spscOpportunitiesHandleSave($bean);
                }
        }
	//END DEE CUSTOMIZATION SUGAR EXPRESS

	function applyOSSC(&$bean, $event, $arguments){ // Open Source Support Cases customization (Sugar Network)
		if($event == 'before_save'){
			require_once("custom/si_custom_files/OSSCFunctions.php");
			osscOpportunitiesHandleSave($bean);
		}
	}
	
	function applyTC(&$bean, $event, $arguments){ // Training credits customization
		if($event == 'before_save'){
			require_once("custom/si_custom_files/TCFunctions.php");
			tcOpportunitiesHandleSave($bean);
		}
	}

	function setTeamFromLeadGroup(&$bean, $event, $arguments){
		// SADEK NOTE: THIS IS NOT WORKING CORRECTLY - IT SEEMS THE OPPORTUNITY_ID IS NOT GETTING POPULATED
		if($event == 'after_save'){
			$team_id_map = array(
				'Inside' => '519912f6-177e-3cb2-ad13-43d9142d7f0f', // Inside team
				'Partner' => '64570d8b-bb74-b1a5-d939-43d914a03625', // Channel team
				'Enterprise' => '90b6d8aa-c79a-f4e3-08df-43d91432d2a3', // Enterprise team
			);
			$lead_group_query = "select leadaccounts_cstm.lead_group_c lead_group_c ".
								"from leadaccounts inner join leadaccounts_cstm on leadaccounts.id = leadaccounts_cstm.id_c ".
								"where leadaccounts.opportunity_id = '{$bean->id}' and leadaccounts.deleted = 0 ".
								"order by leadaccounts.date_modified desc";
			$res = $GLOBALS['db']->query($lead_group_query);
			$row = $GLOBALS['db']->fetchByAssoc($res);
			if($GLOBALS['current_user']->user_name == 'sadek'){
			if(!$GLOBALS['current_user']->check_role_membership('Lead Qual Rep')){
				if($row && ($row['lead_group_c'] == 'Inside' || $row['lead_group_c'] == 'Partner' || $row['lead_group_c'] == 'Enterprise')){
					$GLOBALS['db']->query("update opportunities set team_id = '{$team_id_map[$row['lead_group_c']]}'");
				}
			}
			}
		}
	}

	function insertSalesStageAudits(&$bean, $event, $arguments){
		if($event == 'before_save'){
			if(!empty($bean->fetched_row['id']) && !empty($bean->fetched_row['sales_stage'])){
				if($bean->sales_stage == 'Closed Lost' || $bean->sales_stage == $bean->fetched_row['sales_stage']){
					return;
				}
				require('custom/si_custom_files/meta/OpportunitiesSalesStageConfig.php');
				$before_stage = $bean->fetched_row['sales_stage'];
				$after_stage = $bean->sales_stage;
				$audit_updates = array();
				$include = false;
				$timestamp = gmdate('Y-m-d H:i:s');
				$found_before = false;
				foreach($sales_stage_map as $stage_index => $probability){
					if($stage_index == $before_stage){
						$include = true;
						$found_before = true;
						continue;
					}
					if($include){
						if(!isset($last_stage)){
							$last_stage = $bean->fetched_row['sales_stage'];
						}
						$audit_updates[] = array('from_stage' => $last_stage, 'to_stage' => $stage_index);
						$last_stage = $stage_index;
					}
					if($stage_index == $after_stage){
						// If we've moved backwards in sales stages, we don't insert anything
						if(!$found_before){
							$audit_updates = array();
						}
						break;
					}
				}

				
				foreach($audit_updates as $change_array){
					$insert_query = "insert into opportunities_audit ".
									"set id = '".create_guid()."', parent_id = '{$bean->id}', date_created = '$timestamp', ".
									"created_by = '{$GLOBALS['current_user']->id}', field_name = 'sales_stage', data_type = 'enum', ".
									"before_value_string = '{$change_array['from_stage']}', after_value_string = '{$change_array['to_stage']}', ".
									"before_value_text = NULL, after_value_text = NULL";
					$GLOBALS['db']->query($insert_query);
				}
			}
		}
	}
	
	// Begin Moofcart - IT Request 9614 - Generate Renewal Opportunity
	function generateRenewalOpportunity(&$focus, $event, $arguments){
		$final_closed_stage = 'Finance Closed';

		if($focus->sales_stage != $final_closed_stage || 
			(
				!empty($focus->fetched_row['id']) && $focus->fetched_row['sales_stage'] == $final_closed_stage
			)
		){
			// We do nothing, since the opportunity has not been closed, or was already closed, and has been edited without updating sales stage
			return;
		}
	
		global $current_user;
		$noRenewLog .= '\n'.'---------------- current user is '.$current_user->user_name;	
		$noRenewLog .= '\n'.'1. Opp name is '.$focus->name.', id  is '.$focus->id.', stage is '.$final_closed_stage.', term is '.$focus->Term_c.' and revenue type is '.$focus->Revenue_Type_c;	

		// BEGIN jostrow customization
		// See ITRequest #10698
		// Do not create a renewal Opportunity if the original Opportunity is just for additional users
		if ($focus->Term_c == 'Remainder of Term' && $focus->Revenue_Type_c == 'Additional') {
			$noRenewLog .= '\n'.'2. Opp '.$focus->id.', failed the check for term (cannot be Remainder of Term) and revenue type (cannot be Additional)';	
			$GLOBALS['log']->fatal("Opportunities Logic Hook generateRenewalOpportunity on opp id {$focus->id} :: failed.  Info: ".$noRenewLog);
			return;
		}

		require_once('include/TimeDate.php');
		$timedate = new TimeDate();
		
		require_once('custom/si_custom_files/MoofCartHelper.php');
		$valid_types = MoofCartHelper::getRenewableOpportunityTypes('array');

		if(!in_array($focus->opportunity_type, $valid_types)){
			$noRenewLog .= '\n'.'3. Opp '.$focus->id.', failed the check for opportunity type(needs to be one of '.var_export($valid_types,true).')';	
			// We do nothing, since the opportunity type is not in the list of valid types
			$GLOBALS['log']->fatal("Opportunities Logic Hook generateRenewalOpportunity on opp id {$focus->id} :: failed.  Info: ".$noRenewLog);
			return;
		}
		
		$renewal_opp = new Opportunity();

		// Array of all the fields to be copied over directly		
		$direct_copy_fields = array(
			'associated_rep_c',
			'assigned_user_id',
			'team_id',
			'users',
			'partner_assigned_to_c',
			'account_id',
		);
		
		$fixed_value_fields = array(
			'competitor_1' => 'N/A',
			'additional_support_cases_c' => '0',
			'Term_c' => 'Annual',
			'Revenue_Type_c' => 'Renewal',
			'sales_stage' => 'Interested_Prospect',
		);
		
		// Retrieve subscription data from the related account for values in the new renewal opp
		// JOSTROW customization: after consulting with Sadek, we're not using this data for anything currently; commenting out this line
		//$dist_group_in_clause = MoofCartHelper::getRenewableDistributionGroups('in_clause');
/**/		
		$account = new Account();
		$account->disable_row_level_security = true;
		$account->retrieve($focus->account_id);
		if(empty($account->id)){
			$GLOBALS['log']->fatal("Opportunities Logic Hook generateRenewalOpportunity on opp id {$focus->id} :: Could not retrieve the account associated with opp - account id {$account->id}");
			return;
		}

		$subscription_data = MoofCartHelper::getLastExpirationDate($focus->account_id);
		if(empty($subscription_data)){
			$GLOBALS['log']->fatal("Opportunities Logic Hook generateRenewalOpportunity on opp id {$focus->id} :: Could not locate the last subscription.");
		/*
		 @author: EDDY
		** SUGARINTERNAL CUSTOMIZATION
		** ITRequest #: 16930 :: Auto Creation of Renewal Opps - Changes
		** Description: change conditions under which renewal opportunities are created
		** refactoring code to not fail if subscription is not found
		*/	
			//return;
		}
		
		/*
		 @author: EDDY
		** SUGARINTERNAL CUSTOMIZATION
		** ITRequest #: 16930 :: Auto Creation of Renewal Opps - Changes
		** Description: change conditions under which renewal opportunities are created
		** refactoring code to not fail if subscription is not found
		*/	
		if(empty($subscription_data) || empty($subscription_data['expiration_date'])){
			$subs_expiration_date = date('Y-m-d', strtotime("next year"));
		}else{
			$subs_expiration_date = $subscription_data['expiration_date'];
		}
		
		if(strtotime($subs_expiration_date) < time()){
			$GLOBALS['log']->fatal("Opportunities Logic Hook generateRenewalOpportunity on opp id {$focus->id} :: getLastExpirationDate() returned a date that was in the past");
		/*
		 @author: EDDY
		** SUGARINTERNAL CUSTOMIZATION
		** ITRequest #: 16930 :: Auto Creation of Renewal Opps - Changes
		** Description: change conditions under which renewal opportunities are created
		** refactoring code to not fail if subscription is not found
		*/	
			//return;
		}
/**/
		// See ITRequest #10528
		// If an "OnSite/OnDemand Type => Converge Type" mapping entry exists, use that
		// ...otherwise, copy the old Opportunity Type to the renewal Opportunity
		// NOTE: we're figuring out the Opportunity Type first, because other fields depend on it (Opportunity Name and the Amount)

		if (isset(MoofCartHelper::$opp_type_renewal_type_map[$focus->opportunity_type])) {
			$renewal_opp->opportunity_type = MoofCartHelper::$opp_type_renewal_type_map[$focus->opportunity_type];
		}
		else {
			$renewal_opp->opportunity_type = $focus->opportunity_type;
		}

		global $app_list_strings;
		$partner_string = !empty($focus->partner_assigned_to_c) ? "Partner " : "";
		// Begin setting field values from the previous opportunity
		$renewal_opp->name = $partner_string . $account->name . ' ' . $focus->users . ' ' . 
							$app_list_strings['opportunity_type_dom'][$renewal_opp->opportunity_type] . ' ' .
							substr($subs_expiration_date, 0, 4) . ' from ' . $focus->Revenue_Type_c . ' ' . $focus->Term_c;
		// Set all the fields that are directly copied from the previous opp
		foreach($direct_copy_fields as $field_name){
			$renewal_opp->$field_name = $focus->$field_name;
		}
		// Set all the fields that are set to fixed values
		foreach($fixed_value_fields as $field_name => $field_value){
			$renewal_opp->$field_name = $field_value;
		}
		$appstrings = return_app_list_strings_language('en_us');
		
		$renewal_opp->current_solution = $appstrings['opportunity_type_dom'][$focus->opportunity_type];
		$subscription_expiration = $timedate->to_display_date($subs_expiration_date, false);
		$renewal_opp->renewal_date_c = $subscription_expiration;
		$renewal_opp->date_closed = $subscription_expiration;
		$renewal_opp->load_relationship('contacts');
		$renewal_opp->amount = MoofCartHelper::getAmountFromOpportunityType($renewal_opp, $renewal_opp->users);

		// BEGIN jostrow customization
		// See ITRequest #10917
		// Set the 'Modified User' as the user assigned to the Opportunity; this way Sugar Feeds, etc. will display the assigned user as closing the Opportunity,
		// ... rather than the MoofCart user

		$renewal_opp->update_modified_by = FALSE;
		$renewal_opp->modified_user_id = $renewal_opp->assigned_user_id;
		$renewal_opp->modified_user_name = $renewal_opp->assigned_user_name;

		// END jostrow customization

		// Save the first time, because we have to before loading and adding relationships
		$renewal_opp->save(true);
		
		// BEGIN jostrow customization
		// If $renewal_opp->fetched_row is empty(), the second call to save() will create another item in Sugar Feeds

		$renewal_opp->fetched_row = array(TRUE);

		// END jostrow customization

		// End setting field values from the previous opportunity
		// Add the contacts from the previous opportunity to the renewal
		$focus->load_relationship('contacts');
		$contact_beans = $focus->contacts->getBeans(new Contact());
		foreach($contact_beans as $contact){
			$renewal_opp->contacts->add($contact->id);
		}
		
		// Not sure why, but saving the second time saves bad date format, so I have to reset them again after the first save
		$renewal_opp->renewal_date_c = $subscription_expiration;
		$renewal_opp->date_closed = $subscription_expiration;
		
		// Save the second time to solidify the relationships
		$renewal_opp->save(false);
	}
	// End Moofcart - IT Request 9614 - Generate Renewal Opportunity
	
	/**
	 * Function to update Opp before save when it is closed through the Partner Assigned Wizard.
	 *  IT Request #9851
	 *
	 * DEPRECATED: This function will probably be used for PRM Phase 2 when a user can update Opps from the EditView page to change the
	 * assigned partner.  Until then, all of the assignment related functions will be handled by the assignWizardSave.php function.
	 * 
	 * @param unknown_type $bean
	 * @param unknown_type $event
	 * @param unknown_type $arguments
	 */
	function updateOppFromPartnerWizard(&$bean, $event, $arguments){
		//Event should always be before_save but using the same syntax as previous logic hook definitions.
		if($event == 'before_save')
		{

		}
	}

	/*
	** DEE CUSTOMIZATION
	** ITREQUEST 10280: PRM Phase I: Create task when opportunity rejected
	*/
	function createTaskForRejectedOpp(&$bean, $event, $arguments) {
		if($event == 'before_save') {
			$old_bean = new Opportunity();
			$old_bean->retrieve($bean->id);

			if(isset($bean->accepted_by_partner_c) && !empty($bean->accepted_by_partner_c) && $bean->accepted_by_partner_c != 'R' && ($bean->accepted_by_partner_c == $old_bean->accepted_by_partner_c)) {
				return false;
			}	
		
			if(isset($bean->accepted_by_partner_c) && !empty($bean->accepted_by_partner_c) && $bean->accepted_by_partner_c == 'R' && ($bean->accepted_by_partner_c != $old_bean->accepted_by_partner_c)) {
				require_once('modules/Tasks/Task.php');

				$oppTask = new Task();
				$oppTask->assigned_user_id = $bean->assigned_user_id;
				$oppTask->name = "Rejected: Opportunity ".$bean->name.".";
				$oppTask->status = "Not Started";
				$oppTask->priority = "High";
				$oppTask->description = "Please reassign this Opportunity";
				$oppTask->save();
				
				$bean->load_relationship('tasks');
				$bean->tasks->add($oppTask->id);
			}
		}	
	}
	
	function updateSubscriptionOrderChange(&$bean, $event, $arguments){
		if($event == 'after_save'){
			// The order number has changed. Now we check to see if we can associate a new subscription with the parent account.
			if(!empty($bean->order_number) && (empty($bean->fetched_row['order_number']) || $bean->fetched_row['order_number'] != $bean->order_number)){
				$subscription = file_get_contents("http://www.sugarcrm.com/crm/get_subscription.php?key=934djasd81fDFefads34c234&order_id={$bean->order_number}");
				
				// There was a subscription found on sugarcrm.com
				if(!empty($subscription)){
					require_once('modules/Accounts/Account.php');
					$account = new Account();
					$account->disable_row_level_security = true;
					$account->retrieve($bean->account_id);
					
					// We were successfully able to retrieve the account associated with this opportunity
					if(!empty($account->id)){
						$sub_query = "select id from subscriptions where subscription_id = '{$subscription}' and deleted = 0";
						$res = $GLOBALS['db']->query($sub_query);
						$row = $GLOBALS['db']->fetchByAssoc($res);
						
						// We found the subscription in the database
						if($row){
							require_once('modules/Subscriptions/Subscription.php');
							$subscription = new Subscription();
							$subscription->disable_row_level_security = true;
							$subscription->retrieve($row['id']);
							
							// We now associate this subscription with the account
							if(!empty($subscription->id)){
								$account->load_relationship('subscriptions');
								$account->subscriptions->add($subscription->id);
								$account->update_date_modified = false;
								$account->update_modified_by = false;
								if(!empty($account->description)){
									$account->description .= "\n\n";
								}
								$account->description .= "Script: Automatically added subscription {$subscription->subscription_id} to this account based on order number {$order_number} from opportunity with id {$bean->id}";
								$account->save(false);
							}
						}
					}
				}
			}
		}
	}

	/*
	** DEE CUSTOMIZATION
	** sixtyMinOppTouched logic hook
	** If opportunity is a 60 min opp and is updated <= 120 minutes then unset sixtymin_opp_c
	*/
	function sixtyMinOppTouched(&$bean, $event, $arguments) {
                if($event == 'before_save') {
			if(isset($bean->sixtymin_opp_c) && $bean->sixtymin_opp_c == '1'
                        && isset($bean->sixtymin_opp_pass_c) && !empty($bean->sixtymin_opp_pass_c)
			&& isset($bean->fetched_row['date_entered']) && !empty($bean->fetched_row['date_entered'])
                        && $bean->modified_user_id != '1'
			) {
				//unset sixtymin_opp_c
				$bean->sixtymin_opp_c = 0;
			}
		}
	}


	//** CUSTOMIZATION EDDY :: ITTix 12405
        //this function will take in the current contact score id and return the maximum related score
        function updateMaxScore(&$bean, $event, $arguments){
		static $updateMaxScoreInProgress;

		//if this is coming from oppportunity then query for the right value to return for score but only the first time
		if($bean->table_name == 'opportunities' ){
			//check to see if this update is already in progress (called from contact),
			//we dont want to run this query if the save is from the contact update
			if($updateMaxScoreInProgress){			
				return;
			}else{
				require_once('custom/modules/Opportunities/get_related_interactions_query.php');
				$bean->score_c = get_max_score($bean->id);
				//this is before savc, so just return
				return;
			}

		}


                //process if bean id is passed in
		$query = '';
		$score = 0;
		$updateMaxScoreInProgress = true;
                if ($bean->table_name == 'contacts') $isLeadContact = false;
                //search for all opportunities that this contact/lead contact belongs to
                if (!empty($bean->id)){
			if($bean->table_name == 'leadcontacts'){
		                //if bean score is empty or 0, then return
		                if(!isset($bean->score) || empty($bean->score))return;

	                	$score = $bean->score;
				//create query based on leadcontact 
	                        $query = "select opp.id id, o_c.score_c score from opportunities opp ";
                                $query .="left join opportunities_cstm o_c on o_c.id_c = opp.id ";
                                $query .="left join leadaccounts la on la.opportunity_id = opp.id  ";
                                $query .="left join leadcontacts lc on lc.leadaccount_id = la.id  ";
                                $query .="where opp.deleted = 0 and la.deleted = 0 and lc.deleted = 0  ";
                                $query .="and lc.id = '".$bean->id."' ";
                        }elseif($bean->table_name == 'contacts'){
              			//if bean score is empty or 0, then return
		                if(!isset($bean->score_c) || empty($bean->score_c)) return;

                                $score = $bean->score_c;
                                //create query based on contact
                                $query = "select opp.id id, o_c.score_c score from opportunities opp ";
                                $query .="left join opportunities_cstm o_c on o_c.id_c = opp.id ";
                                $query .="left join accounts_opportunities ao on ao.opportunity_id = opp.id  ";
                                $query .="left join accounts_contacts accon on accon.account_id = ao.account_id ";
                                $query .="left join contacts con on con.id = accon.contact_id ";
                                $query .="where opp.deleted = 0 and ao.deleted = 0 and accon.deleted = 0 and con.deleted = 0 ";
                                $query .="and con.id = '".$bean->id."' ";
			}

                        //execute query and process results, if query is not empty
			if (empty($query)) return;
                        $result =$GLOBALS['db']->query($query);

                        //for each returned opportunity, compare the score
                        while ($row = $GLOBALS['db']->fetchByAssoc($result)){
                                //set to 0 if empty
                                if(empty($row['score'])) $row['score'] = 0;

                                //compare the scores
                                if($score>$row['score']) {
                                        //contact/leadcontact score is greater, retrieve the opportunity bean and update the score_c field
                                        $opp = new Opportunity();
                                        $opp->retrieve($row['id']);
                                        $opp->score_c = $score;
                                        $opp->save();
                                }

                        }
                }
		//reset static var
		$updateMaxScoreInProgress = false;
                return $score;

        }
//** END CUSTOMIZATION EDDY :: ITTix 12405

	/*
        ** DEE CUSTOMIZATION
        ** Send Customer Email when opportunity is Accepted by a Partner
        ** Check for -
        ** 1. Email object attached to the opportunity - In draft + flagged = 1
        ** 2. Contact email is NOT EMPTY
        */
        function sendCustomerEmail(&$bean, $event, $arguments) {
                if($event == 'before_save') {
                        $old_bean = new Opportunity();
                        $old_bean->retrieve($bean->id);

                        if(isset($bean->accepted_by_partner_c)
                                && !empty($bean->accepted_by_partner_c)
                                && $bean->accepted_by_partner_c != 'Y'
                                && ($bean->accepted_by_partner_c == $old_bean->accepted_by_partner_c)
                        ) {
                                return false;
                        }

                        if(isset($bean->accepted_by_partner_c)
                                && !empty($bean->accepted_by_partner_c)
                                && $bean->accepted_by_partner_c == 'Y'
                                && ($bean->accepted_by_partner_c != $old_bean->accepted_by_partner_c)
                        ) {
                                //Get Email attached to the opportunity
                                $db = DBManagerFactory::getInstance();
                                $query = "
                                        SELECT emails.id, emails.name, emails_text.description_html, emails.date_entered
                                        FROM emails
                                        INNER JOIN emails_beans ON (emails.id = emails_beans.email_id 
                                                AND emails_beans.bean_id = '" .$bean->id. "'
                                                AND emails_beans.bean_module = 'Opportunities') 
                                        INNER JOIN emails_text ON emails.id = emails_text.email_id
                                        WHERE emails.flagged = '1'
                                        AND emails.status = 'draft'
                                        AND emails.type = 'draft'
                                        AND emails.deleted = 0
                                        AND emails_beans.deleted = 0
                                        AND emails_text.deleted = 0
					ORDER BY emails.date_entered DESC
                                ";
                                $customer_email = array();
                                $result = $db->query($query);
                                $row = $db->fetchByAssoc($result);
                                if($row != null) {
                                        $customer_email['id'] = $row['id'];
                                        $customer_email['subject'] = $row['name'];
                                        $customer_email['description_html'] = $row['description_html'];
                                }

                                if(!isset($customer_email['id']) || empty($customer_email['id'])) {
                                        $GLOBALS['log']->fatal("--OpportunityHooks.php--sendCustomerEmail()--No customer email attached to opportunity {$bean->id}");
                                        return false;
                                }

                                //Get all contacts associated to this opportunity
                                require_once('modules/P1_Partners/P1_PartnersUtils.php');
                                $contact_arr = P1_PartnerUtils::getContacts($bean);

                                if(empty($contact_arr) || !isset($contact_arr)) {
                                        $GLOBALS['log']->fatal("--OpportunityHooks.php--sendCustomerEmail()--No contacts returned for OPP:{$bean->id}");
                                        return false;
                                }
                                foreach($contact_arr as $contact_id) {
                                        $email_object = P1_PartnerUtils::sendPRMEmail($contact_id, $customer_email['subject'], $customer_email['description_html']);
                                        //Attach email sent to the contact record
                                        $relate_email = clone $email_object;
                                        $relate_email->parent_id = $contact_id;
                                        $relate_email->parent_type = 'Contacts';
                                        $relate_email->save(FALSE);
                                        $relate_email->load_relationship('contacts');
                                        $relate_email->contacts->add($contact_id);
                                        $email_sent = 1;
                                }

				//update email record to status = Sent and flagged = 0
                                if(isset($email_sent) && !empty($email_sent) && $email_sent == '1') {
                                        $update_query = "UPDATE emails SET flagged = '0', status='sent', type='sent' WHERE id = '". $customer_email['id'] ."' and deleted = 0";
                                        $res = $db->query($update_query);
                                }
                        }
                }
        }

//** BEGIN CUSTOMIZATION EDDY IT TIX  13077
        function clearDependantValues(&$bean, $event, $arguments){
                // clear out any straggling values if closed_lost_reason_c is not set to
                //have dependant values
                if($event == 'before_save'){
                	if(isset($bean->closed_lost_reason_c) && ($bean->closed_lost_reason_c !='Abandoning CRM')
                	&& ($bean->closed_lost_reason_c !='Unable To Contact')
                	&& ($bean->closed_lost_reason_c !='Competitor')
                	) {
						$bean->closed_lost_reason_detail_c  = ''; 
						$bean->primary_reason_competitor_c = '';
                	}
                	
                }
        }
//** END CUSTOMIZATION EDDY IT TIX  13077

/*
 @author: EDDY
** SUGARINTERNAL CUSTOMIZATION
** ITRequest #: 16179 :Renewal Opps Not being created
** Description: this is debug code
*/
function debugLog($entry) {
    $nld = '
'.date("Y-m-d H:i:s").'...';

    $log = ('debug_ITR16179_renewalopp.log');

    // create if not exists
    if(!file_exists($log)) {
        $fp = @fopen($log, 'w+'); // attempts to create file
    } else {
        $fp = @fopen($log, 'a+'); // write pointer at end of file
    }
        
    @fwrite($fp, $nld.$entry);
	
    if(is_resource($fp)) {
        fclose($fp);
    }
}
//** END CUSTOMIZATION EDDY IT TIX  16179

	/*
        ** @author: DEE
	** SUGARINTERNAL CUSTOMIZATION
	** ITREQUEST 15907
	** Workflow for Closed Won Admin Fundamental Sales Opportunites
        */
        function createTaskForTrainingOpps(&$bean, $event, $arguments) {
                if($event == 'before_save') {
			//find position in string where name contains Admin Fundamental
			$opp_name_pos = strpos($bean->name, 'Admin Fundamental');
			//if sales stage = Finance Closed and opp name contains Admin Fundamental, create task
			if(
				isset($bean->sales_stage) 
				&& !empty($bean->sales_stage)
				&& $bean->sales_stage == 'Closed Won'
				&& !($bean->fetched_row['sales_stage'] ==  'Closed Won')	
				&& !($opp_name_pos === false)
			) {
                                require_once('modules/Tasks/Task.php');
				//create the task
                                $opp_task = new Task();
                                $opp_task->assigned_user_id = '5b758807-0a4c-df50-eaa1-4af4b4564cd2';
                                $opp_task->name = 'Register customer for class';
				$opp_task->task_type_c = 'university_reg';
                                $opp_task->status = 'Not Started';
                                $opp_task->priority = 'Medium';
                                $opp_task->description = "Register customer for Admin Fundamental class";
                                $opp_task->save();
				//associate task to opportunity
                                $bean->load_relationship('tasks');
                                $bean->tasks->add($opp_task->id);
                        }
                }
        }

}
