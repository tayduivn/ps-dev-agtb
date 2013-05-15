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

require_once('vendor/oauth2-php/lib/OAuth2.php');

/**
 * Sugar OAuth2.0 server, is a wrapper around the php-oauth2 library
 * @api
 */
class SugarOAuth2Server extends OAuth2
{
    // Maximum length of the session after which new login if required
    // and refresh tokens are not allowed
    const CONFIG_MAX_SESSION = 'max_session_lifetime';

    /**
     * This function will return the OAuth2Server class, it will check
     * the custom/ directory so users can customize the authorization
     * types and storage
     */
    public static function getOAuth2Server() {
        static $currentOAuth2Server = null;

        if ( ! isset($currentOAuth2Server) ) {
            SugarAutoLoader::requireWithCustom('include/SugarOAuth2/SugarOAuth2Storage.php');
            $oauthStorageName = SugarAutoLoader::customClass('SugarOAuth2Storage');
            $oauthStorage = new $oauthStorageName();

            SugarAutoLoader::requireWithCustom('include/SugarOAuth2/SugarOAuth2Server.php');
            $oauthServerName = SugarAutoLoader::customClass('SugarOAuth2Server');
            $config = array();
            if(!empty($GLOBALS['sugar_config']['oauth2'])) {
                $config = $GLOBALS['sugar_config']['oauth2'];
            }
            $currentOAuth2Server = new $oauthServerName($oauthStorage, $config);
        }

        return $currentOAuth2Server;
    }

    protected function createAccessToken($client_id, $user_id, $scope = NULL)
    {
        $time_limit = $this->getVariable(self::CONFIG_MAX_SESSION);
        // If we have session time limit, then:
        // 1. We limit time for initial refresh token to session length
        // 2. We inherit this time limit for subsequent refresh tokens
        if($time_limit) {
            // enforce session length limits
            if($this->oldRefreshToken) {
                // inherit expiration from the old token
                $tokenSeed = BeanFactory::newBean('OAuthTokens');
                $token = $tokenSeed->load($this->oldRefreshToken,'oauth2');
                $this->setVariable(self::CONFIG_REFRESH_LIFETIME, $token->expire_ts-time());
            } else {
                $this->setVariable(self::CONFIG_REFRESH_LIFETIME, $time_limit);
            }
        }
        return parent::createAccessToken($client_id, $user_id, $scope);
    }

    /**
     * Sets up visibility where needed
     */
    public function setupVisibility() {
        $this->storage->setupVisibility();
    }

    /**
     * Sets the platform for the storage handler
     *
     * @param string $platform
     */
    public function setPlatform($platform) {
        $this->storage->setPlatform($platform);
    }

    /**
   	 * Generates an unique access token.
   	 *
   	 * Implementing classes may want to override this function to implement
   	 * other access token generation schemes.
   	 *
   	 * @return
   	 * An unique access token.
   	 *
   	 * @ingroup oauth2_section_4
   	 * @see OAuth2::genAuthCode()
   	 */
   	protected function genAccessToken() {
   		return create_guid();
   	}
}