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

require_once 'modules/Emails/EmailRecipientRelationship.php';

/**
 * @coversDefaultClass EmailRecipientRelationship
 */
class EmailRecipientRelationshipTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        OutboundEmailConfigurationTestHelper::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
        OutboundEmailConfigurationTestHelper::setAllowDefaultOutbound(0);
    }

    public static function tearDownAfterClass()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        OutboundEmailConfigurationTestHelper::tearDown();
        parent::tearDownAfterClass();
    }

    protected function tearDown()
    {
        // Clean up any dangling beans that need to be resaved.
        SugarRelationship::resaveRelatedBeans(false);
        parent::tearDown();
    }

    /**
     * The value of email_address_id is patched when adding an EmailAddresses record.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
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
            'bean_id' => $address->id,
            'bean_type' => 'EmailAddresses',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals($address->email_address, $email->to_addrs_names);
    }

    /**
     * The value of email_address_id is always used when it is set in $additionalFields.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
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
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * A new email address is created and it's ID is used for email_address_id when email_address is set in
     * $additionalFields and email_address_id is not -- and the email address does not already exist.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
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
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address}>", $email->to_addrs_names);
    }

    /**
     * The value for email_address_id is retrieved when email_address is set in $additionalFields and email_address_id
     * is not -- and the email address already exists.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
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
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * The email_address_id column can be left empty if the email is a draft and the email address was not chosen when
     * the recipient was added. This comes up when a record -- Accounts, Contacts, Leads, etc. -- is added with the
     * intention of using that record's primary email address at send-time.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailAddressIdIsLeftEmpty()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $email = SugarTestEmailUtilities::createEmail('', array('state' => Email::STATE_DRAFT));
        $contact = SugarTestContactUtilities::createContact();

        $additionalFields = array();
        $actual = $relationship->add($email, $contact, $additionalFields);
        $this->assertTrue($actual);

        $rows = $this->getRows([
            'email_id' => $email->id,
            'address_type' => 'to',
        ]);
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => null,
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
    }

    /**
     * The email_address_id column can be emptied if the email is a draft. This comes up when a record -- Accounts,
     * Contacts, Leads, etc. -- is added but the email address on record is incorrect and the intention is to repair the
     * data while leaving the reference to the person record intact.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailAddressIsRemoved()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = $contact->emailAddress->getPrimaryAddress($contact);
        $addressId = $contact->emailAddress->getGuid($address);

        $additionalFields = [
            'email_address_id' => $addressId,
        ];
        $actual = $relationship->add($email, $contact, $additionalFields);
        $this->assertTrue($actual);

        $rows = $this->getRows([
            'email_id' => $email->id,
            'address_type' => 'to',
        ]);
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = [
            'email_id' => $email->id,
            'email_address_id' => $addressId,
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        ];
        $this->assertRow($expected, $rows[0]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);

        $additionalFields = [
            // An empty string would work too.
            'email_address_id' => null,
        ];
        $relationship->add($email, $contact, $additionalFields);
        $rows = $this->getRows([
            'email_id' => $email->id,
            'address_type' => 'to',
        ]);
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => '',
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
    }

    /**
     * The email_address_id column is set to the recipient's primary email address if the email is not a draft and the
     * email address was not chosen when the recipient was added. This comes up when a record -- Accounts, Contacts,
     * Leads, etc. -- is added with the intention of using that record's primary email address and the email is
     * immediately being archived.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailAddressIdDefaultsToPrimaryAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $email = SugarTestEmailUtilities::createEmail();
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
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
    }

    /**
     * A record -- Accounts, Contacts, Leads, etc. -- can be added as a recipient with email_address set in
     * $additionalFields where that email address is not linked to the record. When this happens, the email address
     * should be linked to the record.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers ::addEmailAddressToRecord
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
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
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        $addresses = $contact->emailAddress->getAddressesByGUID($contact->id, 'Contacts');
        $addresses = array_filter($addresses, function ($addr) use ($address) {
            return $addr['email_address'] === $address->email_address;
        });
        $this->assertCount(1, $addresses, "The contact should be linked to {$address->email_address}");

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_LinkBeanWithAnEmailAddressWhenTheEmailAddressIsAlreadyLinked()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_email_addresses_to');
        $email = SugarTestEmailUtilities::createEmail();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $address);

        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $contact = SugarTestContactUtilities::createContact();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);
        $relationship->add($email, $contact, array('email_address' => $address->email_address));

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address->id,
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_LinkAnEmailAddressWhenTheEmailAddressIsAlreadyLinked()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $email = SugarTestEmailUtilities::createEmail();
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);
        $relationship->add($email, $contact, array('email_address_id' => $address->id));

        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_email_addresses_to');
        $relationship->add($email, $address);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address->id,
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailAddressIsSharedByMoreThanOneBean()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $contact = SugarTestContactUtilities::createContact();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);
        $relationship->add($email, $contact, array('email_address_id' => $address->id));

        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_accounts_to');
        $account = SugarTestAccountUtilities::createAccount();
        SugarTestEmailAddressUtilities::addAddressToPerson($account, $address);
        $relationship->add($email, $account, array('email_address' => $address->email_address));

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(2, $rows, 'There should be two rows');

        /**
         * Sorts the rows by their "bean_type" attribute.
         *
         * @param array $a
         * @param array $b
         * @return int
         */
        $rsort = function (array $a, array $b) {
            return ($a['bean_type'] < $b['bean_type']) ? -1 : 1;
        };
        usort($rows, $rsort);


        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address->id,
            'bean_id' => $account->id,
            'bean_type' => 'Accounts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0]);

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address->id,
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'to',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[1]);

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals(
            "{$account->name} <{$address->email_address}>, {$contact->name} <{$address->email_address}>",
            $email->to_addrs_names
        );
    }

    /**
     * Only rows that match the role columns are deleted.
     *
     * @covers ::remove
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
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
            'address_type' => 'to',
        ));
        $row = $rows[0];
        $this->assertCount(1, $rows, 'There should be one row with address_type=to');
        $this->assertSame('Contacts', $row['bean_type'], 'The row with address_type=to should be for Contacts');
        $this->assertSame($contact->id, $row['bean_id'], "The row with address_type=to should be for {$contact->id}");

        $rows = $this->getRows(array(
            'email_id' => $email->id,
            'address_type' => 'cc',
        ));
        $row = $rows[0];
        $this->assertCount(1, $rows, 'There should be one row with address_type=cc');
        $this->assertSame('Accounts', $row['bean_type'], 'The row with address_type=cc should be for Accounts');
        $this->assertSame($account->id, $row['bean_id'], "The row with address_type=cc should be for {$account->id}");

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
        $this->assertEquals("{$account->name} <{$account->email1}>", $email->cc_addrs_names);
    }

    /**
     * Rows deleted by the right-hand side link are replaced by EmailAddresses rows to preserve the email address used
     * in an email even when the record is deleted.
     *
     * @covers ::removeAll
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testRemoveAll()
    {
        $contact = SugarTestContactUtilities::createContact();
        $primary = $contact->emailAddress->getPrimaryAddress($contact);
        $primaryId = $contact->emailAddress->getGuid($primary);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $email1 = SugarTestEmailUtilities::createEmail('', array('state' => Email::STATE_DRAFT));
        $email1->load_relationship('contacts_to');
        $email1->contacts_to->add($contact);

        $email2 = SugarTestEmailUtilities::createEmail();
        $email2->load_relationship('contacts_to');
        $email2->contacts_to->add($contact, array('email_address' => $primary));

        $email3 = SugarTestEmailUtilities::createEmail();
        $email3->load_relationship('contacts_to');
        $email3->contacts_to->add($contact, array('email_address_id' => $address->id));

        $email4 = SugarTestEmailUtilities::createEmail();
        $email4->load_relationship('contacts_to');
        $email4->contacts_to->add($contact);

        $contact->load_relationship('emails_to');
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_to');
        $relationship->removeAll($contact->emails_to);

        $rows = $this->getRows(array(
            'email_id' => $email1->id,
            'address_type' => 'to',
        ));
        $this->assertCount(0, $rows, 'The row should have been removed');

        $rows = $this->getRows(array(
            'email_id' => $email2->id,
            'address_type' => 'to',
        ));
        $row = $rows[0];
        $this->assertCount(1, $rows, "The row should have been replaced with the contact's primary email address");
        $this->assertSame('EmailAddresses', $row['bean_type'], 'The row should be for EmailAddresses');
        $this->assertSame($primaryId, $row['bean_id'], "The row should use the contact's primary email address");

        $rows = $this->getRows(array(
            'email_id' => $email3->id,
            'address_type' => 'to',
        ));
        $row = $rows[0];
        $this->assertCount(1, $rows, "The row should have been replaced with the chosen email address");
        $this->assertSame('EmailAddresses', $row['bean_type'], 'The row should be for EmailAddresses');
        $this->assertSame($address->id, $row['bean_id'], 'The row should use the chosen email address');

        // When an email is archived, the email address has been set automatically. The row should be replaced instead
        // of completely removed.
        $rows = $this->getRows(
            array(
                'email_id' => $email4->id,
                'address_type' => 'to',
            )
        );
        $row = $rows[0];
        $this->assertCount(1, $rows, "The row should have been replaced with the contact's primary email address");
        $this->assertSame('EmailAddresses', $row['bean_type'], 'The row should be for EmailAddresses');
        $this->assertSame($primaryId, $row['bean_id'], "The row should use the contact's primary email address");

        SugarRelationship::resaveRelatedBeans();
        $email1->retrieveEmailText();
        $email2->retrieveEmailText();
        $email3->retrieveEmailText();
        $email4->retrieveEmailText();
        $this->assertEmpty($email1->to_addrs_names);
        $this->assertEquals($primary, $email2->to_addrs_names);
        $this->assertEquals($address->email_address, $email3->to_addrs_names);
        $this->assertEquals($primary, $email4->to_addrs_names);
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
            'bean_id' => array(
                'name' => 'bean_id',
            ),
            'bean_type' => array(
                'name' => 'bean_type',
            ),
            'address_type' => array(
                'name' => 'address_type',
            ),
            'deleted' => array(
                'name' => 'deleted',
            ),
            'date_modified' => array(
                'name' => 'date_modified',
            ),
        );
        $this->assertEquals($expected, $fields);
    }

    /**
     * Returns the matching set of rows from the emails_email_addr_rel table.
     *
     * @param array $fields
     * @return array
     */
    protected function getRows(array $fields)
    {
        $sql = 'SELECT * FROM emails_email_addr_rel WHERE deleted=0';

        if (!empty($fields)) {
            $where = array();

            foreach ($fields as $field => $value) {
                $where[] = "{$field}='{$value}'";
            }

            $sql .= ' AND ' . implode(' AND ', $where);
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

        // Assert that date_modified is not empty. Then discard it because testing the actual value is unnecessary.
        $this->assertNotEmpty($row['date_modified']);
        unset($row['date_modified']);

        $this->assertEquals($expected, $row);
    }
}
