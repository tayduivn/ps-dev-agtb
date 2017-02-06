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

namespace Sugarcrm\Sugarcrm\IdentityProvider\Authentication\Listener;

use Symfony\Component\Security\Core\Event\AuthenticationEvent;

/**
 * example listener on success auth
 */
class SugarOnSuccessAuthListener
{
    /**
     * runs on success auth
     * @param AuthenticationEvent $event
     */
    public function __invoke(AuthenticationEvent $event)
    {
        return;
    }
}
