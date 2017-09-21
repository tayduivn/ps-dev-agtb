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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Sugar OAuth2.0 server that connects Sugar and OpenID Connect server (e.g. Hydra authentication).
 * @api
 */
class SugarOAuth2ServerOIDC extends SugarOAuth2Server
{
    /**
     * @var OAuth2Authenticate
     */
    protected $auth;

    /**
     * SugarOAuth2ServerOIDC constructor.
     *
     * @param IOAuth2Storage $storage
     * @param array $config
     * @param AuthenticationController $auth
     */
    public function __construct(IOAuth2Storage $storage, array $config, AuthenticationController $auth)
    {
        parent::__construct($storage, $config);
        $this->auth = $auth;
    }

    /**
     * Method is not relevant. Clients should get access token directly from OpenID Connect server.
     *
     * {@inheritdoc}
     *
     * @throws \Exception
     */
    public function grantAccessToken(array $inputData = null, array $authHeaders = null)
    {
        throw new \Exception('Not implemented. Get access token directly from OpenID Connect server.');
    }

    /**
     * Verifies openID connect token delegating it to OIDC server.
     * Loads PHP session bound to the token.
     *
     * @param string $token OIDC Access Token
     * @param string|null $scope
     *
     * @return array
     *
     * @throws UsernameNotFoundException|\RuntimeException
     */
    public function verifyAccessToken($token, $scope = null)
    {
        // ToDo: IDM we should cache these requests so that we do not degrade performance.
        $userData = $this->auth->authController->introspectAccessToken($token);

        if (!is_array($userData) || empty($userData['sub'])) {
            throw new \RuntimeException('Bad OIDC response. User credentials were not found.');
        }

        // ToDo: IDM: move it to OIDC storage and find user by field and identityField there.
        $user = (new SugarLocalUserProvider())->loadUserByUsername($userData['sub'])->getSugarUser();

        // Check if we already created session for this User based on token. Otherwise create a new one.
        $tokenSessionId = base64_encode($token);
        if (!$this->storage->getAccessToken($tokenSessionId)) {
            $this->storage->setAccessToken(
                $tokenSessionId,
                'sugar', // ToDo: IDM: it should be value from $userData['client_id']. Currently only 'sugar' is supported
                $user->id,
                $userData['exp'],
                $userData['scope']
            );
        }

        return $userData;
    }
}
