<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */
/*********************************************************************************

 * Description:
 ********************************************************************************/



require_once('modules/QueryBuilder/QueryBuilder.php');


global $mod_strings;
global $app_strings;
global $app_list_strings;
global $focus, $support_coming_due, $support_expired;
$focus = new QueryBuilder();
if(!empty($_REQUEST['record'])) {
    $result = $focus->retrieve($_REQUEST['record']);
    if($result == null)
    {
    	sugar_die($app_strings['ERROR_NO_RECORD']);
    }
}
else {
	header("Location: index.php?module=QueryBuilder&action=index");
}


if(isset($_REQUEST['isDuplicate']) && $_REQUEST['isDuplicate'] == 'true') {
	$focus->id = "";
}
echo getClassicModuleTitle($mod_strings['LBL_MODULE_NAME'], array($mod_strings['LBL_MODULE_TITLE'],$focus->name), true);



$GLOBALS['log']->info("QueryBuilder detail view");

$xtpl=new XTemplate ('modules/QueryBuilder/DetailView.html');
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("GRIDLINE", $gridline);
$xtpl->assign('ID', $focus->id);
$xtpl->assign('NAME', $focus->name);
$xtpl->assign("DESCRIPTION", nl2br($focus->description));

if ($focus->query_locked == 'on') $xtpl->assign("QUERY_LOCKED", "checked");

//UI Parameters

$xtpl->assign('QUERY_TYPE', $app_list_strings['query_type_dom'][$focus->query_type]);

$xtpl->assign('BASE_MODULE', $focus->base_module);

global $current_user;
if(is_admin($current_user) && $_REQUEST['module'] != 'DynamicLayout' && !empty($_SESSION['editinplace'])){

	$xtpl->assign("ADMIN_EDIT","<a href='index.php?action=index&module=DynamicLayout&from_action=".$_REQUEST['action'] ."&from_module=".$_REQUEST['module'] ."&record=".$_REQUEST['record']. "'>".SugarThemeRegistry::current()->getImage("EditLayout","border='0'  align='bottom'",null,null,'.gif',$mod_strings['LBL_EDITLAYOUT'])."</a>");
}


// adding custom fields:
require_once('modules/DynamicFields/templates/Files/DetailView.php');


$xtpl->parse("main");
$xtpl->out("main");




//Display the UI query builder tool
//
//Sub Panel for the UI Tool

echo "<p><p>";

include('modules/QueryBuilder/AQBPanelView.php');

?>
