<?php
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
require_once('service/v4/SugarWebServiceUtilv4.php');

class SugarWebServiceUtilv5 extends SugarWebServiceUtilv4
{
  function checkSessionAndModuleAccess($session, $login_error_key, $module_name, $access_level, $module_access_level_error_key, $errorObject)
  {
      if(isset($_REQUEST['oauth_token'])) {
          $session = $this->checkOAuthAccess($errorObject);
      }
      if(!$session) return false;
      return parent::checkSessionAndModuleAccess($session, $login_error_key, $module_name, $access_level, $module_access_level_error_key, $errorObject);
  }

  public function checkOAuthAccess($errorObject)
  {
        require_once "include/SugarOAuthServer.php";
        try {
	        $oauth = new SugarOAuthServer();
	        $token = $oauth->authorizedToken();
	        if(empty($token) || empty($token->assigned_user_id)) {
	            return false;
	        }
        } catch(OAuthException $e) {
            $GLOBALS['log']->debug("OAUTH Exception: $e");
            $errorObject->set_error('invalid_login');
			$this->setFaultObject($errorObject);
            return false;
        }

	    $user = new User();
	    $user->retrieve($token->assigned_user_id);
	    if(empty($user->id)) {
	        return false;
	    }
        global $current_user;
		$current_user = $user;
		ini_set("session.use_cookies", 0); // disable cookies to prevent session ID from going out
		session_start();
		session_regenerate_id();
		$_SESSION['oauth'] = $oauth->authorization();
		$_SESSION['avail_modules'] = $this->get_user_module_list($user);
		// TODO: handle role
		// handle session
		$_SESSION['is_valid_session']= true;
		$_SESSION['ip_address'] = query_client_ip();
		$_SESSION['user_id'] = $current_user->id;
		$_SESSION['type'] = 'user';
		$_SESSION['authenticated_user_id'] = $current_user->id;
        return session_id();
  }
}