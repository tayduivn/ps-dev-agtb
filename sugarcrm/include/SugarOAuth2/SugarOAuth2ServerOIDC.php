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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderOIDCManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderBasicManagerBuilder;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\IntrospectToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\JWTBearerToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Sugar OAuth2.0 server that connects Sugar and OpenID Connect server (e.g. Hydra authentication).
 * @api
 */
class SugarOAuth2ServerOIDC extends SugarOAuth2Server
{
    /**
     * @var string
     */
    protected $platform;

    /**
     * SugarOAuth2ServerOIDC constructor.
     *
     * @param IOAuth2Storage $storage
     * @param array $config
     */
    public function __construct(IOAuth2Storage $storage, array $config)
    {
        parent::__construct($storage, $config);
    }

    /**
     * Sets the platform
     *
     * @param string $platform
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;
    }

    /**
     * @inheritdoc
     */
    public function setupVisibility()
    {
    }

    /**
     * Verifies openID connect token delegating it to OIDC server.
     * Loads PHP session bound to the token.
     *
     * @param string $token OIDC Access Token
     * @param string|null $scope
     *
     * @return array
     */
    public function verifyAccessToken($token, $scope = null)
    {
        $userToken = null;
        try {
            $authManager = $this->getAuthProviderBuilder(new Config(\SugarConfig::getInstance()))->buildAuthProviders();
            $introspectToken = new IntrospectToken($token);
            $introspectToken->setAttribute('platform', $this->platform);
            /** @var IntrospectToken $userToken */
            $userToken = $authManager->authenticate($introspectToken);
        } catch (AuthenticationException $e) {
            return [];
        }

        if (!$userToken->isAuthenticated()) {
            return [];
        }

        return [
            'client_id' => $userToken->getAttribute('client_id'),
            'user_id' => $userToken->getUser()->getSugarUser()->id,
            'expires' => $userToken->getAttribute('exp'),
        ];
    }

    /**
     * @inheritdoc
     */
    protected function createAccessToken($client_id, $user_id, $scope = null)
    {
        try {
            $authManager = $this->getAuthProviderBasicBuilder(new Config(\SugarConfig::getInstance()))
                                ->buildAuthProviders();

            $jwtBearerToken = new JWTBearerToken($user_id);
            $accessToken = $authManager->authenticate($jwtBearerToken);

            $token = array(
                'access_token' => $accessToken->getAttribute('token'),
                'expires_in' => $accessToken->getAttribute('expires_in'),
                'token_type' => $accessToken->getAttribute('token_type'),
                'scope' => $accessToken->getAttribute('scope'),
            );

            if ($this->storage instanceof IOAuth2RefreshTokens) {
            }

            return $token;
        } catch (AuthenticationException $e) {
            throw new \SugarApiExceptionNeedLogin($e->getMessage());
        }
    }

    /**
     * @param Config $config
     * @return AuthProviderOIDCManagerBuilder
     */
    protected function getAuthProviderBuilder(Config $config)
    {
        return new AuthProviderOIDCManagerBuilder($config);
    }

    /**
     * @param Config $config
     *
     * @return AuthProviderBasicManagerBuilder
     */
    protected function getAuthProviderBasicBuilder(Config $config)
    {
        return new AuthProviderBasicManagerBuilder($config);
    }
}
