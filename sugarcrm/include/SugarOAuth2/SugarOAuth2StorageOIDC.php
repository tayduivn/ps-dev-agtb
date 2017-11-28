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
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\AuthProviderBasicManagerBuilder;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Token\UsernamePasswordTokenFactory;

/**
 * Provides legacy clients support which do not support oauth2/OIDC protocol
 * and uses username/password for authentication
 */
class SugarOAuth2StorageOIDC extends SugarOAuth2Storage
{
    /**
     * @inheritdoc
     */
    public function checkUserCredentials($client_id, $username, $password)
    {
        try {
            $token = (new UsernamePasswordTokenFactory($username, $password))->createIdPAuthenticationToken();
            $manager = $this->getAuthProviderBasicBuilder(new Config(\SugarConfig::getInstance()))
                                  ->buildAuthProviders();
            $resultToken = $manager->authenticate($token);
            if ($resultToken->isAuthenticated()) {
                return [
                    'user_id' => $resultToken->getUser()->getSugarUser()->id,
                    'scope' => null,
                ];
            }
        } catch (AuthenticationException $e) {
            throw new SugarApiExceptionNeedLogin($e->getMessage());
        }

        throw new SugarApiExceptionNeedLogin(translate('ERR_INVALID_PASSWORD', 'Users'));
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
