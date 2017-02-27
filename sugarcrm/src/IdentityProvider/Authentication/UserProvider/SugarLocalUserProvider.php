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

namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication\UserProvider;

use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\InactiveUserException;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Exception\InvalidUserException;

class SugarLocalUserProvider implements UserProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function loadUserByUsername($username)
    {
        return $this->getUser($username);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!($user instanceof User)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->getUser($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === User::class;
    }

    /**
     * Returns mango base
     *
     * @param string $username
     * @return User
     */
    protected function getUser($username)
    {
        /** @var \User $sugarUser */
        $sugarUser = $this->createUserBean();
        $sugarUserId = $sugarUser->retrieve_user_id($username);
        if (!$sugarUserId) {
            throw new UsernameNotFoundException();
        }
        $sugarUser->retrieve($sugarUserId, true, false);

        if ($sugarUser->status != User::USER_STATUS_ACTIVE) {
            throw new InactiveUserException('Inactive user');
        }

        if (!empty($sugarUser->is_group) || !empty($sugarUser->portal_only)) {
            throw new InvalidUserException('Portal or group user can not log in.');
        }

        $user = new User($username, $sugarUser->user_hash);
        $user->setSugarUser($sugarUser);

        return $user;
    }

    /**
     * create
     * @param $username
     * @param array $additionalFields
     * @return \User
     */
    public function createUser($username, array $additionalFields = [])
    {
        $sugarUser = $this->createUserBean();
        $sugarUser->populateFromRow(array_merge($additionalFields, ['user_name' => $username]));
        $sugarUser->save();
        return $sugarUser;
    }

    /**
     * creates and return empty sugar user bean
     * @return \User|\SugarBean
     */
    protected function createUserBean()
    {
        return \BeanFactory::getBean('Users');
    }
}
