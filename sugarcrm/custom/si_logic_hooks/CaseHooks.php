<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

// Assign a case to a support rep automatically based on account
// account to rep mapping in -> custom/si_logic_hooks/meta/supportAccountRepMap.php

class CaseHooks  {
		
	//DEE CUSTOMIZATION SUGAR EXPRESS
        function applySugarExpress(&$bean, $event, $arguments){ // Open Source Support Cases customization (Sugar Network)
                if($event == 'before_save'){
                        require_once("custom/si_custom_files/SugarExpressFunctions.php");
                        spscCasesHandleSave($bean);
                }
        }
        //END DEE CUSTOMIZATION SUGAR EXPRESS

	function mapCaseToRep(&$bean, $event, $arguments) {
		global $current_user;
		if($event == "before_save"){
			if (empty($bean->fetched_row)) { // For new records only
				require('custom/si_logic_hooks/meta/supportAccountRepMap.php');
				$priority_level = isset($bean->priority_level) ? substr($bean->priority_level, 0, 2) : "";
				$status = isset($bean->status) ? $bean->status : "";
				$account_id = isset($bean->account_id) ? $bean->account_id : "";
				if($status == "New" && $priority_level != "P1" && array_key_exists($account_id, $repToAccountMap)){
					$bean->assigned_user_id = $repToAccountMap[$account_id];

					// if overriding the assigned_user_id in any Cases logic hook, we need to set this flag
					// this prevents the other case routing logic from triggering and overriding the new assigned_user_id value
					$bean->skipCaseRoutingHandler = TRUE;
				}
			}
		}
	}

        function caseportalAssignment(&$bean, $event, $arguments) {
                global $current_user;
                if($event == "before_save"){
                        if (empty($bean->fetched_row)) { // For new records only
                               if($bean->created_by == '1d72284d-6496-c5cb-4312-47433f21629b') {
					$bean->team_id = '1';

                                        switch ( $bean->request_type_c) {
                                                case 'technical_support' :
                                                       $bean->assigned_user_id = '4d3fabcd-f98f-64b4-ec4e-42828344f2e4'; // support
                                                        break;
/*                                              case 'pre_sales_support' :
							$bean->assigned_user_id = '';
                                                        break;
*/
                                                case 'general_sales' :
                                                        $bean->assigned_user_id = 'c15afb6d-a403-b92a-f388-4342a492003e'; // Leads_HotMktg
                                                        break;
/*                                                case 'partner' :
                                                        $bean->assigned_user_id = '';
                                                        break;
*/
                                                case 'university_support' :
                                                        $bean->assigned_user_id = 'e935faaf-87bf-d37f-e1ad-4471e99d4d6a'; // training
                                                        break;
                                                case 'sugarexchange_support' :
                                                        $bean->assigned_user_id = '226c1cfe-2657-bfbe-40dc-45213d623c90'; // rbazzari
                                                        break;
                                                default :
                                                        $bean->assigned_user_id = '4d3fabcd-f98f-64b4-ec4e-42828344f2e4';
                                                        break;
                                                }

					// if overriding the assigned_user_id in any Cases logic hook, we need to set this flag
					// this prevents the other case routing logic from triggering and overriding the new assigned_user_id value
					// in this case, any Cases other than 'technical_support' type cases should not be handled by the routing logic
					if ($bean->request_type_c != 'technical_support') {
						$bean->skipCaseRoutingHandler = TRUE;
					}

                                }
                        }
                }
        }

	// Created for ITRequest 4365 for partner cases.
	function partnerAssignmentMap(&$bean, $event, $arguments) {
		global $current_user;
		if($event == "before_save"){
			if (empty($bean->fetched_row) && !isset($bean->skipCaseRoutingHandler)) { // For new records only
				$account_id = isset($bean->account_id) ? $bean->account_id : "";
				if(!empty($account_id)){
					$partner_account_types = array(
						'Partner',
						'Partner-Pro',
						'Partner-Ent',
					);
					require_once('modules/Accounts/Account.php');
					$parent_account = new Account();
					$parent_account->disable_row_level_security = true;
					$parent_account->retrieve($account_id);
					if(in_array($parent_account->account_type, $partner_account_types)){
						$bean->assigned_user_id = '43ba5092-fd14-9e57-b4bd-48efc5bc07a3'; // user 'support-partner'

						// if overriding the assigned_user_id in any Cases logic hook, we need to set this flag
						// this prevents the other case routing logic from triggering and overriding the new assigned_user_id value
						$bean->skipCaseRoutingHandler = TRUE;
					}
				}
			}
		}
	}

	function caseSurveyInvite(& $focus, $event, $arguments){
		global $app_list_strings;
		if($event=="before_save"){
			if( isset($focus->fetched_row['status']) && $focus->fetched_row['status'] != 'Closed' &&
			    isset($focus->status) && $focus->status == 'Closed' &&
			    isset($focus->request_type_c) && $focus->request_type_c == 'technical_support'
			){
				$department = '';
				if(!empty($focus->assigned_user_id)){
					$user = new User();
					$user->retrieve($focus->assigned_user_id);
					$department = $user->department;
				}

				$department_starts_with = 'Customer Support';

				if(!empty($department) && strpos($department, $department_starts_with) === 0){
					// Uncomment the two commented lines below to stop the Auto Welcome emails from going out
					//if(isset($focus->first_name) && $focus->first_name == 'Sadek' && isset($focus->last_name) && $focus->last_name == 'Baroudi'){
						$this->sendCaseSurveyInvite($focus, $user);
					//}
				}
			}

		}
	}

	function sendCaseSurveyInvite(& $focus, & $user){
	    global $locale;
		$template_id = '';
		switch($user->department){
			case 'Customer Support':
				$template_id = '736401d3-5b46-bde4-7f06-4772c1d3d5ed';
				break;
			default:
				break;
		}

		if(empty($template_id)){
			$GLOBALS['log']->fatal("Error in sendCaseSurveyInvite: No valid template_id");
			return;
		}

		require_once('modules/EmailTemplates/EmailTemplate.php');
		$template = new EmailTemplate();
		$template->retrieve($template_id);
		$macro_nv = array();
		$data = array('subject'=>$template->subject,
					'body_html'=>from_html($template->body_html),
					'body'=>$template->body,
				);
		$template_data = $template->parse_email_template($data, 'Cases', $focus, $macro_nv);

		require_once('include/SugarPHPMailer.php');
		require_once("modules/Administration/Administration.php");
		require_once('include/workflow/alert_utils.php');
		$mail = new SugarPHPMailer();
		$admin = new Administration();
		$admin->retrieveSettings();
		fill_mail_object($mail, $focus, $template_id, '');
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

		$related_contacts = $focus->get_linked_beans('contacts', 'Contact');
		foreach($related_contacts as $contact_bean){
			$name = $contact_bean->first_name . " " . $contact_bean->last_name;
			$mail->AddAddress($contact_bean->email1, $name);
			//$mail->AddBCC($user->email1, 'Support Rep');
			//$mail->AddBCC('sadek@sugarcrm.com', 'Sadek Baroudi');
			/*
			$logFile = 'surveyInviteEmails.log';
			$msg = "\"".date("Y-m-d H:i:s")."\",\"{$focus->id}\",\"{$related_contact->email1}\",\"$name\",\"{$user->user_name}\"";
			*/
			
			$mail->prepForOutbound($locale->getPrecedentPreference('default_email_charset'));
			if (!$mail->Send()) {
			/*
				$msg .= ",\"send_failed\"\n";
				$fp = fopen($logFile, 'a');
				fwrite($fp, $msg);
				fclose($fp);
				$GLOBALS['log']->fatal("sendCaseSurveyInvite() error: " . $mail->ErrorInfo);
			*/
			}
			else{
			/*
				$msg .= ",\"send_success\"\n";
				$fp = fopen($logFile, 'a');
				fwrite($fp, $msg);
				fclose($fp);
			*/
			}
			$mail->ClearAddresses();
			$mail->ClearCCs();
			$mail->ClearBCCs();
		}
	}

	function applyOSSC(&$bean, $event, $arguments){ // Open Source Support Cases customization (Sugar Network)
		if($event == 'before_save'){
			require_once("custom/si_custom_files/OSSCFunctions.php");
			osscCasesHandleSave($bean);
		}
	}

	function fillInSupportServiceLevel(&$focus, $event, $arguments){
		if($event == 'before_save'){
			$reapplyDeploymentOption = false;
			$reapplySupportServiceLevel = false;

			// CHECK FOR SUPPORT SERVICE LEVEL
			// If this is a new case record, the support service level isn't set, and the account is set, we retrieve it from the account
			if(empty($focus->fetched_row['id']) && empty($focus->support_service_level_c) && !empty($focus->account_id)){
				$reapplySupportServiceLevel = true;
			}
			// If someone changed the associated account, we update the support service level
			else if(!empty($focus->fetched_row['id']) && $focus->fetched_row['account_id'] != $focus->account_id){
				$reapplySupportServiceLevel = true;
			}

			// CHECK FOR DEPLOYMENT OPTION
			// If this is a new case record, the support service level isn't set, and the account is set, we retrieve it from the account
			if(empty($focus->fetched_row['id']) && empty($focus->deployment_c) && !empty($focus->account_id)){
				$reapplyDeploymentOption = true;
			}
			// If someone changed the associated account, we update the support service level
			else if(!empty($focus->fetched_row['id']) && $focus->fetched_row['account_id'] != $focus->account_id){
				$reapplyDeploymentOption = true;
			}

			if($reapplySupportServiceLevel || $reapplyDeploymentOption){
				require_once('modules/Accounts/Account.php');
				$parent_account = new Account();
				$parent_account->disable_row_level_security = true;
				$parent_account->retrieve($focus->account_id);
				if($reapplySupportServiceLevel && !empty($parent_account->Support_Service_Level_c)){
					global $app_list_strings;
					$focus->support_service_level_c = $app_list_strings['Support Service Level'][$parent_account->Support_Service_Level_c];
				}
				if($reapplyDeploymentOption && !empty($parent_account->deployment_type_c)){
					$focus->deployment_c = $parent_account->deployment_type_c;
				}
			}
		}
	}

	// DEE 11.18.2008 - ITREQUEST. Implementation of escalation field
	function saveMyEscalation(&$focus, $event, $arguments) {

		if($event == 'after_save') {
			global $current_user;
			if(isset($current_user))
			{
				if(isset($_REQUEST['escalate_case']) && !empty($_REQUEST['escalate_case']))
				{
					$focus->load_relationship('user_escalation');
					$focus->user_escalation->add($current_user->id);
				}
				else
				{
					$focus->load_relationship('user_escalation');
					$focus->cases_users_id = null;
                			$focus->user_escalation->delete($focus->id, $current_user->id);
				}
			}
			/*$focus->description = "";
			if(isset($_REQUEST['escalate_case']) && !empty($_REQUEST['escalate_case'])) {
				$focus->description = $_REQUEST['escalate_case'];
			}
			else
				$focus->description = 'Escalation field is not set';

			$focus->save();*/
		}
	}

	function showMyEscalation(&$focus, $event, $arguments) {
		if($event == 'after_retrieve') {
			global $current_user;
			$focus->load_relationship("user_escalation");
                	$query_array=$focus->user_escalation->getQuery(true);
                	$query_array['where'] .= " AND users.id = '$current_user->id'";
                	$query='';
                	foreach ($query_array as $qstring)
                	{
                        	$query.=' '.$qstring;
                	}
                	$list = $focus->build_related_list($query, new User());
                	if(!empty($list))
                	{
                        	$focus->cases_users_id = $list[0]->id;
                		$focus->escalate_case = true;
			}
			else
				$focus->escalate_case = false;

		}
	}
	// END DEE 11.18.2008

	/*
	** @author: dtam
	** SUGARINTERNAL CUSTOMIZATION
	** ITRequest #: 18265
	** Description: update flag to set case to be onboard customer true on assignment
	** Wiki customization page: internalwiki.sjc.sugarcrm.pvt/index.php/CaseRoutingHandler
	*/
	function setCaseOnBoardFlag(&$focus, $event, $arguments) {
		if ($focus->assigned_user_id == '6db80edb-55ae-5a59-2185-49416eec67e5' && $event == "before_save" && $focus->onboard_customer_c != '1') {
			$focus->onboard_customer_c = '1';
			return;
		}
	}
	/* END SUGARINTERNAL CUSTOMIZATION */
	
	// BEGIN jostrow ITR 5039
	function caseRoutingHandler(&$focus, $event, $arguments) {
		global $current_user;

		require_once('custom/si_custom_files/custom_functions.php');

		$maintenance_customer_user = '4d3fabcd-f98f-64b4-ec4e-42828344f2e4'; // support
		$new_customer_user = '6db80edb-55ae-5a59-2185-49416eec67e5'; // support-onboard
		$support_user = '4d3fabcd-f98f-64b4-ec4e-42828344f2e4'; // support
		$express_user = '14315ffa-0d22-0623-5acc-49f2099b8ad5'; // support-express  // NASSI

		$new_customer_cutoff = 90; // days

		$relevant_account_types = array(
			'Customer',
			'Customer-Ent',
			'Customer-Pro',
		);

// NASSI
		$decrement_account_types = array(
			'network',
			'Customer-Express',
		);
// NASSI

		$relevant_opportunity_types = array(
			'sugar_ent_converge',
			'sugar_pro_converge',
			'Sugar Enterprise',
			'Sugar Professional',
			'Sugar Enterprise On-Demand',
			'Sugar OnDemand',
			'Sugar Cube',
		);

		//_jmotmplog(date("Y-m-d H:i:s") . " - in function caseRoutingHandler() for case: {$focus->id}", FALSE);

		// we will only handle cases that are explicitly assigned to the support user
		if ($focus->assigned_user_id != $support_user) {
			//_jmotmplog("aborting because Case isn't assigned to support user (it's assigned to User {$focus->assigned_user_id}");
			return;
		}

		//_jmotmplog("value of focus->skipCaseRoutingHandler is: " . var_export($focus->skipCaseRoutingHandler, TRUE));
		//_jmotmplog("is focus->fetched_row empty: " . var_export(empty($focus->fetched_row), TRUE));
		//_jmotmplog("focus->status is {$focus->status}");
		//_jmotmplog("focus->request_type_c is {$focus->request_type_c}");

		// this is a new Technical Support type Case, and...
		// this Case has not already been reassigned by another logic hook
		if (
		   empty($focus->skipCaseRoutingHandler) &&
		   empty($focus->fetched_row) &&
		   $focus->status == 'New' &&
		   $focus->request_type_c == 'technical_support'
		) {
			//_jmotmplog("made it past first big check...");

			require_once('modules/Accounts/Account.php');
			$focus_account = new Account();
			$focus_account->disable_row_level_security = TRUE;
			$focus_account->retrieve($focus->account_id);

			//_jmotmplog("related account_id is {$focus->account_id}, focus_account type is {$focus_account->account_type}");

			if (in_array($focus_account->account_type, $relevant_account_types)) {

				//_jmotmplog("account is in the list of relevant account types");
				require_once('modules/Opportunities/Opportunity.php');

				$focus_account->load_relationship('opportunities');
				$related_opps = $focus_account->opportunities->get();

				//_jmotmplog("list of related_opps is: " . var_export($related_opps, TRUE));

				$qualified_opps_list = array();

				foreach ($related_opps as $opp_id) {

					$focus_opportunity = new Opportunity();
					$focus_opportunity->disable_row_level_security = TRUE;
					$focus_opportunity->retrieve($opp_id);


					//_jmotmplog("checking related Opp {$opp_id}... opp type is {$focus_opportunity->opportunity_type}, opp sales stage is {$focus_opportunity->sales_stage}, revenue_type_c is {$focus_opportunity->Revenue_Type_c}");

					if (
					   in_array($focus_opportunity->opportunity_type, $relevant_opportunity_types) &&
					   in_array($focus_opportunity->sales_stage, getSugarInternalClosedStages()) &&
					   $focus_opportunity->Revenue_Type_c == 'New'
					) {
						//_jmotmplog("\tadding to list of qualified opps");
						$qualified_opps_list[] = "'{$opp_id}'";
					}

					unset($focus_opportunity);
				}

				if (!empty($qualified_opps_list)) {
					//_jmotmplog("qualified_opps_list is not empty");
					$qualified_opps_list_query = implode(', ', $qualified_opps_list);

					foreach(getSugarInternalClosedStages() as $stage) {
						$closed_sales_stages[] = "'{$stage}'";
					}

					$closed_sales_stages_list = implode(', ', $closed_sales_stages);
					
					// SADEK IT REQUEST 9583 - Sometimes finance will create an opportunity with a finance closed status, which means there is no audit table entry.
					//                         So, changed the behavior to rely on the opportunity expected close date instead
					// Julian's old query below in the comment
					// $query = "SELECT id, UNIX_TIMESTAMP(date_created) AS date_created FROM opportunities_audit WHERE field_name = 'sales_stage' AND after_value_string IN ({$closed_sales_stages_list}) AND parent_id IN ({$qualified_opps_list_query}) ORDER BY date_created ASC LIMIT 1";
					$query = "SELECT id, UNIX_TIMESTAMP(date_closed) AS date_created FROM opportunities WHERE sales_stage IN ({$closed_sales_stages_list}) AND id IN ({$qualified_opps_list_query}) ORDER BY date_created ASC LIMIT 1";
					$result = $GLOBALS['db']->query($query);

					//_jmotmplog("ran query: {$query}");
					//_jmotmplog("number of results is: " . $GLOBALS['db']->getRowCount($result));

					if ($GLOBALS['db']->getRowCount($result) == 1) {
						$row = $GLOBALS['db']->fetchByAssoc($result);

						//_jmotmplog("checking the result of the query... opportunities_audit id {$row['id']}");
						//_jmotmplog("time() is " . time() . " row[date_created] is {$row['date_created']} ......... time() - row[date_created] / 86400 is " . ((time() - $row['date_created']) / 86400) . " ..... and new_customer_cutoff is {$new_customer_cutoff}");

						if ((time() - $row['date_created']) / 86400 < $new_customer_cutoff) {
							//_jmotmplog("case passed... assigning to new_customer_user User {$new_customer_user}");
							$focus->assigned_user_id = $new_customer_user;
							return;
						}
					}
				}
			}
// NASSI               
			else if (in_array($focus_account->account_type, $decrement_account_types)) {
				$focus->assigned_user_id = $express_user;
				return;
			}
// NASSI
		}

		//_jmotmplog("case fell through... assigning to maintenance_customer_user User {$maintenance_customer_user}");

		// fall through to this line and assign to maintenance customer user
		$focus->assigned_user_id = $maintenance_customer_user;
	}
	// END jostrow

	function scoreCaseHook(&$focus, $event, $arguments){
		if($event == "before_save"){
			require_once('custom/si_custom_files/caseScoringFunctions.php');
			require_once('custom/si_custom_files/custom_functions.php');
			$open_statuses = getSugarInternalOpenCaseStatuses('array');
			
			if(!empty($focus->id)){
				if(in_array($focus->status, $open_statuses)){
					siCaseScore($focus->id);
				}
				else{
					$focus->case_score = -1;
				}
			}
		}
	}


}
