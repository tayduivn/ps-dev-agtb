<?php
/**
 * @author Jim Bartek
 * @project moofcart
 * @tasknum 43
 * After purchase CA Reassignment [After save hook for Opportunities]
*/

if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
require_once('custom/si_custom_files/MoofCartHelper.php');
require_once('custom/si_custom_files/meta/caRepTerritoryMap.php');
require_once('modules/Administration/Administration.php');
require_once('include/SugarPHPMailer.php');
class updateCARep {
	function update(&$bean, $event, $arguments) {
		if($event != "after_save") return false;
		
		// debugging
		//ini_set('display_errors', 1);

		$acc = new Account();
		$acc->retrieve($bean->account_id);
		
		$opps = $acc->get_linked_beans('opportunities_accounts','Opportunity',array(),0,-1,0);

		$user = new User;
		
		// don't run this logic hook if there is more than one opp or the probability is less than 99%, or in the direct Opp type or not New
		if(count($opps) > 1 || ($bean->sales_stage != 'Finance Closed' || $bean->sales_stage != 'Sales Ops Closed')  || !in_array($bean->opportunity_type, MoofCartHelper::$directOpportunityTypes) || $bean->Revenue_Type_c != 'New') {
			return false;
		}

				
		$log = 'Account reassigned to ';
		
		
		if($bean->users <= 5) {
			// assign to CA Group User
			$acc->assigned_user_id = MoofCartHelper::$carep_id;
			$log .= 'CA User after <a href="/index.php?module=Opportunities&action=DetailView&record=' . $bean->id .'>Opportunity</a> reached Sales Stage 99%';
			$log = MoofCartHelper::automationLog($log);
			
			$acc->description = $acc->description . "
{$log}
			";
			$user->retrieve(MoofCartHelper::$carep_id);
			$acc->save();
			
			$cons = $bean->get_linked_beans('contacts', 'Contact', array(), 0, -1, 0);
			
			$to = array();
			foreach($cons AS $contact) {
				$email = reset($contact->get_linked_beans('email_addresses_primary','EmailAddress',array(),0,-1,0));
				
				if(!empty($email->email_address)) {
					$to[] = array(	'name'	=>	$contact->first_name . ' ' . $contact->last_name,
									'email'	=>	$email->email_address	,
					);
				}
			}
			
			if(!empty($to)) {
				$this->sendEmail($user,$to, $acc->id);
			}
			else {
				$failed_email = MoofcartHelper::automationLog("Welcome email could not be sent");
				$acc->description = $acc->description . "
{$failed_email}				
";
			}
			return true;
		}
		
		// get CA Rep
		// country ->
		if($acc->billing_address_country == 'USA') {
			// use State
			
			if((!isset(MoofCartHelper::$regionToCAMap[MoofCartHelper::$stateRegionMap[$acc->billing_address_state]]))) {
				$ca_rep_id = MoofCartHelper::$carep_id;;
				$ca_rep_name = MoofCartHelper::$ca_manager_name;
				$user->retrieve($ca_rep_id);
			}
			else {
				$ca_rep_id = MoofCartHelper::$regionToCAMap[MoofCartHelper::$stateRegionMap[$acc->billing_address_state]];
				$user->retrieve($ca_rep_id);
			}
		}
		else {
			// if you can't find the opportunity assign it to the CA User
			if((!isset(MoofCartHelper::$regionToCAMap[MoofCartHelper::$countryRegionMap[$acc->billing_address_country]]))) {
				$ca_rep_id = MoofCartHelper::$carep_id;;
				$ca_rep_name = MoofCartHelper::$ca_manager_name;
				$user->retrieve($ca_rep_id);
			}
			else {
				$ca_rep_id = MoofCartHelper::$regionToCAMap[MoofCartHelper::$countryRegionMap[$acc->billing_address_country]];
				$user->retrieve($ca_rep_id);
				$ca_rep_name = $user->first_name . ' ' . $user->last_name;
			}
			// assign
			$acc->assigned_user_id = $ca_rep_id;
			
			$log .= "{$ca_rep_name} after <a href='/index.php?module=Opportunities&action=DetailView&record={$bean->id}'>Opportunity</a> reached Sales Stage 99%";
			
		}
		
		$log = MoofCartHelper::automationLog($log);
		
		$acc->description = $acc->description . "
{$log}		
";
		$acc->save();
		$cons = $bean->get_linked_beans('opportunities_contacts', 'Contact', array(), 0, -1, 0);
		$to = array();
		foreach($cons AS $contact) {
			$to[] = array(	'name'	=>	$contact->first_name . ' ' . $contact->last_name,
							'email'	=>	$contact->email1,
			);
		}
		
		if(!empty($to)) {
			$this->sendEmail($user,$to, $acc->id);
		}
		else {
			$GLOBALS['log']->info(__FILE__ . '::' . __FUNCTION__."- can't send email");
			$log = MoofCartHelper::automationLog("Unable to send welcome email");
			$acc->description = $acc->description . "
{$log}			
";
		}
		
		return true;
	}
	
	function sendEmail(&$user,$to=array(), $account_id = NULL) {
		if(empty($to)) {
			return false;
		}
		$mail = new SugarPHPMailer();
		$admin = new Administration();
		
		$email = new Email();
		
		$email->type = 'archived';
		$email->account_id = $account_id;
		
		
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
		}
		else {
			$mail->mailer = 'sendmail';
		}

		$email->from_addr = $mail->From = $user->emailAddress->addresses[0]['email_address'];
		$email->from_name = $mail->FromName = $user->first_name . ' ' . $user->last_name;
		$mail->ContentType = "text/html"; //"text/plain"
		
		$email->name = $email->Subject = $mail->Subject = "Welcome to Sugar!";

		global $sugar_config;
		

		$email->description_html = $body = MoofCartHelper::getWelcomeEmail($user);		
		$email->save();
		
		$mail->Body = $body;

		foreach($to AS $t) {
			$mail->AddAddress($t['email'], $t['name']);
		}
		
		$mail->AddBCC($user->emailAddress->addresses[0]['email_address'],$user->first_name . ' ' . $user->last_name);
		if (!$mail->send()) {
			$GLOBALS['log']->info("Mailer error: " . $mail->ErrorInfo);
			return false;
		}
		return true;
	}
}