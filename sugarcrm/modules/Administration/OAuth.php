<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once 'include/OAuth/SugarOAuth.php';
global $sugar_config;

$title = get_module_title("", translate('LBL_OAUTH').":", true);
$sugar_smarty = new Sugar_Smarty();

$sugar_smarty->assign('APP', $GLOBALS['app_strings']);
$sugar_smarty->assign('MOD', $GLOBALS['mod_strings']);
$sugar_smarty->assign('token', $_REQUEST['token']);
$sugar_smarty->assign('sid', session_id());
$roles = array('' => '');
$allroles = ACLRole::getAllRoles();
foreach($allroles as $role) {
    $roles[$role->id] = $role->name;
}
$sugar_smarty->assign('roles', $roles);

if(!empty($_REQUEST['cregister']) && !empty($_REQUEST['ckey']) && $_REQUEST['sid'] == session_id())
{
    SugarOAuthData::registerConsumer($_REQUEST['ckey'], $_REQUEST['csecret']);
}

if(!empty($_REQUEST['authorize']) && !empty($_REQUEST['token']) && $_REQUEST['sid'] == session_id())
{
    $verify = SugarOAuth::authorize($_REQUEST['token'], array("user" => $current_user->user_name, "role" => $_REQUEST['role']));
    $sugar_smarty->assign('VERIFY', $verify);
}

echo $sugar_smarty->fetch('modules/Administration/OAuth.tpl');

