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

namespace Sugarcrm\Sugarcrm\Notification\Carrier\AddressType;

/**
 * Help carriers to get id from user.
 *
 * Class Id
 * @package Notification
 */
class Id implements AddressTypeInterface
{
    /**
     * Checks received user and returns id for delivery
     *
     * @param \User $user
     * @return string[]
     */
    public function getOptions(\User $user)
    {
        return array('id' => $user->id);
    }

    /**
     * Returns id from user.
     *
     * @param \User $user  for retiring id
     * @param string $option in this case not used but need for implements AddressTypeInterface
     * @return string id
     */
    public function getTransportValue(\User $user, $option)
    {
        return $user->id;
    }
}
