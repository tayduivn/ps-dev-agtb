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
$module_menu = array();
//END SUGARCRM flav=ent ONLY
//BEGIN SUGARCRM flav!=sales ONLY
if(!(ACLController::checkAccess('Reports', 'create', true))) {
	$module_menu[] = array("index.php?module=Reports&report_module=&action=index&page=report&Create+Custom+Report=Create+Custom+Report", $mod_strings['LBL_CREATE_REPORT'],"CreateReport", 'Reports');
}
//END SUGARCRM flav!=sales ONLY

$module_menu[] = array("index.php?module=Reports&action=index", $mod_strings['LBL_ALL_REPORTS'],"Reports", 'Reports');
//BEGIN SUGARCRM flav=ent ONLY
$module_menu[] = array("index.php?module=ReportMaker&action=index&return_module=ReportMaker&return_action=index", $ent_mod_strings['LNK_ADVANCED_REPORTING'],"ReportMaker");
//END SUGARCRM flav=ent ONLY
	
if(!(ACLController::checkAccess('Reports', 'edit', true))) {
    $module_menu[] = array("index.php?module=Reports&favorite=1&action=index", $mod_strings['LBL_FAVORITE_REPORTS'], "FavoriteReports", 'Reports');
    $module_menu[] = array("index.php?module=Reports&action=index", $mod_strings['LBL_ALL_REPORTS'],"Reports", 'Reports');
}

//BEGIN SUGARCRM flav=ent ONLY
if(!(ACLController::checkAccess('Reports', 'create', true))) {
	$module_menu[] = array("index.php?module=ReportMaker&action=index&return_module=ReportMaker&return_action=index", $ent_mod_strings['LNK_ADVANCED_REPORTING'],"ReportMaker",'Reports');
}
//END SUGARCRM flav=ent ONLY


?>
