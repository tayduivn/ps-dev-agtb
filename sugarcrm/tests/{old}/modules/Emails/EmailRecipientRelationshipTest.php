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

    public function stateProvider()
    {
        return [
            [
                Email::STATE_DRAFT,
            ],
            [
                Email::STATE_ARCHIVED,
            ],
        ];
    }

    /**
     * An email address can be linked without a person record.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_AddAnEmailAddress_ParticipantIsSavedWithoutParent($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $result = $relationship->add($email, $this->createEmailParticipant(null, $address));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertEmpty($bean->parent_type);
        $this->assertEmpty($bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals($address->email_address, $email->to_addrs_names);
    }

    /**
     * The value of email_address_id is always used when it is set.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_AddContactWithAnEmailAddress_ParticipantIsSavedWithParentAndEmailAddress($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $result = $relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * The email_address_id column can be left empty if the email is a draft and the email address was not chosen when
     * the recipient was added. This comes up when a record -- Accounts, Contacts, Leads, etc. -- is added with the
     * intention of using that record's primary email address at send-time.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_AddContactWithoutAnEmailAddressToDraft_ParticipantIsSavedWithoutAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();

        $result = $relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

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
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_AddContactWithoutAnEmailAddressToArchivedEmail_ParticipantIsSavedWithPrimaryEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $primaryId = $contact->emailAddress->getGuid($contact->email1);

        $result = $relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($primaryId, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
    }

    /**
     * The email_address_id column can be emptied if the email is a draft. This comes up when a record -- Accounts,
     * Contacts, Leads, etc. -- is added but the email address on record is incorrect and the intention is to repair the
     * data while leaving the reference to the person record intact.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_DraftHasContactWithAnEmailAddress_RemoveEmailAddress_EmailAddressIsRemoved()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $ep = $this->createEmailParticipant($contact, $address);
        $relationship->add($email, $ep);

        // The original object must be used to try to remove the email address from the relationship.
        $ep->email_address_id = '';
        $result = $relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
    }

    /**
     * The email_address_id column cannot be emptied if the email is archived. The record's primary email address will
     * replace the original value of email_address_id.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasContactWithAnEmailAddress_RemoveEmailAddress_PrimaryEmailAddressIsUsed()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $primaryId = $contact->emailAddress->getGuid($contact->email1);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $ep = $this->createEmailParticipant($contact, $address);
        $relationship->add($email, $ep);

        // The original object must be used to try to remove the email address from the relationship.
        $ep->email_address_id = '';
        $result = $relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($primaryId, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
    }

    /**
     * A record -- Accounts, Contacts, Leads, etc. -- can be added as a recipient with an email_address_id where that
     * email address is not linked to the record. When this happens, the email address should be linked to the record.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_AddContactWithAnEmailAddress_EmailAddressIsLinkedToTheContact($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $result = $relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);

        $addresses = $contact->emailAddress->getAddressesByGUID($contact->id, 'Contacts');
        $addresses = array_filter($addresses, function ($addr) use ($address) {
            return $addr['email_address'] === $address->email_address;
        });
        $this->assertCount(1, $addresses, "The contact should be linked to {$address->email_address}");
    }

    /**
     * The `parent_type` and `parent_id` fields are patched when a bean is linked with an email address that is already
     * linked as a plain email address.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailHasAnEmailAddressParticipant_AddContactWithSameEmailAddress_ParentIsAddedToParticipant($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $contact = SugarTestContactUtilities::createContact();
        $relationship->add($email, $this->createEmailParticipant(null, $address));

        $result = $relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * The `parent_type` and `parent_id` fields are preserved if an attempt is made to link an email address when that
     * email address is already linked along with a bean.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers Email::retrieveEmailText
     */
    public function testAdd_EmailHasContactWithAnEmailAddress_AddSameEmailAddress_NoChangeIsMade($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $relationship->add($email, $this->createEmailParticipant(null, $address));
        $this->assertFalse($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * The same email address can be used by more than one bean within the same role on an email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_DraftHasContact_AddLeadWithoutAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead();
        $relationship->add($email, $this->createEmailParticipant($contact));

        $result = $relationship->add($email, $this->createEmailParticipant($lead));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(2, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $bean = array_shift($beans);
        $this->assertSame('Leads', $bean->parent_type);
        $this->assertSame($lead->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$contact->name} <{$contact->email1}>, {$lead->name} <{$lead->email1}>",
            $email->to_addrs_names
        );
    }

    /**
     * The same email address can be used by more than one bean within the same role on an email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_DraftHasContactWithAnEmailAddress_AddLeadWithoutAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $relationship->add($email, $this->createEmailParticipant($lead));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(2, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $bean = array_shift($beans);
        $this->assertSame('Leads', $bean->parent_type);
        $this->assertSame($lead->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$contact->name} <{$address->email_address}>, {$lead->name} <{$lead->email1}>",
            $email->to_addrs_names
        );
    }

    /**
     * The same email address can be used by more than one bean within the same role on an email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasContactWithAnEmailAddress_AddLeadWithoutAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead();
        $primaryId = $lead->emailAddress->getGuid($lead->email1);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $relationship->add($email, $this->createEmailParticipant($lead));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(2, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $bean = array_shift($beans);
        $this->assertSame('Leads', $bean->parent_type);
        $this->assertSame($lead->id, $bean->parent_id);
        $this->assertSame($primaryId, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$contact->name} <{$address->email_address}>, {$lead->name} <{$lead->email1}>",
            $email->to_addrs_names
        );
    }

    /**
     * The same email address can be used by more than one bean within the same role on an email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_DraftHasContactWithoutAnEmailAddress_AddLeadWithAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($contact));

        $result = $relationship->add($email, $this->createEmailParticipant($lead, $address));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(2, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $bean = array_shift($beans);
        $this->assertSame('Leads', $bean->parent_type);
        $this->assertSame($lead->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$contact->name} <{$contact->email1}>, {$lead->name} <{$address->email_address}>",
            $email->to_addrs_names
        );
    }

    /**
     * The same email address can be used by more than one bean within the same role on an email.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailHasContactWithAnEmailAddress_AddLeadWithAnEmailAddress($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $contact = SugarTestContactUtilities::createContact();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $lead = SugarTestLeadUtilities::createLead();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($contact, $address1));

        $result = $relationship->add($email, $this->createEmailParticipant($lead, $address2));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(2, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address1->id, $bean->email_address_id);

        $bean = array_shift($beans);
        $this->assertSame('Leads', $bean->parent_type);
        $this->assertSame($lead->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$contact->name} <{$address1->email_address}>, {$lead->name} <{$address2->email_address}>",
            $email->to_addrs_names
        );
    }

    /**
     * The same email address can be used by more than one bean within the same role on an email.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailHasContactWithAnEmailAddress_AddLeadWithSameEmailAddress($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $lead = SugarTestLeadUtilities::createLead();
        $relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $relationship->add($email, $this->createEmailParticipant($lead, $address));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(2, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $bean = array_shift($beans);
        $this->assertSame('Leads', $bean->parent_type);
        $this->assertSame($lead->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$contact->name} <{$address->email_address}>, {$lead->name} <{$address->email_address}>",
            $email->to_addrs_names
        );
    }

    /**
     * Adding an exact duplicate will result in no change.
     *
     * @dataProvider stateProvider
     * @covers ::add
     */
    public function testAdd_EmailHasContactWithAnEmailAddress_AddSameContactWithSameEmailAddress($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertFalse($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * A person record's email address can be changed for the email.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailHasContactWithAnEmailAddress_AddSameContactWithDifferentEmailAddress($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $contact = SugarTestContactUtilities::createContact();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($contact, $address1));

        $result = $relationship->add($email, $this->createEmailParticipant($contact, $address2));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address2->email_address}>", $email->to_addrs_names);
    }

    /**
     * A person record's email address can be changed for the email.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailHasContactWithAnEmailAddress_AddSameParticipantWithDifferentEmailAddress($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $contact = SugarTestContactUtilities::createContact();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();

        $ep = $this->createEmailParticipant($contact, $address1);
        $relationship->add($email, $ep);

        $ep->email_address_id = $address2->id;
        $result = $relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address2->email_address}>", $email->to_addrs_names);
    }

    /**
     * The `parent_type` and `parent_id` fields are patched when a bean is linked with an email address that is already
     * linked as a plain email address.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailHasAnEmailAddressParticipant_AddSameParticipantWithParent($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $ep = $this->createEmailParticipant(null, $address);
        $relationship->add($email, $ep);

        $ep->parent_type = $contact->getModuleName();
        $ep->parent_id = $contact->id;
        $result = $relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * The `email_address_id` field is preserved if an attempt is made to link the same person record without an email
     * address when that person record is already linked along with an email address.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_DraftHasContactWithAnEmailAddress_AddSameContactWithoutAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertFalse($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->to_addrs_names);
    }

    /**
     * The `email_address_id` field is overwritten with the person record's primary email address because the primary
     * email address is seen as a new email address to assign.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasContactWithAnEmailAddress_AddSameContactWithoutAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $primaryId = $contact->emailAddress->getGuid($contact->email1);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($primaryId, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->to_addrs_names);
    }

    /**
     * When the email address is not already linked, then it is added as a new participant.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_EmailHasAnEmailAddressParticipant_AddDifferentEmailAddress($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant(null, $address1));

        $result = $relationship->add($email, $this->createEmailParticipant(null, $address2));
        $this->assertTrue($result);

        $beans = $email->to_link->getBeans();
        $this->assertCount(2, $beans);

        $bean = array_shift($beans);
        $this->assertEmpty($bean->parent_type);
        $this->assertEmpty($bean->parent_id);
        $this->assertSame($address1->id, $bean->email_address_id);

        $bean = array_shift($beans);
        $this->assertEmpty($bean->parent_type);
        $this->assertEmpty($bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$address1->email_address}, {$address2->email_address}", $email->to_addrs_names);
    }

    /**
     * An Employees record should be seen as a Users record if the employee is a user.
     *
     * @covers ::add
     * @covers ::fixParentModule
     */
    public function testAdd_AddEmployee_ChangesToUser()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $ep = $this->createEmailParticipant($GLOBALS['current_user']);
        $ep->parent_type = 'Employees';
        $relationship->add($email, $ep);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
    }

    /**
     * An Employees record should be seen as an Employees record if the employee is not a user.
     *
     * @covers ::add
     * @covers ::fixParentModule
     */
    public function testAdd_AddEmployee_RemainsAnEmployee()
    {
        $employee = SugarTestUserUtilities::createAnonymousUser(true, 0, [
            'user_name' => '',
            'user_hash' => '',
        ]);

        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $ep = $this->createEmailParticipant($employee);
        $ep->parent_type = 'Employees';
        $relationship->add($email, $ep);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Employees', $bean->parent_type);
        $this->assertSame($employee->id, $bean->parent_id);
    }

    /**
     * Unlinking a person record deletes the EmailParticipants record and updates emails_text for the email.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     */
    public function testRemove_EmailHasContact_RemoveContact($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $contact = SugarTestContactUtilities::createContact();
        $ep = $this->createEmailParticipant($contact);
        $relationship->add($email, $ep);

        $result = $relationship->remove($email, $ep);
        $this->assertTrue($result);
        $this->assertEquals(1, $ep->deleted);

        $beans = $email->to_link->getBeans();
        $this->assertCount(0, $beans);

        $email->retrieveEmailText();
        $this->assertEmpty($email->to_addrs_names);
    }

    /**
     * Unlinking a person record deletes the EmailParticipants record and updates emails_text for the email.
     *
     * @dataProvider stateProvider
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     */
    public function testRemove_EmailHasAnEmailAddressParticipant_RemoveEmailAddressParticipant($state)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => $state]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $ep = $this->createEmailParticipant(null, $address);
        $relationship->add($email, $ep);

        $result = $relationship->remove($email, $ep);
        $this->assertTrue($result);
        $this->assertEquals(1, $ep->deleted);

        $beans = $email->to_link->getBeans();
        $this->assertCount(0, $beans);

        $email->retrieveEmailText();
        $this->assertEmpty($email->to_addrs_names);
    }

    /**
     * There is no need to update emails_text for the email since the email has not been saved yet.
     *
     * @dataProvider stateProvider
     * @covers ::remove
     * @covers EmailParticipant::mark_deleted
     */
    public function testRemove_YetToBeSavedEmailHasContact_RemoveContact($state)
    {
        $email = $this->getMockBuilder('Email')->setMethods(['saveEmailText'])->getMock();
        $email->new_with_id = true;
        $email->id = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $email->state = $state;
        $email->expects($this->never())->method('saveEmailText');
        // Even though the email is never saved, we want to be certain that all of its relationships are deleted after
        // the test.
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email->load_relationship('to_link');
        $contact = SugarTestContactUtilities::createContact();
        $ep = $this->createEmailParticipant($contact);
        $relationship->add($email, $ep);

        $result = $relationship->remove($email, $ep);
        $this->assertTrue($result);
        $this->assertEquals(1, $ep->deleted);

        $beans = $email->to_link->getBeans();
        $this->assertCount(0, $beans);
    }

    /**
     * There is no need to update emails_text for the email since the email has not been saved yet.
     *
     * @dataProvider stateProvider
     * @covers ::remove
     * @covers EmailParticipant::mark_deleted
     */
    public function testRemove_YetToBeSavedEmailHasAnEmailAddressParticipant_RemoveEmailAddressParticipant($state)
    {
        $email = $this->getMockBuilder('Email')->setMethods(['saveEmailText'])->getMock();
        $email->new_with_id = true;
        $email->id = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        $email->state = $state;
        $email->expects($this->never())->method('saveEmailText');
        // Even though the email is never saved, we want to be certain that all of its relationships are deleted after
        // the test.
        SugarTestEmailUtilities::setCreatedEmail($email->id);

        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email->load_relationship('to_link');
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $ep = $this->createEmailParticipant(null, $address);
        $relationship->add($email, $ep);

        $result = $relationship->remove($email, $ep);
        $this->assertTrue($result);
        $this->assertEquals(1, $ep->deleted);

        $beans = $email->to_link->getBeans();
        $this->assertCount(0, $beans);
    }

    /**
     * After a bean is deleted, the EmailParticipants' `parent_type` and `parent_id` fields won't be able to retrieve
     * the bean and resolve the `parent_name`. The email address is still valid, so the EmailParticipants row remains as
     * if it only contains a plain email address. Should the bean be restored, then the `parent_name` will be able to be
     * used again.
     *
     * @covers SugarBean::mark_deleted
     */
    public function testEmailAddressIsStillPresentIfBeanIsDeleted()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_to');
        $email = SugarTestEmailUtilities::createEmail();
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $relationship->add($email, $this->createEmailParticipant($contact, $address));
        $contact->mark_deleted($contact->id);

        $beans = $email->to_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        // The parent fields remain, but the parent can't be loaded anymore.
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        // Since the parent_name field can't be resolved, only the email address should appear. However,
        // Email::saveEmailText isn't called after deleting the contact. If the email is a draft, then it will get
        // called eventually when updating or sending the email. If the email is archived, then it will never get
        // called unless a user updates a field that can be changed for archived emails. We don't use the fields from
        // emails_text in any OOTB UIs, so this is not critical. The emails_text data is not a source of truth for the
        // sender or recipients of an email. The worst case scenario is that it becomes out of date, but it is
        // correctable by simply saving the email.
        //FIXME: MAR-4668 Resave an email's emails_text data after any of its sender/recipient's parents are deleted.
        $email->retrieveEmailText();
        $this->assertEquals(
            "{$contact->name} <{$address->email_address}>",
            $email->to_addrs_names,
            "The contact's name should still be used in emails_text"
        );
    }

    /**
     * Sets up an EmailParticipants bean from the data on the bean and the email address so that it is ready to add to a
     * relationship.
     *
     * @param null|SugarBean $bean
     * @param null|SugarBean $address
     * @return SugarBean
     */
    private function createEmailParticipant($bean, $address = null)
    {
        $ep = BeanFactory::newBean('EmailParticipants');
        $ep->new_with_id = true;
        $ep->id = \Sugarcrm\Sugarcrm\Util\Uuid::uuid1();
        BeanFactory::registerBean($ep);

        if ($bean) {
            $ep->parent_type = $bean->getModuleName();
            $ep->parent_id = $bean->id;
        }

        if ($address) {
            $ep->email_address_id = $address->id;
        }

        return $ep;
    }
}
