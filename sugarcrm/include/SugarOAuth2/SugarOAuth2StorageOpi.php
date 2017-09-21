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

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

/**
 * Class for process OPI tokens
 * require "platform=opi" in request
 */
class SugarOAuth2StorageOpi extends SugarOAuth2StorageBase
{
    /**
     * Predefined user attributes.
     * @var array
     */
    protected $fixedUserAttributes = [
        'employee_status' => User::USER_EMPLOYEE_STATUS_ACTIVE,
        'status' => User::USER_STATUS_ACTIVE,
        'is_admin' => 0,
        'external_auth_only' => 1,
        'system_generated_password' => 0,
    ];

    /**
     * @param string $username
     * @param array $extraData
     * @return User
     */
    public function loadUserFromName($username, array $extraData = [])
    {
        $userProvider = $this->getLocalUserProvider();

        $autoUserProvision = false;

        if (isset($extraData['ext']['amr']) && in_array('PROVIDER_KEY_SAML', $extraData['ext']['amr'])) {
            $autoUserProvision = true;
            $identityField = 'email';
        } else {
            $identityField = 'user_name';
        }

        try {
            return $userProvider->loadUserByField($username, $identityField)->getSugarUser();
        } catch (UsernameNotFoundException $e) {
            if (!$autoUserProvision) {
                throw $e;
            }
        }

        $userAttributes = array_merge(
            ['user_name' => $username, 'last_name' => $username, 'email' => $username],
            $this->fixedUserAttributes
        );
        return $userProvider->createUser($username, $userAttributes);
    }

    /**
     * @return SugarLocalUserProvider
     */
    protected function getLocalUserProvider()
    {
        return new SugarLocalUserProvider();
    }
}
