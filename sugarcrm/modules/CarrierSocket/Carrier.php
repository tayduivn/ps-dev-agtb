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

use Sugarcrm\Sugarcrm\Notification\Carrier\CarrierInterface;

class CarrierSocketCarrier implements CarrierInterface
{

    /**
     * Get Transport to deliver messages to SocketServer.
     * @return \CarrierSocketTransport
     */
    public function getTransport()
    {
        return new CarrierSocketTransport();
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
        );
    }

    /**
     * @return \Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\AddressTypeInterface
     */
    public function getAddressType()
    {
        // TODO: Implement getAddressType() method.
    }
}
