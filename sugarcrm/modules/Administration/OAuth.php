<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'include/OAuth/SugarOAuth.php';
global $sugar_config;

$title = get_module_title("", translate('LBL_OAUTH').":", true);
$sugar_smarty = new Sugar_Smarty();

$sugar_smarty->assign('APP', $GLOBALS['app_strings']);
$sugar_smarty->assign('MOD', $GLOBALS['mod_strings']);

if(!empty($_REQUEST['authorize']) && !empty($_REQUEST['token']))
{
    $verify = SugarOAuth::authorize($_REQUEST['token'], array("user" => $current_user->user_name));
    $sugar_smarty->assign('VERIFY', $verify);
}

echo $sugar_smarty->fetch('modules/Administration/OAuth.tpl');

