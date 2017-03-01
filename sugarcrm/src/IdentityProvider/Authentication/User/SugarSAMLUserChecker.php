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

use Symfony\Component\Security\Core\User\UserInterface;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Sugarcrm\IdentityProvider\Authentication\User\SAMLUserChecker;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider\SugarLocalUserProvider;

/**
 * Class performs post authentication checking for Sugar SAML user.
 * It searches for corresponding Sugar User in database;
 * if User is not found and auto-creation is enabled it creates one.
 *
 * @package Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User
 */
class SugarSAMLUserChecker extends SAMLUserChecker
{
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

    /**
     * Check if SAML user corresponds to Sugar User.
     * If found auth is considered OK.
     * If not found and automatic user-provisioning is set to true, Sugar User is created and auth is OK.
     * If user-provisioning is set to false, auth fails.
     *
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        $this->loadSugarUser($user);
        parent::checkPostAuth($user);
    }

    /**
     * Find or create Sugar User.
     *
     * @param User $user
     */
    protected function loadSugarUser(User $user)
    {
        $nameIdentifier = $user->getUsername();
        $identityField = $user->getAttribute('identityField');
        $provision = $user->getAttribute('provision');

        $fixedAttributes = [
            'employee_status' => User::USER_EMPLOYEE_STATUS_ACTIVE,
            'status' => User::USER_STATUS_ACTIVE,
            'is_admin' => 0,
            'external_auth_only' => 1,
            'system_generated_password' => 0,
        ];

        $defaultAttributes = [
            'user_name' => $nameIdentifier,
            'last_name' => $nameIdentifier,
            'email' => $nameIdentifier,
        ];

        try {
            $sugarUser = $this->localUserProvider->loadUserByField($nameIdentifier, $identityField)->getSugarUser();
        } catch (UsernameNotFoundException $e) {
            if (!$provision) {
                throw $e;
            }
            $sugarUser = $this->processCustomUserCreate($user);
            if (!$sugarUser) {
                $userAttributes = array_merge($defaultAttributes, $user->getAttribute('attributes'));
                $userAttributes = array_merge($userAttributes, $fixedAttributes);
                $sugarUser = $this->localUserProvider->createUser($nameIdentifier, $userAttributes);
            }
        }
        $user->setSugarUser($sugarUser);
    }

    /**
     * Checks if custom SAML create user function exists.
     * If function is specified call it and return user.
     * If function is not specified, skip the whole process of custom user creation.
     *
     * This is done for bwc-support of Sugar ability to use custom user-provision function. See BR-5065
     * When we drop support of such functionality delete this function.
     *
     * @param User $user
     * @return \User|null
     */
    protected function processCustomUserCreate(User $user)
    {
        $settings = \SAMLAuthenticate::loadSettings();
        if (isset($settings->customCreateFunction) && $user->getAttribute('XMLResponse')) {
            $type = \SugarConfig::getInstance()->get('authenticationClass', 'SAMLAuthenticate');

            if (!\SugarAutoLoader::requireWithCustom('modules/Users/authentication/'. $type .'/' . $type . '.php')) {
                require_once 'modules/Users/authentication/SAMLAuthenticate/SAMLAuthenticate.php';
            }

            $sugarSamlAuthController = new $type();
            $userAuth = $sugarSamlAuthController->userAuthenticate;

            $xpath = new \DOMXpath($user->getAttribute('XMLResponse'));
            $nameId = $user->getUsername();
            return call_user_func($settings->customCreateFunction, $userAuth, $nameId, $xpath, $settings);
        } else {
            return null;
        }
    }
}
