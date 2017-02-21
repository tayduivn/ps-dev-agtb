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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Config;

class IdMSugarAuthenticate extends SugarAuthenticate
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
        $isPasswordEncrypted = !empty($params['passwordEncrypted']);

        $authManager = $this->getAuthProviderBuilder(new Config(\SugarConfig::getInstance()))->buildAuthProviders();

        $token = new UsernamePasswordToken(
            $username,
            (new SugarPreAuthPassEncoder())->encodePassword($password, '', $isPasswordEncrypted),
            AuthProviderManagerBuilder::PROVIDER_KEY_LOCAL,
            User::getDefaultRoles()
        );

        // TODO delete this when strtolower+md5 encrypt will be deleted
        $token->setAttribute('isPasswordEncrypted', $isPasswordEncrypted);
        // Raw password is required for password rehash on success auth
        $token->setAttribute('rawPassword', $password);

        $token = $authManager->authenticate($token);
        $isAuth = $token && $token->isAuthenticated();

        return $isAuth;
    }
}
