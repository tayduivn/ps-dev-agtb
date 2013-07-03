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

/**
 * Sugar OAuth2.0 Base Storage system, allows the OAuth2 library we are using to
 * store and retrieve data by interacting with our Base client.
 *
 * This class should only be used by the OAuth2 library and cannot be relied
 * on as a stable API for any other sources.
 */
class SugarOAuth2StorageBase extends SugarOAuth2StoragePlatform {
    /**
     * The user type for this client
     *
     * @var string
     */
    protected $userType = 'user';

    /**
     * Gets a user bean
     *
     * @param  string $user_id The ID of the User to get
     * @return User
     */
    public function getUserBean($user_id) {
        return BeanFactory::getBean('Users', $user_id);
    }

    /**
     * Small validator for child classes to use to determine whether a session can
     * be written to
     *
     * @return boolean
     */
    public function canStartSession() {
        return true;
    }
    /**
     * Fills in any added session data needed by this client type
     *
     * This method is used by child classes like portal
     */
    public function fillInAddedSessionData() {
        return true;
    }

    /**
     * Gets the authentication bean for a given client
     * @param OAuthToken
     * @return mixed
     */
    public function getAuthBean(OAuthToken $token) {
        $authBean = BeanFactory::getBean('Users', $token->assigned_user_id);
        if ( $authBean == null || $authBean->status == 'Inactive' ) {
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
        return array('contact_id' => '', 'user_id' => $user_id);
    }

    /**
     * Sets up necessary visibility for a client. Not all clients will set this
     *
     * @return void
     */
    public function setupVisibility() {}

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
	public function checkUserCredentials(IOAuth2GrantUser $storage, $client_id, $username, $password)
    {
        $clientInfo = $storage->getClientDetails($client_id);
        if ( $clientInfo === false ) {
            return false;
        }

        // Is just a regular Sugar User
        $auth = new AuthenticationController((!empty($sugar_config['authenticationClass'])? $sugar_config['authenticationClass'] : 'SugarAuthenticate'));
        // noHooks since we'll take care of the hooks on API level, to make it more generalized
        $loginSuccess = $auth->login($username,$password,array('passwordEncrypted'=>false,'noRedirect'=>true, 'noHooks'=>true));
        if ( $loginSuccess && !empty($auth->nextStep) ) {
            // Set it here, and then load it in to the session on the next pass
            // TODO: How do we pass the next required step to the client via the REST API?
            $GLOBALS['nextStep'] = $auth->nextStep;
        }

        if ( $loginSuccess ) {
            $this->userBean = $this->loadUserFromName($username);
            return array('user_id' => $this->userBean->id);
        } else {
            throw new SugarApiExceptionNeedLogin();
        }
    }
    // END METHODS FROM IOAuth2GrantUser

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
        $userBean = BeanFactory::newBean('Users');
        $userBean = $userBean->retrieve_by_string_fields(
            array(
                'user_name'=>$username,
                'deleted'=>'0',
                'status'=>'Active',
                'portal_only'=>'0',
                'is_group'=>'0',
            ));
        if ( $userBean == null ) {
            throw new SugarApiExceptionNeedLogin();
        }
        return $userBean;
    }
}
