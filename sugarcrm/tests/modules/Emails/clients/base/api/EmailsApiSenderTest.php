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

/**
 * @coversDefaultClass EmailsApi
 */
class EmailsApiSenderTest extends EmailsApiParticipantsTestCase
{
    public function linkProvider()
    {
        return array(
            array('accounts_from'),
            array('contacts_from'),
            array('leads_from'),
            array('prospects_from'),
            array('users_from'),
        );
    }

    public function useSpecifiedEmailAddressProvider()
    {
        return array(
            array(
                'accounts_from',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'accounts_from',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'contacts_from',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'contacts_from',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'leads_from',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'leads_from',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'prospects_from',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'prospects_from',
                Email::EMAIL_STATE_ARCHIVED,
            ),
            array(
                'users_from',
                Email::EMAIL_STATE_DRAFT,
            ),
            array(
                'users_from',
                Email::EMAIL_STATE_ARCHIVED,
            ),
        );
    }

    public function stateProvider()
    {
        return array(
            array(Email::EMAIL_STATE_DRAFT),
            array(Email::EMAIL_STATE_ARCHIVED),
        );
    }

    public function moreThanOneSenderProvider()
    {
        return array(
            array(
                'accounts_from',
                'accounts_from',
            ),
            array(
                'accounts_from',
                'contacts_from',
            ),
            array(
                'accounts_from',
                'email_addresses_from',
            ),
            array(
                'accounts_from',
                'leads_from',
            ),
            array(
                'accounts_from',
                'prospects_from',
            ),
            array(
                'accounts_from',
                'users_from',
            ),
            array(
                'contacts_from',
                'accounts_from',
            ),
            array(
                'contacts_from',
                'contacts_from',
            ),
            array(
                'contacts_from',
                'email_addresses_from',
            ),
            array(
                'contacts_from',
                'leads_from',
            ),
            array(
                'contacts_from',
                'prospects_from',
            ),
            array(
                'contacts_from',
                'users_from',
            ),
            array(
                'email_addresses_from',
                'accounts_from',
            ),
            array(
                'email_addresses_from',
                'contacts_from',
            ),
            array(
                'email_addresses_from',
                'email_addresses_from',
            ),
            array(
                'email_addresses_from',
                'leads_from',
            ),
            array(
                'email_addresses_from',
                'prospects_from',
            ),
            array(
                'email_addresses_from',
                'users_from',
            ),
            array(
                'leads_from',
                'accounts_from',
            ),
            array(
                'leads_from',
                'contacts_from',
            ),
            array(
                'leads_from',
                'email_addresses_from',
            ),
            array(
                'leads_from',
                'leads_from',
            ),
            array(
                'leads_from',
                'prospects_from',
            ),
            array(
                'leads_from',
                'users_from',
            ),
            array(
                'prospects_from',
                'accounts_from',
            ),
            array(
                'prospects_from',
                'contacts_from',
            ),
            array(
                'prospects_from',
                'email_addresses_from',
            ),
            array(
                'prospects_from',
                'leads_from',
            ),
            array(
                'prospects_from',
                'prospects_from',
            ),
            array(
                'prospects_from',
                'users_from',
            ),
            array(
                'users_from',
                'accounts_from',
            ),
            array(
                'users_from',
                'contacts_from',
            ),
            array(
                'users_from',
                'email_addresses_from',
            ),
            array(
                'users_from',
                'leads_from',
            ),
            array(
                'users_from',
                'prospects_from',
            ),
            array(
                'users_from',
                'users_from',
            ),
        );
    }

    /**
     * Use the sender's primary email address when an email is created in the "Archived" state, the sender is a bean,
     * and no email address is chosen.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailSenderRelationship::add
     * @dataProvider linkProvider
     * @param string $link
     */
    public function testCreateRecord_UsePrimaryEmailAddressForBeanWhenStateIsArchived($link)
    {
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
        $collection = $this->getCollection($record['id'], 'from');

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
        $this->assertOffsets($collection);
    }

    /**
     * The chosen email address can be undefined when an email is created in the "Draft" state and the sender is a bean.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailSenderRelationship::add
     * @dataProvider linkProvider
     * @param string $link
     */
    public function testCreateRecord_DeferChoosingEmailAddressForBeanUntilSendTimeWhenStateIsDraft($link)
    {
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
        $collection = $this->getCollection($record['id'], 'from');

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
        $this->assertOffsets($collection);
    }

    /**
     * No matter the state, the email address, specified by email_address_id, is used for the sender.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailSenderRelationship::add
     * @dataProvider useSpecifiedEmailAddressProvider
     * @param string $link
     * @param string $state
     */
    public function testCreateRecord_UseSpecifiedEmailAddressIdForBean($link, $state)
    {
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
        $collection = $this->getCollection($record['id'], 'from');

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
        $this->assertOffsets($collection);
    }

    /**
     * No matter the state, the specified email address is used for the sender. The ID of the email address must be
     * discovered, since only the email address string is provided.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailSenderRelationship::add
     * @dataProvider useSpecifiedEmailAddressProvider
     * @param string $link
     * @param string $state
     */
    public function testCreateRecord_UseSpecifiedEmailAddressForBean($link, $state)
    {
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
        $collection = $this->getCollection($record['id'], 'from');

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
        $this->assertOffsets($collection);
    }

    /**
     * If an email address is determined to already exist when attempting to create one for the sender, then the
     * existing email address will be used.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailSenderRelationship::add
     * @dataProvider stateProvider
     * @param string $state
     */
    public function testCreateRecord_UseExistingEmailAddress($state)
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $args = array(
            'state' => $state,
            'email_addresses_from' => array(
                'create' => array(
                    array(
                        'email_address' => $address->email_address,
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], 'from');

        $expected = array(
            array(
                '_module' => 'EmailAddresses',
                '_link' => 'email_addresses_from',
                'id' => $address->id,
                'email_address_used' => $address->email_address,
                'date_modified' => $this->getIsoTimestamp($address->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
        $this->assertOffsets($collection);
    }

    /**
     * When the ID of an existing email address is known, it can be used to define the sender's email address.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailSenderRelationship::add
     * @dataProvider stateProvider
     * @param string $state
     */
    public function testCreateRecord_UseExistingEmailAddressId($state)
    {
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $args = array(
            'state' => $state,
            'email_addresses_from' => array(
                'add' => array(
                    $address->id,
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], 'from');

        $expected = array(
            array(
                '_module' => 'EmailAddresses',
                '_link' => 'email_addresses_from',
                'id' => $address->id,
                'email_address_used' => $address->email_address,
                'date_modified' => $this->getIsoTimestamp($address->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
        $this->assertOffsets($collection);
    }

    /**
     * A new email address is created when an previously unknown email address is used for a sender.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::createRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailAddressesApi::createBean
     * @covers EmailSenderRelationship::add
     * @dataProvider stateProvider
     * @param string $state
     */
    public function testCreateRecord_CreateNewEmailAddress($state)
    {
        $address = 'address-' . create_guid() . '@example.com';

        $args = array(
            'state' => $state,
            'email_addresses_from' => array(
                'create' => array(
                    array(
                        'email_address' => $address,
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], 'from');
        $addressId = SugarTestEmailAddressUtilities::setCreatedEmailAddressByAddress($address);

        $expected = array(
            array(
                '_module' => 'EmailAddresses',
                '_link' => 'email_addresses_from',
                'id' => $addressId,
                'email_address_used' => $address,
                'date_modified' => $record['date_modified'],
            ),
        );
        $this->assertRecords($expected, $collection);
        $this->assertOffsets($collection);
    }

    /**
     * When an attempt is made to link more than one sender, only one sender remains. Because of the way that
     * {@link ModuleApi::getRelatedRecordArguments()} parses the arguments, the link operations are ordered according to
     * their order in the primary bean's field definitions and not the order the arguments were submitted to the API.
     * The last link operation to be executed via this ordering is the one whose sender will persist.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailSenderRelationship::add
     * @dataProvider moreThanOneSenderProvider
     * @param string $link1
     * @param string $link2
     */
    public function testCreateRecord_MoreThanOneSender($link1, $link2)
    {
        $args = array(
            'state' => Email::EMAIL_STATE_ARCHIVED,
        );

        $addToArgs = function ($link, SugarBean $bean) use (&$args) {
            if (!isset($args[$link])) {
                $args[$link] = array(
                    'add' => array(),
                );
            }

            $args[$link]['add'][] = $bean->id;
        };

        $bean1 = $this->createParticipantBean($link1);
        $addToArgs($link1, $bean1);

        $bean2 = $this->createParticipantBean($link2);
        $addToArgs($link2, $bean2);

        $record = $this->createRecord($args);

        $collection = $this->getCollection($record['id'], 'from');
        $this->assertCount(1, $collection['records']);
        $this->assertOffsets($collection);
    }

    /**
     * An email can be updated with a new sender to replace the previous one.
     *
     * @covers ::createRecord
     * @covers ::createBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailSenderRelationship::add
     * @dataProvider moreThanOneSenderProvider
     * @param string $link1
     * @param string $link2
     */
    public function testUpdateRecord_ReplaceTheSender($link1, $link2)
    {
        $bean1 = $this->createParticipantBean($link1);

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            $link1 => array(
                'add' => array(
                    array(
                        'id' => $bean1->id,
                        'email_address' => $bean1->email_address,
                    ),
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], 'from');

        $expected = array(
            array(
                '_module' => $bean1->module_name,
                '_link' => $link1,
                'id' => $bean1->id,
                'email_address_used' => $bean1->email_address,
                'date_modified' => $this->getIsoTimestamp($bean1->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
        $this->assertOffsets($collection);

        $bean2 = $this->createParticipantBean($link2);

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            $link2 => array(
                'add' => array(
                    array(
                        'id' => $bean2->id,
                        'email_address' => $bean2->email_address,
                    ),
                ),
            ),
        );
        $record = $this->updateRecord($record['id'], $args);
        $collection = $this->getCollection($record['id'], 'from');

        $expected = array(
            array(
                '_module' => $bean2->module_name,
                '_link' => $link2,
                'id' => $bean2->id,
                'email_address_used' => $bean2->email_address,
                'date_modified' => $this->getIsoTimestamp($bean2->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
        $this->assertOffsets($collection);
    }

    /**
     * An email can be created in the "Draft" state without defining the email address of the sender, when the sender is
     * a bean. The email can then be updated to define the chosen email address for the sender.
     *
     * @covers ::updateRecord
     * @covers ::updateBean
     * @covers ::getRelatedRecordArguments
     * @covers ::linkRelatedRecords
     * @covers ::fixupRelatedEmailAddressesArgs
     * @covers ::getSugarEmailAddress
     * @covers EmailSenderRelationship::add
     * @dataProvider linkProvider
     * @param string $link
     */
    public function testUpdateRecord_ChangeEmailAddressForExistingSender($link)
    {
        $bean = $this->createParticipantBean($link);
        $module = $bean->module_name;

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            $link => array(
                'add' => array(
                    $bean->id,
                ),
            ),
        );
        $record = $this->createRecord($args);
        $collection = $this->getCollection($record['id'], 'from');

        $expected = array(
            array(
                '_module' => $module,
                '_link' => $link,
                'id' => $bean->id,
                'email_address_used' => '',
                'date_modified' => $this->getIsoTimestamp($bean->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
        $this->assertOffsets($collection);

        $args = array(
            'state' => Email::EMAIL_STATE_DRAFT,
            $link => array(
                'add' => array(
                    array(
                        'id' => $bean->id,
                        'email_address' => $bean->email_address,
                    ),
                ),
            ),
        );
        $record = $this->updateRecord($record['id'], $args);
        $collection = $this->getCollection($record['id'], 'from');

        $expected = array(
            array(
                '_module' => $module,
                '_link' => $link,
                'id' => $bean->id,
                'email_address_used' => $bean->email_address,
                'date_modified' => $this->getIsoTimestamp($bean->date_modified),
            ),
        );
        $this->assertRecords($expected, $collection);
        $this->assertOffsets($collection);
    }

    /**
     * Asserts that the specified collection's offsets are all -1, as there should not be any records in addition to the
     * single sender.
     *
     * @param array $collection The response retrieved using {@link EmailsApiParticipantsTestCase::getCollection()}.
     */
    protected function assertOffsets(array $collection)
    {
        $offsets = array(
            'accounts_from' => -1,
            'contacts_from' => -1,
            'email_addresses_from' => -1,
            'leads_from' => -1,
            'prospects_from' => -1,
            'users_from' => -1,
        );
        $this->assertEquals($offsets, $collection['next_offset'], 'There should not be any other records to fetch');
    }
}
