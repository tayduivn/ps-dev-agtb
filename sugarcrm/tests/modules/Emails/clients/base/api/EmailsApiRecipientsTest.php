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

require_once 'tests/modules/Emails/clients/base/api/EmailsApiParticipantsTestCase.php';

class EmailsApiRecipientsTest extends EmailsApiParticipantsTestCase
{
    /**
     * @coversDefaultClass EmailsApi
     */
    public function linkProvider()
    {
        return array(
            array(
                'accounts',
                'to',
            ),
            array(
                'accounts',
                'cc',
            ),
            array(
                'accounts',
                'bcc',
            ),
            array(
                'contacts',
                'to',
            ),
            array(
                'contacts',
                'cc',
            ),
            array(
                'contacts',
                'bcc',
            ),
            array(
                'leads',
                'to',
            ),
            array(
                'leads',
                'cc',
            ),
            array(
                'leads',
                'bcc',
            ),
            array(
                'prospects',
                'to',
            ),
            array(
                'prospects',
                'cc',
            ),
            array(
                'prospects',
                'bcc',
            ),
            array(
                'users',
                'to',
            ),
            array(
                'users',
                'cc',
            ),
            array(
                'users',
                'bcc',
            ),
        );
    }

    public function useSpecifiedEmailAddressProvider()
    {
        return array(
            array(
                'accounts',
                'to',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'accounts',
                'to',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'accounts',
                'cc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'accounts',
                'cc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'accounts',
                'bcc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'accounts',
                'bcc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'contacts',
                'to',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'contacts',
                'to',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'contacts',
                'cc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'contacts',
                'cc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'contacts',
                'bcc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'contacts',
                'bcc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'leads',
                'to',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'leads',
                'to',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'leads',
                'cc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'leads',
                'cc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'leads',
                'bcc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'leads',
                'bcc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'prospects',
                'to',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'prospects',
                'to',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'prospects',
                'cc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'prospects',
                'cc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'prospects',
                'bcc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'prospects',
                'bcc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'users',
                'to',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'users',
                'to',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'users',
                'cc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'users',
                'cc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'users',
                'bcc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'users',
                'bcc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
        );
    }

    public function emailAddressProvider()
    {
        return array(
            array(
                'email_addresses',
                'to',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'email_addresses',
                'to',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'email_addresses',
                'cc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'email_addresses',
                'cc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'email_addresses',
                'bcc',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'email_addresses',
                'bcc',
                Email::EMAIL_STATE_ARCHIVED,
            ),
        );
    }

    /**
     * Use the recipient's primary email address when an email is created in the "Archived" state, the recipient is a
     * bean, and no email address is chosen.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailRecipientRelationship::add
     * @dataProvider linkProvider
     * @param string $module
     * @param string $collectionName
     */
    public function testCreateRecord_UsePrimaryEmailAddressForBeanWhenStateIsArchived($module, $collectionName)
    {
        $link = "{$module}_{$collectionName}";
        $bean = $this->createParticipantBean($link);

        // Add an alternative email address to the bean.
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($bean, $address);

        $args = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
            $link => array(
                'add' => array(
                    $bean->id,
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], $collectionName);

        $expected = array(
            array(
                '_module' => $bean->module_name,
                '_link' => $link,
                'id' => $bean->id,
                'email_address_used' => $bean->email_address,
                'date_modified' => $this->getIsoTimestamp($bean->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
    }

    /**
     * The chosen email address can be undefined when an email is created in the "Draft" state and the recipient is a
     * bean.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailRecipientRelationship::add
     * @dataProvider linkProvider
     * @param string $module
     * @param string $collectionName
     */
    public function testCreateRecord_DeferChoosingEmailAddressForBeanUntilSendTimeWhenStateIsDraft(
        $module,
        $collectionName
    ) {
        $link = "{$module}_{$collectionName}";
        $bean = $this->createParticipantBean($link);

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            $link => array(
                'add' => array(
                    $bean->id,
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], $collectionName);

        $expected = array(
            array(
                '_module' => $bean->module_name,
                '_link' => $link,
                'id' => $bean->id,
                'email_address_used' => '',
                'date_modified' => $this->getIsoTimestamp($bean->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
    }

    /**
     * No matter the state, the email address, specified by email_address_id, is used for the recipient.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailRecipientRelationship::add
     * @dataProvider useSpecifiedEmailAddressProvider
     * @param string $module
     * @param string $collectionName
     * @param string $state
     */
    public function testCreateRecord_UseSpecifiedEmailAddressIdForBean($module, $collectionName, $state)
    {
        $link = "{$module}_{$collectionName}";
        $bean = $this->createParticipantBean($link);

        // Add an alternative email address to the bean.
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($bean, $address);

        $args = array(
            'state' => $state,
            $link => array(
                'add' => array(
                    array(
                        'id' => $bean->id,
                        'email_address_id' => $address->id,
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], $collectionName);

        $expected = array(
            array(
                '_module' => $bean->module_name,
                '_link' => $link,
                'id' => $bean->id,
                'email_address_used' => $address->email_address,
                'date_modified' => $this->getIsoTimestamp($bean->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
    }

    /**
     * No matter the state, the specified email address is used for the recipient. The ID of the email address must be
     * discovered, since only the email address string is provided.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailRecipientRelationship::add
     * @dataProvider useSpecifiedEmailAddressProvider
     * @param string $module
     * @param string $collectionName
     * @param string $state
     */
    public function testCreateRecord_UseSpecifiedEmailAddressForBean($module, $collectionName, $state)
    {
        $link = "{$module}_{$collectionName}";
        $bean = $this->createParticipantBean($link);

        // Add an alternative email address to the bean.
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($bean, $address);

        $args = array(
            'state' => $state,
            $link => array(
                'add' => array(
                    array(
                        'id' => $bean->id,
                        'email_address' => $address->email_address,
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], $collectionName);

        $expected = array(
            array(
                '_module' => $bean->module_name,
                '_link' => $link,
                'id' => $bean->id,
                'email_address_used' => $address->email_address,
                'date_modified' => $this->getIsoTimestamp($bean->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
    }

    /**
     * If an email address is determined to already exist when attempting to create one for the recipient, then the
     * existing email address will be used.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailRecipientRelationship::add
     * @dataProvider emailAddressProvider
     * @param string $module
     * @param string $collectionName
     * @param string $state
     */
    public function testCreateRecord_UseExistingEmailAddress($module, $collectionName, $state)
    {
        $link = "{$module}_{$collectionName}";
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $args = array(
            'state' => $state,
            $link => array(
                'create' => array(
                    array(
                        'email_address' => $address->email_address,
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], $collectionName);

        $expected = array(
            array(
                '_module' => 'EmailAddresses',
                '_link' => $link,
                'id' => $address->id,
                'email_address_used' => $address->email_address,
                'date_modified' => $this->getIsoTimestamp($address->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
    }

    /**
     * When the ID of an existing email address is known, it can be used to define the recipient's email address.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailRecipientRelationship::add
     * @dataProvider emailAddressProvider
     * @param string $module
     * @param string $collectionName
     * @param string $state
     */
    public function testCreateRecord_UseExistingEmailAddressId($module, $collectionName, $state)
    {
        $link = "{$module}_{$collectionName}";
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $args = array(
            'state' => $state,
            $link => array(
                'add' => array(
                    $address->id,
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], $collectionName);

        $expected = array(
            array(
                '_module' => 'EmailAddresses',
                '_link' => $link,
                'id' => $address->id,
                'email_address_used' => $address->email_address,
                'date_modified' => $this->getIsoTimestamp($address->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
    }

    /**
     * A new email address is created when an previously unknown email address is used for a recipient.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::createRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailAddressesApi::createBean
     * @covers EmailRecipientRelationship::add
     * @dataProvider emailAddressProvider
     * @param string $module
     * @param string $collectionName
     * @param string $state
     */
    public function testCreateRecord_CreateNewEmailAddress($module, $collectionName, $state)
    {
        $link = "{$module}_{$collectionName}";
        $address = 'address-' . create_guid() . '@example.com';

        $args = array(
            'state' => $state,
            $link => array(
                'create' => array(
                    array(
                        'email_address' => $address,
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], $collectionName);
        $addressId = SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress($address);

        $expected = array(
            array(
                '_module' => 'EmailAddresses',
                '_link' => $link,
                'id' => $addressId,
                'email_address_used' => $address,
                'date_modified' => $record['date_modified'],
            ),
        );
        $this->assertRecords($expected, $collection);
    }

    /**
     * Recipients can be added to an Emails record. They can then be updated to change their chosen email address or
     * removed altogether. And new recipients can be added.
     *
     * @covers ::createRecord
     * @covers ::updateRecord
     * @covers ::createBean
     * @covers ::updateBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::unlinkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailRecipientRelationship::add
     */
    public function testCreateRecordUpdateRecord()
    {
        $user1 = $this->createParticipantBean('users_bcc');
        $contact1 = $this->createParticipantBean('contacts_to');
        $contact2 = $this->createParticipantBean('contacts_to');
        $account1 = $this->createParticipantBean('accounts_cc');
        $account2 = $this->createParticipantBean('accounts_to');
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($account1, $address);

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'contacts_to' => array(
                'add' => array(
                    $contact1->id,
                    $contact2->id,
                ),
            ),
            'accounts_cc' => array(
                'add' => array(
                    array(
                        'id' => $account1->id,
                        'email_address_id' => $address->id,
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);

        $to = $this->getCollection($record['id'], 'to');
        $expected = array(
            array(
                '_module' => $contact1->module_name,
                '_link' => 'contacts_to',
                'id' => $contact1->id,
                'email_address_used' => '',
                'date_modified' => $this->getIsoTimestamp($contact1->date_modified),
            ),
            array(
                '_module' => $contact2->module_name,
                '_link' => 'contacts_to',
                'id' => $contact2->id,
                'email_address_used' => '',
                'date_modified' => $this->getIsoTimestamp($contact2->date_modified),
            ),
        );
        $this->assertRecords($expected, $to);

        $cc = $this->getCollection($record['id'], 'cc');
        $expected = array(
            array(
                '_module' => $account1->module_name,
                '_link' => 'accounts_cc',
                'id' => $account1->id,
                'email_address_used' => $address->email_address,
                'date_modified' => $this->getIsoTimestamp($account1->date_modified),
            ),
        );
        $this->assertRecords($expected, $cc);

        $bcc = $this->getCollection($record['id'], 'bcc');
        $expected = array();
        $this->assertRecords($expected, $bcc);

        $args = array(
            'contacts_to' => array(
                'add' => array(
                    // Set $contact2's email address in stone.
                    array(
                        'id' => $contact2->id,
                        'email_address' => $contact2->email_address,
                    ),
                ),
                'delete' => array(
                    // Remove $contact1.
                    $contact1->id,
                ),
            ),
            'accounts_cc' => array(
                'add' => array(
                    // Change to $account1's primary email address.
                    array(
                        'id' => $account1->id,
                        'email_address' => $account1->email_address,
                    ),
                    // Add another account.
                    $account2->id,
                ),
            ),
            'users_bcc' => array(
                // Add a BCC recipient.
                'add' => array(
                    $user1->id,
                ),
            ),
        );
        $record = $this->updateRecord($record['id'], $args);

        $to = $this->getCollection($record['id'], 'to');
        $expected = array(
            array(
                '_module' => $contact2->module_name,
                '_link' => 'contacts_to',
                'id' => $contact2->id,
                'email_address_used' => $contact2->email_address,
                'date_modified' => $this->getIsoTimestamp($contact2->date_modified),
            ),
        );
        $this->assertRecords($expected, $to);

        $cc = $this->getCollection($record['id'], 'cc');
        $expected = array(
            array(
                '_module' => $account1->module_name,
                '_link' => 'accounts_cc',
                'id' => $account1->id,
                'email_address_used' => $account1->email_address,
                'date_modified' => $this->getIsoTimestamp($account1->date_modified),
            ),
            array(
                '_module' => $account2->module_name,
                '_link' => 'accounts_cc',
                'id' => $account2->id,
                'email_address_used' => '',
                'date_modified' => $this->getIsoTimestamp($account2->date_modified),
            ),
        );
        $this->assertRecords($expected, $cc);

        $bcc = $this->getCollection($record['id'], 'bcc');
        $expected = array(
            array(
                '_module' => $user1->module_name,
                '_link' => 'users_bcc',
                'id' => $user1->id,
                'email_address_used' => '',
                'date_modified' => $this->getIsoTimestamp($user1->date_modified),
            ),
        );
        $this->assertRecords($expected, $bcc);
    }

    /**
     * It should be possible to use the same record as a recipient in every role for an email.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailRecipientRelationship::add
     */
    public function testCreateRecord_UseTheSameRecipientInEachRole()
    {
        $contact = $this->createParticipantBean('contacts_to');

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            'contacts_from' => array(
                'add' => array(
                    $contact->id,
                ),
            ),
            'contacts_to' => array(
                'add' => array(
                    $contact->id,
                ),
            ),
            'contacts_cc' => array(
                'add' => array(
                    $contact->id,
                ),
            ),
            'contacts_bcc' => array(
                'add' => array(
                    $contact->id,
                ),
            ),
        );
        $record = $this->createRecord($args);

        $expected = array(
            array(
                '_module' => $contact->module_name,
                'id' => $contact->id,
                'email_address_used' => '',
                'date_modified' => $this->getIsoTimestamp($contact->date_modified),
            ),
        );

        $from = $this->getCollection($record['id'], 'from');
        $expected[0]['_link'] = 'contacts_from';
        $this->assertRecords($expected, $from);

        $to = $this->getCollection($record['id'], 'to');
        $expected[0]['_link'] = 'contacts_to';
        $this->assertRecords($expected, $to);

        $cc = $this->getCollection($record['id'], 'cc');
        $expected[0]['_link'] = 'contacts_cc';
        $this->assertRecords($expected, $cc);

        $bcc = $this->getCollection($record['id'], 'bcc');
        $expected[0]['_link'] = 'contacts_bcc';
        $this->assertRecords($expected, $bcc);
    }
}
