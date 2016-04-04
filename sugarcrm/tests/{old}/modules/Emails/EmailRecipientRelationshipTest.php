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

require_once 'modules/Emails/EmailRecipientRelationship.php';

/**
 * @coversDefaultClass EmailRecipientRelationship
 */
class EmailRecipientRelationshipTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }

    public static function tearDownAfterClass()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        parent::tearDownAfterClass();
    }

    /**
     * The value of email_address_id is patched when adding an EmailAddresses record.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     */
    public function testAdd_EmailAddressIdIsSetIfRhsBeanIsAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_email_addresses_to');
        $email = SugarTestEmailUtilities::createEmail();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $additionalFields = array();
        $actual = $relationship->add($email, $address, $additionalFields);
        $this->assertTrue($actual);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address->id,
            'participant_id' => $address->id,
            'participant_module' => 'EmailAddresses',
            'role' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);
    }

    /**
     * The value of email_address_id is always used when it is set in $additionalFields.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     */
    public function testAdd_EmailAddressIdIsUsedIfSet()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $email = SugarTestEmailUtilities::createEmail();
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $additionalFields = array(
            'email_address_id' => $address->id,
            'email_address' => 'foo@bar.com',
        );
        $actual = $relationship->add($email, $contact, $additionalFields);
        $this->assertTrue($actual);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address->id,
            'participant_id' => $contact->id,
            'participant_module' => 'Contacts',
            'role' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);
    }

    /**
     * A new email address is created and it's ID is used for email_address_id when email_address is set in
     * $additionalFields and email_address_id is not -- and the email address does not already exist.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     */
    public function testAdd_EmailAddressIsCreated()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $email = SugarTestEmailUtilities::createEmail();
        $contact = SugarTestContactUtilities::createContact();
        $address = 'address-' . create_guid() . '@example.com';

        $additionalFields = array(
            'email_address' => $address,
        );
        $actual = $relationship->add($email, $contact, $additionalFields);
        $this->assertTrue($actual);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $contact->emailAddress->getGuid($address),
            'participant_id' => $contact->id,
            'participant_module' => 'Contacts',
            'role' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);
    }

    /**
     * The value for email_address_id is retrieved when email_address is set in $additionalFields and email_address_id
     * is not -- and the email address already exists.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     */
    public function testAdd_EmailAddressIdIsDiscovered()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $email = SugarTestEmailUtilities::createEmail();
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $additionalFields = array(
            'email_address' => $address->email_address,
        );
        $actual = $relationship->add($email, $contact, $additionalFields);
        $this->assertTrue($actual);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address->id,
            'participant_id' => $contact->id,
            'participant_module' => 'Contacts',
            'role' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);
    }

    /**
     * The email_address_id column can be left empty if the email is a draft and the email address was not chosen when
     * the recipient was added. This comes up when a record -- Accounts, Contacts, Leads, etc. -- is added with the
     * intention of using that record's primary email address at send-time.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     */
    public function testAdd_EmailAddressIdIsLeftEmpty()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $email = SugarTestEmailUtilities::createEmail('', array('state' => Email::EMAIL_STATE_DRAFT));
        $contact = SugarTestContactUtilities::createContact();

        $additionalFields = array();
        $actual = $relationship->add($email, $contact, $additionalFields);
        $this->assertTrue($actual);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => null,
            'participant_id' => $contact->id,
            'participant_module' => 'Contacts',
            'role' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);
    }

    /**
     * The email_address_id column is set to the recipient's primary email address if the email is not a draft and the
     * email address was not chosen when the recipient was added. This comes up when a record -- Accounts, Contacts,
     * Leads, etc. -- is added with the intention of using that record's primary email address and the email is
     * immediately being archived.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     */
    public function testAdd_EmailAddressIdDefaultsToPrimaryAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $email = SugarTestEmailUtilities::createEmail('', array('state' => Email::EMAIL_STATE_ARCHIVED));
        $contact = SugarTestContactUtilities::createContact();
        $address = $contact->emailAddress->getPrimaryAddress($contact);
        $addressId = $contact->emailAddress->getGuid($address);

        $additionalFields = array();
        $actual = $relationship->add($email, $contact, $additionalFields);
        $this->assertTrue($actual);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $addressId,
            'participant_id' => $contact->id,
            'participant_module' => 'Contacts',
            'role' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);
    }

    /**
     * A record -- Accounts, Contacts, Leads, etc. -- can be added as a recipient with email_address set in
     * $additionalFields where that email address is not linked to the record. When this happens, the email address
     * should be linked to the record.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers ::addEmailAddressToRecord
     */
    public function testAdd_EmailAddressIsLinkedToRecipient()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $email = SugarTestEmailUtilities::createEmail();
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $addresses = $contact->emailAddress->getAddressesByGUID($contact->id, 'Contacts');
        $addresses = array_filter($addresses, function ($addr) use ($address) {
            return $addr['email_address'] === $address->email_address;
        });
        $this->assertCount(0, $addresses, "The contact should not be linked to {$address->email_address}");

        $additionalFields = array(
            'email_address' => $address->email_address,
        );
        $actual = $relationship->add($email, $contact, $additionalFields);
        $this->assertTrue($actual);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address->id,
            'participant_id' => $contact->id,
            'participant_module' => 'Contacts',
            'role' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        $addresses = $contact->emailAddress->getAddressesByGUID($contact->id, 'Contacts');
        $addresses = array_filter($addresses, function ($addr) use ($address) {
            return $addr['email_address'] === $address->email_address;
        });
        $this->assertCount(1, $addresses, "The contact should be linked to {$address->email_address}");
    }

    /**
     * Rows are physically deleted. Only rows that match the role columns are deleted.
     *
     * @covers ::remove
     * @covers ::removeRow
     */
    public function testRemove()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();

        $relationship1 = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $relationship1->add($email, $contact);

        $relationship2 = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_cc');
        $relationship2->add($email, $contact);

        $relationship3 = SugarRelationshipFactory::getInstance()->getRelationship('emails_accounts_cc');
        $relationship3->add($email, $account);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(3, $rows, 'Should start with three rows');

        // Remove from emails_contacts_cc only.
        $relationship2->remove($email, $contact);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(2, $rows, 'Should end with two rows');

        $rows = $this->getRows(array(
            'email_id' => $email->id,
            'role' => 'to',
        ));
        $row = $rows[0];
        $this->assertCount(1, $rows, 'There should be one row with role=to');
        $this->assertSame('Contacts', $row['participant_module'], 'The row with role=to should be for Contacts');
        $this->assertSame($contact->id, $row['participant_id'], "The row with role=to should be for {$contact->id}");

        $rows = $this->getRows(array(
            'email_id' => $email->id,
            'role' => 'cc',
        ));
        $row = $rows[0];
        $this->assertCount(1, $rows, 'There should be one row with role=cc');
        $this->assertSame('Accounts', $row['participant_module'], 'The row with role=cc should be for Accounts');
        $this->assertSame($account->id, $row['participant_id'], "The row with role=cc should be for {$account->id}");
    }

    /**
     * Rows deleted by the right-hand side link are replaced by EmailAddresses rows to preserve the email address used
     * in an email even when the record is deleted.
     *
     * @covers ::removeAll
     */
    public function testRemoveAll()
    {
        $contact = SugarTestContactUtilities::createContact();
        $primary = $contact->emailAddress->getPrimaryAddress($contact);
        $primaryId = $contact->emailAddress->getGuid($primary);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $email1 = SugarTestEmailUtilities::createEmail();
        $email1->load_relationship('contacts_to');
        $email1->contacts_to->add($contact);

        $email2 = SugarTestEmailUtilities::createEmail();
        $email2->load_relationship('contacts_to');
        $email2->contacts_to->add($contact, array('email_address' => $primary));

        $email3 = SugarTestEmailUtilities::createEmail();
        $email3->load_relationship('contacts_to');
        $email3->contacts_to->add($contact, array('email_address_id' => $address->id));

        $contact->load_relationship('emails_to');
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $relationship->removeAll($contact->emails_to);

        $rows = $this->getRows(array(
            'email_id' => $email1->id,
            'role' => 'to',
        ));
        $this->assertCount(0, $rows, 'The row should have been removed');

        $rows = $this->getRows(array(
            'email_id' => $email2->id,
            'role' => 'to',
        ));
        $row = $rows[0];
        $this->assertCount(1, $rows, "The row should have been replaced with the contact's primary email address");
        $this->assertSame('EmailAddresses', $row['participant_module'], 'The row should be for EmailAddresses');
        $this->assertSame($primaryId, $row['participant_id'], "The row should use the contact's primary email address");

        $rows = $this->getRows(array(
            'email_id' => $email3->id,
            'role' => 'to',
        ));
        $row = $rows[0];
        $this->assertCount(1, $rows, "The row should have been replaced with the chosen email address");
        $this->assertSame('EmailAddresses', $row['participant_module'], 'The row should be for EmailAddresses');
        $this->assertSame($address->id, $row['participant_id'], 'The row should use the chosen email address');
    }

    /**
     * The fields date_modified, modified_user_id, created_by are not used by this relationship.
     *
     * @covers ::getStandardFields
     */
    public function testGetStandardFields()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $fields = SugarTestReflection::callProtectedMethod($relationship, 'getStandardFields');

        $expected = array(
            'id' => array(
                'name' => 'id',
            ),
            'email_id' => array(
                'name' => 'email_id',
            ),
            'participant_id' => array(
                'name' => 'participant_id',
            ),
            'participant_module' => array(
                'name' => 'participant_module',
            ),
            'role' => array(
                'name' => 'role',
            ),
            'deleted' => array(
                'name' => 'deleted',
            ),
        );
        $this->assertEquals($expected, $fields);
    }

    /**
     * Returns the matching set of rows from the email_participants table.
     *
     * @param array $fields
     * @return array
     */
    protected function getRows(array $fields)
    {
        $sql = 'SELECT * FROM emails_participants';

        if (!empty($fields)) {
            $where = array();

            foreach ($fields as $field => $value) {
                $where[] = "{$field}='{$value}'";
            }

            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $result = $GLOBALS['db']->query($sql);
        $rows = array();

        while ($row = $GLOBALS['db']->fetchByAssoc($result)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Asserts that the row contains the expected data.
     *
     * @param array $expected
     * @param array $row
     */
    protected function assertRow(array $expected, array $row)
    {
        // Testing for id is unnecessary.
        unset($row['id']);
        $this->assertEquals($expected, $row);
    }
}
