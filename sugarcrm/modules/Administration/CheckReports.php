<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Enterprise Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/products/sugar-enterprise-eula.html
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2010 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
if(!is_admin($current_user)) sugar_die("Unauthorized access to administration.");


require_once('modules/Reports/Report.php');

$altered_cols = array (
    'project_task'=>array('milestone_flag'),
 	'tasks'=>array('date_start_flag','date_due_flag','date_start','time_start','date_due','time_due'),
	'calls'=>array('date_start', 'time_start'),
	'meetings'=>array('date_start', 'time_start'),
	'email_marketing'=>array('date_start', 'time_start'),
	'emails'=>array('date_start', 'time_start', 'date_sent'),
	'leads'=>array('do_not_call'),
	'contacts'=>array('do_not_call'),
	'prospects'=>array('do_not_call'),
	'workflow_alerts'=>array('where_filter'),		
	'workflow_triggershells'=>array('show_past'),		
	'workflow'=>array('status'),		
	'reports'=>array('is_published'),
    );
//$bad_reports = array();
    
function checkEachColInArr ($arr, $full_table_list, $report_id, $report_name, $user_name){
	foreach ($arr as $column) {
		global $beanFiles;
		if(empty($beanFiles)) {
			include('include/modules.php');
		}
		if(is_array($column))
		{
		$module_name = $full_table_list[$column['table_key']]['module'];
		}
		if(!isset($module_name))
		{
			continue;
		}
		$bean_name = get_singular_bean_name($module_name);
		require_once($beanFiles[$bean_name]);
		$module = new $bean_name;	
		$table = $module->table_name;	
		$colName = $column['name'];

		if((isset($altered_cols[$table]) && isset($altered_cols[$table][$colName]))
			|| $colName == 'email1' || $colName == 'email2') {
			echo $user_name.'------'.$report_name."------".$colName;
			//array_push($bad_reports[$report_id], $column);
		}
	}
}
function displayBadReportsList() {
	foreach($bad_reports as $key=>$cols) {
		echo $key.'***'.$cols;
	}
}

function checkReports() {	
	$savedReportBean = new SavedReport();
	$savedReportQuery = "select * from saved_reports where deleted=0";
	
	$result = $savedReportBean->db->query($savedReportQuery, true, "");
	$row = $savedReportBean->db->fetchByAssoc($result);
	while ($row != null) {
		$saved_report_seed = new SavedReport();
		$saved_report_seed->retrieve($row['id'], false);
		$report = new Report($saved_report_seed->content);
	
	
		$display_columns =  $report->report_def['display_columns'];
		$filters_def = $report->report_def['filters_def'];
		$group_defs = $report->report_def['group_defs'];
		if (!empty($report->report_def['order_by']))
			$order_by = $report->report_def['order_by'];
		else 
			$order_by = array();
		$summary_columns = $report->report_def['summary_columns'];
		$full_table_list = $report->report_def['full_table_list'];
		$owner_user = new User();
		$owner_user->retrieve($row['assigned_user_id']);
		checkEachColInArr($display_columns, $full_table_list, $row['id'], $row['name'], $owner_user->name);
		checkEachColInArr($group_defs, $full_table_list, $row['id'], $row['name'], $owner_user->name);
		checkEachColInArr($order_by, $full_table_list, $row['id'], $row['name'], $owner_user->name);
		checkEachColInArr($summary_columns, $full_table_list, $row['id'], $row['name'], $owner_user->name);
		foreach($filters_def as $filters_def_row)
		{
			checkEachColInArr($filters_def_row, $full_table_list, $row['id'], $row['name'], $owner_user->name);
		}
		$row = $savedReportBean->db->fetchByAssoc($result);
	}
}

checkReports();
//displayBadReportsList();


echo $mod_strings['LBL_DIAGNOSTIC_DONE'];

