<?php
if (!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

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

require_once('modules/Reports/schedule/ReportSchedule.php');
require_once('modules/Reports/templates/templates_pdf.php');
require_once("modules/Mailer/lib/phpmailer/class.phpmailer.php");

global $sugar_config;

$language         = $sugar_config['default_language'];
$app_list_strings = return_app_list_strings_language($language);
$app_strings      = return_application_language($language);

$reportSchedule = new ReportSchedule();

// Process Enterprise Schedule reports via CSV
$reportsToEmailEnt = $reportSchedule->get_ent_reports_to_email("", "ent");

global $report_modules,
       $modListHeader,
       $locale;

foreach ($reportsToEmailEnt as $scheduleId => $scheduleInfo) {
    $user = new User();
    $user->retrieve($scheduleInfo['user_id']);

    $current_user   = $user; // should this be the global $current_user? global $current_user isn't referenced
    $modListHeader  = query_module_access_list($current_user);
    $report_modules = getAllowedReportModules($modListHeader);

    if (empty($user->email1)) {
        if (empty($user->email2)) {
            $recipientEmailAddress = '';
        } else {
            $recipientEmailAddress = $user->email2;
        }
    } else {
        $recipientEmailAddress = $user->email1;
    }

    $recipientName = $locale->getLocaleFormattedName($user->first_name, $user->last_name);

    // Acquire the enterprise report to be sent
    $reportMaker = new ReportMaker();
    $reportMaker->retrieve($scheduleInfo['report_id']);
    $mod_strings = return_module_language($language, 'Reports');

    // Process data sets into CSV files

    // loop through data sets;
    $dataSets  = $reportMaker->get_data_sets();
    $tempFiles = array();

    foreach ($dataSets as $key => $dataSet) {
        $csv_output = $dataSet->export_csv();

        $filenamestamp = $dataSet->name . '_' . $user->user_name;
        $filenamestamp .= '_' . date(translate('LBL_CSV_TIMESTAMP', 'Reports'), time());

        $filename = str_replace(' ', '_', $reportMaker->name . $filenamestamp . ".csv");
        $fp       = sugar_fopen(sugar_cached('csv/') . $filename, 'w');
        fwrite($fp, $csv_output);
        fclose($fp);

        $tempFiles[$filename] = $filename;
    }

    $mail      = new PHPMailer();
    $OBCharset = $locale->getPrecedentPreference('default_email_charset');
    $mail->AddAddress($recipientEmailAddress, $locale->translateCharsetMIME(trim($recipientName), 'UTF-8', $OBCharset));

    $admin = new Administration();
    $admin->retrieveSettings();

    if ($admin->settings['mail_sendtype'] == "SMTP") {
        $mail->Mailer = "smtp";
        $mail->Host   = $admin->settings['mail_smtpserver'];
        $mail->Port   = $admin->settings['mail_smtpport'];

        if ($admin->settings['mail_smtpauth_req']) {
            $mail->SMTPAuth = TRUE;
            $mail->Username = $admin->settings['mail_smtpuser'];
            $mail->Password = $admin->settings['mail_smtppass'];
        }

        if ($admin->settings['mail_smtpssl'] == 1) {
            $mail->SMTPSecure = 'ssl';
        } elseif ($admin->settings['mail_smtpssl'] == 2) {
            $mail->SMTPSecure = 'tls';
        }
    }

    $mail->From     = $admin->settings['notify_fromaddress'];
    $mail->FromName = empty($admin->settings['notify_fromname']) ? ' ' : $admin->settings['notify_fromname'];
    $mail->Subject  = empty($reportMaker->name) ? 'Report' : $reportMaker->name;

    $tempCount = 0;

    foreach ($tempFiles as $filename) {
        $filePath       = sugar_cached('csv/') . $filename;
        $attachment_name = $mail->Subject . '_' . $tempCount . '.csv';
        $mail->AddAttachment($filePath, $attachment_name, 'base64', 'application/csv');
        $tempCount++;
    }

    $body = $mod_strings['LBL_HELLO'];

    if ($recipientName != '') {
        $body .= " $recipientName";
    }

    $body .= ",\n\n";
    $body .= $mod_strings['LBL_SCHEDULED_REPORT_MSG_INTRO'] . $reportMaker->date_entered . $mod_strings['LBL_SCHEDULED_REPORT_MSG_BODY1']
             . $reportMaker->name . $mod_strings['LBL_SCHEDULED_REPORT_MSG_BODY2'];
    $mail->Body = $body;

    if ($recipientEmailAddress == '') {
        $GLOBALS['log']->info("No email address for $recipientName");
    } else {
        if ($mail->Send()) {
            $reportSchedule->update_next_run_time($scheduleInfo['id'],
                                                   $scheduleInfo['next_run'],
                                                   $scheduleInfo['time_interval']);
        } else {
            $GLOBALS['log']->error("Mail error: $mail->ErrorInfo");
        }
    }

    // need unlink for loop
    foreach ($tempFiles as $filename) {
        //only un rem if we need to remove cvs and we can't just stream it
        $filePath = sugar_cached('csv/') . $filename;
        unlink($filePath);
    }
}
