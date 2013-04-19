<?php
/*********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
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

require_once 'include/SugarOAuth2/SugarOAuth2StoragePlatform.php';

class SugarOAuth2StoragePortal extends SugarOAuth2StoragePlatform {
    /**
     * The user type for this client
     * 
     * @var string
     */
    protected $userType = 'support_portal';

    /**
     * The client type that this client is associated with
     * 
     * @var string
     */
    protected $clientType = 'support_portal';
    
    /**
     * The Portal API user used as a stand-in user for all portal logins
     * 
     * @var User
     */
    protected $portalApiUser;
    
    /**
     * The Contact record for the portal login
     * 
     * @var Contact
     */
    protected $contactBean;

    /**
     * Gets a user bean. Also sets the contact id for this portal user.
     * 
     * @return User
     */
    public function getUserBean($user_id) {
        $userBean = $this->findPortalApiUser();
        if ( $userBean == null ) {
            return false;
        }
        
        if ( isset($this->contactBean) && $this->contactBean->id == $user_id ) {
            if (!isset($this->userBean)) {
                $this->userBean = $userBean;
            }

            return $userBean;
        } else {
            $contactBean = BeanFactory::newBean('Contacts');
            //BEGIN SUGARCRM flav=pro ONLY
            // Need to disable the row-level security because this user probably doesn't have access to much of anything
            $contactBean->disable_row_level_security = true;
            //END SUGARCRM flav=pro ONLY
            $contactBean->retrieve($user_id);
            if ( empty($contactBean->id) ) {
                return false;
            }
            
            $this->contactBean = $contactBean;
            if (!isset($this->userBean)) {
                $this->userBean = $userBean;
            }
        }
        
        return $userBean;
    }

    /**
     * Small validator for child classes to use to determine whether a session can
     * be written to
     */
    public function canStartSession() {
        return !empty($this->contactBean) && !empty($this->userBean);
    }
    
    /**
     * Fills in any added session data needed by this client type
     */
    public function fillInAddedSessionData() {
        if ($this->canStartSession()) {
            $_SESSION['type'] = $this->userType;
            $_SESSION['contact_id'] = $this->contactBean->id;
            $_SESSION['portal_user_id'] = $this->userBean->id;
            //BEGIN SUGARCRM flav=pro ONLY
            // This is to make sure the licensing is handled correctly for portal logins
            $sessionManager = new SessionManager();
            $sessionManager->session_type = 'contact';
            $sessionManager->last_request_time = TimeDate::getInstance()->nowDb();
            $sessionManager->session_id = session_id();
            $sessionManager->save();
            //END SUGARCRM flav=pro ONLY
        }
    }

    /**
     * Gets the authentication bean for a given client
     * @param OAuthToken
     * @return mixed
     */
    public function getAuthBean(OAuthToken $token) {
        $portalApiUser = $this->findPortalApiUser($token->consumer_obj->c_key);
        if ( $portalApiUser == null ) {
            return false;
        }
        $contact = BeanFactory::newBean('Contacts');
        //BEGIN SUGARCRM flav=pro ONLY
        $contact->disable_row_level_security = true;
        //END SUGARCRM flav=pro ONLY
        $authBean = $contact->retrieve($token->contact_id);
        if ( $authBean->portal_active != 1 ) {
            $authBean = null;
        } else if ( empty($authBean->portal_name) ) {
            $authBean = null;
        }
        
        return $authBean;
    }
    
    /**
     * Gets contact and user ids for a user id. Most commonly different for clients
     * like portal
     * 
     * @param string $client_id The client id for this check
     * @return array An array of contact_id and user_id
     */
    public function getIdsForUser($user_id, $client_id) {
        $return = array('contact_id' => '', 'user_id' => '');
        $portalApiUser = $this->findPortalApiUser($client_id);
        if ( $portalApiUser == null ) {
            return $return;
        }
        
        $return['contact_id'] = $user_id;
        $return['user_id'] = $portalApiUser->id;
        
        return $return;
    }
    
    /**
     * Sets up necessary visibility for a client. Not all clients will set this
     * 
     * @return void
     */
    public function setupVisibility() {
        // Add the necessary visibility and acl classes to the default bean list
        require_once('modules/ACL/SugarACLSupportPortal.php');
        $default_acls = SugarBean::getDefaultACL();
        // This one overrides the Static ACL's, so disable that
        unset($default_acls['SugarACLStatic']);
        $default_acls['SugarACLStatic'] = false;
        $default_acls['SugarACLSupportPortal'] = true;
        SugarBean::setDefaultACL($default_acls);
        SugarACL::resetACLs();

        $default_visibility = SugarBean::getDefaultVisibility();
        $default_visibility['SupportPortalVisibility'] = true;
        SugarBean::setDefaultVisibility($default_visibility);
        $GLOBALS['log']->debug("Added SupportPortalVisibility to session.");
    }
    
    /**
     * This method locates the portal API user for the specified client_id
     * Currently there is no way to associate a specific user with a specific client_id, so that parameter is ignored for now
     * @param $client_id string The client identifier of the portal account, should be used to identifiy different portal types
     * @return User Returs the user bean of the portal user that it found.
     */
    protected function findPortalApiUser() 
    {
        if (isset($this->portalApiUser)) {
            return $this->portalApiUser;
        }

        // Find the Portal API user
        $admin = new Administration();
        $admin->retrieveSettings(false, true);
        if (isset($admin->settings['supportPortal_RegCreatedBy'])) {
            $portalApiUser = BeanFactory::getBean('Users', $admin->settings['supportPortal_RegCreatedBy']);
        }
        if (!empty($portalApiUser->id)) {
            $this->portalApiUser = $portalApiUser;
            return $this->portalApiUser;
        } else {
            return null;
        }
    }
    
    /**
   	 * Grant access tokens for basic user credentials.
   	 *
   	 * Check the supplied username and password for validity.
   	 *
   	 * You can also use the $client_id param to do any checks required based
   	 * on a client, if you need that.
   	 *
   	 * Required for OAuth2::GRANT_TYPE_USER_CREDENTIALS.
   	 *
   	 * @param $client_id
   	 * Client identifier to be check with.
   	 * @param $username
   	 * Username to be check with.
   	 * @param $password
   	 * Password to be check with.
   	 *
   	 * @return
   	 * TRUE if the username and password are valid, and FALSE if it isn't.
   	 * Moreover, if the username and password are valid, and you want to
   	 * verify the scope of a user's access, return an associative array
   	 * with the scope values as below. We'll check the scope you provide
   	 * against the requested scope before providing an access token:
   	 * @code
   	 * return array(
   	 * 'scope' => <stored scope values (space-separated string)>,
   	 * );
   	 * @endcode
   	 *
   	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-4.3
   	 *
   	 * @ingroup oauth2_section_4
   	 */
   	public function checkUserCredentials(IOAuth2GrantUser $storage, $client_id, $username, $password) {
        $clientInfo = $storage->getClientDetails($client_id);
        if ( $clientInfo === false ) {
            return false;
        }
        
        $portalApiUser = $this->findPortalApiUser($client_id);
        if ( $portalApiUser == null ) {
           // Can't login as a portal user if there is no API user
            throw new SugarApiExceptionPortalNotConfigured();
        }
        
        $contact = $this->loadUserFromName($username);
        if ( !empty($contact) && !User::checkPassword($password, $contact->portal_password) ) {
           $contact = null;
        }
        
        if ( !empty($contact) ) {
            //BEGIN SUGARCRM flav=pro ONLY
            $sessionManager = new SessionManager();
            if(!$sessionManager->canAddSession()) {
                //not able to add another session right now
                $GLOBALS['log']->error("Unable to add new session");
                throw new SugarApiExceptionNeedLogin('too_many_concurrent_connections',array('Too many concurrent sessions'));
            }
            //END SUGARCRM flav=pro ONLY
            
            $this->contactBean = $contact;
            if (empty($this->userBean)) {
                $this->userBean = $portalApiUser;
            }
            
            return array('user_id'=>$contact->id);
        } else {
            throw new SugarApiExceptionNeedLogin();
        }
    }

    /**
     * Loads the current user from the user name
     * split out so that portal can load users properly
     *
     * @param string $username The name of the user you want to load
     *
     * @return SugarBean The user from the name
     */
    public function loadUserFromName($username)
    {
        // It's a portal user, log them in against the Contacts table
        $contact = BeanFactory::newBean('Contacts');
        //BEGIN SUGARCRM flav=pro ONLY
        $contact->disable_row_level_security = true;
        //END SUGARCRM flav=pro ONLY
        $contact = $contact->retrieve_by_string_fields(
            array(
                'portal_name'=>$username,
                'portal_active'=>'1',
                'deleted'=>0,
            ));

        return $contact;
    }
}
