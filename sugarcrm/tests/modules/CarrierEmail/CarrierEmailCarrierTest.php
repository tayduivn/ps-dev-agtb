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

namespace Sugarcrm\SugarcrmTests\modules\CarrierEmail;

require_once 'modules/CarrierEmail/Carrier.php';
require_once 'modules/CarrierEmail/Transport.php';

use CarrierEmailCarrier;

/**
 * Test cases for CarrierEmailCarrier.
 */
class CarrierEmailCarrierTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var CarrierEmailCarrier
     */
    protected $carrier;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();
        $this->carrier = new CarrierEmailCarrier();
    }

    /**
     * Test that getTransport() returns current object.
     */
    public function testGetTransport()
    {
        $this->assertInstanceOf('CarrierEmailTransport', $this->carrier->getTransport());
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
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Notification\Carrier\AddressType\Email', $this->carrier->getAddressType());
    }

    /**
     * Should return correct options list.
     *
     * @covers CarrierEmailCarrier::getOptions
     */
    public function testGetOptions()
    {
        $this->assertEquals(
            array(
                'deliveryDisplayStyle' => 'multiselect',
                'deliveryBehavior' => 'multiple',
            ),
            $this->carrier->getOptions()
        );
    }
}
