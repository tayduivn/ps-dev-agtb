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

require_once('include/oauth2-php/lib/IOAuth2Storage.php');
require_once('include/oauth2-php/lib/IOAuth2GrantUser.php');
require_once('include/oauth2-php/lib/IOAuth2RefreshTokens.php');

//BEGIN SUGARCRM flav=pro ONLY
require_once('modules/Administration/SessionManager.php');
//END SUGARCRM flav=pro ONLY

require_once('include/api/SugarApi/SugarApiException.php');

/**
 * Sugar OAuth2.0 Storage system, allows the OAuth2 library we are using to 
 * store and retrieve data.
 * This class should only be used by the OAuth2 library and cannot be relied
 * on as a stable API for any other sources. 
 */
class SugarOAuth2Storage implements IOAuth2GrantUser, IOAuth2RefreshTokens {

    // When we authenticate these beans, store them here so if the user id's match (which it will), we just use these instead
    
    /**
     * The SugarCRM User record for this user
     * @var User
     */
    protected $userBean;
    /**
     * The Portal API user used as a stand-in user for all portal logins
     * @var User
     */
    protected $portalApiUser;
    /**
     * The Contact record for the portal login
     * @var Contact
     */
    protected $contactBean;
    /**
     * The record of the OAuth Key based off of the user's supplide client_id
     * @var OAuthKeys
     */
    protected $oauthKeyRecord;

    /**
     * This method locates the portal API user for the specified client_id
     * Currently there is no way to associate a specific user with a specific client_id, so that parameter is ignored for now
     * @param $client_id string The client identifier of the portal account, should be used to identifiy different portal types
     * @return User Returs the user bean of the portal user that it found.
     */
    protected function findPortalApiUser($client_id) 
    {
        if (isset($this->portalApiUser)) {
            return $this->portalApiUser;
        }

        $portalApiUser = BeanFactory::newBean('Users');

        // Find the Portal API user
        // FIXME: What to do if they have more than one portal user?
        $portalApiUser = $portalApiUser->retrieve_by_string_fields(array('portal_only'=>'1','status'=>'Active'));
        
        if ($portalApiUser != null) {
            $this->portalApiUser = $portalApiUser;
            return $this->portalApiUser;
        } else {
            return null;
        }
        
    }

    // BEGIN METHODS FROM IOAuth2Storage
	/**
	 * Make sure that the client credentials is valid.
	 * 
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $client_secret
	 * (optional) If a secret is required, check that they've given the right one.
	 *
	 * @return
	 * TRUE if the client credentials are valid, and MUST return FALSE if it isn't.
	 * @endcode
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-3.1
	 *
	 * @ingroup oauth2_section_3
	 */
	public function checkClientCredentials($client_id, $client_secret = NULL)
    {
        $clientInfo = $this->getClientDetails($client_id);

        if ($clientInfo === false) {
            return false;
        }
        
        if ( ( !empty($clientInfo['client_secret']) && $client_secret == $clientInfo['client_secret'] ) 
             || (empty($clientInfo['client_secret']) && empty($client_secret)) ) {
            return true;
        } else {
            return false;
        }

    }

	/**
	 * Get client details corresponding client_id.
	 *
	 * OAuth says we should store request URIs for each registered client.
	 * Implement this function to grab the stored URI for a given client id.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 *
	 * @return array
	 * Client details. Only mandatory item is the "registered redirect URI", and MUST
	 * return FALSE if the given client does not exist or is invalid.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function getClientDetails($client_id)
    {
        if ( isset($this->oauthKeyRecord) && $this->oauthKeyRecord->c_key == $client_id ) {
            $clientBean = $this->oauthKeyRecord;
        } else {
            $clientSeed = BeanFactory::newBean('OAuthKeys');
            $clientBean = $clientSeed->fetchKey($client_id,'oauth2');

            $this->oauthKeyRecord = $clientBean;
        }

        // Auto-create beans for the built-in clients, if they don't already exist
        if ( $clientBean == null ) {
            $newKey = BeanFactory::newBean('OAuthKeys');
            $newKey->oauth_type = 'oauth2';
            $newKey->c_secret = '';
            if ( $client_id == 'sugar' ) {
                $newKey->client_type = 'user';
                $newKey->c_key = 'sugar';
                $newKey->name = 'Standard OAuth Username & Password Key';
                $newKey->description = 'This OAuth key is automatically created by the OAuth2.0 system to enable username and password logins';
            } else if ( $client_id == 'support_portal' ) {
                $newKey->client_type = 'support_portal';
                $newKey->c_key = 'support_portal';
                $newKey->name = 'OAuth Support Portal Key';
                $newKey->description = 'This OAuth key is automatically created by the OAuth2.0 system to enable logins to the serf-service portal system in Sugar.';
            }
            
            if ( !empty($newKey->client_type) ) {
                $newKey->save();
                $clientBean = $newKey;
                $this->oauthKeyRecord = $clientBean;
            }
            
        }

        if ( $clientBean != null ) {
            // Other than redirect_uri, there isn't a lot of docs on what else to return here
            $returnData = array('redirect_uri'=>'',
                                'client_id'=>$clientBean->c_key,
                                'client_secret'=>$clientBean->c_secret,
                                'client_type'=>$clientBean->client_type,
                                'record_id'=>$clientBean->id,
            );
            return $returnData;
        } else {
            return false;
        }
    }

	/**
	 * Look up the supplied oauth_token from storage.
	 *
	 * We need to retrieve access token data as we create and verify tokens.
	 *
	 * @param $oauth_token
	 * oauth_token to be check with.
	 *
	 * @return
	 * An associative array as below, and return NULL if the supplied oauth_token
	 * is invalid:
	 * - client_id: Stored client identifier.
	 * - expires: Stored expiration in unix timestamp.
	 * - scope: (optional) Stored scope values in space-separated string.
	 *
	 * @ingroup oauth2_section_7
	 */
	public function getAccessToken($oauth_token)
    {
        if ( session_id() != '' ) {
            // There is already a session, let's see if it's the same one
            if ( session_id() != $oauth_token ) {
                // Oh, we are in trouble, we have a session and it's the wrong one.
                // Let's close this session and start a new one with the correct ID.
                session_write_close();
            }
        }
        session_id($oauth_token);
        // Disable cookies
        ini_set("session.use_cookies",false);
        session_start();
        if ( isset($_SESSION['oauth2']) ) {
            return $_SESSION['oauth2'];
        } else if ( !empty($_SESSION['authenticated_user_id']) ) {
            // It's not an oauth2 session, but a normal sugar session we will let them pass
            return array(
                'client_id'=>'sugar',
                'user_id'=>$_SESSION['authenticated_user_id'],
                'expires'=>(time()+7200), // Fake an expiration way off in the future
            );
        } else {
            return NULL;
        }
    }

	/**
	 * Store the supplied access token values to storage.
	 *
	 * We need to store access token data as we create and verify tokens.
	 *
	 * @param $oauth_token
	 * oauth_token to be stored.
	 * @param $client_id
	 * Client identifier to be stored.
	 * @param $user_id
	 * User identifier to be stored.
	 * @param $expires
	 * Expiration to be stored.
	 * @param $scope
	 * (optional) Scopes to be stored in space-separated string.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function setAccessToken($oauth_token, $client_id, $user_id, $expires, $scope = NULL)
    {
        global $sugar_config;

        $clientInfo = $this->getClientDetails($client_id);
        if ( $clientInfo === false ) {
            return false;
        }

        if ( $clientInfo['client_type'] != 'support_portal' ) {
            $userBean = BeanFactory::getBean('Users',$user_id);

            if ( $userBean == null ) {
                return false;
            }
            $this->userBean = $userBean;
            $userType = 'user';
        } else {
            $userType = 'support_portal';
            $userBean = $this->findPortalApiUser($client_id);
            if ( $userBean == null ) {
                return false;
            }
            $this->userBean = $userBean;

            if ( isset($this->contactBean) && $this->contactBean->id == $user_id ) {
                $contactBean = $this->contactBean;
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
            }
        }

        if ( session_id() != '' && session_id() != $oauth_token ) {
            // Oh, we are in trouble, we have a session and it's the wrong one.
            // Let's close this session and start a new one with the correct ID.
            session_write_close();
        }
        session_id($oauth_token);
        // Disable cookies
        ini_set("session.use_cookies",false);
        session_start();
        // Clear out the old session data
        $_SESSION = array();

        // Since we have to setup the session for oauth2 here, we might as well set up the rest of the session
        $GLOBALS['current_user'] = $userBean;
        $_SESSION['is_valid_session'] = true;
        $_SESSION['ip_address'] = query_client_ip();
        $_SESSION['user_id'] = $userBean->id;
        $_SESSION['type'] = 'user';
        $_SESSION['authenticated_user_id'] = $userBean->id;
        $_SESSION['unique_key'] = $sugar_config['unique_key'];
        
        if ( $userType != 'user' ) {
            $_SESSION['type'] = $userType;
            $_SESSION['contact_id'] = $contactBean->id;
            $_SESSION['portal_user_id'] = $userBean->id;
            //BEGIN SUGARCRM flav=pro ONLY
            // This is to make sure the licensing is handled correctly for portal logins
            $sessionManager = new SessionManager();
            $sessionManager->session_type = 'contact';
            $sessionManager->last_request_time = TimeDate::getInstance()->nowDb();
            $sessionManager->session_id = session_id();
            $sessionManager->save();
            //END SUGARCRM flav=pro ONLY
        }

        $_SESSION['oauth2'] = array(
            'client_id'=>$client_id,
            'user_id'=>$user_id,
            'expires'=>$expires,
        );
        return true;
    }

	/**
	 * Check restricted grant types of corresponding client identifier.
	 *
	 * If you want to restrict clients to certain grant types, override this
	 * function.
	 *
	 * @param $client_id
	 * Client identifier to be check with.
	 * @param $grant_type
	 * Grant type to be check with, would be one of the values contained in
	 * OAuth2::GRANT_TYPE_REGEXP.
	 *
	 * @return
	 * TRUE if the grant type is supported by this client identifier, and
	 * FALSE if it isn't.
	 *
	 * @ingroup oauth2_section_4
	 */
	public function checkRestrictedGrantType($client_id, $grant_type)
    {
        return true;
    }

    // END METHODS FROM IOAuth2Storage


    // BEGIN METHODS FROM IOAuth2GrantUser
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
	public function checkUserCredentials($client_id, $username, $password)
    {

        $clientInfo = $this->getClientDetails($client_id);
        if ( $clientInfo === false ) {
            return false;
        }

        if ( $clientInfo['client_type'] != 'support_portal' ) {
            // Is just a regular Sugar User
            $auth = new AuthenticationController((!empty($sugar_config['authenticationClass'])? $sugar_config['authenticationClass'] : 'SugarAuthenticate'));
            $loginSuccess = $auth->login($username,$password,array('passwordEncrypted'=>false,'noRedirect'=>true));
            if ( $loginSuccess && !empty($auth->nextStep) ) {
                // Set it here, and then load it in to the session on the next pass
                // TODO: How do we pass the next required step to the client via the REST API?
                $GLOBALS['nextStep'] = $auth->nextStep;
            }
            if ( $loginSuccess ) {
                $userBean = BeanFactory::newBean('Users');
                $userBean = $userBean->retrieve_by_string_fields(array('user_name'=>$username));
                if ( $userBean == null ) {
                    throw new SugarApiExceptionNeedLogin();
                }
                $this->userBean = $userBean;
                return array('user_id' => $this->userBean->id);
            } else {
                throw new SugarApiExceptionNeedLogin();
            }
        } else {
            $portalApiUser = $this->findPortalApiUser($client_id);
            if ( $portalApiUser == null ) {
                // Can't login as a portal user if there is no API user
                throw new SugarApiExceptionPortalNotConfigured();
            }
            // It's a portal user, log them in against the Contacts table
            $contact = BeanFactory::newBean('Contacts');
            //BEGIN SUGARCRM flav=pro ONLY
            $contact->disable_row_level_security = true;
            //END SUGARCRM flav=pro ONLY
            $contact = $contact->retrieve_by_string_fields(array('portal_name'=>$username,  'portal_active'=>'1', 'deleted'=>0) );
            if ( !empty($contact) && !User::checkPassword($password, $contact->portal_password) ) {
                $contact = null;
            }
            if ( !empty($contact) ) {
                //BEGIN SUGARCRM flav=pro ONLY
                $sessionManager = new SessionManager();
                if(!$sessionManager->canAddSession()){
                    //not able to add another session right now
                    $GLOBALS['log']->error("Unable to add new session");
                    throw new SugarApiExceptionNeedLogin('Too many concurrent sessions',0,'too_many_concurrent_connections');
                }
                //END SUGARCRM flav=pro ONLY
                $this->contactBean = $contact;
                return array('user_id'=>$contact->id);
            } else {
                throw new SugarApiExceptionNeedLogin();
            }
        }
        
    }
    // END METHODS FROM IOAuth2GrantUser

    // BEGIN METHODS FROM IOAuth2RefreshTokens
	/**
	 * Grant refresh access tokens.
	 *
	 * Retrieve the stored data for the given refresh token.
	 *
	 * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
	 *
	 * @param $refresh_token
	 * Refresh token to be check with.
	 *
	 * @return
	 * An associative array as below, and NULL if the refresh_token is
	 * invalid:
	 * - client_id: Stored client identifier.
	 * - expires: Stored expiration unix timestamp.
	 * - scope: (optional) Stored scope values in space-separated string.
	 *
	 * @see http://tools.ietf.org/html/draft-ietf-oauth-v2-20#section-6
	 *
	 * @ingroup oauth2_section_6
	 */
	public function getRefreshToken($refresh_token)
    {
        $tokenSeed = BeanFactory::newBean('OAuthTokens');
        $token = $tokenSeed->load($refresh_token,'oauth2');
        if ( empty($token) ) {
            return null;
        }

        if ( $token->consumer_obj->client_type == 'support_portal' ) {
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
        } else {
            $authBean = BeanFactory::getBean('Users',$token->assigned_user_id);
            if ( $authBean == null || $authBean->status == 'Inactive' ) {
                $authBean = null;
            }
        }

        if ( $token === FALSE || $token->consumer_obj === FALSE || $authBean === null ) {
            return null;
        } else {
            return array(
                'refresh_token'=>$token->id,
                'client_id'=>$token->consumer_obj->c_key,
                'expires'=>$token->expire_ts,
                'user_id'=>$authBean->id,
            );
        }
    }

	/**
	 * Take the provided refresh token values and store them somewhere.
	 *
	 * This function should be the storage counterpart to getRefreshToken().
	 *
	 * If storage fails for some reason, we're not currently checking for
	 * any sort of success/failure, so you should bail out of the script
	 * and provide a descriptive fail message.
	 *
	 * Required for OAuth2::GRANT_TYPE_REFRESH_TOKEN.
	 *
	 * @param $refresh_token
	 * Refresh token to be stored.
	 * @param $client_id
	 * Client identifier to be stored.
	 * @param $expires
	 * expires to be stored.
	 * @param $scope
	 * (optional) Scopes to be stored in space-separated string.
	 *
	 * @ingroup oauth2_section_6
	 */
	public function setRefreshToken($refresh_token, $client_id, $user_id, $expires, $scope = NULL)
    {
        $keyInfo = $this->getClientDetails($client_id);

        $contact_id = '';
        if ( $keyInfo['client_type'] == 'support_portal' ) {
            $portalApiUser = $this->findPortalApiUser($client_id);
            if ( $portalApiUser == null ) {
                return;
            }
            $contact_id = $user_id;
            $user_id = $portalApiUser->id;
        }

        $token = BeanFactory::newBean('OAuthTokens');
        
        $token->id = $refresh_token;
        $token->new_with_id = true;
        $token->consumer = $keyInfo['record_id'];
        $token->assigned_user_id = $user_id;
        $token->contact_id = $contact_id;
        $token->expire_ts = $expires;
        
        $token->save();
        
    }

	/**
	 * Expire a used refresh token.
	 *
	 * This is not explicitly required in the spec, but is almost implied.
	 * After granting a new refresh token, the old one is no longer useful and
	 * so should be forcibly expired in the data store so it can't be used again.
	 *
	 * If storage fails for some reason, we're not currently checking for
	 * any sort of success/failure, so you should bail out of the script
	 * and provide a descriptive fail message.
	 *
	 * @param $refresh_token
	 * Refresh token to be expirse.
	 *
	 * @ingroup oauth2_section_6
	 */
	public function unsetRefreshToken($refresh_token)
    {
        $token = BeanFactory::newBean('OAuthTokens');
        $token->mark_deleted($refresh_token);
    }
    // END METHODS FROM IOAuth2RefreshTokens
}
