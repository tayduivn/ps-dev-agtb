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

namespace Sugarcrm\SugarcrmTests\modules\CarrierSugar;

require_once 'modules/CarrierSugar/Carrier.php';
require_once 'modules/CarrierSugar/Transport.php';

use CarrierSugarCarrier;

/**
 * Test cases for CarrierSugarCarrier.
 */
class CarrierSugarCarrierTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var CarrierSugarCarrier
     */
    protected $carrier;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->carrier = new CarrierSugarCarrier();
    }

    /**
     * Test that getTransport() returns current object.
     */
    public function testGetTransport()
    {
        $this->assertInstanceOf('CarrierSugarTransport', $this->carrier->getTransport());
    }

    /**
     * Test that getMessageSignature() returns a correct message signature.
     */
    public function testGetMessageSignature()
    {
        $signature = array(
            'title' => '',
            'text' => '',
            'html' => '',
        );
        $this->assertEquals($signature, $this->carrier->getMessageSignature());
    }

    /**
     * Test that getAddressType() returns current object.
     */
    public function testGetAddressType()
    {
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Id', $this->carrier->getAddressType());
    }
}
