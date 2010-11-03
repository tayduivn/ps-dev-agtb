<?php

class SupportReleaseNotifier{

	public function SupportReleaseNotifier(){
	}
	
	public function queryRelease($release, $send_notifications = false, $test_email = ''){
		$release_id = $release;
		
		if(strlen($release) < 30){
			$release_query = "select id from releases where name = '$release' and deleted = 0 and status = 'Active' ";
			$GLOBALS['log']->debug("SupportReleaseNotifier::queryRelease(): $release_query");
			$res = $GLOBALS['db']->query($release_query);
			$row = $GLOBALS['db']->fetchByAssoc($res);
			if(!$row){
				$GLOBALS['log']->fatal("Error in SupportReleaseNotifier::queryRelease(): \$release less than 30 chars, and couldn't find release id from name");
				return array();
			}
			else{
				$release_id = $row['id'];
			}
		}
		
		$bugs_cases_query = 
			"select cases.id 'case_id', bugs.id 'bug_id' \n".
			"from cases inner join cases_bugs on cases.id = cases_bugs.case_id and cases_bugs.deleted = 0 \n".
			"           inner join bugs on cases_bugs.bug_id = bugs.id and bugs.deleted = 0 \n".
			"where bugs.fixed_in_release = '$release_id' and bugs.status = 'Closed' and cases.deleted = 0 \n";
		
		$res = $GLOBALS['db']->query($bugs_cases_query);
		$result_array = array();
		
		while($row = $GLOBALS['db']->fetchByAssoc($res)){
			$result_array[$row['case_id']][$row['bug_id']] = $row['bug_id'];
		}
		
		if(!empty($result_array) && $send_notifications){
			$this->sendNotificationsFromArray($result_array, $test_email);
		}
		
		return $result_array;
	}
	
	public function sendNotificationsFromArray($result_array, $test_email = ''){
		require_once('modules/Cases/Case.php');
		require_once('modules/Bugs/Bug.php');
		foreach($result_array as $case_id => $bug_array){
			$case = new aCase();
			$case->disable_row_level_security = true;
			$case->retrieve($case_id);
			if(!empty($case->id)){
				$this->sendNotification($case, $bug_array, $test_email);
			}
		}
	}
	
	private function sendNotification(&$caseFocus, $bug_id_array, $test_email = ''){
		global $locale;
		$template_id = '';

		require_once('modules/Accounts/Account.php');
		$accountFocus = new Account();
		$accountFocus->disable_row_level_security = true;
		$accountFocus->retrieve($caseFocus->account_id);
		if(empty($accountFocus->id)){
				$GLOBALS['log']->fatal("Error in SupportReleaseNotifier::sendNotification(): could not find account associated with case {$caseFocus->id}");
				return false;
		}
		
		if(!empty($accountFocus->deployment_type_c)){
			if($accountFocus->deployment_type_c == 'onpremise'){
				$template_id = 'd5084f97-5429-0057-5c67-49a5bc0dc5a9';
			}
			else if($accountFocus->deployment_type_c == 'ondemand'){
				if(empty($accountFocus->code_customized_by_c)){
					$template_id = '6537ae98-f030-4066-1231-49a5bdeb5355';
				}
				else{
					$template_id = '6b2345de-106c-06d8-6c35-49a5bdb1f0b7';
				}
			}
		}
		
		if(empty($template_id)){
			$GLOBALS['log']->fatal("Error in SupportReleaseNotifier::sendNotification(): No valid template_id");
			return false;
		}

		require_once('include/SugarPHPMailer.php');
		require_once("modules/Administration/Administration.php");
		require_once('include/workflow/alert_utils.php');
		$mail = new SugarPHPMailer();
		$admin = new Administration();
		$admin->retrieveSettings();
		fill_mail_object($mail, $caseFocus, $template_id, '');
		
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
		
        $mail->From = "support-no-reply@sugarcrm.com";
        $mail->FromName = "SugarCRM Customer Support";
				
		$current_user_backup = $GLOBALS['current_user'];
		$admin_user = new User();
		$admin_user->disable_row_level_security = true;
		$admin_user->retrieve('1');
		$GLOBALS['current_user'] = $admin_user;
		$related_contacts = $caseFocus->get_linked_beans('contacts', 'Contact');
		$GLOBALS['current_user'] = $current_user_backup;
		
		$mail_pre_contact = clone $mail;
		require_once('modules/EmailTemplates/EmailTemplate.php');
		$template = new EmailTemplate();
		$template->disable_row_level_security = true;
		$template->retrieve($template_id);
		$macro_nv = array();
		foreach($related_contacts as $contact_bean){
			if($contact_bean->email_opt_out || $contact_bean->invalid_email){
				$GLOBALS['log']->debug("Debug in SupportReleaseNotifier::sendNotification(): skipping contact {$contact_bean->id} since email_opt_out or invalid are set");
				continue;
			}
			
			$mail = clone $mail_pre_contact;
			
			$contact_body_html = str_replace('SadekCustomContacts', '{::future::Contacts::full_name::}', $mail_pre_contact->Body);
			$template_body = parse_alert_template($contact_bean, $contact_body_html);
			
			$name = $contact_bean->first_name . " " . $contact_bean->last_name;
			
			require_once('modules/Bugs/Bug.php');
			foreach($bug_id_array as $bug_id){
				$bug = new Bug();
				$bug->disable_row_level_security = true;
				$bug->retrieve($bug_id);
				if(empty($bug->id)){
					continue;
				}
				
				$template_body = str_replace('<ul><li>SadekCustomBugs', "<ul><li>[BUG:{$bug->bug_number}] -&nbsp;{$bug->name}</li></ul><ul><li>SadekCustomBugs", $template_body);
			}
			$template_body = str_replace("<ul><li>SadekCustomBugs", "", $template_body);
			
			$mail->Body = $template_body;
			$mail->Subject = str_replace("SadekReleaseName", $bug->fixed_in_release_name, $mail->Subject);
			$mail->Body = str_replace("SadekReleaseName", $bug->fixed_in_release_name, $mail->Body);
			
			if(!empty($test_email)){
				$name = "Notification Test Run";
				$email_sent_to = $test_email;
				$mail->AddAddress($test_email, "Notification Test Run");
			}
			else{
				$email_sent_to = $contact_bean->email1;
				$mail->AddAddress($contact_bean->email1, $name);
			}
			
			$mail->prepForOutbound($locale->getPrecedentPreference('default_email_charset'));
			if (!$mail->Send()) {
				$GLOBALS['log']->fatal("Error in SupportReleaseNotifier::sendNotification(): \$mail-\>Send() failed");
				$fp = fopen('/var/www/sugarinternal/logs/supportReleaseNotifications.log', 'a');
				fwrite($fp, "[" . date("Y-m-d H:i:s") . "] FAILED Send to " . $email_sent_to . " " . $name . " for release " . $bug->fixed_in_release_name . "\n");
				fclose($fp);
			}
			else{
				$fp = fopen('/var/www/sugarinternal/logs/supportReleaseNotifications.log', 'a');
				fwrite($fp, "[" . date("Y-m-d H:i:s") . "] Sent to " . $email_sent_to . " " . $name . " for release " . $bug->fixed_in_release_name . "\n");
				fclose($fp);
			}
			
			$mail->ClearAddresses();
			$mail->ClearCCs();
			$mail->ClearBCCs();
		}
		
		return true;
	}
}
