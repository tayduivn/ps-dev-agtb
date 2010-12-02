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
/*********************************************************************************
 * $Id: Menu.php 53116 2009-12-10 01:24:37Z mitani $
 * Description:  TODO To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
global $mod_strings;
//BEGIN SUGARCRM flav=ent ONLY
global $current_language;
$ent_mod_strings = return_module_language($current_language, "ReportMaker");
//END SUGARCRM flav=ent ONLY
$module_menu = Array(
//BEGIN SUGARCRM flav=dce ONLY
    Array("index.php?module=DCEReports&action=LicensingReport", $mod_strings['LBL_DCE_LICENSING_REPORT'], "DCELicensingReport", 'Reports'),

//END SUGARCRM flav=dce ONLY
//BEGIN SUGARCRM flav!=sales ONLY

    Array("index.php?module=Reports&report_module=&action=index&page=report&Create+Custom+Report=Create+Custom+Report", $mod_strings['LBL_CREATE_REPORT'],"CreateReport", 'Reports'),
    Array("index.php?module=Reports&favorite=1&action=index", $mod_strings['LBL_FAVORITE_REPORTS'], "FavoriteReports", 'Reports'), 
//END SUGARCRM flav!=sales ONLY
    Array("index.php?module=Reports&action=index", $mod_strings['LBL_ALL_REPORTS'],"Reports", 'Reports'),
//	Array("index.php?module=Reports&action=ActivitiesReports", $mod_strings['LBL_ACTIVITIES_REPORTS'],"Reports", 'Reports'),
	/*
	Array("index.php?module=Reports&action=index&report_module=Accounts&query=true", $mod_strings['LBL_ACCOUNT_REPORTS'],"AccountReports", 'Accounts'),
	Array("index.php?module=Reports&action=index&report_module=Contacts&query=true", $mod_strings['LBL_CONTACT_REPORTS'],"ContactReports", 'Contacts'),
	Array("index.php?module=Reports&action=index&report_module=Leads&query=true", $mod_strings['LBL_LEAD_REPORTS'],"LeadReports", 'Leads'),
	Array("index.php?module=Reports&action=index&report_module=Opportunities&query=true", $mod_strings['LBL_OPPORTUNITY_REPORTS'],"OpportunityReports", 'Opportunities'),
	Array("index.php?module=Reports&action=index&report_module=Quotes&query=true", $mod_strings['LBL_QUOTE_REPORTS'],"QuoteReports", 'Quotes'),
	Array("index.php?module=Reports&action=index&report_module=Cases&query=true", $mod_strings['LBL_CASE_REPORTS'],"CaseReports", 'Cases'),
	Array("index.php?module=Reports&action=index&report_module=Bugs&query=true", $mod_strings['LBL_BUG_REPORTS'],"BugReports", 'Bugs'),
	Array("index.php?module=Reports&action=index&report_module=Calls&query=true", $mod_strings['LBL_CALL_REPORTS'],"CallReports"),
	Array("index.php?module=Reports&action=index&report_module=Meetings&query=true", $mod_strings['LBL_MEETING_REPORTS'],"MeetingReports"),
	Array("index.php?module=Reports&action=index&report_module=Tasks&query=true", $mod_strings['LBL_TASK_REPORTS'],"TaskReports", 'Tasks'),
	Array("index.php?module=Reports&action=index&report_module=Emails&query=true", $mod_strings['LBL_EMAIL_REPORTS'],"EmailReports", 'Emails'),
	Array("index.php?module=Reports&action=index&report_module=Forecasts&query=true", $mod_strings['LBL_FORECAST_REPORTS'],"ForecastReports", 'Forecasts'),
	Array("index.php?module=Reports&action=index&report_module=ProjectTask&query=true", $mod_strings['LBL_PROJECT_TASK_REPORTS'],"TaskReports", 'Project'),
	Array("index.php?module=Reports&action=index&report_module=Prospects&query=true", $mod_strings['LBL_PROSPECT_REPORTS'],"TaskReports", 'Prospects'),	
	Array("index.php?module=Reports&action=index&report_module=Contracts&query=true", $mod_strings['LBL_CONTRACT_REPORTS'],"ContractReports", 'Contracts'),
	*/
		
//BEGIN SUGARCRM flav=ent ONLY
	Array("index.php?module=ReportMaker&action=index&return_module=ReportMaker&return_action=index", $ent_mod_strings['LNK_ADVANCED_REPORTING'],"ReportMaker"),
//END SUGARCRM flav=ent ONLY
	);
	
if(!(ACLController::checkAccess('Reports', 'edit', true)))
{
    $module_menu = Array(
//BEGIN SUGARCRM flav=dce ONLY
    Array("index.php?module=DCEReports&action=LicensingReport", $mod_strings['LBL_DCE_LICENSING_REPORT'], "DCELicensingReport", 'Reports'),
//END SUGARCRM flav=dce ONLY
    Array("index.php?module=Reports&favorite=1&action=index", $mod_strings['LBL_FAVORITE_REPORTS'], "FavoriteReports", 'Reports'), 
    Array("index.php?module=Reports&action=index", $mod_strings['LBL_ALL_REPORTS'],"Reports", 'Reports'),        
//BEGIN SUGARCRM flav=ent ONLY
    Array("index.php?module=ReportMaker&action=index&return_module=ReportMaker&return_action=index", $ent_mod_strings['LNK_ADVANCED_REPORTING'],"ReportMaker"),
//END SUGARCRM flav=ent ONLY
    );
}	

?>
