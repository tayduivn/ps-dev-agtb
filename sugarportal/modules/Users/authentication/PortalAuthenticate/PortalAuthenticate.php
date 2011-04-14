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
/**
 * This file is used to control the authentication process.
 * It will call on the user authenticate and controll redirection
 * based on the users validation
 *
 */
class PortalAuthenticate {
	var $userAuthenticateClass = 'PortalAuthenticateUser';
	var $authenticationDir = 'PortalAuthenticate';
	/**
	 * Constructs PortalAuthenticate
	 * This will load the user authentication class
	 *
	 * @return SugarAuthenticate
	 */
	function PortalAuthenticate(){
		require_once('modules/Users/authentication/'. $this->authenticationDir . '/'. $this->userAuthenticateClass . '.php');
		$this->userAuthenticate = new $this->userAuthenticateClass();
	}
	/**
	 * Authenticates a user based on the username and password
	 * returns true if the user was authenticated false otherwise
	 * it also will load the user into current user if he was authenticated
	 *
	 * @param string $username
	 * @param string $password
	 * @return boolean
	 */
	function loginAuthenticate($username, $password, $PARAMS){
		global $mod_strings;
		unset($_SESSION['login_error']);

        if ($this->userAuthenticate->loadUserOnLogin($username, $password, $PARAMS)) {
			return $this->postLoginAuthenticate();
		}

		$_SESSION['login_user_name'] = $PARAMS['username'];
		$_SESSION['login_password'] = $PARAMS['password'];
		if(empty($_SESSION['login_error'])){
		  $_SESSION['login_error'] = $mod_strings['ERR_INVALID_PASSWORD'];
		}
		return false;

	}

	/**
	 * Once a user is authenticated on login this function will be called. Populate the session with what is needed and log anything that needs to be logged
	 *
	 */
	function postLoginAuthenticate(){
        global $reset_theme_on_default_user, $reset_language_on_default_user, $sugar_config;
        //THIS SECTION IS TO ENSURE VERSIONS ARE UPTODATE

        //just do a little house cleaning here
        session_unregister('login_password');
        session_unregister('login_error');
        session_unregister('login_user_name');
        session_unregister('ACL');

        //set the server unique key
        if (isset ($sugar_config['unique_key']))$_SESSION['unique_key'] = $sugar_config['unique_key'];

        //set the user theme
        if ($reset_theme_on_default_user && $this->focus->user_name == $sugar_config['default_user_name']) {
            $authenticated_user_theme = $sugar_config['default_theme'];
        } else {
            $authenticated_user_theme = (isset ($_REQUEST['login_theme'])) ? $_REQUEST['login_theme'] : (isset ($_REQUEST['ck_login_theme_20'])) ? $_REQUEST['ck_login_theme_20'] : $sugar_config['default_theme'];
        }
        //set user language
//        if (isset ($reset_language_on_default_user) && $reset_language_on_default_user && $$GLOBALS['current_user']->user_name == $sugar_config['default_user_name']) {
//            $authenticated_user_language = $sugar_config['default_language'];
//        } else {
//            $authenticated_user_language = (isset ($_REQUEST['login_language'])) ? $_REQUEST['login_language'] : (isset ($_REQUEST['ck_login_language_20'])) ? $_REQUEST['ck_login_language_20'] : $sugar_config['default_language'];
//        }

        $_SESSION['authenticated_user_theme'] = 'SUGAR';
        $_SESSION['authenticated_user_language'] = $sugar_config['default_language'];



        return true;
	}

	/**
	 * On every page hit this will be called to ensure a user is authenticated
	 * @return boolean
	 */
	function sessionAuthenticate(){
		global $module, $action, $allowed_actions;
		$authenticated = false;
		$allowed_actions = array ("Authenticate", "Login"); // these are actions where the user/server keys aren't compared
		if (isset ($_SESSION['authenticated_user_id'])) {
			$GLOBALS['log']->debug("We have an authenticated user id: ".$_SESSION["authenticated_user_id"]);
			$authenticated = $this->postSessionAuthenticate();

		} else
		if (isset ($action) && isset ($module) && $action == "Authenticate" && $module == "Users") {
			$GLOBALS['log']->debug("We are authenticating user now");
		} else {
			$GLOBALS['log']->debug("The current user does not have a session.  Going to the login page");
			$action = "Login";
			$module = "Users";
			$_REQUEST['action'] = $action;
			$_REQUEST['module'] = $module;
		}

		if($authenticated){
			$this->validateIP();
		}
		return $authenticated;
	}




	/**
	 * Called after a session is authenticated - if this returns false the sessionAuthenticate will return false and destroy the session
	 * and it will load the  current user
	 * @return boolean
	 */

	function postSessionAuthenticate(){
		global $action, $allowed_actions, $sugar_config;
		$_SESSION['userTime']['last'] = time();
		$user_unique_key = (isset ($_SESSION['unique_key'])) ? $_SESSION['unique_key'] : '';
		$server_unique_key = (isset ($sugar_config['unique_key'])) ? $sugar_config['unique_key'] : '';

		//CHECK IF USER IS CROSSING SITES
		if (($user_unique_key != $server_unique_key) && (!in_array($action, $allowed_actions)) && (!isset ($_SESSION['login_error']))) {

			session_destroy();
			$post_login_nav = '';
			if (!empty ($record) && !empty ($action) && !empty ($module)) {
				$post_login_nav = "&login_module=".$module."&login_action=".$action."&login_record=".$record;
			}
			$GLOBALS['log']->debug('Destroying Session User has crossed Sites');
			header("Location: index.php?action=Login&module=Users".$post_login_nav);
			sugar_cleanup(true);
		}
		if (!$this->userAuthenticate->loadUserOnSession($_SESSION['authenticated_user_id'])) {
			session_destroy();
			header("Location: index.php?action=Login&module=Users");
			$GLOBALS['log']->debug('Current user session does not exist redirecting to login');
			sugar_cleanup(true);
		}
//		$GLOBALS['log']->debug('Current user is: '.$GLOBALS['current_user']->user_name);
		return true;
	}

	/**
	 * Make sure a user isn't stealing sessions so check the ip to ensure that the ip address hasn't dramatically changed
	 *
	 */
	function validateIP() {
		global $sugar_config;
		// grab client ip address
		$clientIP = query_client_ip();
		$classCheck = 0;
		// check to see if config entry is present, if not, verify client ip
		if (!isset ($sugar_config['verify_client_ip']) || $sugar_config['verify_client_ip'] == true) {
			// check to see if we've got a current ip address in $_SESSION
			// and check to see if the session has been hijacked by a foreign ip
			if (isset ($_SESSION["ipaddress"])) {
				$session_parts = explode(".", $_SESSION["ipaddress"]);
				$client_parts = explode(".", $clientIP);
				
			    if(count($session_parts) < 4) {
                    $classCheck = 0;
                } else { 
    				// match class C IP addresses
    				for ($i = 0; $i < 3; $i ++) {
    					if ($session_parts[$i] == $client_parts[$i]) {
    						$classCheck = 1;
    						continue;
    					} else {
    						$classCheck = 0;
    						break;
    					}
    				}
                }

				// we have a different IP address
				if ($_SESSION["ipaddress"] != $clientIP && empty ($classCheck)) {
					$GLOBALS['log']->fatal("IP Address mismatch: SESSION IP: {$_SESSION['ipaddress']} CLIENT IP: {$clientIP}");
					session_destroy();
					die("Your session was terminated due to a significant change in your IP address.  <a href=\"{$sugar_config['site_url']}\">Return to Home</a>");
				}
			} else {
				$_SESSION["ipaddress"] = $clientIP;
			}
		}

	}




	/**
	 * Called when a user requests to logout
	 *
	 */
	function logout(){
            $portal->logout();
			session_destroy();
			ob_clean();
			header('Location:index.php?module=Users&action=Login');
			sugar_cleanup(true);
	}


	/**
	 * Encodes a users password. This is a static function and can be called at any time.
	 *
	 * @param STRING $password
	 * @return STRING $encoded_password
	 */
	function encodePassword($password){
		return strtolower(md5($password));
	}

	/**
	 * If a user may change there password through the Sugar UI
	 *
	 */
	function canChangePassword(){
		return true;
	}
	/**
	 * If a user may change there user name through the Sugar UI
	 *
	 */
	function canChangeUserName(){
		return true;
	}




}