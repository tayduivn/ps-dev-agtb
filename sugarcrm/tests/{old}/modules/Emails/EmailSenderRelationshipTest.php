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

require_once 'modules/Emails/EmailSenderRelationship.php';

/**
 * @coversDefaultClass EmailSenderRelationship
 */
class EmailSenderRelationshipTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        OutboundEmailConfigurationTestHelper::setUp();
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('current_user');
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
     * Only the current user can be added as the sender of a draft.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_AddAnEmailAddressToDraft_ParticipantIsSavedWithCurrentUserAsParentAndEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $result = $relationship->add($email, $this->createEmailParticipant(null, $address));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <$address->email_address>", $email->from_addr_name);
    }

    /**
     * An email address can be linked to an archived email without a person record.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_AddAnEmailAddressToAnArchivedEmail_ParticipantIsSavedWithoutParent()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $result = $relationship->add($email, $this->createEmailParticipant(null, $address));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertEmpty($bean->parent_type);
        $this->assertEmpty($bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals($address->email_address, $email->from_addr_name);
    }

    /**
     * Only the current user can be added as the sender of a draft.
     *
     * @covers ::add
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testAdd_AddContactWithAnEmailAddressToDraft_ThrowsException()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $relationship->add($email, $this->createEmailParticipant($contact, $address));
    }

    /**
     * An email address can be linked to an archived email without a person record.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_AddContactWithAnEmailAddressToAnArchivedEmail_ParticipantIsSavedWithParentAndEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();

        $result = $relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * Only the current user can be added as the sender of a draft.
     *
     * @covers ::add
     * @expectedException SugarApiExceptionNotAuthorized
     */
    public function testAdd_AddContactWithoutAnEmailAddressToDraft_ThrowsException()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();

        $relationship->add($email, $this->createEmailParticipant($contact));
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_DraftHasCurrentUserWithoutAnEmailAddress_AddCurrentUserWithAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));

        $result = $relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user'], $address));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_DraftHasCurrentUserWithAnEmailAddress_AddCurrentUserWithDifferentEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user'], $address1));

        $result = $relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user'], $address2));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address2->email_address}>", $email->from_addr_name);
    }

    /**
     * The `email_address_id` field is preserved if an attempt is made to link the same person record without an email
     * address when that person record is already linked along with an email address.
     *
     * @covers ::add
     */
    public function testAdd_DraftHasCurrentUserWithAnEmailAddress_AddCurrentUserWithoutAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user'], $address));

        $result = $relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));
        $this->assertFalse($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * It's a noop when adding the same sender without any change to the email address.
     *
     * @covers ::add
     */
    public function testAdd_DraftHasCurrentUserWithoutAnEmailAddress_AddCurrentUserWithoutAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));

        $result = $relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user']));
        $this->assertFalse($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $email->from_addr_name
        );
    }

    /**
     * It's a noop when adding the same sender without any change to the email address.
     *
     * @covers ::add
     */
    public function testAdd_DraftHasCurrentUserWithAnEmailAddress_AddCurrentUserWithSameEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user'], $address));

        $result = $relationship->add($email, $this->createEmailParticipant($GLOBALS['current_user'], $address));
        $this->assertFalse($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_DraftHasCurrentUserWithAnEmailAddress_AddSameParticipantWithDifferentEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $ep = $this->createEmailParticipant($GLOBALS['current_user'], $address1);
        $relationship->add($email, $ep);

        $ep->email_address_id = $address2->id;
        $result = $relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address2->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_DraftHasCurrentUserWithoutAnEmailAddress_AddSameParticipantWithAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $ep = $this->createEmailParticipant($GLOBALS['current_user']);
        $relationship->add($email, $ep);

        $ep->email_address_id = $address->id;
        $result = $relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_DraftHasCurrentUserWithAnEmailAddress_AddSameParticipantWithoutAnEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $ep = $this->createEmailParticipant($GLOBALS['current_user'], $address);
        $relationship->add($email, $ep);

        $ep->email_address_id = '';
        $result = $relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Users', $bean->parent_type);
        $this->assertSame($GLOBALS['current_user']->id, $bean->parent_id);
        $this->assertEmpty($bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals(
            "{$GLOBALS['current_user']->name} <{$GLOBALS['current_user']->email1}>",
            $email->from_addr_name
        );
    }

    /**
     * The email_address_id column is set to the sender's primary email address if the email is not a draft and the
     * email address was not chosen when the sender was added. This comes up when a record -- Accounts, Contacts, Leads,
     * etc. -- is added with the intention of using that record's primary email address and the email is immediately
     * being archived.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_AddContactWithoutAnEmailAddressToArchivedEmail_ParticipantIsSavedWithPrimaryEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $primaryId = $contact->emailAddress->getGuid($contact->email1);

        $result = $relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($primaryId, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->from_addr_name);
    }

    /**
     * The `parent_type` and `parent_id` fields are patched when a bean is linked with an email address that is already
     * linked as a plain email address.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasAnEmailAddress_AddContactWithSameEmailAddress_ParentIsAddedToParticipant()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $contact = SugarTestContactUtilities::createContact();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);
        $relationship->add($email, $this->createEmailParticipant(null, $address));

        $result = $relationship->add($email, $this->createEmailParticipant($contact, $address));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->from_addr_name);
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
    public function testAdd_ArchivedEmailHasAnEmailAddress_AddContactWithoutEmailAddress_ContactIsAddedWithPrimaryEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $contact = SugarTestContactUtilities::createContact();
        $primaryId = $contact->emailAddress->getGuid($contact->email1);
        $relationship->add($email, $this->createEmailParticipant(null, $address));

        $result = $relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($primaryId, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasAnEmailAddress_AddContactWithDifferentEmailAddress_ParticipantIsSavedWithParentAndEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $contact = SugarTestContactUtilities::createContact();
        $relationship->add($email, $this->createEmailParticipant(null, $address1));

        $result = $relationship->add($email, $this->createEmailParticipant($contact, $address2));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address2->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasAnEmailAddress_AddContactToSameParticipant_ParticipantIsSavedWithParent()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $contact = SugarTestContactUtilities::createContact();
        $ep = $this->createEmailParticipant(null, $address);
        $relationship->add($email, $ep);

        $ep->parent_type = $contact->getModuleName();
        $ep->parent_id = $contact->id;
        $result = $relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasAnEmailAddress_AddDifferentEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant(null, $address1));

        $result = $relationship->add($email, $this->createEmailParticipant(null, $address2));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertEmpty($bean->parent_type);
        $this->assertEmpty($bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals($address2->email_address, $email->from_addr_name);
    }

    /**
     * The `parent_type` and `parent_id` fields are preserved if an attempt is made to link an email address when that
     * email address is already linked along with a bean.
     *
     * @covers ::add
     * @covers Email::retrieveEmailText
     */
    public function testAdd_EmailHasContactWithAnEmailAddress_AddSameEmailAddress_NoChangeIsMade()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        $relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $relationship->add($email, $this->createEmailParticipant(null, $address));
        $this->assertFalse($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender can be replaced. There can only be one.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasContact_AddLeadWithoutAnEmailAddress_LeadIsAddedWithPrimaryEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead();
        $primaryId = $lead->emailAddress->getGuid($lead->email1);
        $relationship->add($email, $this->createEmailParticipant($contact));

        $result = $relationship->add($email, $this->createEmailParticipant($lead));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Leads', $bean->parent_type);
        $this->assertSame($lead->id, $bean->parent_id);
        $this->assertSame($primaryId, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$lead->name} <{$lead->email1}>", $email->from_addr_name);
    }

    /**
     * The sender can be replaced. There can only be one.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasContact_AddLeadWithAnEmailAddress_LeadIsAddedWithEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($lead, $address);
        $relationship->add($email, $this->createEmailParticipant($contact));

        $result = $relationship->add($email, $this->createEmailParticipant($lead, $address));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Leads', $bean->parent_type);
        $this->assertSame($lead->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$lead->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender can be replaced. There can only be one.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasContactWithAnEmailAddress_AddLeadWithSameEmailAddress_LeadIsAddedWithEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $lead = SugarTestLeadUtilities::createLead();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);
        SugarTestEmailAddressUtilities::addAddressToPerson($lead, $address);
        $relationship->add($email, $this->createEmailParticipant($contact, $address));

        $result = $relationship->add($email, $this->createEmailParticipant($lead, $address));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Leads', $bean->parent_type);
        $this->assertSame($lead->id, $bean->parent_id);
        $this->assertSame($address->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$lead->name} <{$address->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasContactWithAnEmailAddress_AddSameContactWithDifferentEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address1);
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address2);
        $relationship->add($email, $this->createEmailParticipant($contact, $address1));

        $result = $relationship->add($email, $this->createEmailParticipant($contact, $address2));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address2->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasContactWithAnEmailAddress_AddSameParticipantWithDifferentEmailAddress()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address1);
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address2);
        $ep = $this->createEmailParticipant($contact, $address1);
        $relationship->add($email, $ep);

        $ep->email_address_id = $address2->id;
        $result = $relationship->add($email, $ep);
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($address2->id, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address2->email_address}>", $email->from_addr_name);
    }

    /**
     * The sender's email address can be changed for the email.
     *
     * @covers ::add
     * @covers EmailParticipant::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_ArchivedEmailHasContactWithAnEmailAddress_AddSameContactWithoutAnEmailAddress_PrimaryEmailAddressIsUsed()
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_from');
        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_ARCHIVED]);
        $contact = SugarTestContactUtilities::createContact();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $primaryId = $contact->emailAddress->getGuid($contact->email1);
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address1);
        $relationship->add($email, $this->createEmailParticipant($contact, $address1));

        $result = $relationship->add($email, $this->createEmailParticipant($contact));
        $this->assertTrue($result);

        $beans = $email->from_link->getBeans();
        $this->assertCount(1, $beans);

        $bean = array_shift($beans);
        $this->assertSame('Contacts', $bean->parent_type);
        $this->assertSame($contact->id, $bean->parent_id);
        $this->assertSame($primaryId, $bean->email_address_id);

        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$contact->email1}>", $email->from_addr_name);
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
