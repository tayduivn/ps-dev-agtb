<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Token\AccessToken;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;

/**
 * ExtAPIMicrosoft
 */
class ExtAPIMicrosoftEmail extends ExternalAPIBase
{
    public $supportedModules = array('OutboundEmail', 'InboundEmail');
    public $authMethod = 'oauth2';
    public $connector = 'ext_eapm_microsoft';

    public $useAuth = true;
    public $requireAuth = true;

    protected $scopes = array(
        'offline_access',
        'user.read',
        'mail.read',
        'mail.send',
    );

    public $docSearch = false;
    public $needsUrl = false;
    public $sharingOptions = null;

    protected $provider = null;

    const APP_STRING_ERROR_PREFIX = 'ERR_MICROSOFT_API_';
    const URL_AUTHORIZE = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    const URL_ACCESS_TOKEN = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';

    /**
     * Returns the Microsoft client used to query the Microsoft Graph API
     *
     * @return Graph
     */
    public function getClient()
    {
        $graph = new GraphProxy();
        $graph->setAuthURL($this->getAuthURL());
        return $graph;
    }

    /**
     * Returns the authorization URL used by the frontend to initialize the user
     * authorization process
     *
     * @return string
     */
    public function getAuthURL()
    {
        $config = $this->getMicrosoftOauth2Config();
        $params = array(
            'client_id' => $config['properties']['oauth2_client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'response_type' => 'code',
            'prompt' => 'select_account',
            'scope' => implode(' ', $this->scopes),
            'state' => 'email',
        );

        return self::URL_AUTHORIZE . '?' . http_build_query($params);
    }

    /**
     * Authenticates a user's authorization code with Microsoft servers. On success,
     * returns the token information as well as the ID of the EAPM bean created
     * to store the token information
     *
     * @param string $code the authorization code to authenticate
     * @return array|bool the token and EAPM information iff successful; false otherwise
     */
    public function authenticate($code)
    {
        $token = $this->getAccessTokenFromServer('authorization_code', [
            'code' => $code,
        ]);

        $eapmId = null;
        if (!empty($token)) {
            $eapmId = $this->saveToken(json_encode($token));
        }

        return array(
            'token' => $token,
            'eapmId' => $eapmId,
        );
    }

    /**
     * Revokes an access token for the given EAPM bean ID by deleting the bean
     *
     * @param string $eapmId the ID of the EAPM bean to revoke access tokens for
     * @return bool true iff successful; false otherwise
     */
    public function revokeToken($eapmId)
    {
        try {
            $eapmBean = $this->getEAPMBean($eapmId);
            if (!empty($eapmBean->id)) {
                $eapmBean->mark_deleted($eapmBean->id);
            }
            return true;
        } catch (Exception $e) {
            $GLOBALS['log']->error($e->getMessage());
            return false;
        }
    }

    /**
     * Uses an authenticated token to query the Microsoft server to retrieve the
     * Microsoft account's email address
     *
     * @param string $eapmId the ID of the EAPM bean storing the account's Oauth2 token
     * @return string|bool the email address if successful; false otherwise
     */
    public function getEmailAddress($eapmId)
    {
        try {
            $accessToken = $this->getAccessToken($eapmId);
            $client = $this->getClient();
            $client->setAccessToken($accessToken);
            $user = $client->createRequest('GET', '/me?$select=mail,userPrincipalName')
                ->setReturnType(Model\User::class)
                ->execute();
            return $user->getMail() ?? $user->getUserPrincipalName();
        } catch (Exception $e) {
            $GLOBALS['log']->error($e->getMessage());
        }
        return false;
    }

    /**
     * Retrieves an access token from the given EAPM bean. If the token is
     * expired, will automatically refresh it.
     *
     * @param string $eapmId the ID of the EAPM bean storing the access token
     * @return string|bool The access token string iff successful; false otherwise
     */
    protected function getAccessToken($eapmId)
    {
        $eapmBean = $this->getEAPMBean($eapmId);
        if (!empty($eapmBean->id)) {
            $token = json_decode($eapmBean->api_data, true);
            if ($token) {
                // If the token is expired, refresh it
                if (!empty($token['refresh_token']) && !empty($token['expires']) && time() > $token['expires']) {
                    return $this->refreshToken($eapmId);
                } elseif (!empty($token['access_token'])) {
                    return $token['access_token'];
                }
            }
        }
        return false;
    }

    /**
     * Uses a refresh token to refresh the token stored in the given EAPM bean
     *
     * @param string $eapmId the ID of the EAPM bean to save the refreshed token to
     * @return string|bool The new access token string iff successful; false otherwise
     */
    protected function refreshToken($eapmId)
    {
        $eapmBean = $this->getEAPMBean($eapmId);
        if (!empty($eapmBean->id)) {
            $token = json_decode($eapmBean->api_data, true);
            if (!empty($token['refresh_token'])) {
                $newToken = $this->getAccessTokenFromServer('refresh_token', [
                    'refresh_token' => $token['refresh_token'],
                ]);
                if (!empty($newToken)) {
                    $this->saveToken(json_encode($newToken), $eapmId);
                    return $newToken->getToken();
                }
            }
        }
        return false;
    }

    /**
     * Calls the Microsoft server to get a new access token using the specified
     * token grant flow. See https://oauth.net/2/grant-types/ for information
     * on grant flow types (each one may or may not be supported by Microsoft)
     *
     * @param string $grantType the token grant method used to get the access token
     *      Examples are 'authorization_code', when getting an access token with
     *      an authorization code, or 'refresh_token' when using a valid refresh
     *      token to get the new access token
     * @param array $params the parameters to accompany the specified $grantType
     *      For 'authorization_code', this should contain the authorization code
     *      from Microsoft. For 'refresh_token', this should contain a valid
     *      refresh token from a previously granted token
     * @return bool|AccessToken
     */
    protected function getAccessTokenFromServer($grantType, $params)
    {
        $config = $this->getMicrosoftOauth2Config();

        try {
            if (empty($this->provider)) {
                $this->provider = new GenericProvider([
                    'clientId' => $config['properties']['oauth2_client_id'],
                    'clientSecret' => $config['properties']['oauth2_client_secret'],
                    'redirectUri' => $config['redirect_uri'],
                    'urlAuthorize' => self::URL_AUTHORIZE,
                    'urlAccessToken' => self::URL_ACCESS_TOKEN,
                    'urlResourceOwnerDetails' => '',
                    'scopes' => implode(' ', $this->scopes),
                ]);
            }
            return $this->provider->getAccessToken($grantType, $params);
        } catch (Exception $e) {
            $GLOBALS['log']->error($e->getMessage());
            return false;
        }
    }

    /**
     * Saves a token in the EAPM table. If an EAPM bean ID is provided (and it
     * exists), that row will be updated. Otherwise, will create a new row
     *
     * @param string $tokenJSON the token information to store
     * @param string|null $eapmId optional: ID of the EAPM record to resave
     * @return string
     */
    protected function saveToken($tokenJSON, $eapmId = null)
    {
        $bean = $this->getEAPMBean($eapmId);
        if (empty($bean->id)) {
            $bean->assigned_user_id = null;
            $bean->application = 'Microsoft';
            $bean->validated = true;
        }
        $bean->api_data = $tokenJSON;
        return $bean->save();
    }

    /**
     * Helper function for retrieving an EAPM bean by ID. Encoding is set to
     * false, so JSON formatted token strings will not be encoded. If no bean
     * is found, will return a new EAPM bean
     *
     * @param string|null $eapmId the ID of the EAPM bean to retrieve
     * @return SugarBean|null the retrieved EAPM bean, or a new one if not found
     */
    protected function getEAPMBean($eapmId)
    {
        return BeanFactory::getBean('EAPM', $eapmId, false);
    }

    /**
     * Gets the stored Microsoft connector properties
     *
     * @return array
     */
    protected function getMicrosoftOauth2Config()
    {
        $config = array();
        require SugarAutoLoader::existingCustomOne('modules/Connectors/connectors/sources/ext/eapm/microsoft/config.php');
        $config['redirect_uri'] = rtrim(SugarConfig::getInstance()->get('site_url'), '/')
            . '/oauth-handler/MicrosoftOauth2Redirect';

        return $config;
    }
}
