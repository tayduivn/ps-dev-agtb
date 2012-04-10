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
require_once('include/SugarSecurity/SugarSecurity.php');
//BEGIN SUGARCRM flav=pro ONLY
require_once('modules/ACLFields/ACLField.php');
//END SUGARCRM flav=pro ONLY

class SugarSecurityUser extends SugarSecurity {
    function loginUserPass($username, $password, $passwordType = 'PLAIN', $options = null ) {
        global $sugar_config, $system_config;

        if ( $passwordType == 'PLAIN' ) {
            $passwordEncrypted = false;
        } else {
            $passwordEncrypted = true;
        }

        if ( isset($options['language']) ) {
            $language = $options['language'];
        } else {
            $language = $sugar_config['default_language'];
        }
        if ( !isset($sugar_config['languages'][$language]) ) {
            // The requested language is not on the list of allowed languages, pick the first one off of the list
            list($language) = array_keys($sugar_config['languages']);
        }
        

		$system_config = new Administration();
        $system_config->retrieveSettings('system');
        $authController = new AuthenticationController((!empty($sugar_config['authenticationClass'])? $sugar_config['authenticationClass'] : 'SugarAuthenticate'));
        $isLoginSuccess = $authController->login($username, $password, array('passwordEncrypted' => $passwordEncrypted));
        if ( $isLoginSuccess ) {
            $user = new User();
            $this->userId=$user->retrieve_user_id($username);
            if($this->userId) {
                $user->retrieve($this->userId);
                
                if ( isset($user->id) 
                     && !empty($user->user_name)
                     && !$user->is_group ) {
                    // Everything checks out, let's set this up properly
                    session_start();
                    $GLOBALS['current_user'] = $user;
                    $_SESSION['is_valid_session'] = true;
                    $_SESSION['ip_address'] = query_client_ip();
                    $_SESSION['user_id'] = $user->id;
                    $_SESSION['type'] = 'user';
                    $_SESSION['authenticated_user_id'] = $user->id;
                    $_SESSION['authenticated_user_language'] = $language;
                    $GLOBALS['current_language'] = $language;
                    $_SESSION['unique_key'] = $sugar_config['unique_key'];
                    
                    $_SESSION['sugarSec'] = array('type'=>'User',
                                                  'userId'=>$this->userId);

                    $GLOBALS['current_user']->call_custom_logic('after_login');
                    
                    $this->sessionId = session_id();
                    
                    return true;
                }
            }
        }

        // Something didn't quite work
        return false;
    }
    function loginOAuth2Token($token) {
        // Not yet implemented
    }
    function loginSingleSignOnToken($token) {
        // Not yet implemented
    }
    function loadFromSession() {
        
        if ( !isset($_SESSION['authenticated_user_id']) ) {
            // No authenticated user id, probably not logged in
            return false;
        }

        $this->sessionId = session_id();
        $this->userId = $_SESSION['authenticated_user_id'];
        $user = new User();
        $user->retrieve($this->userId);
        $GLOBALS['current_user'] = $user;
        $GLOBALS['current_language'] = $_SESSION['authenticated_user_language'];

        if ( !isset($_SESSION['sugarSec'])) {
            // Probably someone hitting the API while being logged in to the normal site.
            $_SESSION['sugarSec'] = array('type'=>'User',
                                          'userId' =>$this->userId);
        }

        //BEGIN SUGARCRM flav=pro ONLY
        SugarApplication::trackLogin();
        //END SUGARCRM flav=pro ONLY

        LogicHook::initialize()->call_custom_logic('', 'after_session_start');
        
        
        return true;
                    
    }
    function canAccessModule($bean,$accessType='view') {
        return $bean->ACLAccess($accessType);
        
    }
    //BEGIN SUGARCRM flav=pro ONLY
    function canAccessField($bean,$fieldName,$accessType) {
        $isOwner = false;
        if ( empty($bean->id) || $bean->new_with_id ) {
            // It's new
            $isOwner = true;
        } else if ( isset($bean->assigned_user_id) && $this->userId == $bean->assigned_user_id ) {
            // They are set as the assigned user id.
            $isOwner = true;
        }
        $fieldLevel = ACLField::hasAccess($fieldName,$bean->module_dir,$this->userId,$isOwner);
        
        if ( ( $accessType == 'view' || $accessType == 'list' ) && $fieldLevel >= 1) {
            // Do they have read-level access?
            return true;
        } else if ( ( $accessType == 'edit' || $accessType == 'create' ) && $fieldLevel >= 2 ) {
            // Do they have write-level access
            return true;
        }
    }
    //END SUGARCRM flav=pro ONLY
    function hasExtraSecurity($bean,$action='list') {
        // This is used to add extra security for limited access users (such as portal users)
        return false;
        
    }
    function isSugarUser() {
        return true;
    }
}