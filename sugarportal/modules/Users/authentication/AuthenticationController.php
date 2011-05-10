<?PHP
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
class AuthenticationController {
	var $loggedIn = false; //if a user has attempted to login
	var $authenticated = false;
	var $loginSuccess = false;// if a user has successfully logged in

	/**
	 * Creates an instance of the authentication controller and loads it
	 *
	 * @param STRING $type - the authentication Controller - default to SugarAuthenticate
	 * @return AuthenticationController -
	 */
	function AuthenticationController($type = 'SugarAuthenticate') {
		if(!file_exists('modules/Users/authentication/'.$type.'/' . $type . '.php'))$type = 'SugarAuthenticate';

		require_once ('modules/Users/authentication/'.$type.'/' . $type . '.php');
		$this->authController = new $type();
	}


	/**
	 * Returns an instance of the authentication controller
	 *
	 * @param STRING $type this is the type of authetnication you want to use default is SugarAuthenticate
	 * @return an instance of the authetnciation controller
	 */
	function &getInstance($type='SugarAuthenticate'){
		static $authcontroller;
		if(empty($authcontroller)){
			$authcontroller = new AuthenticationController($type);
		}
		return $authcontroller;
	}

	/**
	 * This function is called when a user initially tries to login.
	 * It will return true if the user successfully logs in or false otherwise.
	 *
	 * @param STRING $username
	 * @param STRING $password
	 * @param ARRAY $PARAMS
	 * @return boolean
	 */
	function login($username, $password, $PARAMS = array ()) {
		$SESSION['loginAttempts'] = (isset($SESSION['loginAttempts']))? $SESSION['loginAttempts'] + 1: 1;
		unset($GLOBALS['login_error']);
		if($this->loggedIn)return $this->loginSuccess;
		$this->loginSuccess = $this->authController->loginAuthenticate($username, $password, $PARAMS);
		$this->loggedIn = true;
		if($this->loginSuccess){
			//Ensure the user is authorized
			checkAuthUserStatus();

			if(!empty($GLOBALS['login_error'])){
				unset($_SESSION['authenticated_user_id']);
				$GLOBALS['log']->fatal('FAILED LOGIN: potential hack attempt');
				return false;
			}


		}else{
			$GLOBALS['log']->fatal('FAILED LOGIN:attempts[' .$SESSION['loginAttempts'] .'] - '. $username);
		}
		return $this->loginSuccess;
	}

	/**
	 * This is called on every page hit.
	 * It returns true if the current session is authenticated or false otherwise
	 * @return booelan
	 */
	function sessionAuthenticate() {

		if(!$this->authenticated){
			$this->authenticated = $this->authController->sessionAuthenticate();
		}
		if($this->authenticated){
			if(!isset($_SESSION['userStats']['pages'])){
			    $_SESSION['userStats']['loginTime'] = time();
			    $_SESSION['userStats']['pages'] = 0;
			}
			$_SESSION['userStats']['lastTime'] = time();
			$_SESSION['userStats']['pages']++;

		}
		return $this->authenticated;
	}

	/**
	 * Called when a user requests to logout. Should invalidate the session and redirect
	 * to the login page.
	 *
	 */
	function logout(){
		$this->authController->logout();
	}


}
?>