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
use Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Id as AddressTypeId;

require_once 'modules/CarrierSugar/Transport.php';

class CarrierSugarCarrier implements CarrierInterface
{

    /**
     * Get Transport to deliver messages to SocketServer.
     * @return \CarrierSugarTransport
     */
    public function getTransport()
    {
        return new CarrierSugarTransport();
    }

    /**
     * Messages to SocketServer have only 'title' and 'text'.
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
     * Return carrier address type(AddressTypeId)
     *
     * @return AddressTypeId
     */
    public function getAddressType()
    {
        return new AddressTypeId();
    }
}
