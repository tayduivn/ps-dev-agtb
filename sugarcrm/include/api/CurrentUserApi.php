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

        if ( isset($_SESSION['type']) && $_SESSION['type'] == 'support_portal' ) {
            $contact = BeanFactory::getBean('Contacts',$_SESSION['contact_id']);
            $user_data['type'] = 'support_portal';
            $user_data['user_id'] = $current_user->id;
            $user_data['user_name'] = $current_user->user_name;
            $user_data['id'] = $_SESSION['contact_id'];
            
            // We need to ask the visibility system for the list of account ids
            $visibility = new SupportPortalVisibility($contact);
            $user_data['account_ids'] = $visibility->getAccountIds();

            $user_data['full_name'] = $contact->full_name;
            $user_data['portal_name'] = $contact->portal_name;
            if(isset($contact->preferred_language))
            {
                $user_data['preferred_language'] = $contact->preferred_language;
            }
        } else {
            $user_data['type'] = 'user';
            $user_data['id'] = $current_user->id;
            $user_data['full_name'] = $current_user->full_name;
            $user_data['user_name'] = $current_user->user_name;
            $user_data['picture'] = $current_user->picture;
            if(isset($current_user->preferred_language))
            {
                $user_data['preferred_language'] = $current_user->preferred_language;
            }
        }
        if(class_exists('BoxOfficeClient')) {
            $box = BoxOfficeClient::getInstance();
            $inst = $box->getCurrentInstance();
            $user_data['instance_name'] = $inst['name'];
            $user_data['instance_id'] = $inst['id'];
        }

        return $data = array('current_user'=>$user_data);
    }
    /**
     * Updates current user info
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function updateCurrentUser($api, $args) {
        global $current_user;

        if ( isset($_SESSION['type']) && $_SESSION['type'] == 'support_portal' ) {
            $bean = BeanFactory::getBean('Contacts',$_SESSION['contact_id']);
        } else {
            $bean = $current_user;
        }

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

        global $current_user;
        $user_data['valid'] = false;
        
        // Deals with missing required args else assigns oldpass and new paswords
        if (empty($args['old_password']) || empty($args['new_password'])) {
            // @TODO Localize this exception message
            throw new SugarApiExceptionMissingParameter('Error: Missing argument.');
        } else {
            $oldpass = $args['old_password'];
            $newpass = $args['new_password'];
        }

        if ( isset($_SESSION['type']) && $_SESSION['type'] == 'support_portal' ) {

            // Update password for portal user. They must have entered correct "old password"
            if (null !== ($contact = $this->getPortalUserIfPassword($oldpass))) {
                $contact->portal_password = User::getPasswordHash($newpass);
                $contact->save();
                $user_data['valid'] = true;
                $user_data['message'] = 'Password updated.'; 
                $user_data['expiration'] = null;
            } else {
                $user_data['message'] = 'Error: Incorrect password.'; 
            }
        } else {
            // Update password for normal non support_portal user
            if (null !== ($user = $this->getUserIfPassword($oldpass))) {
                if($user->change_password($oldpass, $newpass)) {
                    $user_data['valid'] = true;
                    $user_data['message'] = 'Password updated.'; 
                    $user_data['expiration'] = $user->getPreference('loginexpiration');
                } else {
                    $user_data['message'] = 'Error: There was a problem updating password for this user.';
                }
            } else {
                $user_data['message'] = 'Error: Incorrect password.'; 
            }
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

        global $current_user;
        $user_data['valid'] = false;
        
        // Deals with missing required args else assigns oldpass and new paswords
        if (empty($args['password_to_verify'])) {
            // @TODO Localize this exception message
            throw new SugarApiExceptionMissingParameter('Error: Missing argument.');
        } else {
            if ( isset($_SESSION['type']) && $_SESSION['type'] == 'support_portal' ) {
                if(!is_null($this->getPortalUserIfPassword($args['password_to_verify']))) {
                    $user_data['valid'] = true;
                    $user_data['message'] = 'Password verified.'; 
                    $user_data['expiration'] = null;
                } 
            } else {
                if(!is_null($this->getUserIfPassword($args['password_to_verify']))) {
                    $user_data['valid'] = true;
                    $user_data['message'] = 'Password verified.'; 
                    $user_data['expiration'] = $current_user->getPreference('loginexpiration');
                } 
            }
        }
        return $user_data;
    }

    private function getPortalUserIfPassword($passwordToVerify) {
        $contact = BeanFactory::getBean('Contacts',$_SESSION['contact_id']);
        $currentPassword = $contact->portal_password;
        if (User::checkPassword($passwordToVerify, $currentPassword)) {
            return $contact;
        }
        return null;
    }

    private function getUserIfPassword($passwordToVerify) {
        $user = BeanFactory::getBean('Users', $GLOBALS['current_user']->id); 
        $currentPassword = $user->user_hash;
        if (User::checkPassword($passwordToVerify, $currentPassword)) {
            return $user;
        }
        return null;
    }
}
