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
        parent::tearDownAfterClass();
    }

    protected function tearDown()
    {
        // Clean up any dangling beans that need to be resaved.
        SugarRelationship::resaveRelatedBeans(false);
        parent::tearDown();
    }

    public function setParticipantModuleProvider()
    {
        return array(
            array(null),
            array('Accounts'),
            array('Contacts'),
            array('EmailAddresses'),
            array('Leads'),
            array('Prospects'),
            array('Users'),
        );
    }

    /**
     * There should always only be at most one row with address_type=from for an email.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers ::checkExisting
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();
        $lead = SugarTestLeadUtilities::createLead();
        $address1 = SugarTestEmailAddressUtilities::createEmailAddress();
        $address2 = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address2);
        SugarTestEmailAddressUtilities::addAddressToPerson($lead, $address2);

        $contactsRelationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_from');
        $accountsRelationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_accounts_from');
        $addressesRelationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_email_addresses_from');
        $leadsRelationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_leads_from');

        $addressesRelationship->add($email, $address1);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address1->id,
            'bean_id' => $address1->id,
            'bean_type' => 'EmailAddresses',
            'address_type' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the email address');

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals($address1->email_address, $email->from_addr_name);

        $addressesRelationship->add($email, $address2);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address2->id,
            'bean_id' => $address2->id,
            'bean_type' => 'EmailAddresses',
            'address_type' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the new email address');

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals($address2->email_address, $email->from_addr_name);

        $additionalFields = array(
            'email_address_id' => $address2->id,
        );
        $leadsRelationship->add($email, $lead, $additionalFields);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address2->id,
            'bean_id' => $lead->id,
            'bean_type' => 'Leads',
            'address_type' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the lead with the same email address');

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$lead->name} <{$address2->email_address}>", $email->from_addr_name);

        $additionalFields = array(
            'email_address_id' => $address2->id,
        );
        $contactsRelationship->add($email, $contact, $additionalFields);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address2->id,
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the contact with the same email address');

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address2->email_address}>", $email->from_addr_name);

        $addressesRelationship->add($email, $address2);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address2->id,
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should still reference the contact');

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address2->email_address}>", $email->from_addr_name);

        $additionalFields = array(
            'email_address_id' => $address1->id,
        );
        $contactsRelationship->add($email, $contact, $additionalFields);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address1->id,
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the contact with the new email address');

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$contact->name} <{$address1->email_address}>", $email->from_addr_name);

        $accountPrimaryAddress = $account->emailAddress->getPrimaryAddress($account);
        $accountPrimaryAddressId = $account->emailAddress->getGuid($accountPrimaryAddress);

        $additionalFields = array();
        $accountsRelationship->add($email, $account, $additionalFields);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $accountPrimaryAddressId,
            'bean_id' => $account->id,
            'bean_type' => 'Accounts',
            'address_type' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the account with its primary email address');

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEquals("{$account->name} <{$accountPrimaryAddress}>", $email->from_addr_name);
    }

    /**
     * The sender of a draft is always replaced by the current user once the email is resaved.
     *
     * @covers ::add
     * @covers Email::save
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testAdd_TheCurrentUserIsTheSenderForDrafts()
    {
        $primaryAddress = $GLOBALS['current_user']->emailAddress->getPrimaryAddress($GLOBALS['current_user']);
        $primaryAddressId = $GLOBALS['current_user']->emailAddress->getGuid($primaryAddress);

        $email = SugarTestEmailUtilities::createEmail('', ['state' => Email::STATE_DRAFT]);
        $contact = SugarTestContactUtilities::createContact();
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_from');

        $additionalFields = [];
        $relationship->add($email, $contact, $additionalFields);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => null,
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the contact without an email address');

        // The sender is replaced with the current user after re-saving the draft.
        SugarRelationship::resaveRelatedBeans();
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => null,
            'bean_id' => $GLOBALS['current_user']->id,
            'bean_type' => 'Users',
            'address_type' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the current user without an email address');

        $email->retrieveEmailText();
        $this->assertEquals("{$GLOBALS['current_user']->name} <{$primaryAddress}>", $email->from_addr_name);
    }

    /**
     * Rows are deleted by the left-hand side link regardless of whether or not the right-hand side bean's module
     * matches the value of bean_type in the existing row.
     *
     * @covers ::removeAll
     * @covers ::checkExisting
     * @covers ::remove
     * @covers Email::saveEmailText
     * @covers Email::retrieveEmailText
     * @covers SugarRelationship::resaveRelatedBeans
     */
    public function testRemoveAll_UsingLhsLink()
    {
        $email = SugarTestEmailUtilities::createEmail();
        $contact = SugarTestContactUtilities::createContact();

        $relationship1 = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_from');
        $relationship1->add($email, $contact);

        $relationship2 = SugarRelationshipFactory::getInstance()->getRelationship('emails_accounts_from');

        $link = $this->getMockBuilder('Link2')
            ->disableOriginalConstructor()
            ->setMethods(array('getSide', 'getFocus'))
            ->getMock();
        $link->method('getSide')
            ->willReturn(REL_LHS);
        $link->method('getFocus')
            ->willReturn($email);

        $actual = $relationship2->removeAll($link);
        $this->assertTrue($actual);

        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(0, $rows, 'The row should have been removed when using the left-hand side link');

        SugarRelationship::resaveRelatedBeans();
        $email->retrieveEmailText();
        $this->assertEmpty($email->from_addr_name);
    }

    /**
     * The value of bean_type is always set to the correct module for senders.
     *
     * @covers ::getRowToInsert
     * @dataProvider setParticipantModuleProvider
     * @param null|string $module
     */
    public function testGetRowToInsert($module)
    {
        $relationship = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_from');
        // Use draft state to avoid automatically setting the email_address_id column. That functionality is covered by
        // tests in EmailRecipientRelationshipTest.
        $email = SugarTestEmailUtilities::createEmail('', array('state' => Email::STATE_DRAFT));
        $contact = SugarTestContactUtilities::createContact();

        $additionalFields = array(
            'bean_type' => $module,
        );
        // Drop empty values, like when $module comes in as null.
        $additionalFields = array_filter($additionalFields);

        $row = SugarTestReflection::callProtectedMethod(
            $relationship,
            'getRowToInsert',
            array(
                $email,
                $contact,
                $additionalFields,
            )
        );

        $expected = array(
            'email_id' => $email->id,
            'bean_id' => $contact->id,
            'bean_type' => 'Contacts',
            'address_type' => 'from',
            'deleted' => 0,
            'email_address_id' => null,
        );
        $this->assertRow($expected, $row);
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
     * @param string $message
     */
    protected function assertRow(array $expected, array $row, $message = '')
    {
        // Testing for id is unnecessary.
        unset($row['id']);

        // Assert that date_modified is not empty. Then discard it because testing the actual value is unnecessary.
        $this->assertNotEmpty($row['date_modified']);
        unset($row['date_modified']);

        $this->assertEquals($expected, $row, $message);
    }
}
