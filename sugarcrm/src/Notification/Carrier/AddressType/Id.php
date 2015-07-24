<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\Sugarcrm\Notification\Carrier\AddressType;

class Id implements AddressTypeInterface
{

    /**
     * @param \User $user
     * @return array
     */
    public function getOptions(\User $user)
    {
        return array('id');
    }

    /**
     * @param \User $user
     * @param string $option
     * @return mixed
     */
    public function getTransportValue(\User $user, $option)
    {
        return $user->getFieldValue($option);
    }

}
