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

require_once('data/BeanFactory.php');
require_once('include/SugarFields/SugarFieldHandler.php');
require_once('include/MetaDataManager/MetaDataManager.php');

class CurrentUserApi extends SugarApi {
    public function registerApiRest() {
        return array(
            'retrieve' => array(
                'reqType' => 'GET',
                'path' => array('me',),
                'pathVars' => array(),
                'method' => 'retrieveCurrentUser',
                'shortHelp' => 'Returns current user',
                'longHelp' => 'include/api/help/me.html',
            ),
            'update' => array(
                'reqType' => 'PUT',
                'path' => array('me',),
                'pathVars' => array(),
                'method' => 'updateCurrentUser',
                'shortHelp' => 'Updates current user',
                'longHelp' => 'include/api/help/me.html',
            ),
            'updatePassword' =>  array(
                'reqType' => 'PUT',
                'path' => array('me','password'),
                'pathVars'=> array(''),
                'method' => 'updatePassword',
                'shortHelp' => "Updates current user's password",
                'longHelp' => 'include/api/help/change_password.html',
            ),
            'verifyPassword' =>  array(
                'reqType' => 'POST',
                'path' => array('me','password'),
                'pathVars'=> array(''),
                'method' => 'verifyPassword',
                'shortHelp' => "Verifies current user's password",
                'longHelp' => 'include/api/help/verify_password.html',
            ),
        );
    }

    /**
     * Retrieves the current user info
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function retrieveCurrentUser($api, $args) {

        $current_user = $this->getUserBean();
        
        // Get the basics
        $user_data = $this->getBasicUserInfo();
        
        if ( isset($args['platform']) ) {
            $platform = array(basename($args['platform']),'base');
        } else {
            $platform = array('base');
        }
        // Fill in the rest
        $user_data['type'] = 'user';
        $user_data['id'] = $current_user->id;
        $user_data['full_name'] = $current_user->full_name;
        $user_data['user_name'] = $current_user->user_name;
        $user_data['acl'] = $this->getAcls($platform);
        if(isset($current_user->preferred_language)) {
            $user_data['preferred_language'] = $current_user->preferred_language;
        }
        
        return array('current_user' => $user_data);
    }
    
    /**
     * Updates current user info
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function updateCurrentUser($api, $args) {
        $bean = $this->getUserBean();

        // setting these for the loadBean
        $args['module'] = $bean->module_name;
        $args['record'] = $bean->id;

        $id = $this->updateBean($bean, $api, $args);

        return $this->retrieveCurrentUser($api, $args);
    }

    /**
     * Updates the current user's password
     *
     * @param $api
     * @param $args
     * @return array
     * @throws SugarApiExceptionMissingParameter|SugarApiExceptionNotFound
     */
    public function updatePassword($api, $args) {
        $user_data['valid'] = false;
        
        // Deals with missing required args else assigns oldpass and new paswords
        if (empty($args['old_password']) || empty($args['new_password'])) {
            // @TODO Localize this exception message
            throw new SugarApiExceptionMissingParameter('Error: Missing argument.');
        } else {
            $oldpass = $args['old_password'];
            $newpass = $args['new_password'];
        }
        
        $bean = $this->getUserIfPassword($oldpass);
        if (null !== $bean) {
            $change = $this->changePassword($bean, $oldpass, $newpass);
            if (!$change) {
                $user_data['message'] = 'Error: There was a problem updating password for this user.';
            } else {
                $user_data = array_merge($user_data, $change);
            }
        } else {
            $user_data['message'] = 'Error: Incorrect password.'; 
        }
        
        return $user_data;
    }

    /**
     * Verifies against the current user's password
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function verifyPassword($api, $args) {
        $user_data['valid'] = false;
        
        // Deals with missing required args else assigns oldpass and new paswords
        if (empty($args['password_to_verify'])) {
            // @TODO Localize this exception message
            throw new SugarApiExceptionMissingParameter('Error: Missing argument.');
        }
        
        // If the user password is good, send that messaging back
        if (!is_null($this->getUserIfPassword($args['password_to_verify']))) {
            $user_data['valid'] = true;
            $user_data['message'] = 'Password verified.'; 
            $user_data['expiration'] = $this->getUserLoginExpirationPreference();
        }
        return $user_data;
    }

    protected function getMetadataManager( $platform = 'base', $public = false) {
        $current_user = $this->getUserBean();
        return new MetaDataManager($current_user, $platform, $public);
    }

    /**
     * Gets acls given full module list passed in.
     * @param string The platform e.g. portal, mobile, base, etc.
     * @return array
     */  
    public function getAcls($platform) {
        $mm = $this->getMetadataManager($platform);
        $current_user = $this->getUserBean();
        $fullModuleList = array_keys($GLOBALS['app_list_strings']['moduleList']);
        $acls = array();
        foreach ($fullModuleList as $modName) {
            $bean = BeanFactory::newBean($modName);
            if (!$bean || !is_a($bean,'SugarBean') ) {
                // There is no bean, we can't get data on this
                continue;
            }


            $acls[$modName] = $mm->getAclForModule($modName,$current_user->id);
            $acls[$modName] = $this->verifyACLs($acls[$modName]);
        }
        // Handle enforcement of acls for clients that override this (e.g. portal)
        $acls = $this->enforceModuleACLs($acls);

        return $acls;
    }

    /**
     * Manipulates the ACLs as needed, per client
     * 
     * @param array $acls
     * @return array
     */
    protected function verifyACLs(Array $acls) {
        // No manipulation for base acls
        return $acls;
    }

    /**
     * Enforces module specific ACLs for users without accounts, as needed
     * 
     * @param array $acls
     * @return array
     */
    protected function enforceModuleACLs(Array $acls) {
        // No manipulation for base acls
        return $acls;
    }

    /**
     * Checks a given password and sends back the user bean if the password matches
     * 
     * @param string $passwordToVerify
     * @return User
     */
    protected function getUserIfPassword($passwordToVerify) {
        $user = BeanFactory::getBean('Users', $GLOBALS['current_user']->id); 
        $currentPassword = $user->user_hash;
        if (User::checkPassword($passwordToVerify, $currentPassword)) {
            return $user;
        }
        
        return null;
    }

    /**
     * Gets the basic user data that all users that are logged in will need. Client
     * specific user information will be filled in within the client API class.
     * 
     * @return array
     */
    protected function getBasicUserInfo() {
        global $current_user;
        global $locale;
        
        $user_data = array(
            'timezone' => $current_user->getPreference('timezone'),
            'datepref' => $current_user->getPreference('datef'),
            'timepref' => $current_user->getPreference('timef'),
        );

        // user currency prefs
        $currency = BeanFactory::getBean('Currencies');
        $currency_id = $current_user->getPreference('currency');
        $currency->retrieve($currency_id);
        $user_data['currency_id'] = $currency->id;
        $user_data['currency_name'] = $currency->name;
        $user_data['currency_symbol'] = $currency->symbol;
        $user_data['currency_iso'] = $currency->iso4217;
        $user_data['currency_rate'] = $currency->conversion_rate;
        // user number formatting prefs
        $user_data['decimal_precision'] = $locale->getPrecision();
        $user_data['decimal_separator'] = $locale->getDecimalSeparator();
        $user_data['number_grouping_separator'] = $locale->getNumberGroupingSeparator();

        return $user_data;
    }

    /**
     * Gets the user bean for the user of the api
     * 
     * @return User
     */
    protected function getUserBean() {
        global $current_user;
        return $current_user;
    }

    /**
     * Changes a password for a user from old to new
     * 
     * @param User $bean User bean
     * @param string $old Old password 
     * @param string $new New password
     * @return array
     */
    protected function changePassword($bean, $old, $new) {
        if ($bean->change_password($old, $new)) {
            return array(
                'valid' => true,
                'message' => 'Password updated.',
                'expiration' => $bean->getPreference('loginexpiration'),
            );
        }
        
        return array();
    }
    
    /**
     * Gets the preference for user login expiration
     * 
     * @return string
     */
    protected function getUserLoginExpirationPreference() {
        global $current_user;
        return $current_user->getPreference('loginexpiration');
    }



}
