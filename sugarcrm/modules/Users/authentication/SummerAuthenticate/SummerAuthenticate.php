<?php
 if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
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
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

require_once('modules/Users/authentication/SugarAuthenticate/SugarAuthenticate.php');

class SummerAuthenticate extends SugarAuthenticate
{
	var $userAuthenticateClass = 'SummerAuthenticateUser';
	var $authenticationDir = 'SummerAuthenticate';

	public function __construct()
	{
		parent::SugarAuthenticate();
		$this->box = BoxOfficeClient::getInstance();
	}

    public function pre_login()
    {
        parent::pre_login();
        // go straight to authentication
        $token = $this->box->getToken();
        unset($_REQUEST['login_token']);
        SugarApplication::redirect("?module=Users&action=Authenticate&token=$token&".http_build_query($GLOBALS['app']->getLoginVars(false)));
    }

    public function loginAuthenticate()
    {
        $user = $this->box->getCurrentUser();
        if(empty($user)) {
            SugarApplication::redirect($this->box->loginUrl());
        }
        if(parent::loginAuthenticate($user['email'], '', false)) {
            // delete session when done
            // $this->box->deleteSession();
            return true;
        }
        return false;
    }

    public function logout()
    {
        session_destroy();
        ob_clean();
        $this->box->deleteSession();
        SugarApplication::redirect($this->box->loginUrl());
    }
}