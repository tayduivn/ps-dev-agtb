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
require_once('modules/Reports/schedule/ReportSchedule.php');
require_once('modules/Reports/utils.php');
require_once('include/modules.php');
require_once('config.php');

/** @var Localization $locale */
global $sugar_config,
       $current_language,
       $app_list_strings,
       $app_strings,
       $locale,
       $timedate;

$language         = $sugar_config['default_language']; // here we'd better use English, because pdf coding problem.
$app_list_strings = return_app_list_strings_language($language);
$app_strings      = return_application_language($language);

$reportSchedule = new ReportSchedule();
$reportSchedule->handleFailedReports();
$reportsToEmail = $reportSchedule->get_reports_to_email();

//BEGIN SUGARCRM flav=ent ONLY
//Process Enterprise Schedule reports via CSV
//bug: 23934 - enable Advanced reports
require_once('modules/ReportMaker/process_scheduled.php');
//END SUGARCRM flav=ent ONLY

global $report_modules,
       $modListHeader,
       $current_user;

$queue = new SugarJobQueue();
foreach ($reportsToEmail as $scheduleInfo) {
    $job = BeanFactory::getBean('SchedulersJobs');
    $job->name = 'Send Scheduled Report ' . $scheduleInfo['report_id'];
    $job->assigned_user_id = $scheduleInfo['user_id'];
    $job->target = 'class::SugarJobSendScheduledReport';
    $job->data = $scheduleInfo['id'];
    $job->job_group = 'Report ' . $scheduleInfo['report_id'];
    $queue->submitJob($job);
}

sugar_cleanup(false); // continue script execution so that if run from Scheduler, job status will be set back to "Active"
