<?php
if (!defined('sugarEntry') || !sugarEntry) {
    die('Not A Valid Entry Point');
}

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
require_once "modules/Mailer/MailerFactory.php"; // imports all of the Mailer classes that are needed

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

    // Acquire the enterprise report to be sent
    $reportMaker = new ReportMaker();
    $reportMaker->retrieve($scheduleInfo['report_id']);
    $mod_strings = return_module_language($language, 'Reports');

    // Process data sets into CSV files

    // loop through data sets;
    $dataSets  = $reportMaker->get_data_sets();
    $tempFiles = array();

    foreach ($dataSets as $key => $dataSet) {
        $csv           = $dataSet->export_csv();
        $filenamestamp = "{$dataSet->name}_{$user->user_name}_" . date(translate("LBL_CSV_TIMESTAMP", "Reports"), time());
        $filename      = str_replace(" ", "_", "{$reportMaker->name}{$filenamestamp}.csv");
        $fp            = sugar_fopen(sugar_cached("csv/") . $filename, "w");
        fwrite($fp, $csv);
        fclose($fp);

        $tempFiles[$filename] = $filename;
    }

    // get the recipient data...

    // first get all email addresses known for this recipient
    $recipientEmailAddresses = array($user->email1, $user->email2);
    $recipientEmailAddresses = array_filter($recipientEmailAddresses);

    // then retrieve first non-empty email address
    $recipientEmailAddress = array_shift($recipientEmailAddresses);

    // get the recipient name that accompanies the email address
    $recipientName = $locale->getLocaleFormattedName($user->first_name, $user->last_name);

    try {
        $mailer = MailerFactory::getMailerForUser($current_user);

        // set the subject of the email
        $subject = empty($reportMaker->name) ? "Report" : $reportMaker->name;
        $mailer->setSubject($subject);

        // add the recipient
        $mailer->addRecipientsTo(new EmailIdentity($recipientEmailAddress, $recipientName));

        // add the attachments
        $tempCount = 0;

        foreach ($tempFiles as $filename) {
            $filePath       = sugar_cached("csv/") . $filename;
            $attachmentName = "{$subject}_{$tempCount}.csv";
            $attachment     = new Attachment($filePath, $attachmentName, Encoding::Base64, "application/csv");
            $mailer->addAttachment($attachment);
            $tempCount++;
        }

        // set the body of the email
        $body = $mod_strings["LBL_HELLO"];

        if ($recipientName != "") {
            $body .= " {$recipientName}";
        }

        $body .= ",\n\n" .
                 $mod_strings["LBL_SCHEDULED_REPORT_MSG_INTRO"] .
                 $reportMaker->date_entered .
                 $mod_strings["LBL_SCHEDULED_REPORT_MSG_BODY1"] .
                 $reportMaker->name .
                 $mod_strings["LBL_SCHEDULED_REPORT_MSG_BODY2"];

        // the compared strings will be the same if strip_tags had no affect
        // if the compared strings are equal, then it's a text-only message
        $textOnly = (strcmp($body, strip_tags($body)) == 0);

        if ($textOnly) {
            $mailer->setTextBody($body);
        } else {
            $textBody = strip_tags(br2nl($body)); // need to create the plain-text part
            $mailer->setTextBody($textBody);
            $mailer->setHtmlBody($body);
        }

        $mailer->send();

        $reportSchedule->update_next_run_time($scheduleInfo["id"],
                                              $scheduleInfo["next_run"],
                                              $scheduleInfo["time_interval"]);
    } catch (MailerException $me) {
        switch ($me->getCode()) {
            case MailerException::InvalidEmailAddress:
                $GLOBALS["log"]->info("No email address for {$recipientName}");
                break;
            default:
                $GLOBALS["log"]->fatal("Mail error: " . $me->getMessage());
                break;
        }
    }

    // need unlink for loop
    foreach ($tempFiles as $filename) {
        //only un rem if we need to remove cvs and we can't just stream it
        $filePath = sugar_cached('csv/') . $filename;
        unlink($filePath);
    }
}
