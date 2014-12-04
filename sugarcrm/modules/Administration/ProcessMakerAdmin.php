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
global $sugar_config;

$moduleName = 'pmse_Project';

$links = array();


$links[$moduleName]['Settings'] = array(
    'Settings',
    'LBL_PMSE_ADMIN_TITLE_SETTINGS',
    'LBL_PMSE_ADMIN_DESC_SETTINGS',
    'javascript:parent.SUGAR.App.router.navigate("pmse_Inbox/config", {trigger: true});',
    //'./index.php?module=' . $moduleName . '&action=about',
);

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

$admin_group_header []= array(
    'LBL_PMSE_ADMIN_TITLE_MODULE',
    '',
    false,
    $links,
    'LBL_PMSE_ADMIN_DESC_MODULE'
);
