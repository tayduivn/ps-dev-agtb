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

/**
 * Help carriers to get e-mail from user.
 *
 * Class Email
 * @package Notification
 */
class Email implements AddressTypeInterface
{

    /**
     * Checks received user and returns e-mails values for delivery.
     *
     * @param \User $user for retiring list of e-mails
     * @return string[] list of e-mails
     */
    public function getOptions(\User $user)
    {
        $emails = $user->emailAddress->getAddressesForBean($user);
        $options = array();
        foreach ($emails as $row) {
            if (!$row['opt_out'] && !$row['invalid_email']) {
                $options[] = $row['email_address'];
            }
        }
        return $options;
    }

    /**
     * Returns e-mail from user by option.
     *
     * @param \User $user for retiring e-mail
     * @param string $option key for e-mail
     * @return string|null e-mail
     */
    public function getTransportValue(\User $user, $option)
    {
        $list = $this->getOptions($user);
        if (array_key_exists($option, $list)) {
            return $list[$option];
        } elseif ($list) {
            return $list[0];
        }
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isSelectable()
    {
        return true;
    }
}
