<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
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
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

global $sugar_version, $sugar_config;
echo '<script type="text/javascript" src="include/javascript/sugar_3.js?s=' . $sugar_version . '&c=' . $sugar_config['js_custom_version'] . '"></script>';
	echo '<link rel="stylesheet" type="text/css" media="all" href="' . getJSPath(SugarThemeRegistry::current()->getCSSURL('style.css')) . '">';
	echo '<script type="text/javascript" src="jscalendar/calendar.js?s=' . $sugar_version . '&c=' . $sugar_config['js_custom_version'] . '"></script>';
	echo '<script type="text/javascript" src="jscalendar/lang/calendar-' . substr($GLOBALS['current_language'], 0, 2) . '.js?s=' . $sugar_version . '&c=' . $sugar_config['js_custom_version'] . '"></script>';
	echo '<script type="text/javascript" src="jscalendar/calendar-setup_3.js?s=' . $sugar_version . '&c=' . $sugar_config['js_custom_version'] . '"></script>';
include_once('modules/Reports/schedule/save_schedule.php');

global $timedate;
global $app_strings;
global $app_list_strings;
global $mod_strings;




$xtpl = new XTemplate('modules/Reports/schedule/add_schedule.html');


echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_SCHEDULE_EMAIL'],'');
$xtpl->assign('STYLESHEET', SugarThemeRegistry::current()->getCSS());
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("CALENDAR_LANG", "en");
$xtpl->assign("CALENDAR_DATEFORMAT", $timedate->get_cal_date_format());
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$refreshPage = (isset($_REQUEST['refreshPage']) ? $_REQUEST['refreshPage'] : "true");

$xtpl->assign("REFRESH_PAGE", $refreshPage);
$time_interval_select = translate('DROPDOWN_SCHEDULE_INTERVALS', 'Reports');

require_once('modules/Reports/schedule/ReportSchedule.php');
$rs = new ReportSchedule();
$schedule = $rs->get_report_schedule_for_user($_REQUEST['id']);
include_once('include/formbase.php');
$xtpl->assign('FIELDS', getAnyToForm(''));
if($schedule){
	$xtpl->assign('SCHEDULE_ID', $schedule['id']);	
	$xtpl->assign('DATE_START',$timedate->to_display_date($schedule['date_start'],false));	
	
	if($schedule['active']){
		
		$xtpl->assign('SCHEDULE_ACTIVE_CHECKED', 'checked');
	}
	$xtpl->assign('NEXT_RUN', $timedate->to_display_date_time($schedule['next_run']));	
	$xtpl->assign('TIME_INTERVAL_SELECT', get_select_options_with_id($time_interval_select,$schedule['time_interval'] ));
	$xtpl->assign('SCHEDULE_TYPE',$schedule['schedule_type']);
}else{
	$xtpl->assign('NEXT_RUN',$mod_strings['LBL_NONE']);
	$xtpl->assign('TIME_INTERVAL_SELECT', get_select_options_with_id($time_interval_select, ''));
	if(isset($_REQUEST['schedule_type']) && $_REQUEST['schedule_type']!=""){
	$xtpl->assign('SCHEDULE_TYPE',$_REQUEST['schedule_type']);
	}
}

$xtpl->parse('main');
$xtpl->out('main');
	
?>
