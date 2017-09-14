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

/**
 * Auth user using in hydra oauth2-client library
 * The config must be like:
 * $sugar_config['oidc_oauth'] = [
 *      'clientId' => 'testLocal',
 *      'clientSecret' => 'testLocalSecret',
 *      'oidcUrl' => 'http://OIDC_URL',
 * ];
 */

class OAuth2Authenticate implements SugarAuthenticateExternal
{
    /**
     * @var GenericProvider
     */
    protected $oAuthProvider;

    /**
     * @var string
     */
    protected $oidcUrl;

    /**
     * @var string
     */
    protected $clientId;

    /**
     * @var string
     */
    protected $clientSecret;

    /**
     * @var string
     */
    protected $redirectUri;

    /**
     * Constructor
     * @throws \RuntimeException
     */
    public function __construct()
    {
        $config = SugarConfig::getInstance()->get('oidc_oauth');

        if (empty($config) || empty($config['oidcUrl'])) {
            throw new \RuntimeException('Oidc config and url does not found.');
        }

        $this->oidcUrl = rtrim($config['oidcUrl'], '/ ');
        $this->clientId = $config['clientId'] ?: null;
        $this->clientSecret = $config['clientSecret'] ?: null;
        $this->redirectUri = rtrim(SugarConfig::getInstance()->get('site_url'), '/') . OAuth2Api::OAUTH2_CONSUMER;

        $this->oAuthProvider = new GenericProvider($this->getOauthConfig());
    }

    /**
     * {@inheritdoc}
     */
    public function getLoginUrl($returnQueryVars = [])
    {
        return $this->oAuthProvider->getAuthorizationUrl();
    }

    /**
     * {@inheritdoc}
     */
    public function getLogoutUrl()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getOidcUrl()
    {
        return $this->oidcUrl;
    }


    /**
     * prepare config for oAuth2 client
     * @return array
     */
    protected function getOauthConfig()
    {
        return [
            'clientId' => $this->clientId,
            'clientSecret' => $this->clientSecret,
            'redirectUri' => $this->redirectUri,
            'urlAuthorize' => $this->oidcUrl . '/oauth2/auth',
            'urlAccessToken' => $this->oidcUrl . '/oauth2/token',
            'urlResourceOwnerDetails' => $this->oidcUrl . '/oauth2/introspect',
        ];
    }
}
