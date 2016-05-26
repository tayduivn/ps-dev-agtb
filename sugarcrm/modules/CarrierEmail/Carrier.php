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

use Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface;
use Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email as AddressTypeEmail;

require_once 'modules/CarrierEmail/Transport.php';

class CarrierEmailCarrier implements CarrierInterface
{
    /**
     * Get Transport to deliver messages via Email.
     * @return CarrierEmailTransport
     */
    public function getTransport()
    {
        return new CarrierEmailTransport();
    }

    /**
     * Messages to SocketServer have only 'title', 'text' and 'html';
     * 'title' is subject, 'text' and 'html' are parts of email body.
     * {@inheritdoc}
     */
    public function getMessageSignature()
    {
        return array(
            'title' => '',
            'text' => '',
            'html' => '',
        );
    }

    /**
     * Return carrier address type(AddressTypeEmail)
     *
     * @return AddressTypeEmail
     */
    public function getAddressType()
    {
        return new AddressTypeEmail();
    }

    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return array(
            'deliveryOptionsDisplayStyle' => static::DELIVERY_DISPLAY_STYLE_MULTISELECT,
            'deliveryOptionsBehavior' => static::DELIVERY_BEHAVIOR_MULTIPLE,
        );
    }
}
