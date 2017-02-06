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

/**
 * example subscriber
 */
class SugarOnAuthSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents()
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onSuccess',
            AuthenticationEvents::AUTHENTICATION_FAILURE => 'onFailure'
        ];
    }

    /**
     * runs on success
     * @param AuthenticationEvent $event
     */
    public function onSuccess(AuthenticationEvent $event)
    {
        return;
    }

    /**
     * runs on failure
     * @param AuthenticationEvent $event
     */
    public function onFailure(AuthenticationEvent $event)
    {
        return;
    }
}
