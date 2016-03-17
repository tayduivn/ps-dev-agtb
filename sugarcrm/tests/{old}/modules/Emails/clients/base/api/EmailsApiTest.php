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

require_once 'modules/Emails/clients/base/api/EmailsApi.php';

/**
 * @coversDefaultClass EmailsApi
 * @group api
 * @group email
 */
class EmailsApiTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $api;
    protected $service;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->api = new EmailsApi();
        $this->service = SugarTestRestUtilities::getRestServiceMock();
    }

    protected function tearDown()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        parent::tearDown();
    }

    public function createProvider()
    {
        return array(
            array(
                array(
                    'name' => 'Sugar Email' . mt_rand(),
                    'state' => Email::EMAIL_STATE_DRAFT,
                    'assigned_user_id' => $GLOBALS['current_user']->id,
                ),
            ),
            array(
                array(
                    'name' => 'Sugar Email' . mt_rand(),
                    'state' => Email::EMAIL_STATE_ARCHIVED,
                    'assigned_user_id' => create_guid(),
                ),
            ),
            array(
                array(
                    'name' => 'Sugar Email' . mt_rand(),
                    'state' => Email::EMAIL_STATE_READY,
                    'assigned_user_id' => $GLOBALS['current_user']->id,
                ),
            ),
            array(
                array(
                    'name' => 'Sugar Email' . mt_rand(),
                    'state' => Email::EMAIL_STATE_SCHEDULED,
                    'assigned_user_id' => create_guid(),
                ),
            ),
        );
    }

    public function noStateChangeProvider()
    {
        return array(
            array(
                array(
                    'name' => 'SugarEmail' . mt_rand(),
                    'state' => Email::EMAIL_STATE_DRAFT,
                    'assigned_user_id' => create_guid(),
                ),
            ),
            array(
                array(
                    'name' => 'SugarEmail' . mt_rand(),
                    'state' => Email::EMAIL_STATE_SCHEDULED,
                    'assigned_user_id' => create_guid(),
                ),
            ),
            array(
                array(
                    'name' => 'SugarEmail' . mt_rand(),
                    'state' => Email::EMAIL_STATE_ARCHIVED,
                    'assigned_user_id' => create_guid(),
                ),
            ),
        );
    }

    public function invalidStateTransitionProvider()
    {
        return array(
            array(
                Email::EMAIL_STATE_ARCHIVED,
                Email::EMAIL_STATE_READY,
            ),
            array(
                Email::EMAIL_STATE_DRAFT,
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                Email::EMAIL_STATE_SCHEDULED,
                Email::EMAIL_STATE_DRAFT,
            ),
        );
    }

    /**
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::isValidStateTransition
     * @dataProvider createProvider
     * @param array $args
     */
    public function testCreateRecord(array $args)
    {
        $args['module'] = 'Emails';
        $result = $this->api->createRecord($this->service, $args);

        $this->assertNotEmpty($result);
        $this->assertArrayHasKey('id', $result);
        SugarTestEmailUtilities::setCreatedEmail($result['id']);

        $this->assertEquals($args['name'], $result['name']);
        $this->assertEquals($args['state'], $result['state']);
        $this->assertEquals($args['assigned_user_id'], $result['assigned_user_id']);
    }

    /**
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::isValidStateTransition
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testCreateRecord_StatusIsInvalid()
    {
        $args = array(
            'module' => 'Emails',
            'name' => 'Sugar Email' . mt_rand(),
            'state' => 'SomeBogusToState',
        );
        $this->api->createRecord($this->service, $args);
    }

    /**
     * @covers ::updateRecord
     * @covers ::updateBean
     * @dataProvider noStateChangeProvider
     * @param array $args
     */
    public function testUpdateRecord_NoStateChange(array $args)
    {
        $email = SugarTestEmailUtilities::createEmail('', array('state' => $args['state']));

        $args['module'] = 'Emails';
        $args['record'] = $email->id;
        $result = $this->api->updateRecord($this->service, $args);

        $this->assertNotEmpty($result);
        $this->assertEquals($email->id, $result['id']);
        $this->assertEquals($args['name'], $result['name']);
        $this->assertEquals($args['state'], $result['state']);
        $this->assertEquals($args['assigned_user_id'], $result['assigned_user_id']);
    }

    /**
     * @covers ::updateRecord
     * @covers ::updateBean
     * @covers ::isValidStateTransition
     * @dataProvider invalidStateTransitionProvider
     * @param string $fromState
     * @param string $toState
     * @expectedException SugarApiExceptionInvalidParameter
     */
    public function testUpdateRecord_StateTransitionIsInvalid($fromState, $toState)
    {
        $email = SugarTestEmailUtilities::createEmail('', array('state' => $fromState));

        $args = array(
            'module' => 'Emails',
            'record' => $email->id,
            'state' => $toState,
        );
        $this->api->updateRecord($this->service, $args);
    }

    /**
     * Existing Notes records cannot be used as attachments.
     *
     * @covers ::linkRelatedRecords
     */
    public function testLinkRelatedRecords()
    {
        $relateRecordApi = $this->getMockBuilder('RelateRecordApi')
            ->disableOriginalConstructor()
            ->setMethods(array('createRelatedLinks'))
            ->getMock();
        $relateRecordApi->expects($this->never())
            ->method('createRelatedLinks');

        $api = $this->getMockBuilder('EmailsApi')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelateRecordApi'))
            ->getMock();
        $api->method('getRelateRecordApi')
            ->willReturn($relateRecordApi);

        $email = BeanFactory::newBean('Emails');
        $email->id = create_guid();
        $args = array(
            'attachments' => array(
                'id' => create_guid(),
                'email_id' => $email->id,
                'email_type' => $email->module_name,
                'team_id' => $email->team_id,
                'team_set_id' => $email->team_set_id,
            ),
        );

        SugarTestReflection::callProtectedMethod($api, 'linkRelatedRecords', array($this->service, $email, $args));
    }

    /**
     * The sender cannot be unlinked. The sender can only be replaced.
     *
     * @covers ::linkRelatedRecords
     */
    public function testUnlinkRelatedRecords()
    {
        $relateRecordApi = $this->getMockBuilder('RelateRecordApi')
            ->disableOriginalConstructor()
            ->setMethods(array('deleteRelatedLink'))
            ->getMock();
        $relateRecordApi->expects($this->never())
            ->method('deleteRelatedLink');

        $api = $this->getMockBuilder('EmailsApi')
            ->disableOriginalConstructor()
            ->setMethods(array('getRelateRecordApi'))
            ->getMock();
        $api->method('getRelateRecordApi')
            ->willReturn($relateRecordApi);

        $email = BeanFactory::newBean('Emails');
        $email->id = create_guid();
        $args = array(
            'accounts_from' => array(
                create_guid(),
            ),
            'contacts_from' => array(
                create_guid(),
            ),
            'email_addresses_from' => array(
                create_guid(),
            ),
            'leads_from' => array(
                create_guid(),
            ),
            'prospects_from' => array(
                create_guid(),
            ),
            'users_from' => array(
                create_guid(),
            ),
        );

        SugarTestReflection::callProtectedMethod($api, 'unlinkRelatedRecords', array($this->service, $email, $args));
    }

    /**
     * Create related record arguments for email_addresses_from, email_addresses_to, email_address_cc, and
     * email_addresses_bcc are moved to the "add" arguments when the email address is a duplicate.
     *
     * @covers ::getRelatedRecordArguments
     */
    public function testGetRelatedRecordArguments()
    {
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address3 = 'address-' . create_guid() . '@example.com';

        $email = BeanFactory::newBean('Emails');
        $args = array(
            'email_addresses_from' => array(
                'create' => array(
                    // Using an existing email address.
                    array(
                        'email_address' => $address1->email_address,
                    ),
                ),
            ),
            'email_addresses_to' => array(
                'create' => array(
                    // Creating a new email address.
                    array(
                        'email_address' => $address3,
                    ),
                    // Using an existing email address.
                    array(
                        'email_address' => $address1->email_address,
                    ),
                ),
            ),
            'email_addresses_cc' => array(
                'add' => array(
                    // Using an existing email address.
                    $address1->id,
                ),
                'create' => array(
                    // Using an existing email address.
                    array(
                        'email_address' => $address2->email_address,
                    ),
                ),
            ),
            'email_addresses_bcc' => array(
                'add' => array(
                    // Using an existing email address.
                    $address2->id,
                ),
            ),
        );

        $expected = array(
            'email_addresses_from' => array(
                // Moved to add.
                array(
                    'email_address' => $address1->email_address,
                    'id' => $address1->id,
                ),
            ),
            'email_addresses_to' => array(
                // Moved to add.
                array(
                    'email_address' => $address1->email_address,
                    'id' => $address1->id,
                ),
            ),
            'email_addresses_cc' => array(
                // Remained in add.
                $address1->id,
                // Moved to add.
                array(
                    'email_address' => $address2->email_address,
                    'id' => $address2->id,
                ),
            ),
            'email_addresses_bcc' => array(
                // Remained in add.
                $address2->id,
            ),
        );
        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'getRelatedRecordArguments',
            array($email, $args, 'add')
        );
        $this->assertSame($expected, $actual);

        $expected = array(
            'email_addresses_to' => array(
                // Remained in create.
                array(
                    'email_address' => $address3,
                ),
            ),
        );
        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'getRelatedRecordArguments',
            array($email, $args, 'create')
        );
        $this->assertSame($expected, $actual);

        $actual = SugarTestReflection::callProtectedMethod(
            $this->api,
            'getRelatedRecordArguments',
            array($email, $args, 'delete')
        );
        $this->assertEmpty($actual);

        SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress($address3);
    }
}
