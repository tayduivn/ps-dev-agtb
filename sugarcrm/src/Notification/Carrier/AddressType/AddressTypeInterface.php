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
 * Address Types represent support classes which help carriers to get delivery value from user.
 *
 * Interface AddressTypeInterface
 * @package Notification
 */
interface AddressTypeInterface
{
    /**
     * Checks received user and returns suitable values for delivery.
     *
     * @param \User $user user for retiring suitable values
     * @return string[] suitable values for delivery
     */
    public function getOptions(\User $user);

    /**
     * Returns delivery value for user.
     *
     * @param \User $user for retiring delivery value
     * @param string $option key for delivery value
     * @return mixed delivery value
     */
    public function getTransportValue(\User $user, $option);
}
