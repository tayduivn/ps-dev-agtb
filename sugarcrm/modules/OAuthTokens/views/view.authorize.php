<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

class OauthTokensViewAuthorize extends SugarView
{
	public function display()
    {
        global $current_user;
        $sugar_smarty = new Sugar_Smarty();
        $sugar_smarty->assign('APP', $GLOBALS['app_strings']);
        $sugar_smarty->assign('MOD', $GLOBALS['mod_strings']);
        $sugar_smarty->assign('token', $_REQUEST['token']);
        $sugar_smarty->assign('sid', session_id());

        $token = OAuthToken::load($_REQUEST['token']);
        if(empty($token) || empty($token->consumer) || $token->tstate != OAuthToken::REQUEST || empty($token->consumer_obj)) {
            sugar_die('Invalid token');
        }

        if(empty($_REQUEST['confirm'])) {
            $sugar_smarty->assign('consumer', sprintf($GLOBALS['mod_strings']['LBL_OAUTH_CONSUMERREQ'], $token->consumer_obj->name));
            $roles = array('' => '');
            $allroles = ACLRole::getAllRoles();
            foreach($allroles as $role) {
                $roles[$role->id] = $role->name;
            }
            $sugar_smarty->assign('roles', $roles);
            $hash = md5(rand());
            $_SESSION['oauth_hash'] = $hash;
            $sugar_smarty->assign('hash', $hash);
            echo $sugar_smarty->fetch('modules/OAuthTokens/tpl/authorize.tpl');
        } else {
            if($_REQUEST['sid'] != session_id() || $_SESSION['oauth_hash'] != $_REQUEST['hash']) {
                sugar_die('Invalid request');
            }
            $verify = $token->authorize(array("user" => $current_user->id, "role" => $_REQUEST['role']));
            $sugar_smarty->assign('VERIFY', $verify);
            $sugar_smarty->assign('token', '');
            echo $sugar_smarty->fetch('modules/OAuthTokens/tpl/authorized.tpl');
        }
    }

}

