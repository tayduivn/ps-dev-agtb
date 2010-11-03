<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');


///////////
//////////////
//////////////////
///////////////
//
// Don't forget to add logging code to tally auto-links versus non-auto-links
// Also make sure, when running this against stage, to log percentage of successful auto-links
//
//////////////
//////////////
///////////
/////////////


class LeadQualAutomation {
    
    function autoWelcomeEmail(& $focus, $event, $arguments){
		$GLOBALS['log']->fatal('Called autoWelcomeEmail');
		global $app_list_strings;
		if ($event == "before_save") {
			if ( ! empty($focus->fetched_row['id']) ) {
				// If it's not new, we don't need to do anything else.
				$GLOBALS['log']->fatal('Bailed early from autowelcomeemail, ID:'.$focus->fetched_row['id']);
				return;
			}
			
			if(isset($focus->no_auto_welcome) && $focus->no_auto_welcome){
				return;
			}
			
			/*
			 * These should all be set for beans, so this test
			 * might no longer be necessary
			 */
			$isset_checks = array('status', 'email1', 'assigned_user_id');
			$failed_checks = false;
			foreach($isset_checks as $field_check){
				if (!isset($focus->$field_check)) {
					$GLOBALS['log']->fatal('Failed check: '.$field_check);
					$failed_checks = true;
				}
			}
			
			
			if (!empty($focus->assigned_user_id)) {
				$user = new User();
				$user->retrieve($focus->assigned_user_id);
				$department = $user->department;
			} else {
				/* No user assigned? */
				$user = null;
				$department = '';
			}
			
			$GLOBALS['log']->fatal('assigned users department: '.$department);

			$department_starts_with = 'Sales - ';
			$tests = empty($failed_checks)
				&& $user
				&& $department
				&& empty($focus->email_opt_out)
				&& empty($focus->do_not_call)
				&& strpos($department, $department_starts_with) === 0;
			
			if ($tests) {
				// Uncomment the two commented lines below to stop the Auto Welcome emails from going out
				//if(isset($focus->first_name) && $focus->first_name == 'Sadek' && isset($focus->last_name) && $focus->last_name == 'Baroudi'){
				$focus->status = 'Auto Welcome';
				$GLOBALS['log']->fatal('sending email');
				$this->sendAutoWelcomeEmail($focus, $user);
				//}
			}
		}	
    }
    
    function postAutoWelcomeEmail(&$focus, $event, $arguments){
		if ('after_save' == $event) {
			$testing = FALSE
				&& $focus->first_name == 'jmullan'
				&& $focus->last_name == 'test';
	    
			if (!empty($focus->assigned_user_id)) {
				$user = new User();
				$user->retrieve($focus->assigned_user_id);
				$department = $user->department;
			} else {
				$user = null;
				$department = '';
			}
			$focus->load_relationship('calls');
			$calls = $focus->get_linked_beans('calls', 'Call');
			$found_call = false;
			if ($calls) {
				foreach ($calls as $call) {
					if (isset($call->autoscheduledcall_c)
						&& $call->autoscheduledcall_c == 'LQ Initial call') {
						$found_call = true;
						break;
					}
				}
			}
	    
			$department_starts_with = 'Sales - Inside - Lead Qual';
			
			if ($testing
				|| (!$found_call
					&& $focus->status == 'Auto Welcome'
					&& empty($focus->email_opt_out)
					&& empty($focus->do_not_call)
					&& !empty($focus->email1)
					&& !empty($department)
					&& strpos($department, $department_starts_with) === 0)) {
				/* Per IT Request 3454
				 * After the initial call is created, the additional logic hooks are
				 * attached to the call.
				 */
				require_once('modules/Calls/Call.php');
				$today = date('j');
				switch (date('w')) {
					case 6:
						/* Saturday */
						$today += 2;
						break;
					case 5:
						/* Friday */
						$today += 3;
						break;
					default:
						/* All others */
						$today += 1;
						break;
				}
				/* YYYY-MM-DD HH:MM:SS */
				$call = new Call();
				$call->assigned_user_id = $focus->assigned_user_id;
				$call->autoscheduledcall_c = 'LQ Initial call';
				$call->name = 'Auto Scheduled Lead Qual Call';
				$timeDate = new TimeDate();
				$call->date_start = $timeDate->to_display_date_time(gmdate($GLOBALS['timedate']->get_db_date_time_format(), mktime(13, 0, 0, date('n'), $today, date('Y'))));
				$call->duration_hours = '0';
				$call->duration_minutes = '15';
				$call->status = 'Planned';
				$call->direction = 'Outbound';
				//$call->load_relationship('leads');
				//$call->leads->add($focus->id);
				$call->save();
				$focus->calls->add($call->id);
				// $focus->status = 'Auto Welcome Sent';
				// $focus->save();
				//exit;
			}
		}	
    }
    
	
	
	function sendAutoWelcomeEmail(& $focus, & $user){
	    /* IT Request 5591 */
	    /*
	     * We need the locale to pass in a character set to the email
	     * client later
	     */
	    global $locale;
		$template_id = '';
		switch($user->department){
			case 'Sales - Inside - Lead Qual I':
			case 'Sales - Inside - Lead Qual II':
				$template_id = '98440731-847f-56d3-5493-4818eb8f5308';
				break;
			case 'Sales - Enterprise - Lead Qual':
				$template_id = '559e2136-d6ba-abc4-9751-4818ebfccf7f';
				break;
			default:
			    break;
		}
		
		if (empty($template_id)){
			return;
		}
		
		require_once('modules/EmailTemplates/EmailTemplate.php');
		$template = new EmailTemplate();
		$template->retrieve($template_id);
		$macro_nv = array();
		$data = array('subject'=>$template->subject,
					'body_html'=>$template->body_html,
					'body'=>$template->body,
				);
		$template_data = $template->parse_email_template($data, 'Contacts', $focus, $macro_nv);
		
		require_once('include/SugarPHPMailer.php');
		require_once("modules/Administration/Administration.php");
		$mail = new SugarPHPMailer();
		$admin = new Administration();
		$admin->retrieveSettings();
		if ($admin->settings['mail_sendtype'] == "SMTP") {
			$mail->Host = $admin->settings['mail_smtpserver'];
			$mail->Port = $admin->settings['mail_smtpport'];
			if ($admin->settings['mail_smtpauth_req']) {
				$mail->SMTPAuth = TRUE;
				$mail->Username = $admin->settings['mail_smtpuser'];
				$mail->Password = $admin->settings['mail_smtppass'];
			}
			$mail->Mailer   = "smtp";
			$mail->SMTPKeepAlive = true;
		} else {
			$mail->mailer = 'sendmail';
		}
		$mail->From = $user->email1;
		$mail->FromName = $user->first_name . " " . $user->last_name;
		$mail->ContentType = "text/plain"; // "text/html";
		$mail->Subject = $template_data['subject'];
		if($mail->ContentType == 'text/plain'){
			$mail->Body = $template_data['body'];
		}
		else{
			$mail->Body = $template_data['body_html'];
		}
		$name = $focus->first_name . " " . $focus->last_name;
		$mail->AddAddress($focus->email1, $name);
		$mail->AddBCC($user->email1, $name);


		require_once('modules/Emails/Email.php');
		$email_bean = new Email();
		$email_bean->name = $template_data['subject'];
		$email_bean->from_addr = $user->email1;
		$email_bean->to_addrs = $focus->email1;
		if ($mail->ContentType == 'text/plain') {
		    $email_bean->description = $template_data['body'];
		} else {
		    $email_bean->description_html = $template_data['body_html'];
		}
		$email_bean->date_sent = date('Y-m-d h:i:s');
		$email_bean->assigned_user_id = $focus->assigned_user_id;
		$email_bean->save();
		$email_bean->load_relationship('leadcontacts');
		$email_bean->leadcontacts->add($focus->id);
		
		$logFile = '/var/www/sugarinternal/logs/welcomeEmails.log';
		$theid = !empty($focus->id) ? $focus->id : "new_record_no_id";
		$msg = "\"".date("Y-m-d H:i:s")."\",\"$theid\",\"{$focus->email1}\",\"$name\",\"{$mail->From}\",\"{$mail->FromName}\",\"{$user->department}\"";
		/*
		 * IT Request 5591 : This does the correct amount of fromHtml()
		 * calls so that quotes and other things go out correctly.
		 */
		$mail->prepForOutbound($locale->getPrecedentPreference('default_email_charset'));
		if (!$mail->Send()) {
			$msg .= ",\"send_failed\"\n";
			$fp = fopen($logFile, 'a');
			fwrite($fp, $msg);
			fclose($fp);
			$GLOBALS['log']->fatal("sendAutoWelcomeEmail() error: " . $mail->ErrorInfo);
		}
		else{
			$msg .= ",\"send_success\"\n";
			$fp = fopen($logFile, 'a');
			fwrite($fp, $msg);
			fclose($fp);
		}
	}
	
	// This will take incoming leads and assign them to the various reps in the 
	function leadQualRoundRobin(&$focus, $event, $arguments){
		if($event=='before_save'){
			// If it's assigned to the round_robin user (first) or Inside_Sales user (second one), perform the action
			if($focus->assigned_user_id == 'a35f3ce7-bcc8-5d71-1bc8-4727a25c8472' || $focus->assigned_user_id == 'ee815bc4-5279-a3a1-3ba5-443bdb6c6e94'){
				// Users who are a member of this department get the leads assigned to them
				$department = 'Sales - Inside - Lead Qual II';
				$category = 'InsideLeadQual';
				
				// SADEK BEGIN SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - ROUND ROBIN TO INSIDE SALES BASED ON REGION AND STATE
				if($focus->assigned_user_id == 'ee815bc4-5279-a3a1-3ba5-443bdb6c6e94'){
					require('custom/si_custom_files/meta/leadRoundRobinMap.php');
					if(isset($leadRoundRobinMap[$focus->region_c])){
						if(is_array($leadRoundRobinMap[$focus->region_c])){
							if(isset($leadRoundRobinMap[$focus->region_c][$focus->primary_address_state])){
								$department = $leadRoundRobinMap[$focus->region_c][$focus->primary_address_state];
								$category = $leadRoundRobinMap[$focus->region_c][$focus->primary_address_state];
							}
							else{
								// If we can't find one in the map, we assign to Leads_HotMktg
								$focus->assigned_user_id = 'c15afb6d-a403-b92a-f388-4342a492003e';
								return;
							}
						}
						else{
							$department = $leadRoundRobinMap[$focus->region_c];
							$category = $leadRoundRobinMap[$focus->region_c];
						}
						$department = "Sales - Inside - ".$department;
						$category = "InsideSales".$category;
					}
				}
				// SADEK END SUGARINTERNAL CUSTOMIZATION - IT REQUEST 8508 - ROUND ROBIN TO INSIDE SALES BASED ON REGION AND STATE
				
				
				// Get all the users in the department
				$users_query = "select id from users where department = '$department' and status = 'Active'";
				// if($GLOBALS['current_user']->user_name == 'sadek') $users_query = "select id from users where user_name = 'sadek'";
				$result = $GLOBALS['db']->query($users_query);
				$users_array = array();
				$counter = 1;
				while($row = $GLOBALS['db']->fetchByAssoc($result)){
					$users_array[$counter] = $row['id'];
					$counter++;
				}
				
				// Set the user that this lead will be getting assigned to
				$assign_user = 1;
				$assign_user_query = "select value from round_robin_tracker where category = '$category'";
				if($assign_user_res = $GLOBALS['db']->query($assign_user_query)){
					$assign_user_row = $GLOBALS['db']->fetchByAssoc($assign_user_res);
					if(!$assign_user_row){
						$insert_query = "insert into round_robin_tracker set category = '{$category}', value = 1, last_round_robin = NOW()";
						$GLOBALS['db']->query($insert_query);
					}
					else{
						$assign_user = $assign_user_row['value'];
					}
					if(!isset($users_array[$assign_user]))
						$assign_user = 1;
				}
			
				$assigned_user_id_backup = $focus->assigned_user_id;
				// Update the assigned user id
				if(!empty($users_array[$assign_user])){
					$focus->assigned_user_id = $users_array[$assign_user];
					$GLOBALS['db']->query("insert into round_robin_log set assigned_user_id = '{$users_array[$assign_user]}', date_entered = NOW(), record_id = '{$focus->id}', record_type = '".get_class($focus)."', created_by = '{$GLOBALS['current_user']->id}'");
				}
				
				if(count($users_array) < 1){
					$GLOBALS['log']->fatal("round_robin count of users array is 0. break out");
					return;
				}
				
				// Set the next assigned user for the next lead
				$assign_user_write_query = "update round_robin_tracker set value = '".((intval($assign_user) % count($users_array)) + 1)."', last_round_robin = NOW() where category = '{$category}'";
				$GLOBALS['db']->query($assign_user_write_query);
			}
		}
	}
	
	/**
	 * Instead of the Round Robin workflow: M2: LQ - C3:O2: Round Robin - O1 check to see if the assigned
	 * user's department is already part of the Lead Qual dept, is so then do not assigned to round robin user.
	 *
	 * @param SugarBean $focus
	 * @param String $event
	 * @param Array $arguments
	 */


	function leadQualAssignRoundRobinUser(&$focus, $event, $arguments){
		if($event=='before_save'){
			// do not touch the bean if it is already assigned a user in this department
			$departments = array ( 'Sales - Inside', 'Sales - Channels' );
			$user_department = '';
			if (!empty($focus->assigned_user_id)) {
				$user = new User();
				$user->retrieve($focus->assigned_user_id);
				$user_department = $user->department;
			}
			$created_user_department = '';
			if(isset($_REQUEST['action']) && $_REQUEST['action'] == 'ScrubSave') {
				$touchpoint_id = $_REQUEST['record'];
				$touchpoint_row = $GLOBALS['db']->fetchByAssoc($GLOBALS['db']->query("select created_by from touchpoints where id = '{$touchpoint_id}'"));
				$created_user = new User();
				$created_user->retrieve($touchpoint_row['created_by']);
				$created_user_department = $created_user->department;
			}
			//if the user's department is the one above, then leave it alone, otherwise
			//assign to round robin user
			$regions = array('canada', 'usa', 'australia');
			$region_check = (in_array(strtolower($focus->region_c), $regions)) ? true : false;
			$department_check = (in_array($user_department, $departments)) ? true : false;
			$created_dept_check = (strpos($created_user_department, 'Sales - Inside') === 0) ? true : false;
			if( !$department_check && $region_check && $focus->scrub_flag && !$created_dept_check){
				$focus->assigned_user_id = 'ee815bc4-5279-a3a1-3ba5-443bdb6c6e94'; // Inside_Sales
			}
			else{
				$focus->no_auto_welcome = true;
			}
		}
	}

	// This is for lead auto routing based on territory
	function leadAutoRoute(&$focus, $event, $arguments){
		// remove the line below to go live with this
		return;
			
		if($event=='before_save'){
			// If the end user just checked the lead pass checkbox, we can auto route
			if( (!isset($focus->fetched_row['lead_pass_c']) || $focus->fetched_row['lead_pass_c'] == '0') &&
				  isset($focus->lead_pass_c) && ($focus->lead_pass_c == '1' || $focus->lead_pass_c == 'on') ){
				
				// If the values are set, we can attempt to auto rout
				if(!empty($focus->lead_group_c) && $focus->lead_group_c != 'Unknown' && !empty($focus->primary_address_country)){
					require_once('custom/si_custom_files/meta/leadRoutingMeta.php');

					// If the state is set and there is a mapping for the state
					if(!empty($focus->primary_address_state) && !empty($leadBreakdownMap[$focus->lead_group_c][$focus->primary_address_country][$focus->primary_address_state])){
						$focus->assigned_user_id = $leadBreakdownMap[$focus->lead_group_c][$focus->primary_address_country][$focus->primary_address_state];
					}
					// Otherwise, if the country is set (checked in the last major if condition) and the mapping for it is not empty
					else if(!empty($leadBreakdownMap[$focus->lead_group_c][$focus->primary_address_country])){
						$focus->assigned_user_id = $leadBreakdownMap[$focus->lead_group_c][$focus->primary_address_country];
					}
				}
			}
		}
	}
	
	// NOTE NOTE NOTE
	// ANY TIME YOU UPDATE THIS FUNCTION, MAKE SURE YOU UPDATE ./scripts/siLeadScoring.php ACCORDINGLY - IT'S A SCRIPT THAT RUNS IN CRON TO MIMIC THIS FUNCTION
	// NOTE NOTE NOTE
	function leadScore(&$focus, $event, $arguments){
		if($event == 'before_save'){
			global $lead_score_details;
			global $set_lead_score_details;
			global $mod_strings;
			
			require('custom/si_custom_files/meta/leadScoringMeta.php');
			$multiplier = 1;
			$lead_score = 0;
			foreach($leadScoringMeta as $field_name => $value_array){
				$arr_index = '';
				$field_display = $field_name;
				if(!empty($focus->field_defs[$field_name]['vname']) && !empty($mod_strings[$focus->field_defs[$field_name]['vname']])){
					$field_display = $mod_strings[$focus->field_defs[$field_name]['vname']];
				}
				if(isset($focus->$field_name) && array_key_exists($focus->$field_name, $value_array)){
					$arr_index = $focus->$field_name;
				}
				else if(array_key_exists('_OTHER_', $value_array)){
					$arr_index = '_OTHER_';
				}
				
				if(!empty($arr_index) && !empty($value_array[$arr_index]['value'])){
					$action = '';
					$display_value = ($arr_index == '_OTHER_' ? '<i>(other weighted value)</i>' : $arr_index);
					switch($value_array[$arr_index]['type']){
						case 'multiplier':
								if(isset($set_lead_score_details) && $set_lead_score_details){ $action = 'multplying'; }
								$multiplier *= $value_array[$arr_index]['value'];
								break;
						case 'division':
								if(isset($set_lead_score_details) && $set_lead_score_details){ $action = 'dividing'; }
								$multiplier /= $value_array[$arr_index]['value'];
								break;
						case 'addition':
								if(isset($set_lead_score_details) && $set_lead_score_details){ $action = 'adding to'; }
								$lead_score += $value_array[$arr_index]['value'];
								break;
						case 'subtraction':
								if(isset($set_lead_score_details) && $set_lead_score_details){ $action = 'subtracting from'; }
								$lead_score -= $value_array[$arr_index]['value'];
								break;
						default; break;
					}
					if(!empty($action) && !empty($focus->id)){
						if($field_name == 'campaign_name' && !empty($focus->$field_name)){
							$c_res = $GLOBALS['db']->query("select name from campaigns where id = '{$focus->$field_name}'");
							$c_row = $GLOBALS['db']->fetchByAssoc($c_res);
							$display_value = $c_row['name'];
						}
						$msg = "The field <b>$field_display</b> had a value of <b>$display_value</b>, $action the lead score by <b>{$value_array[$arr_index]['value']}</b>.";
						$lead_score_details[$focus->id][] = $msg;
					}
				}
			}
			//DEE CUSTOMIZATION - ITREQUEST 4502
			if(isset($focus->parent_lead_id) && !empty($focus->parent_lead_id))
			{
				$lead_score = $lead_score + 50;  
				if(isset($set_lead_score_details) && $set_lead_score_details){
					$lead_score_details[$focus->id][] = "The field <b>Lead Role</b> is <b>Child</b>, adding the lead score by <b>50</b>.";
				}
			}
			//END DEE CUSTOMIZATION - ITREQUEST 4502
			$focus->lead_score = $lead_score * $multiplier;
		}
	}
	
	// This function will redirect Lead Passes over to the Lead Convert screen
	function leadPassConvertRedirect(&$focus, $event, $arguments){
		// DEBUG CODE BELOW
		// sugar_die("{$_REQUEST['module']} {$_REQUEST['action']} {$_REQUEST['record']} {$focus->fetched_row['lead_pass_c']} {$focus->lead_pass_c} {$focus->lead_relation_c}");
		
		// If the user did not come from the Lead EditView, we ignore this
		if(!isset($_REQUEST['module']) || !isset($_REQUEST['action']) || !isset($_REQUEST['record']) || 
				  $_REQUEST['module'] != 'Leads' || $_REQUEST['action'] != 'Save' || $_REQUEST['record'] != $focus->id){
			return;
		}
		
		if($event=='before_save'){
			$lead_pass_old = (!isset($focus->fetched_row['lead_pass_c']) || $focus->fetched_row['lead_pass_c'] == '0');
			$lead_pass_new = (isset($focus->lead_pass_c) && ($focus->lead_pass_c == '1' || $focus->lead_pass_c == 'on'));
			//remove for M2
			//$lead_role = (!empty($focus->lead_relation_c) && $focus->lead_relation_c == 'Parent');
			$lead_group = (!empty($focus->lead_group_c) && $focus->lead_group_c == 'Inside');
			
			if($lead_pass_old && $lead_pass_new && $lead_group){
				$focus->lead_redirect_url = "index.php?module=LeadAccounts&action=ConvertLead&record={$focus->id}";
			}
		}
	}

	function salesRecordAssignLogic(&$focus, $event, $arguments){
		if($event == 'before_save'){
			require_once('custom/si_custom_files/custom_functions.php');
			$return_assignment = siGetSalesAssignmentMap($focus);
			
			if(!empty($return_assignment)){
			    $focus->assigned_user_id = $return_assignment['assigned_user_id'];
			}
		}
	}
	
}


?>
