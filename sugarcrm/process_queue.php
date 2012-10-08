<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright(C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */
// $Id: process_queue.php 56786 2010-06-02 18:29:56Z jenny $
//FILE SUGARCRM flav=pro ONLY

$modListHeader = array();
require_once('modules/Reports/SavedReport.php');
require_once('modules/Reports/schedule/ReportSchedule.php');
require_once('modules/Reports/templates/templates_pdf.php');
require_once('include/SugarPHPMailer.php');
require_once('include/modules.php');
require_once('config.php');
require_once "modules/Mailer/MailerException.php";

global $sugar_config, $current_language, $app_list_strings, $app_strings, $locale;
$language = $sugar_config['default_language'];//here we'd better use English, because pdf coding problem.

$app_list_strings = return_app_list_strings_language($language);
$app_strings = return_application_language($language);

$report_schedule = new ReportSchedule();
$reports_to_email = $report_schedule->get_reports_to_email();

//BEGIN SUGARCRM flav=ent ONLY
//Process Enterprise Schedule reports via CSV
//bug: 23934 - enable Advanced reports
require_once('modules/ReportMaker/process_scheduled.php');
//END SUGARCRM flav=ent ONLY



global $report_modules,$modListHeader,$current_user;

foreach($reports_to_email as $schedule_info)
{
	$GLOBALS['log']->debug('-----> in Reports foreach() loop');
	
	$user = new User();
	$user->retrieve($schedule_info['user_id']);
	
	$current_user = $user; // this changes the global $current_user
	
	$modListHeader = query_module_access_list($current_user);
	$report_modules = getAllowedReportModules($modListHeader);

    $recipientEmailAddresses = array($user->email1, $user->email2);
    $recipientEmailAddresses = array_filter($recipientEmailAddresses);
    $recipientEmailAddress   = array_shift($recipientEmailAddresses);
	$recipientName           = $locale->getLocaleFormattedName($user->first_name, $user->last_name);

	$theme = $sugar_config['default_theme'];
	$saved_report = new SavedReport();
	$saved_report->retrieve($schedule_info['report_id']);
	
	
	$GLOBALS['log']->debug('-----> Generating Reporter');
	$reporter = new Report(html_entity_decode($saved_report->content));
	
    $mod_strings = return_module_language($current_language, 'Reports');

    // prevent invalid report from being processed
    if (!$reporter->is_definition_valid())
    {
        $invalidFields = $reporter->get_invalid_fields();
        $args          = array($schedule_info['report_id'], implode(', ', $invalidFields));
        $message       = string_format($mod_strings['ERR_REPORT_INVALID'], $args);

        $GLOBALS['log']->fatal('-----> ' . $message);

        try {
            require_once 'modules/Reports/utils.php';

            $reportOwner  = new User();
            $reportOwner->retrieve($saved_report->assigned_user_id);

            $reportsUtils = new ReportsUtilities();
            $reportsUtils->sendNotificationOfInvalidReport($reportOwner, $message);
        } catch (MailerException $me) {
            //@todo consider logging the error at the very least
        }

        continue;
    }

	$GLOBALS['log']->debug('-----> Reporter settings attributes');
	$reporter->layout_manager->setAttribute("no_sort",1);
	$module_for_lang = $reporter->module;

	$GLOBALS['log']->debug('-----> Reporter Handling PDF output');
	$report_filename = template_handle_pdf($reporter, false);

	$GLOBALS['log']->debug('-----> Generating SugarPHPMailer');
	$mail = new SugarPHPMailer();
    global $locale;
    $OBCharset = $locale->getPrecedentPreference('default_email_charset');

	$mail->AddAddress($recipientEmailAddress, $locale->translateCharsetMIME(trim($recipientName), 'UTF-8', $OBCharset));

	$admin = new Administration();
	$admin->retrieveSettings();
	
	if($admin->settings['mail_sendtype'] == "SMTP") 
	{
    	$mail->Mailer = "smtp";
    	$mail->Host = $admin->settings['mail_smtpserver'];
    	$mail->Port = $admin->settings['mail_smtpport'];
    
    	if($admin->settings['mail_smtpauth_req']) {
    		$mail->SMTPAuth = TRUE;
    		$mail->Username = $admin->settings['mail_smtpuser'];
    		$mail->Password = $admin->settings['mail_smtppass'];
    	}
    	if ($admin->settings['mail_smtpssl'] == 1) {
                $mail->SMTPSecure = 'ssl';
    	}
    	else if ($admin->settings['mail_smtpssl'] == 2) {
                $mail->SMTPSecure = 'tls';
    	}
	}
	else 
		$mail->Mailer = 'sendmail';
	
	$mail->From = $admin->settings['notify_fromaddress'];
	$mail->FromName = empty($admin->settings['notify_fromname']) ? ' ' : $admin->settings['notify_fromname'];
	$mail->Subject = empty($saved_report->name) ? 'Report' : $saved_report->name;
	$cr = array("\r", "\n");
	$attachment_name = str_replace(' ', '_', str_replace($cr,'',$mail->Subject).'.pdf');
	$mail->AddAttachment($report_filename, $locale->translateCharsetMIME(trim($attachment_name), 'UTF-8', $OBCharset), 'base64', 'application/pdf');

	$body = $mod_strings['LBL_HELLO'];
	if($recipientName != '') {
		$body .= " $recipientName";
	}
	$body .= ",\n\n";
	$body .= 	$mod_strings['LBL_SCHEDULED_REPORT_MSG_INTRO']. $saved_report->date_entered . $mod_strings['LBL_SCHEDULED_REPORT_MSG_BODY1']
				 . $saved_report->name . $mod_strings['LBL_SCHEDULED_REPORT_MSG_BODY2'];
	$mail->Body = $body;

	if($recipientEmailAddress == '') {
		$GLOBALS['log']->info("No email address for $recipientName");
	} else {
		$GLOBALS['log']->debug('-----> Sending PDF via Email to [ '.$recipientEmailAddress.' ]');
		
	$mail->prepForOutbound();
		
		if($mail->Send()) {
			$GLOBALS['log']->debug('-----> Send successful');
			$report_schedule->update_next_run_time($schedule_info['id'], $schedule_info['next_run'], $schedule_info['time_interval']);
		} else {
			$GLOBALS['log']->fatal("Mail error: $mail->ErrorInfo");
		}
	}
	$GLOBALS['log']->debug('-----> Removing temporary PDF file');
	unlink($report_filename);
}
sugar_cleanup(false); // continue script execution so that if run from Scheduler, job status will be set back to "Active"
?>