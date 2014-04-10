<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc. All rights reserved.
 */

require_once 'include/SugarEmailAddress/SugarEmailAddress.php';

/**
 * @covers SugarEmailAddress
 */
class SugarEmailAddressTest extends Sugar_PHPUnit_Framework_TestCase
{
    /** @var SugarEmailAddress */
    private $ea;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    protected function setUp()
    {
        $this->ea = BeanFactory::getBean('EmailAddresses');
    }

    public static function tearDownAfterClass()
    {
        SugarTestHelper::tearDown();

        parent::tearDownAfterClass();
    }

    public function testAddressesAreZeroBased()
    {
        // make sure that initially there are no addresses
        $this->assertCount(0, $this->ea->addresses);

        $this->ea->addAddress('test@example.com');
        $this->ea->addAddress('test@example.com');

        // make sure duplicate address is replaced
        $this->assertCount(1, $this->ea->addresses);

        reset($this->ea->addresses);
        $this->assertEquals(0, key($this->ea->addresses), 'Email addresses is not a 0-based array');
    }

    public function testEmail1SavesWhenEmailIsEmpty()
    {
        $bean = BeanFactory::getBean('Accounts');
        $bean->email1 = 'a@a.com';
        $this->ea->handleLegacySave($bean);

        // Begin assertions
        $this->assertNotEmpty($this->ea->addresses);
        $this->assertArrayHasKey(0, $this->ea->addresses);
        $this->assertArrayHasKey('email_address', $this->ea->addresses[0]);
        $this->assertEquals('a@a.com', $this->ea->addresses[0]['email_address']);
    }
    
    public function testSavedEmailsPersistAfterSave()
    {
        $addresses = array(
            array('email_address' => 'a@a.com', 'primary_address' => true),
            array('email_address' => 'b@b.com'),
            array('email_address' => 'c@c.com'),
            array('email_address' => 'd@d.com'),
        );
        $bean = BeanFactory::getBean('Accounts');
        $bean->email = $addresses;
        $this->ea->handleLegacySave($bean);

        // Begin assertions
        $this->assertNotEmpty($this->ea->addresses);
        $this->assertEquals(4, count($this->ea->addresses));
        $this->assertArrayHasKey(0, $this->ea->addresses);
        $this->assertArrayHasKey('email_address', $this->ea->addresses[0]);
        $this->assertEquals('a@a.com', $this->ea->addresses[0]['email_address']);
        $this->assertArrayHasKey(3, $this->ea->addresses);
        $this->assertArrayHasKey('email_address', $this->ea->addresses[3]);
        $this->assertEquals('d@d.com', $this->ea->addresses[3]['email_address']);
    }
    
    public function testSaveUsesCorrectValues()
    {
        // Set values on the email address object for testing
        $test = array(
            array(
                'email_address' => 'a@a.com',
                'email_address_id' => null,
                'primary_address' => '1',
                'invalid_email' => '0',
                'opt_out' => '0',
                'reply_to_address' => '0',
            ),
            array(
                'email_address' => 'b@b.com',
                'email_address_id' => null,
                'primary_address' => '0',
                'invalid_email' => '0',
                'opt_out' => '0',
                'reply_to_address' => '0',
            ),
            array(
                'email_address' => 'c@c.com',
                'email_address_id' => null,
                'primary_address' => '0',
                'invalid_email' => '0',
                'opt_out' => '0',
                'reply_to_address' => '0',
            ),
            array(
                'email_address' => 'd@d.com',
                'email_address_id' => null,
                'primary_address' => '0',
                'invalid_email' => '0',
                'opt_out' => '0',
                'reply_to_address' => '0',
            ),
        );

        $expect = array(
            array(
                'email_address' => 'z@z.com',
                'primary_address' => '1',
                'reply_to_address' => '0',
                'invalid_email' => '0',
                'opt_out' => '0',
                'email_address_id' => null,
            ),
        );

        // Setup the test case
        $this->ea->fetchedAddresses = $this->ea->addresses = $test;
        $bean = BeanFactory::getBean('Contacts');
        $bean->email = $test;
        $bean->email1 = 'z@z.com';
        $bean->email2 = '';
        $this->ea->handleLegacySave($bean);

        // Expectation is that email1 will win
        $this->assertEquals($expect, $this->ea->addresses);
    }
}
