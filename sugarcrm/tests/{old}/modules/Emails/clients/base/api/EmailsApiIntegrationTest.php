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

require_once 'tests/{old}/modules/Emails/clients/base/api/EmailsApiIntegrationTestCase.php';
require_once 'tests/{old}/modules/OutboundEmailConfiguration/OutboundEmailConfigurationTestHelper.php';

/**
 * @coversDefaultClass EmailsApi
 */
class EmailsApiIntegrationTest extends EmailsApiIntegrationTestCase
{
    protected static $systemConfig;
    protected static $overrideConfig;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        OutboundEmailConfigurationTestHelper::backupExistingConfigurations();
        static::$systemConfig = OutboundEmailConfigurationTestHelper::createSystemOutboundEmailConfiguration();
        static::$overrideConfig = OutboundEmailConfigurationTestHelper::createSystemOverrideOutboundEmailConfiguration(
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
        OutboundEmailConfigurationTestHelper::restoreExistingConfigurations();
        parent::tearDownAfterClass();
    }

    public function createArchivedEmailProvider()
    {
        return array(
            array('accounts_from'),
            array('contacts_from'),
            array('email_addresses_from'),
            array('leads_from'),
            array('prospects_from'),
            array('users_from'),
        );
    }

    /**
     * When creating an archived email, any sender and recipients are allowed.
     *
     * @dataProvider createArchivedEmailProvider
     * @covers ::createRecord
     * @covers ::isValidStateTransition
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     * @param string $fromLink
     */
    public function testCreateArchivedEmail($fromLink)
    {
        $from = $this->createRhsBean($fromLink);
        $contact = $this->createRhsBean('contacts_to');
        $account = $this->createRhsBean('accounts_cc');

        $args = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            $fromLink => array(
                'add' => array($from->id),
            ),
            'contacts_to' => array(
                'add' => array($contact->id),
            ),
            'accounts_cc' => array(
                'add' => array($account->id),
            ),
        );
        $record = $this->createRecord($args);
        $this->assertSame(Email::EMAIL_STATE_ARCHIVED, $record['state'], 'Should be archived');

        $expected = array(
            array(
                '_module' => $this->getRhsModule($fromLink),
                '_link' => $fromLink,
                'id' => $from->id,
                'email_address_used' => $from->email1,
                'date_modified' => $this->getIsoTimestamp($from->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender did not match expectations');

        $expected = array(
            array(
                '_module' => 'Contacts',
                '_link' => 'contacts_to',
                'id' => $contact->id,
                'email_address_used' => $contact->email1,
                'date_modified' => $this->getIsoTimestamp($contact->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $expected = array(
            array(
                '_module' => 'Accounts',
                '_link' => 'accounts_cc',
                'id' => $account->id,
                'email_address_used' => $account->email1,
                'date_modified' => $this->getIsoTimestamp($account->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'cc');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $expectedFrom = ($from instanceof EmailAddress) ? $from->email_address : "{$from->name} <{$from->email1}>";
        $this->assertEquals($expectedFrom, $bean->from_addr_name);
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $bean->to_addrs_names);
        $this->assertEquals("{$account->name} <{$account->email1}>", $bean->cc_addrs_names);
    }

    /**
     * When updating an archived email, the sender and recipients cannot change.
     *
     * @covers ::updateRecord
     * @covers ::isValidStateTransition
     * @covers ::getRelatedRecordArguments
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testUpdateArchivedEmail()
    {
        $contact = $this->createRhsBean('contacts_from');
        $lead = $this->createRhsBean('leads_cc');

        $args = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            'contacts_from' => array(
                'add' => array($contact->id),
            ),
            'users_to' => array(
                'add' => array($GLOBALS['current_user']->id),
            ),
        );
        $record = $this->createRecord($args);
        $this->assertSame(Email::EMAIL_STATE_ARCHIVED, $record['state'], 'Should be archived after create');

        $expected = array(
            array(
                '_module' => 'Contacts',
                '_link' => 'contacts_from',
                'id' => $contact->id,
                'email_address_used' => $contact->email1,
                'date_modified' => $this->getIsoTimestamp($contact->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender does not match expectations after create');

        $expected = array(
            array(
                '_module' => 'Users',
                '_link' => 'users_to',
                'id' => $GLOBALS['current_user']->id,
                'email_address_used' => $GLOBALS['current_user']->email1,
                'date_modified' => $this->getIsoTimestamp($GLOBALS['current_user']->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field does not match expectations after create');

        $args = array(
            'email_addresses_from' => array(
                'create' => array(
                    'email_address' => 'myname@mydomain.com',
                ),
            ),
            'leads_cc' => array(
                'add' => array($lead->id),
            ),
        );
        $record = $this->updateRecord($record['id'], $args);
        $this->assertSame(Email::EMAIL_STATE_ARCHIVED, $record['state'], 'Should be archived after update');

        $expected = array(
            array(
                '_module' => 'Contacts',
                '_link' => 'contacts_from',
                'id' => $contact->id,
                'email_address_used' => $contact->email1,
                'date_modified' => $this->getIsoTimestamp($contact->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should not have changed');

        $expected = array(
            array(
                '_module' => 'Users',
                '_link' => 'users_to',
                'id' => $GLOBALS['current_user']->id,
                'email_address_used' => $GLOBALS['current_user']->email1,
                'date_modified' => $this->getIsoTimestamp($GLOBALS['current_user']->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field should not have changed');

        //FIXME: The following assertion fails because we do not yet prevent changes to the recipient links on updates
        // of archived emails.
        //$collection = $this->getCollection($record['id'], 'cc');
        //$this->assertEmpty($collection['records'], 'The CC field should not have changed');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $bean->from_addr_name);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $bean->to_addrs_names
        );
        //FIXME: Not asserting the CC field for the same reason as above.
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
        $lead = $this->createRhsBean('leads_to');

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'outbound_email_id' => static::$systemConfig->id,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'email_addresses_from' => array(
                'create' => array(
                    'email_address' => 'myname@mycompany.com',
                ),
            ),
            'leads_to' => array(
                'add' => array(
                    array(
                        'id' => $lead->id,
                        'email_address' => $lead->email1,
                    ),
                ),
            ),
            // The same email participant can appear in multiple roles.
            'users_bcc' => array(
                'add' => array($GLOBALS['current_user']->id),
            ),
        );
        $record = $this->createRecord($args);
        $this->assertSame(Email::EMAIL_STATE_DRAFT, $record['state'], 'Should be a draft');
        $this->assertSame(
            static::$systemConfig->id,
            $record['outbound_email_id'],
            'Should use the specified configuration'
        );

        $expected = array(
            array(
                '_module' => 'Users',
                '_link' => 'users_from',
                'id' => $GLOBALS['current_user']->id,
                'email_address_used' => null,
                'date_modified' => $this->getIsoTimestamp($GLOBALS['current_user']->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = array(
            array(
                '_module' => 'Leads',
                '_link' => 'leads_to',
                'id' => $lead->id,
                'email_address_used' => $lead->email1,
                'date_modified' => $this->getIsoTimestamp($lead->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $expected = array(
            array(
                '_module' => 'Users',
                '_link' => 'users_bcc',
                'id' => $GLOBALS['current_user']->id,
                'email_address_used' => null,
                'date_modified' => $this->getIsoTimestamp($GLOBALS['current_user']->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'bcc');
        $this->assertRecords($expected, $collection, 'The BCC field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
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
        $prospect = $this->createRhsBean('prospects_cc');
        $user = $this->createRhsBean('users_from');
        $address = $this->createRhsBean('email_addresses_to');

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'outbound_email_id' => static::$systemConfig->id,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'prospects_cc' => array(
                'add' => array($prospect->id),
            ),
        );
        $record = $this->createRecord($args);
        $this->assertSame(Email::EMAIL_STATE_DRAFT, $record['state'], 'Should be draft after create');
        $this->assertSame(
            static::$systemConfig->id,
            $record['outbound_email_id'],
            'The configuration did not match expectations after create'
        );

        $expected = array(
            array(
                '_module' => 'Prospects',
                '_link' => 'prospects_cc',
                'id' => $prospect->id,
                'email_address_used' => null,
                'date_modified' => $this->getIsoTimestamp($prospect->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'cc');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations after create');

        $args = array(
            'outbound_email_id' => static::$overrideConfig->id,
            'users_from' => array(
                'add' => array($user->id),
            ),
            'email_addresses_to' => array(
                'add' => array($address->id),
            ),
            'prospects_cc' => array(
                'add' => array(
                    array(
                        'id' => $prospect->id,
                        'email_address' => $prospect->email1,
                    ),
                ),
            ),
        );
        $record = $this->updateRecord($record['id'], $args);
        $this->assertSame(Email::EMAIL_STATE_DRAFT, $record['state'], 'Should be draft after update');
        $this->assertSame(
            static::$overrideConfig->id,
            $record['outbound_email_id'],
            'The configuration should not have changed'
        );

        $expected = array(
            array(
                '_module' => 'Users',
                '_link' => 'users_from',
                'id' => $GLOBALS['current_user']->id,
                'email_address_used' => null,
                'date_modified' => $this->getIsoTimestamp($GLOBALS['current_user']->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should not have changed');

        $expected = array(
            array(
                '_module' => 'EmailAddresses',
                '_link' => 'email_addresses_to',
                'id' => $address->id,
                'email_address_used' => $address->email_address,
                'date_modified' => $this->getIsoTimestamp($address->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations after update');

        $expected = array(
            array(
                '_module' => 'Prospects',
                '_link' => 'prospects_cc',
                'id' => $prospect->id,
                'email_address_used' => $prospect->email1,
                'date_modified' => $this->getIsoTimestamp($prospect->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'cc');
        $this->assertRecords($expected, $collection, 'The CC field did not match expectations after update');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
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
        $account1 = $this->createRhsBean('accounts_to');
        $account2 = $this->createRhsBean('accounts_to');
        $lead = $this->createRhsBean('leads_to');

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'accounts_to' => array(
                'add' => array($account1->id),
            ),
            'leads_to' => array(
                'add' => array($lead->id),
            ),
        );
        $record = $this->createRecord($args);
        $this->assertSame(Email::EMAIL_STATE_DRAFT, $record['state'], 'Should be draft after create');
        $this->assertEmpty($record['outbound_email_id'], 'No configuration was specified during create');

        $expected = array(
            array(
                '_module' => 'Users',
                '_link' => 'users_from',
                'id' => $GLOBALS['current_user']->id,
                'email_address_used' => null,
                'date_modified' => $this->getIsoTimestamp($GLOBALS['current_user']->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = array(
            array(
                '_module' => 'Accounts',
                '_link' => 'accounts_to',
                'id' => $account1->id,
                'email_address_used' => null,
                'date_modified' => $this->getIsoTimestamp($account1->date_modified),
            ),
            array(
                '_module' => 'Leads',
                '_link' => 'leads_to',
                'id' => $lead->id,
                'email_address_used' => null,
                'date_modified' => $this->getIsoTimestamp($lead->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations after create');

        $args = array(
            'state' => Email::EMAIL_STATE_READY,
            'accounts_to' => array(
                'add' => array($account2->id),
                'delete' => array($account1->id),
            ),
        );
        $record = $this->updateRecord($record['id'], $args);
        $this->assertSame(Email::EMAIL_STATE_ARCHIVED, $record['state'], 'Should be archived after sending');
        $this->assertSame(
            static::$overrideConfig->id,
            $record['outbound_email_id'],
            "Should use the user's configuration"
        );

        $expected = array(
            array(
                '_module' => 'Users',
                '_link' => 'users_from',
                'id' => $GLOBALS['current_user']->id,
                'email_address_used' => $GLOBALS['current_user']->email1,
                'date_modified' => $this->getIsoTimestamp($GLOBALS['current_user']->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should not have changed');

        $expected = array(
            array(
                '_module' => 'Accounts',
                '_link' => 'accounts_to',
                'id' => $account2->id,
                'email_address_used' => $account2->email1,
                'date_modified' => $this->getIsoTimestamp($account2->date_modified),
            ),
            array(
                '_module' => 'Leads',
                '_link' => 'leads_to',
                'id' => $lead->id,
                'email_address_used' => $lead->email1,
                'date_modified' => $this->getIsoTimestamp($lead->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations after sending');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $bean->from_addr_name
        );
        $this->assertEquals(
            "{$account2->name} <{$account2->email1}>, {$lead->name} <{$lead->email1}>",
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
        $contact1 = $this->createRhsBean('contacts_to');
        $contact2 = $this->createRhsBean('contacts_to');

        $args = array(
            'state' => Email::EMAIL_STATE_READY,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'contacts_from' => array(
                'add' => array(
                    $contact1,
                    $contact2,
                ),
            ),
            'contacts_to' => array(
                'add' => array(
                    $contact1->id,
                    $contact2->id,
                ),
            ),
        );
        $record = $this->createRecord($args);
        $this->assertSame(Email::EMAIL_STATE_ARCHIVED, $record['state'], 'Should be archived');
        $this->assertSame(
            static::$overrideConfig->id,
            $record['outbound_email_id'],
            "Should use the user's configuration"
        );

        $expected = array(
            array(
                '_module' => 'Users',
                '_link' => 'users_from',
                'id' => $GLOBALS['current_user']->id,
                'email_address_used' => $GLOBALS['current_user']->email1,
                'date_modified' => $this->getIsoTimestamp($GLOBALS['current_user']->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'from');
        $this->assertRecords($expected, $collection, 'The sender should be the current user');

        $expected = array(
            array(
                '_module' => 'Contacts',
                '_link' => 'contacts_to',
                'id' => $contact1->id,
                'email_address_used' => $contact1->email1,
                'date_modified' => $this->getIsoTimestamp($contact1->date_modified),
            ),
            array(
                '_module' => 'Contacts',
                '_link' => 'contacts_to',
                'id' => $contact2->id,
                'email_address_used' => $contact2->email1,
                'date_modified' => $this->getIsoTimestamp($contact2->date_modified),
            ),
        );
        $collection = $this->getCollection($record['id'], 'to');
        $this->assertRecords($expected, $collection, 'The TO field did not match expectations');

        $bean = $this->retrieveEmailText($record['id']);
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $bean->from_addr_name
        );
        $to = array(
            "{$contact1->name} <{$contact1->email1}>",
            "{$contact2->name} <{$contact2->email1}>",
        );
        sort($to);
        $to = implode(', ', $to);
        $this->assertEquals($to, $bean->to_addrs_names);
    }

    /**
     * When creating an email and immediately sending it, the status and type are set for compatibility
     * with previous Emails implementation based on email2send()
     *
     * @covers ::createRecord
     * @covers ::isValidStateTransition
     * @covers ::sendEmail
     * @covers Email::sendEmail
     */
    public function testCreateAndSendEmail_StatusAndTypeAreSetCorrectlyForCompatibility()
    {
        $contact1 = $this->createRhsBean('contacts_to');
        $contact2 = $this->createRhsBean('contacts_to');

        $args = array(
            'state' => Email::EMAIL_STATE_READY,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'contacts_from' => array(
                'add' => array(
                    $contact1,
                    $contact2,
                ),
            ),
            'contacts_to' => array(
                'add' => array(
                    $contact1->id,
                    $contact2->id,
                ),
            ),
        );
        $record = $this->createRecord($args);
        $this->assertSame(Email::EMAIL_STATE_ARCHIVED, $record['state'], 'State should be archived');
        $this->assertSame('out', $record['type'], 'Type should be out');
        $this->assertSame('sent', $record['status'], 'Status should be sent');
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
            'state' => Email::EMAIL_STATE_ARCHIVED,
            'reply_to_status' => false,
        );
        $repliedToEmail = SugarTestEmailUtilities::createEmail('', $emailValues);

        $user = $this->createRhsBean('users_to');
        $args = array(
            'state' => Email::EMAIL_STATE_READY,
            'assigned_user_id' => $GLOBALS['current_user']->id,
            'reply_to_id' => $repliedToEmail->id,
            'users_from' => array(
                'add' => array(
                    $user,
                ),
            ),
            'users_to' => array(
                'add' => array(
                    $user->id,
                ),
            ),
        );
        $record = $this->createRecord($args);
        $this->assertSame(Email::EMAIL_STATE_ARCHIVED, $record['state'], 'Should be archived');
        $this->assertSame($repliedToEmail->id, $record['reply_to_id'], 'Should contain id of Email being replied to');

        $repliedToEmail = $repliedToEmail->retrieve($repliedToEmail->id);
        $this->assertEquals('1', $repliedToEmail->reply_to_status, 'reply_to_status value should be True');
    }
}
