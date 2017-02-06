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

use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\User\UserInterface;

class SugarUserChecker extends UserChecker
{
    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(UserInterface $user)
    {
        parent::checkPreAuth($user);
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(UserInterface $user)
    {
        parent::checkPostAuth($user);
    }
}
