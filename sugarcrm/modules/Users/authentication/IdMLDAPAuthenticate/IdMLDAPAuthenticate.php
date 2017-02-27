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
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderManagerBuilder;

/**
 * IdM LDAP login
 */
class IdMLDAPAuthenticate extends \LDAPAuthenticate
{
    /**
     * auth user over ldap service
     * @param string $username
     * @param string $password
     * @param bool $fallback
     * @param array $params
     * @return bool
     */
    public function loginAuthenticate($username, $password, $fallback = false, $params = [])
    {
        $authManager = $this->getAuthProviderBuilder(new Config(\SugarConfig::getInstance()))->buildAuthProviders();
        $token = new UsernamePasswordToken(
            $username,
            $password,
            AuthProviderManagerBuilder::PROVIDER_KEY_LDAP,
            User::getDefaultRoles()
        );
        $token = $authManager->authenticate($token);
        return $token && $token->isAuthenticated();
    }
}
