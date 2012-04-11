<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');
/********************************************************************************
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
 * This class provides security functions in a centralized location.
 * The intent is to not only allow for a more convenient way to check field/module access
 * it also allows for overriding the SugarSecurity model to allow for reduced/restricted
 * access limitations for portal and permission-based oAuth2 logins.
 */

abstract class SugarSecurity {
	/**
     * This function logs a user in via username and password
     *
	 * @param string $username Username
	 * @param string $password Password
	 * @param string $passwordType How is the password being passed, recognized values are PLAIN and MD5
	 * @return bool Was the login successful
	 */
    abstract function loginUserPass($username, $password, $passwordType = 'PLAIN' );
    /**
     * This function logs a user in via an OAuth2 token.
     * @param string $token OAuth Token
     * @return bool Was the login successful
     */
    abstract function loginOAuth2Token($token);
    /**
     * This function logs a user in via a single sign on token.
     * @param string $token Single Sign On Token
     * @return bool Was the login successful
     */
    abstract function loginSingleSignOnToken($token);
    /**
     * This function loads a user from the current session. This allows for existing Sugar User sessions to use this authentication system.
     * @return bool Was a user successfully loaded from the session
     */
    abstract function loadFromSession();
    /**
     * Can the user access this module
     * @param SugarBean $bean The bean that you want to check the access against
     * @param string $accessType What type of access are you checking, supported types are: view, edit, list, delete, create, import, export
     * @return bool Is the user allowed to perform that action on that bean
     */
    abstract function canAccessModule(SugarBean $bean,$accessType='view');
    /**
     * Can the user access this field in this module
     * @param SugarBean $bean The bean that you want to check the access against
     * @param string $fieldName The name of the field in this module
     * @param string $accessType What type of access are you checking, supported types are: view, edit, list, create, import, export
     * @return bool Is the user allowed to perform that action on that field
     */
    abstract function canAccessField(SugarBean $bean,$fieldName,$accessType);
    /**
     * Does this security model need to add additional security restrictions for this action
     * @param SugarBean $bean The bean that you want to check the security against
     * @param string $action What action are you checking for extra security, supported types are: view, edit, list, delete, create, import, export
     * @return bool Is extra security required for this action on this bean
     */
    abstract function hasExtraSecurity(SugarBean $bean,$action='list');
    /**
     * Is this user a Sugar user, or a Portal/lesser user
     * @return bool Is the user a full SugarCRM user?
     */
    abstract function isSugarUser();
}