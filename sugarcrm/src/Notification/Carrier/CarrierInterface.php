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

namespace Sugarcrm\Sugarcrm\Notification\Carrier;

use Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\AddressTypeInterface;

interface CarrierInterface
{

    /**
     * @return TransportInterface
     */
    public function getTransport();

    /**
     * ToDo: decide 'label' or 'title'.
     * @return array(
     *      'label' => '', - full info, but short
     *      'url' => '', - url to event
     *      'subject' => '',- can be not full info, used for email, rss
     *      'text' => '', - full info can be long
     *      'html' => '', full info can be long
     *  );
     *
     */
    public function getMessageSignature();

    /**
     * @return AddressTypeInterface
     */
    public function getAddressType();

}
