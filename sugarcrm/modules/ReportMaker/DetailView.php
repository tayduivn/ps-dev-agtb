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
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
/*********************************************************************************
 * $Id: DetailView.php 55779 2010-04-02 19:51:23Z jmertic $
 * Description:
 ********************************************************************************/








global $mod_strings;
global $app_strings;
global $app_list_strings;
global $focus;
$focus = new ReportMaker();
if(!empty($_REQUEST['record'])) {
    $result = $focus->retrieve($_REQUEST['record']);
    if($result == null)
    {
    	sugar_die($app_strings['ERROR_NO_RECORD']);
    }
}
else {
	header("Location: index.php?module=ReportMaker&action=index");
}

if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
	$focus->id = "";
}

$params = array();
$params[] = "<span class='pointer'>&raquo;</span>".$focus->get_summary_text();
echo getClassicModuleTitle("ReportMaker", $params, true);

$GLOBALS['log']->info("ReportMaker detail view");

$xtpl=new XTemplate ('modules/ReportMaker/DetailView.html');
$sub_xtpl = new XTemplate ('modules/ReportMaker/DetailView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("GRIDLINE", $gridline);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);

$xtpl->assign("ID", $focus->id);
$xtpl->assign('NAME', $focus->name);
$xtpl->assign('TITLE', $focus->title);
$xtpl->assign("DESCRIPTION", nl2br($focus->description));


$xtpl->assign("REPORT_ALIGN", $app_list_strings['report_align_dom'][$focus->report_align]);

$xtpl->assign("TEAM", $focus->assigned_name);

global $current_user;
/*
if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){

	$xtpl->assign("ADMIN_EDIT","<a href='index.php?action=index&module=DynamicLayout&from_action=".$_REQUEST['action'] ."&from_module=".$_REQUEST['module'] ."&record=".$_REQUEST['record']. "'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0' alt='Edit Layout' align='bottom'")."</a>");
}
*/

// adding custom fields:
require_once('modules/DynamicFields/templates/Files/DetailView.php');


$xtpl->parse("main");
$xtpl->out("main");

//Show the datasets

$old_contents = ob_get_contents();
ob_end_clean();

if($sub_xtpl->var_exists('subpanel', 'SUBDATASETS')){
ob_start();
global $focus_list;
$focus_list = $focus->get_data_sets("ORDER BY list_order_y ASC");
include('modules/DataSets/SubPanelView.php');
echo "<BR>\n";
$subdatasets =ob_get_contents();
ob_end_clean();
}

ob_start();
echo $old_contents;

if(!empty($subdatasets))$sub_xtpl->assign('SUBDATASETS', $subdatasets);

$sub_xtpl->parse("subpanel");
$sub_xtpl->out("subpanel");

	
		
?>
