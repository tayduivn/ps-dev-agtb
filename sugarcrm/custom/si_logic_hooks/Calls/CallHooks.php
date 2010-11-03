<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
class CallHooks {
    function AutoSetAttachedLeadToQualifying(&$focus, $event, $arguments) {
	if ($event == "before_save") {
	    $testing = FALSE && ('LQ Test Initial Call' == $focus->name);
	    $department = '';
	    if (!empty($focus->assigned_user_id)) {
		$user = new User();
		$user->retrieve($focus->assigned_user_id);
		$department = $user->department;
	    }
	    $department_starts_with = 'Sales - Inside - Lead Qual';
	    if ($testing
		|| (isset($focus->autoscheduledcall_c) && 'LQ Initial call' == $focus->autoscheduledcall_c
		    && ($focus->status == 'Held' || $focus->status == 'Voicemail')
		    && !empty($department)
		    && (strpos($department, $department_starts_with) === 0))) {
		$leads = $focus->get_linked_beans('leadcontact_related', 'LeadContact');
		if ($leads) {
		    foreach ($leads as $lead) {
			if ('Qualifying' != $lead->status) {
			    $lead->status = 'Qualifying';
			    $lead->save();
			    break;
			}
		    }
		}
	    }
	}
    }
    function FirstCallWasVoicemail(&$focus, $event, $arguments) {
	if ($event == "before_save") {
	    $testing = false && ('LQ Test Initial Call Voicemail' == $focus->name);
	    $department = '';
	    if (!empty($focus->assigned_user_id)) {
		$user = new User();
		$user->retrieve($focus->assigned_user_id);
		$department = $user->department;
	    }
			
	    $department_starts_with = 'Sales - Inside - Lead Qual';			
	    if ($testing
		|| (isset($focus->autoscheduledcall_c) && 'LQ Initial call' == $focus->autoscheduledcall_c
		    && $focus->status == 'Voicemail'
		    && !empty($department)
		    && (strpos($department, $department_starts_with) === 0))) {
		
		$focus->autoscheduledcall_c = '2nd Call Scheduled';
		$leads = $focus->get_linked_beans('leadcontact_related', 'LeadContact');
		$lead = null;
		if ($leads) {
		    foreach ($leads as $temp_lead) {
			$lead = $temp_lead;
			break;
		    }
		}
		if ($lead) {
		    /* Per IT Request 3454
		     * After the initial call is created, the additional logic hooks are
		     * attached to the call.
		     */
		    /*
		     * FIXME: This new call needs to be
		     * related to the lead
		     */
		    $today = date('j');
		    switch (date('w')) {
		    case 4:
			/* Saturday */
			$today += 4;
			break;
		    case 3:
			/* Friday */
			$today += 5;
			break;
		    default:
			/* All others */
			$today += 3;
			break;
		    }
		    /* YYYY-MM-DD HH:MM:SS */
		    $call = new Call();
		    $call->assigned_user_id = $focus->assigned_user_id;
		    $call->autoscheduledcall_c = 'LQ 2nd call';
		    $call->name = 'Auto Scheduled 2nd Lead Qual Call';
			$timeDate = new TimeDate();
			$call->date_start = $timeDate->to_display_date_time(gmdate($GLOBALS['timedate']->get_db_date_time_format(), mktime(13, 0, 0, date('n'), $today, date('Y'))));
		    $call->duration_hours = '0';
		    $call->duration_minutes = '15';
		    $call->status = 'Planned';
		    $call->direction = 'Outbound';
		    $call->save();
		    $lead->load_relationship('calls');
		    $lead->calls->add($call->id);
		    self::sendLQNoResponseSecondEmail($lead, $user);
		}
	    }
	}
    }
    function SendFinalEmailAndNurture(&$focus, $event, $arguments) {
	if ($event == "before_save") {
	    $department = '';
	    if (!empty($focus->assigned_user_id)) {
		$user = new User();
		$user->retrieve($focus->assigned_user_id);
		$department = $user->department;
	    }
	    $department_starts_with = 'Sales - Inside - Lead Qual';
			
	    if (isset($focus->autoscheduledcall_c) && 'LQ 2nd call' == $focus->autoscheduledcall_c
		&& $focus->status == 'Voicemail'
		&& !empty($department)
		&& (strpos($department, $department_starts_with) === 0)) {
		//$focus->autoscheduledcall_c = '2nd Call No Response';

		$leads = $focus->get_linked_beans('leadcontact_related', 'LeadContact');
		if ($leads) {
		    foreach ($leads as $lead) {
			if ('Nurture' != $lead->status) {
			    $lead->status = 'Nurture';
			    $lead->save();
			    self::sendLQNoResponseFinalEmail($lead, $user);
			    break;
			}
		    }
		}
	    }
	}
    }

    function sendLQNoResponseSecondEmail(&$focus, &$user){
	/* Needed for the later prepforoutput call */
	global $locale;
	/* Stage template */
	// $template_id = 'e9d16fe5-5945-5ea4-bc77-4927539fafff';
	/* Live template */
	$template_id = '3743a5e4-f7c0-0ab0-b847-4927450f458f';
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
		
	$logFile = '/var/www/sugarinternal/logs/welcomeEmails.log';
	$theid = !empty($focus->id) ? $focus->id : "new_record_no_id";

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

	$msg = "\""
		. date("Y-m-d H:i:s")
		. "\",\"$theid\",\"{$focus->email1}\",\"$name\",\"{$mail->From}\",\"{$mail->FromName}\",\"{$user->department}\"";
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

    function sendLQNoResponseFinalEmail(& $focus, & $user){
	/* Needed for the later prepforoutput call */
	global $locale;
	/* stage template */
	$template_id = 'e1fc69b8-0e74-c721-3c06-4927539ae918';
	/* Live template */
	$template_id = '51a708b8-fd72-e861-2a44-4927465aaef7';
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

    
}
