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

namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\User;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Lockout;

/**
 * example subscriber
 */
class SugarOnAuthLockoutSubscriber implements EventSubscriberInterface
{
    /**
     * @var Lockout
     */
    protected $lockout;

    /**
     * @var UserProviderInterface
     */
    protected $userProvider;

    /**
     * @param Lockout $lockout
     * @param UserProviderInterface $userProvider
     */
    public function __construct(Lockout $lockout, UserProviderInterface $userProvider)
    {
        $this->lockout = $lockout;
        $this->userProvider = $userProvider;
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onSuccess',
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onFailure',
        ];
    }

    /**
     * runs on success
     * @param AuthenticationEvent $event
     */
    public function onSuccess(AuthenticationEvent $event)
    {
        if (!$this->lockout->isEnabled()) {
            return;
        }

        /** @var User $user */
        $user = $event->getAuthenticationToken()->getUser();
        if ($user->getLoginFailed() || $user->getLockout()) {
            $user->clearLockout();
        }

        return;
    }

    /**
     * runs on failure
     * @param AuthenticationEvent $event
     */
    public function onFailure(AuthenticationEvent $event)
    {
        if (!$this->lockout->isEnabled()) {
            return;
        }

        /** @var User $user */
        $user = $this->userProvider->loadUserByUsername($event->getAuthenticationToken()->getUsername());
        if (!$user) {
            return;
        }

        if (($user->getLoginFailed() + 1) >= $this->lockout->getFailedLoginsCount()) {
            $user->lockout($this->lockout->getTimeDate()->nowDb());
        } else {
            $user->incrementLoginFailed();
        }
        
        return;
    }
}
