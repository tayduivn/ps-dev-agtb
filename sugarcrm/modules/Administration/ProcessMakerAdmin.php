<?php
//FILE SUGARCRM flav=ent ONLY
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

global $admin_group_header;
//global $current_user;
global $sugar_config;
//global $sugar_flavor;

$moduleName = 'pmse_Project';

// initialize a temp array that will hold the options we want to create
$links = array();
// injecting the acl module name in the SESSION super global in order to
//$current_user->getDeveloperModules();
//if (!isset($_SESSION[$current_user->user_name.'_get_developer_modules_for_user'][$moduleName])) {
//    $_SESSION[$current_user->user_name.'_get_developer_modules_for_user'][] = $moduleName;
//}

$links[$moduleName]['CasesList'] = array(
    'CasesList',
    'LBL_PMSE_ADMIN_TITLE_CASESLIST',
    'LBL_PMSE_ADMIN_DESC_CASESLIST',
    'javascript:parent.SUGAR.App.router.navigate("pmse_Inbox/layout/casesList", {trigger: true});',
    //'./index.php?module=' . $moduleName . '&action=caseslist',
);

$links[$moduleName]['EngineLogs'] = array(
    'EngineLogs',
    'LBL_PMSE_ADMIN_TITLE_ENGINELOGS',
    'LBL_PMSE_ADMIN_DESC_ENGINELOGS',
    'javascript:parent.SUGAR.App.router.navigate("pmse_Inbox/layout/logView", {trigger: true});',
    //'./index.php?module=' . $moduleName . '&action=enginelogs',
);

//$links[$moduleName]['About'] = array(
//    'About',
//    'LBL_PMSE_ADMIN_TITLE_ABOUT',
//    'LBL_PMSE_ADMIN_DESC_ABOUT',
//    'javascript:parent.SUGAR.App.router.navigate("pmse_Project/layout/about", {trigger: true});',
//    //'./index.php?module=' . $moduleName . '&action=about',
//);

$admin_group_header []= array(
    'LBL_PMSE_ADMIN_TITLE_MODULE',
    '',
    false,
    $links,
    'LBL_PMSE_ADMIN_DESC_MODULE'
);
