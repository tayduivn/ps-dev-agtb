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

/**
 * Sugar OAuth2.0 Storage system, allows the OAuth2 library we are using to 
 * store and retrieve data.
 * This class should only be used by the OAuth2 library and cannot be relied
 * on as a stable API for any other sources. 
 */
abstract class SugarOAuth2StoragePlatform  {
    /**
     * The name of the platform. Does not have to be set but if it is will be used
     * to identify the platform for this storage mechanism.
     * 
     * @var string
     */
    protected $platformName = null;
    
    /**
     * The client type that this client is associated with
     * 
     * @var string
     */
    protected $clientType = null;
    
    // When we authenticate these beans, store them here so if the user id's match (which it will), we just use these instead
    
    /**
     * The SugarCRM User record for this user
     * @var User
     */
    protected $userBean;
    
    /**
     * The record of the OAuth Key based off of the user's supplide client_id
     * @var OAuthKeys
     */
    protected $oauthKeyRecord;

    /**
     * The user type for this client
     * 
     * @var string
     */
    protected $userType;

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
	abstract public function checkUserCredentials(IOAuth2GrantUser $storage, $client_id, $username, $password);
    // END METHODS FROM IOAuth2GrantUser
    
    /**
     * Allows setting of the platform name from the server
     * 
     * @param string $name The name of the platform to set to
     */
    public function setPlatformName($name) {
        $this->platformName = $name;
    }

    /**
     * Gets the platform name of the given storage mechanism
     * 
     * @return string
     */
    public function getPlatformName() {
        // If the class sets the name of its platform, use it
        if (!empty($this->platformName)) {
            return $this->platformName;
        }
        
        // Send back the name of the platform from the class name
        return strtolower(str_replace('SugarOAuth2Storage', '', get_class($this)));
    }

    /**
     * Gets the client type associated with this storage
     * 
     * @return string
     */
    public function getClientType() {
        return $this->clientType;
    }
    
    /**
     * Get the user type for this user
     * 
     * @return string
     */
    public function getUserType() {
        return $this->userType;
    }
}
