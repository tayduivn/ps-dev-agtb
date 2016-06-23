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
    }

    public static function tearDownAfterClass()
    {
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestEmailAddressUtilities::removeAllCreatedAddresses();
        parent::tearDownAfterClass();
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
     * There should always only be at most one row with role=from for an email.
     *
     * @covers ::add
     * @covers ::getRowToInsert
     * @covers ::checkExisting
     */
    public function testAdd()
    {
        $email = SugarTestEmailUtilities::createEmail('', array('state' => Email::EMAIL_STATE_DRAFT));
        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();
        $address = SugarTestEmailAddressUtilities::createEmailAddress();
        SugarTestEmailAddressUtilities::addAddressToPerson($contact, $address);

        $relationship1 = SugarRelationshipFactory::getInstance()->getRelationship('emails_contacts_from');
        $relationship2 = SugarRelationshipFactory::getInstance()->getRelationship('emails_accounts_from');

        $additionalFields = array();
        $relationship1->add($email, $contact, $additionalFields);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => null,
            'participant_id' => $contact->id,
            'participant_module' => 'Contacts',
            'role' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the contact without a defined email address');

        $additionalFields = array(
            'email_address_id' => $address->id,
        );
        $relationship1->add($email, $contact, $additionalFields);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => $address->id,
            'participant_id' => $contact->id,
            'participant_module' => 'Contacts',
            'role' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the contact with a defined email address');

        $additionalFields = array();
        $relationship2->add($email, $account, $additionalFields);
        $rows = $this->getRows(array('email_id' => $email->id));
        $this->assertCount(1, $rows, 'There should be one row');

        $expected = array(
            'email_id' => $email->id,
            'email_address_id' => null,
            'participant_id' => $account->id,
            'participant_module' => 'Accounts',
            'role' => 'from',
            'deleted' => '0',
        );
        $this->assertRow($expected, $rows[0], 'The row should be the account without a defined email address');
    }

    /**
     * Rows are deleted by the left-hand side link regardless of whether or not the right-hand side bean's module
     * matches the value of participant_module in the existing row.
     *
     * @covers ::removeAll
     * @covers ::checkExisting
     * @covers ::remove
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
    }

    /**
     * The value of participant_module is always set to the correct module for senders.
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
        $email = SugarTestEmailUtilities::createEmail('', array('state' => Email::EMAIL_STATE_DRAFT));
        $contact = SugarTestContactUtilities::createContact();

        $additionalFields = array(
            'participant_module' => $module,
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
            'participant_id' => $contact->id,
            'participant_module' => 'Contacts',
            'role' => 'from',
            'deleted' => 0,
        );
        $this->assertRow($expected, $row);
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
