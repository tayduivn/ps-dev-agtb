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

require_once('modules/Users/authentication/SugarAuthenticate/SugarAuthenticateUser.php');
class SummerAuthenticateUser extends SugarAuthenticateUser
{
    public function __construct()
    {
        $this->box = BoxOfficeClient::getInstance();
    }

	public function authenticateUser($username, $password)
	{
	    $user = $this->box->getCurrentUser();
	    if(empty($user)) return '';

	    $usr = new User();
		$usr_id = $usr->retrieve_user_id($username);
		if(empty($usr_id)) {
		    $usr_id = $this->createUser($user);
		}
		$usr->retrieve($usr_id);

		return $usr->id;
	}

	protected function createUser($userData)
	{
	    $user = new User();
	    $user->user_name = $userData['email'];
	    $user->email = $userData['email'];
	    $user->email1 = $userData['email'];
	    $user->first_name = $userData['first_name'];
	    $user->last_name = $userData['last_name'];
	    $user->status = 'Active';
	    $user->is_admin = 0;
	    $user->external_auth_only = 1;
	    $user->system_generated_password = 0;
	    $user->authenticate_id = $userData['remote_id'];
        $user->receive_notifications = 0;
	    if(!empty($userData['photo'])) {
	        $picid = create_guid();
	        if(copy($userData['photo'], "upload://$picid")) {
	            $user->picture = $picid;
	        }
	    }
        $user->id = 'rmt-'.md5($userData['remote_id']);
        $user->new_with_id = true;
        $user->save();
        $user->setPreference('ut', 1);
        $user->savePreferencesToDB();
        return $user->id;
	}

	/**
     * This is called when a user logs in
     *
     * @param string $name
     * @param string $password
     * @param boolean $fallback - is this authentication a fallback from a failed authentication
     * @param array $PARAMS
     * @return boolean
     */
    public function loadUserOnLogin($name, $password, $fallback = false, $PARAMS = array())
    {
        // provide dummy login and password to parent class so that authentication
        // process could go on
        return parent::loadUserOnLogin($name, 'summer', $fallback, $PARAMS);
    }
}