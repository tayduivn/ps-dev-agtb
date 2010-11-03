<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// updates an Opportunity's probability based on sales stage
// sales stage -> probability mapping stored in custom/si_custom_files/meta/OpportunitiesSalesStageConfig.php

global $default_sales_stage;
$default_sales_stage = 'Interested_Prospect';

class OpportunityHooks  {
	function verifyCloseDate(&$bean, $event, $arguments) {
		global $current_user;
		if($event == "before_save"){
			require_once('custom/si_custom_files/custom_functions.php');
			// This block prevents the non Finance members from changing the close date to prior to this month
			if(!$current_user->check_role_membership('Finance') && !$current_user->check_role_membership('Sales Operations')){
				$closed_sales_stages = getSugarInternalClosedStages('array');
				
				$year_idx = 0;
				$month_idx = 1;
				$day_idx = 2;
				
				// if they didn't change the date, don't do anything
				/* moved condition below since there is additional checking below, not just on the dates
				if($bean->date_closed == $bean->fetched_row['date_closed']){
					return;
				}
				*/
				
				$newrecord = false;
				if(empty($bean->fetched_row['id'])){
					$newrecord = true;
				}
				
				// Default to the value the user entered
				$final_insert = $bean->date_closed;
				$notify = false;
				
				// Get the user date format
				$userpref = $current_user->getUserDateTimePreferences();
				$TimeDate = new TimeDate();
				
				// Get the entered value in standard format
				$ent_arr = explode("-", $bean->date_closed);
				
				// Get today's date in standard format
				$today_date = date("Y-m-d");
				$tod_arr = explode("-", $today_date);
				
				// The message in the email that gets sent out
				$msg = '';
				
				$previous = '';
				$prv_arr = array();
				if(!$newrecord){
					// Get the previous value in standard format
					$prv_arr = explode("-", $bean->fetched_row['date_closed']);
					
					// If the record is in the past, don't allow the users to change the Closed Date, Sales Stage, or Amount
					if(
						($prv_arr[$year_idx] < $tod_arr[$year_idx]) ||
					 	($prv_arr[$year_idx] == $tod_arr[$year_idx] && $prv_arr[$month_idx] < $tod_arr[$month_idx])
					){
						$audit_fields = array(
							/*
							array(
								'display' => 'Expected Close Date',
								'field' => 'date_closed',
							),
							*/
							array(
								'display' => 'Sales Stage',
								'field' => 'sales_stage',
							),
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
							$msg .= "\nThis opportunity was closed in a previous month.\n$additional_msg";
						}
					}
				}
				
				// If the entered year is before this year or the entered year is equal and the month is prior, fail
				if( 
					$bean->date_closed != $bean->fetched_row['date_closed'] && 
					(
						($ent_arr[$year_idx] < $tod_arr[$year_idx]) ||
						($ent_arr[$year_idx] == $tod_arr[$year_idx] && $ent_arr[$month_idx] < $tod_arr[$month_idx])
					)
				){
					// if it's a new record, default to the current date, else don't change it
					if($newrecord){
						$final_insert = date("Y-m-d", mktime(0,0,0,$tod_arr[$month_idx],$tod_arr[$day_idx],$tod_arr[$year_idx]));
						$final_insert = $TimeDate->swap_formats($final_insert, "Y-m-d", $userpref['date']);
						$msg .= "You changed the Expected Close Date to a month prior to the current month.  The system has set the date to today's date.\n";
					}
					else{
						$final_insert = $bean->fetched_row['date_closed'];
						$msg .= "You set the Expected Close Date to a month prior to the current month. The system has left the value unchanged.\n";
					}
					$notify = true;
				}
				else if( !$newrecord && in_array($bean->fetched_row['sales_stage'], $closed_sales_stages) && $bean->date_closed != $bean->fetched_row['date_closed'] && 
					 (
						($prv_arr[$year_idx] < $ent_arr[$year_idx]) ||
					 	($prv_arr[$year_idx] == $ent_arr[$year_idx] && $prv_arr[$month_idx] < $ent_arr[$month_idx])
					 )
				){
					$final_insert = $bean->fetched_row['date_closed'];
					$msg .= "You moved the Expected Close Date on a closed opportunity from a previous month to a current or future month. The system has left the value unchanged.\n";
					$notify = true;
				}
				
				/* DEBUG
				echo $msg."<BR>";
				echo $bean->fetched_row['sales_stage']."<BR>";
				echo $bean->sales_stage."<BR>";
				echo $bean->fetched_row['amount']."<BR>";
				echo $bean->amount."<BR>";
				print_r($prv_arr); echo "<BR>";
				print_r($ent_arr); echo "<BR>";
				print_r($tod_arr); echo "<BR>";
				sugar_die('');
				*/
				
				if($notify){
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
			if($current_user->check_role_membership('Finance') || $current_user->check_role_membership('Sales Operations Opportunity Admin')){
				return;
			}
			
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

		if(isset($bean->sales_stage) && $bean->sales_stage == 'Initial_Opportunity'){
			if(!empty($bean->fetched_row) && $bean->fetched_row['sales_stage'] != 'Initial_Opportunity'){
				$bean->sales_stage = $bean->fetched_row['sales_stage'];
			}
			else if(empty($bean->fetched_row)){
				if(!isset($_REQUEST['action']) || $_REQUEST['action'] != 'ConvertLead'){
					$bean->sales_stage = $default_sales_stage;
				}
			}
		}
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
	
	function teamSecurityRetrieveLogic(&$bean, $event, $arguments){
		if($event == 'before_retrieve'){
			if($GLOBALS['current_user']->check_role_membership('Lead Qual Rep')){
				if(isset($_REQUEST['action']) && ($_REQUEST['action'] == 'DetailView' || $_REQUEST['action'] == 'EditView')){
					$bean->disable_row_level_security = true;
				}
			}
		}
	}
	
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
}
