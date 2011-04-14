<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement 
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.  
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may 
 *not use this file except in compliance with the License. Under the terms of the license, You 
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or 
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or 
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit 
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the 
 *Software without first paying applicable fees is strictly prohibited.  You do not have the 
 *right to remove SugarCRM copyrights from the source code or user interface. 
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and 
 * (ii) the SugarCRM copyright notice 
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer 
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.  
 ********************************************************************************/
/*********************************************************************************
 * $Id: UserDCEInstanceRelationshipEdit.php 13782 2006-06-06 17:58:55Z majed $
 * Description:  TODO: To be written.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/
//FILE SUGARCRM flav=dce ONLY


require_once('modules/Users/UserDCEInstanceRelationship.php');
require_once('modules/Users/Forms.php');


global $app_strings;
global $app_list_strings;
global $mod_strings;
global $sugar_version, $sugar_config;

$focus = new UserDCEInstanceRelationship();

if(isset($_REQUEST['record'])) {
    $focus->retrieve($_REQUEST['record']);
}

if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
	$focus->id = "";
}
// Prepopulate either side of the relationship if passed in.
safe_map('dceinstance_name', $focus);
safe_map('instance_id', $focus);
safe_map('user_name', $focus);
safe_map('user_id', $focus);
safe_map('user_role', $focus);





$GLOBALS['log']->info("User dceinstance relationship");

$json = getJSONobj();
require_once('include/QuickSearchDefaults.php');
$qsd = new QuickSearchDefaults();
$sqs_objects = array('dceinstance_name' => $qsd->getQSParent());
$sqs_objects['dceinstance_name']['populate_list'] = array('dceinstance_name', 'instance_id');
$quicksearch_js = '<script type="text/javascript" language="javascript">sqs_objects = ' . $json->encode($sqs_objects) . '</script>';
echo $quicksearch_js;

$xtpl=new XTemplate ('modules/Users/UserDCEInstanceRelationshipEdit.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);

$xtpl->assign("RETURN_URL", "&return_module=$currentModule&return_action=DetailView&return_id=$focus->id");
$xtpl->assign("RETURN_MODULE", $_REQUEST['return_module']);
$xtpl->assign("RETURN_ACTION", $_REQUEST['return_action']);
$xtpl->assign("RETURN_ID", $_REQUEST['return_id']);
$xtpl->assign("PRINT_URL", "index.php?".$GLOBALS['request_string']);
$xtpl->assign("ID", $focus->id);
$xtpl->assign("USER",$userName = Array("NAME" => $focus->user_name, "ID" => $focus->user_id));
$xtpl->assign("DCEINSTANCE",$instName = Array("NAME" => $focus->dceinstance_name, "ID" => $focus->instance_id));

echo get_module_title($mod_strings['LBL_MODULE_NAME'], $mod_strings['LBL_USER_DCEINST_FORM_TITLE']." ".$userName['NAME'] . " - ". $instName['NAME'], true);

$xtpl->assign("USER_ROLE_OPTIONS", get_select_options_with_id($app_list_strings['dceinstance_user_relationship_type_dom'], $focus->user_role));




$xtpl->parse("main");

$xtpl->out("main");


$javascript = new javascript();
$javascript->setFormName('EditView');
$javascript->setSugarBean($focus);
$javascript->addToValidateBinaryDependency('dceinstance_name', 'alpha', $app_strings['ERR_SQS_NO_MATCH_FIELD'] . $mod_strings['LBL_DCEINST_NAME'], 'false', '', 'instance_id');
echo $javascript->getScript();


?>