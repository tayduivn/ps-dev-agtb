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

require_once 'modules/EmailAddresses/clients/base/api/EmailAddressesApi.php';

/**
 * @coversDefaultClass EmailAddressesApi
 */
class EmailAddressesApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $service;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    public static function tearDownAfterClass()
    {
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    /**
     * @covers ::createBean
     * @expectedException SugarApiExceptionMissingParameter
     */
    public function testCreateBean_CannotCreateWithoutAnEmailAddress()
    {
        $api = new EmailAddressesApi();
        $args = array(
            'module' => 'EmailAddresses',
        );
        $api->createBean($this->service, $args);
    }

    /**
     * @covers createBean
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testCreateBean_CannotCreateWithAnInvalidEmailAddress()
    {
        $api = new EmailAddressesApi();
        $args = array(
            'module' => 'EmailAddresses',
            'email_address' => 'a',
        );
        $api->createBean($this->service, $args);
    }

    /**
     * @covers createBean
     */
    public function testCreateBean_CreateNewEmailAddress()
    {
        $address = 'address-' . create_guid() . '@example.com';

        $ea = new SugarEmailAddress();
        $this->assertEmpty($ea->getGuid($address), "{$address} should not already exist");

        $api = $this->getMockBuilder('EmailAddressesApi')
            ->setMethods(array('updateRecord'))
            ->getMock();
        $api->expects($this->never())->method('updateRecord');

        $args = array(
            'module' => 'EmailAddresses',
            'email_address' => $address,
            'email_address_caps' => 'foo@bar.com',
        );
        $bean = $api->createBean($this->service, $args);

        $this->assertNotEmpty($bean->id);
        $this->assertSame($address, $bean->email_address);
        $this->assertSame(strtoupper($address), $bean->email_address_caps);
    }

    /**
     * @covers createBean
     */
    public function testCreateBean_ReturnExistingEmailAddressWithoutMakingChanges()
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $api = new EmailAddressesApi();
        $args = array(
            'module' => 'EmailAddresses',
            'email_address' => $address->email_address,
            'invalid_email' => true,
        );
        $bean = $api->createBean($this->service, $args);

        $this->assertSame($address->id, $bean->id);
        $this->assertSame($address->email_address, $bean->email_address);
        $this->assertFalse($address->invalid_email);
    }

    /**
     * @covers updateRecord
     */
    public function testUpdateRecord_EmailAddressDoesNotChange()
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $this->assertFalse($address->invalid_email);

        $api = new EmailAddressesApi();
        $args = array(
            'module' => 'EmailAddresses',
            'record' => $address->id,
            'email_address' => 'foo@bar.com',
            'invalid_email' => true,
        );
        $record = $api->updateRecord($this->service, $args);

        $this->assertSame($address->email_address, $record['email_address']);
        $this->assertTrue($record['invalid_email']);
    }
}
