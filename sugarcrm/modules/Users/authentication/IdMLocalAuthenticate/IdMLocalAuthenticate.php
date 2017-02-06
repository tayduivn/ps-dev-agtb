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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderManagerBuilder;
use Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Encoder\SugarPreAuthPassEncoder;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class IdMLocalAuthenticate extends SugarAuthenticate
{
    /**
     * Authenticates a user based on the username and password
     * returns true if the user was authenticated false otherwise
     * it also will load the user into current user if he was authenticated
     *
     * @param string $username
     * @param string $password
     * @param boolean $fallback
     * @param array $params
     * @throws SugarApiExceptionNeedLogin
     * @return boolean
     */
    public function loginAuthenticate($username, $password, $fallback = false, $params = [])
    {
        try {
            $authManager = $this->getAuthProviderBuilder()->buildAuthProviders();

            $token = new UsernamePasswordToken(
                $username,
                (new SugarPreAuthPassEncoder())->encodePassword($password, '', $params),
                AuthProviderManagerBuilder::PROVIDER_KEY_LOCAL,
                User::getDefaultRoles()
            );

            $token = $authManager->authenticate($token);
            $isAuth = $token && $token->isAuthenticated();
        } catch (\Exception $e) {
            throw new SugarApiExceptionNeedLogin();
        }

        return $isAuth;
    }

    /**
     * @return AuthProviderManagerBuilder
     */
    protected function getAuthProviderBuilder()
    {
        $config = \SugarConfig::getInstance();
        return new AuthProviderManagerBuilder($config);
    }
}
