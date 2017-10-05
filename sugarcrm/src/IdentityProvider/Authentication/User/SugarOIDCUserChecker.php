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

namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class SugarOIDCUserChecker extends UserChecker
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
     * @var SugarLocalUserProvider
     */
    protected $localUserProvider;

    /**
     * @param SugarLocalUserProvider $localUserProvider
     */
    public function __construct(SugarLocalUserProvider $localUserProvider)
    {
        $this->localUserProvider = $localUserProvider;
    }

    public function checkPostAuth(UserInterface $user)
    {
        $this->loadSugarUser($user);
        parent::checkPostAuth($user);
    }

    /**
     * Find or create Sugar User.
     *
     * @param User $user
     * @throws \Exception
     */
    protected function loadSugarUser(User $user)
    {
        $sugarUser = null;
        $extraData = $user->getAttribute('ext');
        $externalAuthInfo = isset($extraData['amr']) ? $extraData['amr'] : [];
        $identityValue = $user->getUsername();

        $defaultAttributes = [
            'user_name' => $identityValue,
            'last_name' => $identityValue,
            'email' => $identityValue,
        ];

        if (in_array('PROVIDER_KEY_SAML', $externalAuthInfo)) {
            $identityField = 'email';
        } else {
            $identityField = 'user_name';
        }

        try {
            $sugarUser = $this->localUserProvider->loadUserByField($identityValue, $identityField)->getSugarUser();
        } catch (UsernameNotFoundException $e) {
            $userAttributes = array_merge($defaultAttributes, $this->fixedUserAttributes);
            $sugarUser = $this->localUserProvider->createUser($identityValue, $userAttributes);
        }
        $user->setSugarUser($sugarUser);
    }
}
