<?php

/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
* subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
* the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
* http://www.sugarcrm.com/master-subscription-agreement
*
* If Company is not bound by the MSA, then by installing or using this file
* you are agreeing unconditionally that Company will be bound by the MSA and
* certifying that you have authority to bind Company accordingly.
*
* Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
********************************************************************************/
class EmailRelationshipsTest extends Sugar_PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        if (!empty($GLOBALS['sugar_config']['inbound_email_case_subject_macro'])) {
            $this->macro = $GLOBALS['sugar_config']['inbound_email_case_subject_macro'];
            unset($GLOBALS['sugar_config']['inbound_email_case_subject_macro']);
        }
    }

    public function tearDown()
    {
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestEmailUtilities::removeAllCreatedEmails();
        SugarTestCaseUtilities::removeAllCreatedCases();
        SugarTestHelper::tearDown();
        if (!empty($this->macro)) {
            $GLOBALS['sugar_config']['inbound_email_case_subject_macro'] = $this->macro;
        } else {
            unset($GLOBALS['sugar_config']['inbound_email_case_subject_macro']);
        }
    }

    public function testContact()
    {
        $cont = SugarTestContactUtilities::createContact('',
            array("email" => "testcontact@test.com"));
        // test direct link
        $email1 = SugarTestEmailUtilities::createEmail('',
            array("parent_id" => $cont->id, "parent_type" => 'Contacts',
                'from_addr' => "unit@test.com", "name" => "Test email 1")
        );
        // test link by email
        $email2 = SugarTestEmailUtilities::createEmail('',
            array('from_addr' => "testcontact@test.com", "name" => "Test email 2")
        );
        $email3 = SugarTestEmailUtilities::createEmail('',
            array('from_addr' => "unit@test.com",
                "to_addrs" => "unit@test.com,testcontact@test.com", "name" => "Test email 3")
        );

        $newcont = $cont->getCleanCopy();
        $newcont->retrieve($cont->id);
        $newcont->load_relationship('archived_emails');
        $beans = $newcont->archived_emails->getBeans();
        $this->assertCount(3, $beans);
        $this->assertArrayHasKey($email1->id, $beans, "Email 1 missing");
        $this->assertArrayHasKey($email2->id, $beans, "Email 2 missing");
        $this->assertArrayHasKey($email3->id, $beans, "Email 3 missing");
        $this->assertEquals($email1->name, $beans[$email1->id]->name, "Email 1 subject wrong");
        $this->assertEquals($email2->name, $beans[$email2->id]->name, "Email 2 subject wrong");
        $this->assertEquals($email3->name, $beans[$email3->id]->name, "Email 3 subject wrong");
    }

    public function testAccount()
    {
        $acct = SugarTestAccountUtilities::createAccount('', array("email" => "testacct@test.com"));
        $cont = SugarTestContactUtilities::createContact('',
            array("email" => "testcontact@test.com", "account_id" => $acct->id));
        $acct->load_relationship("contacts");
        $acct->contacts->add($cont);
        // test direct link
        $email1 = SugarTestEmailUtilities::createEmail('',
            array("parent_id" => $acct->id, "parent_type" => 'Accounts',
                'from_addr' => "unit@test.com", "name" => "Test email 1")
        );
        // test link by email
        $email2 = SugarTestEmailUtilities::createEmail('',
            array('from_addr' => "testacct@test.com", "name" => "Test email 2")
        );
        // test link direct by contact
        $email3 = SugarTestEmailUtilities::createEmail('',
            array("parent_id" => $cont->id, "parent_type" => 'Contacts',
                'from_addr' => "unit@test.com", "name" => "Test email 3")
        );
        // test link by contact email
        $email4 = SugarTestEmailUtilities::createEmail('',
            array('from_addr' => "unit@test.com",
                "to_addrs" => "unit@test.com,testcontact@test.com", "name" => "Test email 4")
        );

        $newacc = $acct->getCleanCopy();
        $newacc->retrieve($acct->id);
        $newacc->load_relationship('archived_emails');
        $beans = $newacc->archived_emails->getBeans();
        $this->assertCount(4, $beans);
        $this->assertArrayHasKey($email1->id, $beans, "Email 1 missing");
        $this->assertArrayHasKey($email2->id, $beans, "Email 2 missing");
        $this->assertArrayHasKey($email3->id, $beans, "Email 3 missing");
        $this->assertArrayHasKey($email4->id, $beans, "Email 4 missing");
        $this->assertEquals($email1->name, $beans[$email1->id]->name, "Email 1 subject wrong");
        $this->assertEquals($email2->name, $beans[$email2->id]->name, "Email 2 subject wrong");
        $this->assertEquals($email3->name, $beans[$email3->id]->name, "Email 3 subject wrong");
        $this->assertEquals($email4->name, $beans[$email4->id]->name, "Email 4 subject wrong");
    }

    public function testCase()
    {
        $case = SugarTestCaseUtilities::createCase();
        $case->retrieve($case->id);
        $cont = SugarTestContactUtilities::createContact('',
            array("email" => "testcontact@test.com"));
        $case->load_relationship("contacts");
        $case->contacts->add($cont);
        // test direct link
        $email1 = SugarTestEmailUtilities::createEmail('',
            array("parent_id" => $case->id, "parent_type" => 'Cases',
                'from_addr' => "unit@test.com", "name" => "Test email 1")
        );
        // test link direct by contact
        $email2 = SugarTestEmailUtilities::createEmail('',
            array("parent_id" => $cont->id, "parent_type" => 'Contacts',
                'from_addr' => "unit@test.com", "name" => "Test email 2")
        );
        // test link direct by contact - right subject
        $email3 = SugarTestEmailUtilities::createEmail('',
            array("parent_id" => $cont->id, "parent_type" => 'Contacts',
                'from_addr' => "unit@test.com",
                "name" => "[CASE=>{$case->case_number}] Test email 3")
        );
        // test link by contact email
        $email4 = SugarTestEmailUtilities::createEmail('',
            array('from_addr' => "unit@test.com",
                "to_addrs" => "unit@test.com,testcontact@test.com", "name" => "Test email 4")
        );
        // test link by contact email - - right subject
        $email5 = SugarTestEmailUtilities::createEmail('',
            array('from_addr' => "unit@test.com",
                "cc_addrs" => "unit@test.com,testcontact@test.com",
                "name" => "Re: [CASE=>{$case->case_number}] Test email 5")
        );

        $newcase = $case->getCleanCopy();
        $newcase->retrieve($case->id);
        $newcase->load_relationship('archived_emails');
        $newcase->emailSubjectMacro = "[CASE=>%1]";

        $beans = $newcase->archived_emails->getBeans();

        $this->assertCount(3, $beans);
        $this->assertArrayHasKey($email1->id, $beans, "Email 1 missing");
        $this->assertArrayNotHasKey($email2->id, $beans, "Email 2 should not be there");
        $this->assertArrayHasKey($email3->id, $beans, "Email 3 missing");
        $this->assertArrayNotHasKey($email4->id, $beans, "Email 4 should not be there");
        $this->assertArrayHasKey($email5->id, $beans, "Email 5 missing");

        $this->assertEquals($email1->name, $beans[$email1->id]->name, "Email 1 subject wrong");
        $this->assertEquals($email3->name, $beans[$email3->id]->name, "Email 3 subject wrong");
        $this->assertEquals($email5->name, $beans[$email5->id]->name, "Email 5 subject wrong");
    }
}