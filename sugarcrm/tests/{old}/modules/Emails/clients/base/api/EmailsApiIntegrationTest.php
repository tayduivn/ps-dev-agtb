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

require_once 'tests/{old}/modules/Emails/clients/base/api/EmailsApiIntegrationTestCase.php';

/**
 * @coversDefaultClass EmailsApi
 */
class EmailsApiIntegrationTest extends EmailsApiIntegrationTestCase
{
    private static $systemConfiguration;
    private static $overrideConfig;
    private static $userConfig;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$systemConfiguration = OutboundEmailConfigurationTestHelper::getSystemConfiguration();
        static::$overrideConfig = OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
            $GLOBALS['current_user']->id
        );
        static::$userConfig = OutboundEmailConfigurationTestHelper::createUserOutboundEmailConfiguration(
            $GLOBALS['current_user']->id
        );
    }

    public static function tearDownAfterClass()
    {
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestProspectUtilities::removeAllCreatedProspects();
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        parent::setUp();

        // By default, system configuration is not used, but can be safely overwritten by any test if needed
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);
    }

    /**
     * When creating an archived email, any sender and recipients are allowed.
     *
     * @covers ::createRecord
     * @covers ::isValidStateTransition
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testCreateArchivedEmail()
    {
        $user = SugarTestUserUtilities::createAnonymousUser();
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();

        $args = [
            'state' => Email::STATE_ARCHIVED,
            'from_link' => [
                'create' => [
                    $this->createEmailParticipant($user),
                ],
            ],
            'to_link' => [
                'create' => [
                    $this->createEmailParticipant($contact),
                ],
            ],
            'cc_link' => [
                'create' => [
                    $this->createEmailParticipant($account),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_ARCHIVED, $record['state'], 'Should be archived');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from_link',
                'parent' => [
                    'type' => $user->getModuleName(),
                    'id' => $user->id,
                    'name' => $user->name,
                ],
                'parent_name' => $user->name,
                'email_addresses' => [
                    'id' => $user->emailAddress->getGuid($user->email1),
                    'email_address' => $user->email1,
                ],
                'email_address_id' => $user->emailAddress->getGuid($user->email1),
                'email_address' => $user->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender did not match expectations');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to_link',
                'parent' => [
                    'type' => $contact->getModuleName(),
                    'id' => $contact->id,
                    'name' => $contact->name,
                ],
                'parent_name' => $contact->name,
                'email_addresses' => [
                    'id' => $contact->emailAddress->getGuid($contact->email1),
                    'email_address' => $contact->email1,
                ],
                'email_address_id' => $contact->emailAddress->getGuid($contact->email1),
                'email_address' => $contact->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'cc_link',
                'parent' => [
                    'type' => $account->getModuleName(),
                    'id' => $account->id,
                    'name' => $account->name,
                ],
                'parent_name' => $account->name,
                'email_addresses' => [
                    'id' => $account->emailAddress->getGuid($account->email1),
                    'email_address' => $account->email1,
                ],
                'email_address_id' => $account->emailAddress->getGuid($account->email1),
                'email_address' => $account->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'cc');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals("{$user->name} <{$user->email1}>", $bean->from_addr_name);
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $bean->to_addrs_names);
        $this->assertEquals("{$account->name} <{$account->email1}>", $bean->cc_addrs_names);
    }

    /**
     * When creating a draft, the current user is always the sender, any recipients are allowed, and the specified
     * configuration is persisted.
     *
     * @covers ::createRecord
     * @covers ::isValidStateTransition
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testCreateDraftEmail()
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $lead = SugarTestLeadUtilities::createLead();
        $leadEmailAddress = BeanFactory::retrieveBean('EmailAddresses', $lead->emailAddress->getGuid($lead->email1));

        $args = [
            'state' => Email::STATE_DRAFT,
            'outbound_email_id' => static::$overrideConfig->id,
            'to_link' => [
                'create' => [
                    $this->createEmailParticipant($lead, $leadEmailAddress),
                ],
            ],
            // The same email participant can appear in multiple roles.
            'bcc_link' => [
                'create' => [
                    $this->createEmailParticipant($GLOBALS['current_user']),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_DRAFT, $record['state'], 'Should be a draft');
        $this->assertSame(
            static::$overrideConfig->id,
            $record['outbound_email_id'],
            'Should use the specified configuration'
        );

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from_link',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => $address->getGuid(static::$overrideConfig->email_address),
                    'email_address' => static::$overrideConfig->email_address,
                ],
                'email_address_id' => $address->getGuid(static::$overrideConfig->email_address),
                'email_address' => static::$overrideConfig->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to_link',
                'parent' => [
                    'type' => $lead->getModuleName(),
                    'id' => $lead->id,
                    'name' => $lead->name,
                ],
                'parent_name' => $lead->name,
                'email_addresses' => [
                    'id' => $lead->emailAddress->getGuid($lead->email1),
                    'email_address' => $lead->email1,
                ],
                'email_address_id' => $leadEmailAddress->id,
                'email_address' => $lead->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'bcc_link',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => '',
                    'email_address' => '',
                ],
                'email_address_id' => '',
                'email_address' => '',
            ],
        ];
        $collection = $this->getCollection($record['id'], 'bcc');
        $this->assertRecords($expected, $collection, 'The BCC field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <" . static::$overrideConfig->email_address . ">",
            $bean->from_addr_name
        );
        $this->assertEquals("{$lead->name} <{$lead->email1}>", $bean->to_addrs_names);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $bean->bcc_addrs_names
        );
    }

    /**
     * When updating a draft, the sender always remains the current user and the recipients and configuration may
     * change.
     *
     * @covers ::updateRecord
     * @covers ::isValidStateTransition
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testUpdateDraftEmail()
    {
        $prospect = SugarTestProspectUtilities::createProspect();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $args = [
            'state' => Email::STATE_DRAFT,
            'outbound_email_id' => static::$overrideConfig->id,
            'cc_link' => [
                'create' => [
                    $this->createEmailParticipant($prospect),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_DRAFT, $record['state'], 'Should be draft after create');
        $this->assertSame(
            static::$overrideConfig->id,
            $record['outbound_email_id'],
            'The configuration did not match expectations after create'
        );

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from_link',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                    'email_address' => static::$overrideConfig->email_address,
                ],
                'email_address_id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                'email_address' => static::$overrideConfig->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'cc_link',
                'parent' => [
                    'type' => $prospect->getModuleName(),
                    'id' => $prospect->id,
                    'name' => $prospect->name,
                ],
                'parent_name' => $prospect->name,
                'email_addresses' => [
                    'id' => '',
                    'email_address' => '',
                ],
                'email_address_id' => '',
                'email_address' => '',
            ],
        ];
        $collection = $this->getCollection($record['id'], 'cc');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations after create');

        $args = [
            'outbound_email_id' => static::$userConfig->id,
            'to_link' => [
                'create' => [
                    $this->createEmailParticipant(null, $address),
                ],
            ],
            // This should patch the email address onto the existing row for the prospect.
            'cc_link' => [
                'create' => [
                    $this->createEmailParticipant(
                        $prospect,
                        BeanFactory::retrieveBean('EmailAddresses', $prospect->emailAddress->getGuid($prospect->email1))
                    ),
                ],
            ],
        ];
        $record = $this->updateRecord($record['id'], $args);
        $this->assertSame(Email::STATE_DRAFT, $record['state'], 'Should be draft after update');
        $this->assertSame(
            static::$userConfig->id,
            $record['outbound_email_id'],
            'The configuration should have changed'
        );

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from_link',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => $this->getEmailAddressId(static::$userConfig->email_address),
                    'email_address' => static::$userConfig->email_address,
                ],
                'email_address_id' => $this->getEmailAddressId(static::$userConfig->email_address),
                'email_address' => static::$userConfig->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords(
            $expected,
            $collection,
            'The sender should still be the current user, but with the email address assigned to the user configuration'
        );

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to_link',
                'parent' => [],
                'parent_name' => '',
                'email_addresses' => [
                    'id' => $address->id,
                    'email_address' => $address->email_address,
                ],
                'email_address_id' => $address->id,
                'email_address' => $address->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations after update');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'cc_link',
                'parent' => [
                    'type' => $prospect->getModuleName(),
                    'id' => $prospect->id,
                    'name' => $prospect->name,
                ],
                'parent_name' => $prospect->name,
                'email_addresses' => [
                    'id' => $prospect->emailAddress->getGuid($prospect->email1),
                    'email_address' => $prospect->email1,
                ],
                'email_address_id' => $prospect->emailAddress->getGuid($prospect->email1),
                'email_address' => $prospect->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'cc');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations after update');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <" . static::$userConfig->email_address . ">",
            $bean->from_addr_name
        );
        $this->assertEquals($address->email_address, $bean->to_addrs_names);
        $this->assertEquals("{$prospect->name} <{$prospect->email1}>", $bean->cc_addrs_names);
    }

    /**
     * When sending a previously saved draft, the sender always remains the current user, the recipients and
     * configuration may change, and the email is ultimately archived.
     *
     * @covers ::updateRecord
     * @covers ::isValidStateTransition
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::unlinkRelatedRecords
     * @covers ::sendEmail
     * @covers Email::sendEmail
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testSendDraftEmail()
    {
        $account1 = SugarTestAccountUtilities::createAccount();
        $account2 = SugarTestAccountUtilities::createAccount();
        $lead = SugarTestLeadUtilities::createLead();

        $args = [
            'state' => Email::STATE_DRAFT,
            'to_link' => [
                'create' => [
                    $this->createEmailParticipant($account1),
                    $this->createEmailParticipant($lead),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_DRAFT, $record['state'], 'Should be draft after create');
        $this->assertTrue(empty($record['outbound_email_id']), 'No configuration was specified during create');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from_link',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => '',
                    'email_address' => '',
                ],
                'email_address_id' => '',
                'email_address' => '',
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to_link',
                'parent' => [
                    'type' => $account1->getModuleName(),
                    'id' => $account1->id,
                    'name' => $account1->name,
                ],
                'parent_name' => $account1->name,
                'email_addresses' => [
                    'id' => '',
                    'email_address' => '',
                ],
                'email_address_id' => '',
                'email_address' => '',
            ],
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to_link',
                'parent' => [
                    'type' => $lead->getModuleName(),
                    'id' => $lead->id,
                    'name' => $lead->name,
                ],
                'parent_name' => $lead->name,
                'email_addresses' => [
                    'id' => '',
                    'email_address' => '',
                ],
                'email_address_id' => '',
                'email_address' => '',
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations after create');

        // Need to extract the data that represents the EmailParticipants record for $account1, so we can use its ID.
        $epAccount1 = array_filter($collection['records'], function ($ep) {
            return $ep['parent']['type'] === 'Accounts';
        });

        $args = [
            'state' => Email::STATE_READY,
            'to_link' => [
                'create' => [
                    $this->createEmailParticipant($account2),
                ],
                'delete' => [
                    $epAccount1[0]['id'],
                ],
            ],
        ];
        $record = $this->updateRecord($record['id'], $args);
        $this->assertSame(Email::STATE_ARCHIVED, $record['state'], 'Should be archived after sending');
        $this->assertSame(
            static::$overrideConfig->id,
            $record['outbound_email_id'],
            "Should use the user's system override configuration"
        );

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from_link',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                    'email_address' => static::$overrideConfig->email_address,
                ],
                'email_address_id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                'email_address' => static::$overrideConfig->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should not have changed');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to_link',
                'parent' => [
                    'type' => $account2->getModuleName(),
                    'id' => $account2->id,
                    'name' => $account2->name,
                ],
                'parent_name' => $account2->name,
                'email_addresses' => [
                    'id' => $account2->emailAddress->getGuid($account2->email1),
                    'email_address' => $account2->email1,
                ],
                'email_address_id' => $account2->emailAddress->getGuid($account2->email1),
                'email_address' => $account2->email1,
            ],
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to_link',
                'parent' => [
                    'type' => $lead->getModuleName(),
                    'id' => $lead->id,
                    'name' => $lead->name,
                ],
                'parent_name' => $lead->name,
                'email_addresses' => [
                    'id' => $lead->emailAddress->getGuid($lead->email1),
                    'email_address' => $lead->email1,
                ],
                'email_address_id' => $lead->emailAddress->getGuid($lead->email1),
                'email_address' => $lead->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations after sending');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <" . static::$overrideConfig->email_address . ">",
            $bean->from_addr_name
        );
        $this->assertEquals(
            "{$lead->name} <{$lead->email1}>, {$account2->name} <{$account2->email1}>",
            $bean->to_addrs_names
        );
    }

    /**
     * When creating an email and immediately sending it, the current user is always the sender, any recipients are
     * allowed, the configuration that is used is persisted, and the email is ultimately archived.
     *
     * @covers ::createRecord
     * @covers ::isValidStateTransition
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::sendEmail
     * @covers Email::sendEmail
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testCreateAndSendEmail()
    {
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(2);
        $contact1 = SugarTestContactUtilities::createContact();
        $contact2 = SugarTestContactUtilities::createContact();

        $args = [
            'state' => Email::STATE_READY,
            'to_link' => [
                'create' => [
                    $this->createEmailParticipant($contact1),
                    $this->createEmailParticipant($contact2),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_ARCHIVED, $record['state'], 'Should be archived');

        $this->assertSame(
            static::$systemConfiguration->id,
            $record['outbound_email_id'],
            'Should use the system configuration'
        );

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'from_link',
                'parent' => [
                    'type' => $GLOBALS['current_user']->getModuleName(),
                    'id' => $GLOBALS['current_user']->id,
                    'name' => $GLOBALS['current_user']->name,
                ],
                'parent_name' => $GLOBALS['current_user']->name,
                'email_addresses' => [
                    'id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                    'email_address' => static::$overrideConfig->email_address,
                ],
                'email_address_id' => $this->getEmailAddressId(static::$overrideConfig->email_address),
                'email_address' => static::$overrideConfig->email_address,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = [
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to_link',
                'parent' => [
                    'type' => $contact1->getModuleName(),
                    'id' => $contact1->id,
                    'name' => $contact1->name,
                ],
                'parent_name' => $contact1->name,
                'email_addresses' => [
                    'id' => $contact1->emailAddress->getGuid($contact1->email1),
                    'email_address' => $contact1->email1,
                ],
                'email_address_id' => $contact1->emailAddress->getGuid($contact1->email1),
                'email_address' => $contact1->email1,
            ],
            [
                '_module' => 'EmailParticipants',
                '_link' => 'to_link',
                'parent' => [
                    'type' => $contact2->getModuleName(),
                    'id' => $contact2->id,
                    'name' => $contact2->name,
                ],
                'parent_name' => $contact2->name,
                'email_addresses' => [
                    'id' => $contact2->emailAddress->getGuid($contact2->email1),
                    'email_address' => $contact2->email1,
                ],
                'email_address_id' => $contact2->emailAddress->getGuid($contact2->email1),
                'email_address' => $contact2->email1,
            ],
        ];
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <" . static::$overrideConfig->email_address . ">",
            $bean->from_addr_name
        );
        $to = "{$contact1->name} <{$contact1->email1}>, {$contact2->name} <{$contact2->email1}>";
        $this->assertEquals($to, $bean->to_addrs_names);
    }

    /**
     * When replying to an email, the reply_to_id is set on the new Email record being created. The reply_to_id must
     * refer to an existing Email Record in the 'Archived' state. If successfully sent, that Replied-To Email record's
     * reply_to status is set to true.
     *
     * @covers ::createRecord
     * @covers ::isValidStateTransition
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::sendEmail
     * @covers Email::sendEmail
     */
    public function testCreateAndSendReplyEmail()
    {
        $emailValues = array(
            'state' => Email::STATE_ARCHIVED,
            'reply_to_status' => false,
        );
        $repliedToEmail = SugarTestEmailUtilities::createEmail('', $emailValues);

        $contact = SugarTestContactUtilities::createContact();
        $args = [
            'state' => Email::STATE_READY,
            'reply_to_id' => $repliedToEmail->id,
            'to_link' => [
                'create' => [
                    $this->createEmailParticipant($contact),
                ],
            ],
        ];
        $record = $this->createRecord($args);
        $this->assertSame(Email::STATE_ARCHIVED, $record['state'], 'Should be archived');
        $this->assertSame($repliedToEmail->id, $record['reply_to_id'], 'Should contain id of Email being replied to');

        $repliedToEmail = $repliedToEmail->retrieve($repliedToEmail->id);
        $this->assertEquals('1', $repliedToEmail->reply_to_status, 'reply_to_status value should be True');
    }
}
