<?php
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

global $sugar_version, $js_custom_version, $app_strings, $mod_strings;

if (!isset($install_script) || !$install_script) {
    die($mod_strings['ERR_NO_DIRECT_SCRIPT']);
}

$sugar_smarty = new Sugar_Smarty();

$sugar_smarty->assign('icon', $icon);
$sugar_smarty->assign('css', $css);
$sugar_smarty->assign('loginImage', $loginImage);

$sugar_smarty->assign('help_url', $help_url);
$sugar_smarty->assign('sugar_md', $sugar_md);
$sugar_smarty->assign('langHeader', get_language_header());
$sugar_smarty->assign('versionToken', getVersionedPath(null));
$sugar_smarty->assign('next_step', $next_step);

$sugar_smarty->assign('APP', $app_strings);
$sugar_smarty->assign('MOD', $mod_strings);

$sugar_smarty->display("install/templates/websocketConfig.tpl");
