<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2005 SugarCRM, Inc.; All Rights Reserved.
 */

// $Id: process_scheduled.php 53409 2010-01-04 03:31:15Z roger $

//FILE SUGARCRM flav=ent ONLY
$modListHeader = array();




//require_once('modules/Reports/ReportBug.php');
//require_once('modules/Reports/ReportOpportunity.php');
require_once('modules/Reports/schedule/ReportSchedule.php');
require_once('modules/Reports/templates/templates_pdf.php');
require_once("modules/Mailer/lib/phpmailer/class.phpmailer.php");

$report_schedule = new ReportSchedule();

global $sugar_config;
$language = $sugar_config['default_language'];

$app_list_strings = return_app_list_strings_language($language);
$app_strings = return_application_language($language);
// retrieve the user

//Process Enterprise Schedule reports via CSV
$reports_to_email_ent = $report_schedule->get_ent_reports_to_email("", "ent");

global $report_modules,$modListHeader;
global $locale;

foreach($reports_to_email_ent as $schedule_id => $schedule_info)
{
	$user = new User();
	$user->retrieve($schedule_info['user_id']);
	$current_user =$user;
	$modListHeader = query_module_access_list($current_user);
	$report_modules = getAllowedReportModules($modListHeader);

	if(empty($user->email1))
	{
		if(empty($user->email2)){
			$address = '';
		} else {
			$address = $user->email2;
		}
	} else {
		$address = $user->email1;
	}
	$name = $locale->getLocaleFormattedName($user->first_name, $user->last_name);

//Aquire the enterprise report to be sent				
	$report_object = new ReportMaker();			
	$report_object->retrieve($schedule_info['report_id']);			
	$mod_strings = return_module_language($language, 'Reports');


//Process data sets into CSV files

	//loop through data sets;
	$data_set_array = $report_object->get_data_sets();
	$temp_file_array = array();
	foreach($data_set_array as $key =>$data_set_object){

		$csv_output = $data_set_object->export_csv();

		$filenamestamp = '';
		$filenamestamp .= $data_set_object->name.'_'.$user->user_name;
		$filenamestamp .= '_'.date(translate('LBL_CSV_TIMESTAMP', 'Reports'), time());

		$filename = str_replace(' ', '_', $report_object->name. $filenamestamp.  ".csv");
		$fp = sugar_fopen(sugar_cached('csv/').$filename,'w');
		fwrite($fp, $csv_output);
		fclose($fp);

		$temp_file_array[$filename] = $filename;

	}

	$mail = new PHPMailer();
	$OBCharset = $locale->getPrecedentPreference('default_email_charset');
	$mail->AddAddress($address, $locale->translateCharsetMIME(trim($name), 'UTF-8', $OBCharset));

	$admin = new Administration();
	$admin->retrieveSettings();

	if ($admin->settings['mail_sendtype'] == "SMTP")
	{
		$mail->Mailer = "smtp";
		$mail->Host = $admin->settings['mail_smtpserver'];
		$mail->Port = $admin->settings['mail_smtpport'];

		if ($admin->settings['mail_smtpauth_req'])
		{
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


	$mail->From = $admin->settings['notify_fromaddress'];
	$mail->FromName = empty($admin->settings['notify_fromname']) ?
							' ' : $admin->settings['notify_fromname'];
	$mail->Subject = empty($report_object->name) ? 'Report' : $report_object->name;

	$temp_count = 0;
	foreach($temp_file_array as $filename){
		$file_path = sugar_cached('csv/').$filename;
		$attachment_name = $mail->Subject . '_'.$temp_count.'.csv';
		$mail->AddAttachment($file_path, $attachment_name, 'base64', 'application/csv');
		$temp_count ++;
	//end foreach loop
	}

	$body = $mod_strings['LBL_HELLO'];
	if($name != '') {
		$body .= " $name";
	}
	$body .= ",\n\n";
	$body .= 	$mod_strings['LBL_SCHEDULED_REPORT_MSG_INTRO']. $report_object->date_entered . $mod_strings['LBL_SCHEDULED_REPORT_MSG_BODY1']
				 . $report_object->name . $mod_strings['LBL_SCHEDULED_REPORT_MSG_BODY2'];
	$mail->Body = $body;

	if($address == '')
	{
		$GLOBALS['log']->info("No email address for $name");
	}
	else
	{
		if($mail->Send())
		{
			$report_schedule->update_next_run_time($schedule_info['id'],
										$schedule_info['next_run'],
										$schedule_info['time_interval']);
		}
		else
		{
			$GLOBALS['log']->error("Mail error: $mail->ErrorInfo");
		}
	}

	//need unlink for loop
	foreach($temp_file_array as $filename){
		//only un rem if we need to remove cvs and we can't just stream it
		$file_path = sugar_cached('csv/').$filename;
		unlink($file_path);
	//end foreach temp_file_array
	}
}


?>
