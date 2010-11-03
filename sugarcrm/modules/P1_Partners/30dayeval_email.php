<?php
function trials_send_welcome_email($eval_url, $expiration_date, $email, $pw) {
	global $locale, $current_user;
		require_once('include/SugarPHPMailer.php');
		require_once("modules/Administration/Administration.php");
		require_once('modules/Emails/Email.php');
		require_once('include/workflow/alert_utils.php');
		$mail = new SugarPHPMailer();
		$admin = new Administration();
		$admin->retrieveSettings();
		$mail->IsHTML(true);
		
		//Setup the reply to and from name for the email object.
		$mail->AddReplyTo('no-reply@sugarcrm.com','No Reply');
		$mail->From = $admin->settings['notify_fromaddress'];
		$mail->FromName = $admin->settings['notify_fromname'];
		
		//Setup the outbound email send method.
		if ($admin->settings['mail_sendtype'] == "SMTP") 
		{
			$mail->Host = $admin->settings['mail_smtpserver'];
			$mail->Port = $admin->settings['mail_smtpport'];
			if ($admin->settings['mail_smtpauth_req']) 
			{
				$mail->SMTPAuth = TRUE;
				$mail->Username = $admin->settings['mail_smtpuser'];
				$mail->Password = $admin->settings['mail_smtppass'];
			}
			$mail->Mailer   = "smtp";
			$mail->SMTPKeepAlive = true;
		} 
		else 
		{
			$mail->mailer = 'sendmail';
		}
		$mail->AddAddress($email, $email);
		
	$thirtydayemail_subject = "Welcome to SugarCRM";
	$sevendayemail_string = "<table style=\"border-right: #ccc 1px solid; border-left: #ccc 1px solid; border-bottom: #ccc 1px solid;font-size: 12px;font-family: arial, verdana, helvetica, sans-serif; line-height: 16px \" cellspacing=\"0\" cellpadding=\"0\" width=\"600\" align=\"center\" bgcolor=\"#ffffff\" border=\"0\">
			        <tr>
			            <td colspan=\"2\">
					<a href=\"http://www.sugarcrm.com\" style=\"color: #9D0C0B;\"><img src=\"http://media.sugarcrm.com/newsletter/discover/SugarCRMheaderT.jpg\" width=\"600\" height=\"200\" alt=\"SugarCRM\"  border=\"0\"  /></a></td>
				</tr>
			        <tr>
			            <td style=\"padding: 20px 20px 40px 40px;\" colspan=\"2\">

			            <p>Thank you for signing up for an evaluation of Sugar. Sugar is a powerful web-based CRM system that helps your company improve marketing, sales, support and collaboration activities.</p>

						<h1 style=\"font-size: 18px; font-weight: normal; border-bottom: 3px solid #ccc; padding-bottom: 5px; color: #333; margin-top: 40px; margin-bottom: 15px;\">Your Sugar Evaluation</h1>



			                    <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"FONT-FAMILY: Arial, Verdana, Helvetica, sans-serif; FONT-SIZE: 12px; line-height: 16px; margin-left: 40px\">
			                <tr><td valign=\"top\" style=\"border-bottom: 1px solid #e0e0e0; padding: 3px 8px 3px 5px;\"><b style=\"color: #666666;\">Unique URL:</b></td>
			              		<td valign=\"top\" style=\"border-bottom: 1px solid #e0e0e0;  padding: 3px 5px 3px 5px;\"><a href=\"__URL__\" style=\"color: #9D0C0B;\">__URL__</a></td></tr>
								<tr><td valign=\"top\" style=\"border-bottom: 1px solid #e0e0e0; padding: 3px 8px 3px 5px;\"><b style=\"color: #666666;\">User Name:</b></td>
								<td valign=\"top\" style=\"border-bottom: 1px solid #e0e0e0;  padding: 3px 5px 3px 5px;\">admin</td></tr>
								<tr><td valign=\"top\" style=\"border-bottom: 1px solid #e0e0e0; padding: 3px 8px 3px 5px;\"><b style=\"color: #666666;\">Password:</b></td>
								<td valign=\"top\" style=\"border-bottom: 1px solid #e0e0e0;  padding: 3px 5px 3px 5px;\"> __PW__</td></tr>
			              	<tr><td valign=\"top\" style=\"border-bottom: 1px solid #e0e0e0; padding: 3px 8px 3px 5px;\"><b style=\"color: #666666;\">This evaluation account will expire on:<br /></b></td>
			              		<td valign=\"top\" style=\"border-bottom: 1px solid #e0e0e0; padding: 3px 5px 3px 5px;\">__EXPIRATIONDATE__</td></tr>

					<tr><td colspan=\"2\"><span style='font-size: 11px;'>Please note it may take 1-5 minutes for the evaluation to be fully created and accessible. Your free evaluation account will be deleted 14 days after your evaluation has expired.  All data in your evaluation, including data that you may have uploaded during this evaluation will be permanently erased.</span></td></tr>
			                </table>

	<h1 style=\"font-size: 18px; font-weight: normal; border-bottom: 3px solid #ccc; padding-bottom: 5px; color: #333; margin-top: 40px\">Getting Started</h1>
	<p style=\"margin-left: 40px\">Please visit the <a href=\"http://www.sugarcrm.com/crm/demo/getting-started.html\" style=\"color: #9D0C0B;\"><strong>Discover Sugar</strong></a> page to get the most from your evaluation.</p>
	<p style=\"margin-left: 40px\">To view video tutorials go here:  <a href=\"http://www.youtube.com/discoversugarcrm\" style=\"color: #9D0C0B;\"><strong>http://www.youtube.com/discoversugarcrm</strong></a></p>
	<p style=\"margin-left: 40px\">To view the application guide go here and select the corresponding version to your evaluation:  <a href=\"http://www.sugarcrm.com/crm/support/documentation\" style=\"color: #9D0C0B;\"><strong>http://www.sugarcrm.com/crm/support/documentation</strong></a></p>
	<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"FONT-FAMILY: Arial, Verdana, Helvetica, sans-serif; FONT-SIZE: 12px; line-height: 19px; margin-left: 40px\">
			                <tr><td valign=\"top\" style=\"padding: 10px 25px 0px 0px;\">A SugarCRM representative will be in contact with you shortly to offer assistance. In the meantime, please contact us at <a href=\"mailto:trials@sugarcrm.com?subject=Free trial questions\" style=\"color: #9D0C0B;\">trials@sugarcrm.com</a> with any questions. </td><td valign=\"top\"><a href=\"http://www.sugarcrm.com/crm/we-call-you.html\"><img border=\"0\" style=\"margin-left: 5px;\" alt=\"More Info\" src=\"http://www.sugarcrm.com/crm/images/content_images/products/contact.jpg\"/></a></td></tr></table>
	<br>
	<p>Sincerely,</p>
			<p>
			<b>SugarCRM</b></p>
				            </td>
			        </tr>
					<tr><td style=\"padding: 0;border-right: #ccc 1px solid; border-left: #ccc 1px solid; \">
			<a href=\"http://www.sugarcrm.com/crm/customer-snapshot.html\" style=\"color: #9D0C0B;\"><img src=\"http://media.sugarcrm.com/newsletter/discover/DiscoverCustomers.gif\" width=\"598\" height=\"62\" alt=\"Sugar Customer Success Stories\"  border=\"0\"  /></a></td></tr>
			</table>
	<table width=\"600\" cellspacing=\"0\" cellpadding=\"0\" align=\"center\" style=\"font-family:  Arial, Verdana, Helvetica, sans-serif; font-size: 10px;line-height: 14px;color: #666666;\">
	<tr><td style=\"padding: 10px 10px 10px 40px\">&copy; SugarCRM Inc. If you choose not to receive SugarCRM sent emails, simply <a href=\"http://www.sugarcrm.com/crm/removeme.html\" style=\"color: #666666;\">unsubscribe</a> or email <a href=\"mailto:news@sugarcrm.com\" style=\"color: #666666;\">news@sugarcrm.com</a> or mail 10050 N. Wolfe Rd. SW2-130, Cupertino CA 95014, USA, or call +1 408.454.6900
	</td></tr>
	</table>";

	$evalurl = "http://trial.sugarcrm.com/{$trial_name}";

	$search = array(
		'__PW__',
		'__URL__',
		'__EXPIRATIONDATE__',
	);

	$replace = array(
		$pw,
		$eval_url,
		$expiration_date,
	);

	$mail_string = str_replace($search, $replace, $sevendayemail_string);
	$mail->Body = $mail_string;
	$mail->Subject = $thirtydayemail_subject;
	$mail->prepForOutbound($locale->getPrecedentPreference('default_email_charset'));
	//send message
	if (!$mail->Send()) 
	{
		$GLOBALS['log']->fatal("Unable to send 30 day eval email: $subject");
		$GLOBALS['log']->fatal("30 day eval email error message received: {$mail->ErrorInfo}");
	}
	else
	{
		$GLOBALS['log']->debug("Email with subject: send successfully.$subject ");
	}
		
		//Cleanup.
		$mail->ClearAddresses();
		$mail->ClearCCs();
		$mail->ClearBCCs();
		
		// create email object
		$email_object = new Email();
		$email_object->name = $mail->Subject;
		$email_object->type = "archived";
		$email_object->from_addr = $current_user->email1;
		$email_object->status = "archived";
		$email_object->intent = "pick";
		$email_object->parent_type = "Opportunities";
		$email_object->description = $mail->Body;
		$email_object->description_html = $mail->Body;
		$email_object->assigned_user_id = $current_user->id;
		$email_object->to_addrs = $cnt->email1;
		//Add the date sent, not automatically added.
		$today = gmdate($GLOBALS['timedate']->get_db_date_time_format());
		$email_object->date_start = $GLOBALS['timedate']->to_display_date($today);
		$email_object->time_start = $GLOBALS['timedate']->to_display_time($today, true);
		
		return $email_object;
}
