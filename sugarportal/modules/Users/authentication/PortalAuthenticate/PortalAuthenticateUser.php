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
 * This file is where the user authentication occurs. No redirection should happen in this file.
 *
 */

require_once("include/nusoap/nusoap.php");

class PortalAuthenticateUser{
	/**
	 * this is called when a user logs in
	 *
	 * @param STRING $name
	 * @param STRING $password
	 * @return boolean
	 */
	function loadUserOnLogin($name, $password, $PARAMS) {
		global $login_error, $portal;

		$GLOBALS['log']->debug("Starting user load for ". $name);

        if(empty($name) || empty($password)) return false;

		$login_result = $portal->login($name, $password, $PARAMS['contactUsername'], $PARAMS['contactPassword']);
        $user_id = $login_result['id'];

        if($user_id == -1) {
            if($login_result['error']['number'] == '60')
                $_SESSION['login_error'] = $GLOBALS['app_strings']['LBL_LOGIN_SESSION_EXCEEDED'];
            return false;
        }
        $result = $portal->getCurrentUserID();
        $_SESSION['current_user_id'] = $result['id'];
        $_SESSION['contact_user_name'] = $PARAMS['contactUsername'];
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
            global $portal, $sugar_config;

            $portal->soapClient = new nusoapclient($sugar_config['parent_site_url'] . '/soap.php?wsdl', true);
            $portal->soapSession = $_SESSION['authenticated_user_id'];
            $portal->soapClientProxy = $portal->soapClient->getProxy();

            return true;
        }
        return false;

    }
}

?>