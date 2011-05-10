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

 // $Id: LDAPAuthenticateUser.php 47028 2009-05-11 21:50:51Z majed $

/**
 * This file is where the user authentication occurs. No redirection should happen in this file.
 *
 */
require_once('modules/Users/authentication/SugarAuthenticate/SugarAuthenticateUser.php');


class SAMLAuthenticateUser extends SugarAuthenticateUser{

	/**
	 * Does the actual authentication of the user and returns an id that will be used
	 * to load the current user (loadUserOnSession)
	 *
	 * @param STRING $name
	 * @param STRING $password
	 * @return STRING id - used for loading the user
	 *
	 * Contributions by Erik Mitchell erikm@logicpd.com
	 */
	function authenticateUser($name, $password) {
		if(empty($_POST['SAMLResponse']))return parent::authenticateUser($name, $password);
		
		require 'modules/Users/authentication/SAMLAuthenticate/settings.php';
		require 'modules/Users/authentication/SAMLAuthenticate/lib/onelogin/saml.php';
		$samlresponse = new SamlResponse($_POST['SAMLResponse']);
 		$samlresponse->user_settings = get_user_settings();
  		if ($samlresponse->is_valid()){
  			$dbresult = $GLOBALS['db']->query("SELECT id, status FROM users WHERE user_name='" . $samlresponse->get_nameid() . "' AND deleted = 0");

			//user already exists use this one
			if($row = $GLOBALS['db']->fetchByAssoc($dbresult)){
				if($row['status'] != 'Inactive')
					return $row['id'];
				else
					return '';
			}else{
				return $this->createUser($samlresponse->get_nameid());
			}
  		}	
  		return '';
	}
	
	
	
	
		
	

	/**
	 * Creates a user with the given User Name and returns the id of that new user
	 * populates the user with what was set in ldapUserInfo
	 *
	 * @param STRING $name
	 * @return STRING $id
	 */
	function createUser($name){

			$user = new User();
			$user->user_name = $name;
			$user->email1 = $name;
			$user->last_name = $name;
			$user->employee_status = 'Active';
			$user->status = 'Active';
			$user->is_admin = 0;
			$user->external_auth_only = 1;
			$user->system_generated_password = 0;
			$user->save();
			return $user->id;

	}
	

	







}

?>
