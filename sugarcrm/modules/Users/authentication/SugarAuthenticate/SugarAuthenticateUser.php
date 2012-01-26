<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/**
 * LICENSE: The contents of this file are subject to the SugarCRM Professional
 * End User License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You
 * may not use this file except in compliance with the License.  Under the
 * terms of the license, You shall not, among other things: 1) sublicense,
 * resell, rent, lease, redistribute, assign or otherwise transfer Your
 * rights to the Software, and 2) use the Software for timesharing or service
 * bureau purposes such as hosting the Software for commercial gain and/or for
 * the benefit of a third party.  Use of the Software may be subject to
 * applicable fees and any use of the Software without first paying applicable
 * fees is strictly prohibited.  You do not have the right to remove SugarCRM
 * copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2006 SugarCRM, Inc.; All Rights Reserved.
 */

 // $Id: SugarAuthenticateUser.php 54098 2010-01-28 18:15:47Z jmertic $

/**
 * This file is where the user authentication occurs. No redirection should happen in this file.
 *
 */
class SugarAuthenticateUser{

	/**
	 * Does the actual authentication of the user and returns an id that will be used
	 * to load the current user (loadUserOnSession)
	 *
	 * @param STRING $name
	 * @param STRING $password
	 * @param STRING $fallback - is this authentication a fallback from a failed authentication
	 * @return STRING id - used for loading the user
	 */
	function authenticateUser($name, $password, $fallback=false)
	{
	    $row = User::findUserPassword($name, $password, "(portal_only IS NULL OR portal_only !='1') AND (is_group IS NULL OR is_group !='1') AND status !='Inactive'");
    
	    // set the ID in the seed user.  This can be used for retrieving the full user record later
		//if it's falling back on Sugar Authentication after the login failed on an external authentication return empty if the user has external_auth_disabled for them
		if (empty ($row) || !empty($row['external_auth_only'])) {
			return '';
		} else {
			return $row['id'];
		}
	}
	/**
	 * Checks if a user is a sugarLogin user
	 * which implies they should use the sugar authentication to login
	 *
	 * @param STRING $name
	 * @param STRIUNG $password
	 * @return boolean
	 */
	function isSugarLogin($name, $password)
	{
	    $row = User::findUserPassword($name, $password, "(portal_only IS NULL OR portal_only !='1') AND (is_group IS NULL OR is_group !='1') AND status !='Inactive' AND sugar_login=1");
	    return !empty($row);
	}

	/**
	 * this is called when a user logs in
	 *
	 * @param STRING $name
	 * @param STRING $password
	 * @param STRING $fallback - is this authentication a fallback from a failed authentication
	 * @return boolean
	 */
	function loadUserOnLogin($name, $password, $fallback = false, $PARAMS = array()) {
		global $login_error;

		$GLOBALS['log']->debug("Starting user load for ". $name);
		if(empty($name) || empty($password)) return false;
		$input_hash = $password;
		$passwordEncrypted = false;
		if (!empty($PARAMS) && isset($PARAMS['passwordEncrypted']) && $PARAMS['passwordEncrypted']) {
			$passwordEncrypted = true;
		}// if
		if (!$passwordEncrypted) {
			$input_hash = SugarAuthenticate::encodePassword($password);
		} // if
		$user_id = $this->authenticateUser($name, $input_hash, $fallback);
		if(empty($user_id)) {
			$GLOBALS['log']->fatal('SECURITY: User authentication for '.$name.' failed');
			return false;
		}
		$this->loadUserOnSession($user_id);
		return true;
	}
	/**
	 * Loads the current user bassed on the given user_id
	 *
	 * @param STRING $user_id
	 * @return boolean
	 */
	function loadUserOnSession($user_id=''){
		if(!empty($user_id)){
			$_SESSION['authenticated_user_id'] = $user_id;
		}

		if(!empty($_SESSION['authenticated_user_id']) || !empty($user_id)){
			$GLOBALS['current_user'] = new User();
			if($GLOBALS['current_user']->retrieve($_SESSION['authenticated_user_id'])){

				return true;
			}
		}
		return false;

	}

}

?>
