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
require_once 'modules/CarrierEmail/Carrier.php';
require_once 'modules/CarrierEmail/Transport.php';

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
     * {@inheritdoc}
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test that getTransport() returns Transport for CarrierEmail and nothing else.
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
}
