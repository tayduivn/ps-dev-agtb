<?php
//FILE SUGARCRM flav=pro ONLY
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use Sugarcrm\Sugarcrm\Security\InputValidation\InputValidation;

global $timedate;
global $app_strings;
global $app_list_strings;
global $current_language;
global $current_user;
global $hilite_bg;
global $sugar_version, $sugar_config;



insert_popup_header();

$GLOBALS['log']->info("Project Resource Report view");

echo getClassicModuleTitle($mod_strings['LBL_MODULE_NAME'], array($mod_strings['LBL_RESOURCE_REPORT']), false);

$sugar_smarty = new Sugar_Smarty();
///
/// Assign the template variables
///
$sugar_smarty->assign('MOD', $mod_strings);
$sugar_smarty->assign('APP', $app_strings);
$sugar_smarty->assign("BG_COLOR", $hilite_bg);
$sugar_smarty->assign("CALENDAR_DATEFORMAT", $timedate->get_cal_date_format());
$sugar_smarty->assign("DATE_FORMAT", $timedate->get_date_format());
$sugar_smarty->assign("CURRENT_USER", $current_user->id);
$sugar_smarty->assign("CALENDAR_LANG_FILE", getJSPath('jscalendar/lang/calendar-' . substr($GLOBALS['current_language'], 0, 2).'.js'));


$focus = BeanFactory::getBean('Project');

$request = InputValidation::getService();
if(!empty($_REQUEST['record']))
{
    $id = $request->getValidInputRequest('record', 'Assert\Guid');
    $focus->retrieve($id);
    $sugar_smarty->assign('ID', $id);
}

$userBean = BeanFactory::getBean('Users');
$focus->load_relationship("user_resources");
$users = $focus->user_resources->getBeans($userBean);
$contactBean = BeanFactory::getBean('Contacts');
$focus->load_relationship("contact_resources");
$contacts = $focus->contact_resources->getBeans($contactBean);

$resources = array();
foreach($users as $user) {
    $resources[$user->full_name] = $user;
}
foreach($contacts as $contact) {
    $resources[$contact->full_name] = $contact;
}
ksort($resources);
$sugar_smarty->assign("RESOURCES", $resources);

$projectTasks = array();
$projectTaskBean = BeanFactory::getBean('ProjectTask');
$holidayBean = BeanFactory::getBean('Holidays');
$holidays = array();
$projects= array();
$projectBean = BeanFactory::getBean('Project');
$dateRangeArray = array();

if (!empty($_REQUEST['resource'])) {
    $sugar_smarty->assign('DATE_START', $request->getValidInputRequest('date_start'));
    $sugar_smarty->assign('DATE_FINISH', $request->getValidInputRequest('date_finish'));
    $sugar_smarty->assign('SELECTED_RESOURCE', $request->getValidInputRequest('resource'));

    $dateStartDb = $timedate->to_db_date($_REQUEST['date_start'], false);
    $dateFinishDb = $timedate->to_db_date($_REQUEST['date_finish'], false);

    $query = "SELECT project_task.id as id, project.id as project_id FROM project_task, project WHERE project_task.resource_id like '".$projectTaskBean->db->quote($_REQUEST['resource'])."'".
        " AND (project_task.date_start BETWEEN '$dateStartDb' AND '$dateFinishDb' OR project_task.date_finish BETWEEN '$dateStartDb' AND '$dateFinishDb')".
        " AND project_task.deleted=0 AND (project_task.project_id = project.id) AND (project.is_template = 0) order by project_task.date_start";

    $result = $projectTaskBean->db->query($query, true, "");
    while(($row = $projectTaskBean->db->fetchByAssoc($result)) != null) {
        $projectTask = BeanFactory::getBean('ProjectTask');
        $projectTask->id = $row['id'];
        $projectTask->retrieve();
        $projectTasks[] = $projectTask;
    }

    //Projects //////////////////////
    $result = $projectBean->db->query($query, true, "");
    while(($row = $projectBean->db->fetchByAssoc($result)) != null) {
        $project = BeanFactory::getBean('Project');
        $project->id = $row['project_id'];
        $project->retrieve();
        $projects[$project->id] = $project;
    }

    //Holidays //////////////////////
    $query = "select holidays.*, holidays.holiday_date AS hol_date, project.name AS project_name from holidays, project where ";
    $query .= "person_id like '". $holidayBean->db->quote($_REQUEST['resource']) ."'";
    $query .= " and holiday_date between '$dateStartDb' and '$dateFinishDb'".
    	" AND holidays.related_module_id = project.id AND holidays.deleted=0 ";
    $query .= "UNION ALL ";
    $query .= "select holidays.*, holidays.holiday_date AS hol_date, '" . $mod_strings['LBL_PERSONAL_HOLIDAY'] . "' AS project_name from holidays where ";
    $query .= "person_id like '". $holidayBean->db->quote($_REQUEST['resource']) ."'";
    $query .= " and holiday_date between '$dateStartDb' and '$dateFinishDb'".
    " AND holidays.related_module_id IS NULL AND holidays.deleted=0 ORDER BY hol_date ";
    $result = $holidayBean->db->query($query, true, "");

    $i = 0;
    $isHoliday = array();
    while (($row = $holidayBean->db->fetchByAssoc($result)) != null) {
        $holiday = BeanFactory::getBean('Holidays');
        $holiday->id = $row['id'];
        $holiday->retrieve();
        $holidayDate = $timedate->fromUserDate($holiday->holiday_date, false);
        $holidays[$i]['holidayDate'] = $timedate->asUserDate($holidayDate, false);
        $holidays[$i]['projectName'] = $row['project_name'];
        $isHoliday[$holidayDate->ts] = true;
        $i++;
    }

    // Daily Report //////////////////////
    $workDayHours = 8;

//    $dateRangeStart = $timedate->to_db_date($_REQUEST['date_start'], false);
//    $dateRangeFinish = $timedate->to_db_date($_REQUEST['date_finish'], false);
//    $dateStart = $dateRangeStart;
    $dateRangeStart = $timedate->fromDbDate($dateStartDb);
    $dateRangeFinish = $timedate->fromDbDate($dateFinishDb);
    $dateRangeFinishTs = $dateRangeFinish->ts;

    while ($dateRangeStart->ts <= $dateRangeFinishTs) {
        $dateRangeArray[$timedate->asUserDate($dateRangeStart, false)] = 0;
        $dateRangeStart->modify("+1 day");
        while ($dateRangeStart->day_of_week == 6 || $dateRangeStart->day_of_week == 0) {
            $dateRangeStart->modify("+1 day");
        }
    }

    foreach($projectTasks as $projectTask) {
        $duration = $projectTask->duration;
        $dateStart = $timedate->fromDbFormat($timedate->to_db_date($projectTask->date_start, false), TimeDate::DB_DATE_FORMAT);
        $dateFinish = $timedate->fromDbFormat($timedate->to_db_date($projectTask->date_finish, false), TimeDate::DB_DATE_FORMAT);
        $dateFinishTs = $dateFinish->ts;

        if ($projectTask->duration_unit == "Days") {
            $duration = $duration * $workDayHours;
        }
        $remainingDuration = $duration;

        while ($remainingDuration > 0) {
            // We don't need to look at tasks that start outside our selected date range.
            if ($dateStart->ts > $dateRangeFinishTs)
                break;

            $displayDate = $timedate->asUserDate($dateStart, false);
            if (isset($dateRangeArray[$displayDate])) {
                if ($remainingDuration > $workDayHours)
                    $dateRangeArray[$displayDate] += $workDayHours;
                else
                    $dateRangeArray[$displayDate] += $remainingDuration;
            }
            if (!isset($isHoliday[$dateStart->ts])){
	            $remainingDuration -= $workDayHours;
            }
            $dateStart->modify("+1 day");
            while ($dateStart->day_of_week == 6 || $dateStart->day_of_week == 0 || isset($isHoliday[$dateStart->ts])) {
                $dateStart->modify("+1 day");
            }
        }
    }

    // Calculate the percentage
    foreach($dateRangeArray as $index=>$eachDay){
        if ($eachDay > 0) {
            $eachDay = round(($eachDay / $workDayHours) * 100);
            $dateRangeArray[$index] = $eachDay;
        }
    }

    foreach ($holidays as $holiday) {
        $displayDate = $holiday['holidayDate'];
        if (isset($dateRangeArray[$displayDate])) {
            $dateRangeArray[$displayDate] = -8;
        }
    }
}

$sugar_smarty->assign("TASKS", $projectTasks);
$sugar_smarty->assign("HOLIDAYS", $holidays);
$sugar_smarty->assign("PROJECTS", $projects);
$sugar_smarty->assign("DATE_RANGE_ARRAY", $dateRangeArray);

echo $sugar_smarty->fetch('modules/Project/ResourceReport.tpl');
insert_popup_footer();
