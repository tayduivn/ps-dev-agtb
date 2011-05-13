<?php
if(!defined('sugarEntry'))define('sugarEntry', true);
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
 *
 ********************************************************************************/

/**
 * This class is an implemenatation class for all the rest services
 */
require_once('service/v4/SugarWebServiceImplv4.php');
require_once('SugarWebServiceUtilv5.php');


class SugarWebServiceImplv5 extends SugarWebServiceImplv4 {

    public function __construct()
    {
        self::$helperObject = new SugarWebServiceUtilv5();
    }


    public function oauth_request_token()
    {
        require_once "include/SugarOAuthServer.php";
        try {
	        $oauth = new SugarOAuthServer($GLOBALS['sugar_config']['site_url'].'service/v5/rest.php');
	        return $oauth->requestToken()."&oauth_callback_confirmed=true&authorize_url=".$oauth->authURL();
        } catch(OAuthException $e) {
            $GLOBALS['log']->debug("OAUTH Exception: $e");
            $errorObject = new SoapError();
            $errorObject->set_error('invalid_login');
			self::$helperObject->setFaultObject($errorObject);
            return null;
        }
    }

    public function oauth_access_token()
    {
        require_once "include/SugarOAuthServer.php";
        try {
	        $oauth = new SugarOAuthServer();
	        return $oauth->accessToken();
        } catch(OAuthException $e) {
            $GLOBALS['log']->debug("OAUTH Exception: $e");
            $errorObject = new SoapError();
            $errorObject->set_error('invalid_login');
			self::$helperObject->setFaultObject($errorObject);
            return null;
        }
    }

    public function oauth_access($session)
    {
    	$error = new SoapError();
    	$output_list = array();
    	if (!self::$helperObject->checkSessionAndModuleAccess($session, 'invalid_session', '', '', '', $error)) {
    		$error->set_error('invalid_login');
    		$GLOBALS['log']->info('End: SugarWebServiceImpl->oauth_access');
    		return $error;
    	}
    	global $current_user;
        return array('id'=>session_id());
    }

}

SugarWebServiceImplv5::$helperObject = new SugarWebServiceUtilv5();
