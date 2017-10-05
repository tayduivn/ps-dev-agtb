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

namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Provider;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessToken;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\OIDC\IntrospectToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User\SugarOIDCUserChecker;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarOIDCUserProvider;
use Sugarcrm\Sugarcrm\League\OAuth2\Client\Provider\HttpBasicAuth\GenericProvider;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\AuthenticationServiceException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class OIDCAuthenticationProvider
 * Provides all authentication operations on OIDC server.
 */
class OIDCAuthenticationProvider implements AuthenticationProviderInterface
{
    /**
     * @var GenericProvider
     */
    protected $oAuthProvider = null;

    /**
     * @var SugarOIDCUserProvider
     */
    protected $userProvider;

    /**
     * @var SugarOIDCUserChecker
     */
    protected $userChecker;

    /**
     * List of handlers that can be used to handle tokens.
     * Actually, they correspond to steps of SAML authentication flow.
     *
     * @var array
     */
    protected $handlers = [
        IntrospectToken::class => 'introspectToken',
    ];

    public function __construct(
        AbstractProvider $oAuthProvider,
        UserProviderInterface $userProvider,
        UserCheckerInterface $userChecker
    ) {
        $this->oAuthProvider = $oAuthProvider;
        $this->userProvider = $userProvider;
        $this->userChecker = $userChecker;
    }

    /**
     * @inheritdoc
     */
    public function authenticate(TokenInterface $token)
    {
        $handlerMethod = null;
        foreach ($this->handlers as $tokenClass => $handler) {
            if ($token instanceof $tokenClass) {
                $handlerMethod = $handler;
                break;
            }
        }
        if (!$handlerMethod) {
            throw new AuthenticationServiceException('There is no authentication handler for ' . get_class($token));
        }

        try {
            return $this->{$handlerMethod}($token);
        } catch (AuthenticationException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new AuthenticationException($e->getMessage());
        }
    }

    /**
     * Token introspection on OIDC server.
     *
     * @param TokenInterface $token
     * @return IntrospectToken
     */
    protected function introspectToken(TokenInterface $token)
    {
        $accessToken = new AccessToken(['access_token' => $token->getCredentials()]);
        $result = $this->oAuthProvider->introspectToken($accessToken);

        if (empty($result) || empty($result['active'])) {
            throw new AuthenticationException('OIDC Token is not valid');
        }

        $resultToken = new IntrospectToken($token->getCredentials());
        $resultToken->setAttributes($result);
        $resultToken->setAttribute('platform', $token->getAttribute('platform'));
        /** @var User $user */
        $user = $this->userProvider->loadUserByUsername($result['sub']);
        foreach ($result as $key => $value) {
            $user->setAttribute($key, $value);
        }
        $this->userChecker->checkPostAuth($user);
        $resultToken->setUser($user);
        $resultToken->setAuthenticated(true);

        return $resultToken;
    }

    /**
     * @inheritdoc
     */
    public function supports(TokenInterface $token)
    {
        return $token instanceof IntrospectToken;
    }
}
