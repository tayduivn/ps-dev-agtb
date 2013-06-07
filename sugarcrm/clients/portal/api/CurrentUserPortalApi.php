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

require_once 'clients/base/api/CurrentUserApi.php';

class CurrentUserPortalApi extends CurrentUserApi {
    /**
     * Retrieves the current portal user info
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function retrieveCurrentUser($api, $args) {
        global $current_user;

        // Get the basics
        $user_data = $this->getBasicUserInfo();
        // Fill in the portal specific stuff
        $contact = $this->getPortalContact();
        $user_data['type'] = 'support_portal';
        $user_data['user_id'] = $current_user->id;
        $user_data['user_name'] = $current_user->user_name;
        $user_data['acl'] = $this->getAcls('portal');
        $user_data['id'] = $_SESSION['contact_id'];
        
        // We need to ask the visibility system for the list of account ids
        $visibility = new SupportPortalVisibility($contact);
        $user_data['account_ids'] = $visibility->getAccountIds();

        $user_data['full_name'] = $contact->full_name;
        $user_data['picture'] = $current_user->picture;
        $user_data['portal_name'] = $contact->portal_name;
        if(isset($contact->preferred_language)) {
            $user_data['preferences']['language'] = $contact->preferred_language;
        }
        
        return array('current_user'=>$user_data);
    }

    /**
     * Updates current portal users info
     *
     * @param $api
     * @param $args
     * @return array
     */
    public function updateCurrentUser($api, $args) {
        $bean = $this->getPortalContact();
        // setting these for the loadBean
        $args['module'] = $bean->module_name;
        $args['record'] = $bean->id;

        $id = $this->updateBean($bean, $api, $args);

        return $this->retrieveCurrentUser($api, $args);
    }

    /**
     * Gets the current portal user's Contact bean.
     * When working with Portal this contains the interesting user info
     *
     * @return Contact
     */
    protected function getPortalContact(){
        if(!isset($this->portal_contact)){
            $this->portal_contact = BeanFactory::getBean('Contacts', $_SESSION['contact_id']);
        }
        return $this->portal_contact;
    }

    /**
     * Checks a given password and sends back the contact bean if the password matches
     * 
     * @param string $passwordToVerify
     * @return Contact
     */
    protected function getUserIfPassword($passwordToVerify) {
        $contact = $this->getPortalContact();
        $currentPassword = $contact->portal_password;
        if (User::checkPassword($passwordToVerify, $currentPassword)) {
            return $contact;
        }
        
        return null;
    }

    /**
     * Changes a portal password for a contact from old to new
     * 
     * @param Contact $bean Contact bean
     * @param string $old Old password 
     * @param string $new New password
     * @return array
     */
    protected function changePassword($bean, $old, $new) {
        $bean->portal_password = User::getPasswordHash($new);
        $bean->save();
        return array(
            'valid' => true,
            'message' => 'Password updated.',
            'expiration' => null,
        );
    }

    /**
     * Gets the preference for user login expiration
     * 
     * @return null
     */
    protected function getUserLoginExpirationPreference() {
        return null;
    }


    /**
     * Manipulates the ACLs for portal
     * 
     * @param array $acls
     * @return array
     */
    protected function verifyACLs(Array $acls) {
        $acls['admin'] = 'no';
        $acls['developer'] = 'no';
        $acls['delete'] = 'no';
        $acls['import'] = 'no';
        $acls['export'] = 'no';
        $acls['massupdate'] = 'no';
        
        return $acls;
    }

    /**
     * Enforces module specific ACLs for users without accounts
     * 
     * @param array $acls
     * @return array
     */
    protected function enforceModuleACLs(Array $acls) {
        $apiPerson = $this->getPortalContact();
        // This is a change in the ACL's for users without Accounts
        $vis = new SupportPortalVisibility($apiPerson);
        $accounts = $vis->getAccountIds();
        if (count($accounts)==0) {
            // This user has no accounts, modify their ACL's so that they match up with enforcement
            $acls['Accounts']['access'] = 'no';
            $acls['Cases']['access'] = 'no';
        }
        foreach ($acls as $modName => $modAcls) {
            if ($modName === 'Contacts') continue;
            $acls[$modName]['edit'] = 'no';
        }
        
        return $acls;
    }

    public function getModuleList() {
        // Use SugarPortalBrowser to get the portal modules that would appear
        // in Studio
        require_once 'modules/ModuleBuilder/Module/SugarPortalBrowser.php';
        $pb = new SugarPortalBrowser();
        $pb->loadModules();
        $moduleList = $this->filterDisplayModules($pb->modules);
        array_unshift($moduleList, 'Home');
        return $moduleList;
    }
}
