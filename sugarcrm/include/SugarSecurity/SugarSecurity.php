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
    abstract function loginUserPass($username, $password, $passwordType = 'PLAIN' );
    abstract function loginOAuth2Token($token);
    abstract function loginSingleSignOnToken($token);
    abstract function loadFromSession();
    abstract function canAccessModule($bean,$accessType='view');
    abstract function canAccessField($bean,$fieldName,$accessType);
    abstract function hasExtraSecurity($bean,$action='list');
    abstract function isSugarUser();
}