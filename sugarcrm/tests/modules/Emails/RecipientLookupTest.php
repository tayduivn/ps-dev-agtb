<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once('modules/Emails/RecipientLookup.php');


class RecipientLookupTest extends Sugar_PHPUnit_Framework_TestCase
{
    private $recipientLookup;

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->recipientLookup = new RecipientLookup();
    }

    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestProspectUtilities::removeAllCreatedProspects();
        SugarTestHelper::tearDown();
    }

    public function testLookupRecipient_SetAllProperties_RecipientResolved()
    {
        $contact = SugarTestContactUtilities::createContact();

        $input = array(
            "module" => 'Contacts',
            "id" => $contact->id,
            "email" => $contact->email1,
            "name" => $contact->name
        );
        $expected = array(
            "module" => 'Contacts',
            "id" => $contact->id,
            "email" => $contact->email1,
            "name" => $contact->name,
            "resolved" => true
        );
        $actual = $this->recipientLookup->lookup($input);

        $this->assertEquals($expected, $actual, "Expected Recipient to be Resolved From ID and Module");
    }

    public function testLookupRecipient_SetIdAndModule_RecipientResolved()
    {
        $contact = SugarTestContactUtilities::createContact();

        $input = array("module" => 'Contacts', "id" => $contact->id, "email" => '', "name" => '');
        $expected = array(
            "module" => 'Contacts',
            "id" => $contact->id,
            "email" => $contact->email1,
            "name" => $contact->name,
            "resolved" => true
        );
        $actual = $this->recipientLookup->lookup($input);

        $this->assertEquals($expected, $actual, "Expected Recipient to be Resolved From ID and Module");
    }

    public function testLookupRecipient_SetEmailAndModuleOnly_RecipientResolvesToModuleExpected()
    {
        $email = "unit_test_" . create_guid() . "@yahoo.com";
        $contact = SugarTestContactUtilities::createContact();
        $contact->email1 = $email;
        $contact->save();

        $lead = SugarTestLeadUtilities::createLead();
        $lead->email1 = $email;
        $lead->save();

        $input = array("module" => 'Leads', "id" => '', "email" => $email, "name" => '');
        $expected = array(
            "module" => 'Leads',
            "id" => $lead->id,
            "email" => $lead->email1,
            "name" => $lead->name,
            "resolved" => true
        );
        $actual = $this->recipientLookup->lookup($input);
        $this->assertEquals($expected, $actual, "Expected Lead Recipient to be Resolved From Email Address");

        $input = array("module" => 'Contacts', "id" => '', "email" => $email, "name" => '');
        $expected = array(
            "module" => 'Contacts',
            "id" => $contact->id,
            "email" => $contact->email1,
            "name" => $contact->name,
            "resolved" => true
        );
        $actual = $this->recipientLookup->lookup($input);
        $this->assertEquals($expected, $actual, "Expected Contact Recipient to be Resolved From Email Address");
    }


    public function testLookupRecipient_SetMultiplePotentialMatchesOnEmail_UnpredictableMatchingRecipientResolvedToFirstMatchFound()
    {
        $email = "unit_test_" . create_guid() . "@yahoo.com";

        $contact = SugarTestContactUtilities::createContact();
        $contact->email1 = $email;
        $contact->save();

        $lead = SugarTestLeadUtilities::createLead();
        $lead->email1 = $email;
        $lead->save();

        $input = array("module" => '', "id" => '', "email" => $email, "name" => '');
        $expected1 = array(
            "module" => 'Contacts',
            "id" => $contact->id,
            "email" => $contact->email1,
            "name" => $contact->name,
            "resolved" => true
        );
        $expected2 = array(
            "module" => 'Leads',
            "id" => $lead->id,
            "email" => $lead->email1,
            "name" => $lead->name,
            "resolved" => true
        );
        $actual = $this->recipientLookup->lookup($input);

        $this->assertTrue(
            ($expected1 == $actual) || ($expected2 == $actual),
            "Unexpected One of Multiple Recipients to Match"
        );
    }


    public function testLookupRecipient_SetInvalidContactId_RecipientNotFoundAndBadIdReturned()
    {
        $invalid_contact_id = create_guid();

        $input = array("module" => 'Contacts', "id" => $invalid_contact_id, "email" => '', "name" => '');
        $expected = array(
            "module" => 'Contacts',
            "id" => $invalid_contact_id,
            "email" => '',
            "name" => '',
            "resolved" => false
        );
        $actual = $this->recipientLookup->lookup($input);

        $this->assertEquals($expected, $actual, "Expected Recipient not to Resolve - Module Required with an ID");
    }

    public function testLookupRecipient_SetContactIdAndModuleAndUnmatchingName_RecipientResolvedAndInputPreserved()
    {
        $name = "George Jetson";
        $email = "unit_test_" . create_guid() . "@yahoo.com";

        $contact = SugarTestContactUtilities::createContact();

        $input = array("module" => 'Contacts', "id" => $contact->id, "email" => $email, "name" => $name);
        $expected = array(
            "module" => 'Contacts',
            "id" => $contact->id,
            "email" => $email,
            "name" => $name,
            "resolved" => true
        );
        $actual = $this->recipientLookup->lookup($input);
        $this->assertEquals($expected, $actual, "Unexpected Recipient to Resolve and Supplied Name not to be Replaced");
    }

    public function testLookupRecipient_SetContactIdAndEmail_IdAndEmailFound_RecipientResolved()
    {
        $email = "unit_test_" . create_guid() . "@yahoo.com";
        $contact1 = SugarTestContactUtilities::createContact();
        $contact1->email1 = $email;
        $contact1->save();

        $contact2 = SugarTestContactUtilities::createContact();
        $contact2->email1 = $email;
        $contact2->save();

        $contact3 = SugarTestContactUtilities::createContact();
        $contact3->email1 = $email;
        $contact3->save();

        $id = $contact2->id;

        $input = array("module" => '', "id" => $id, "email" => $contact2->email1, "name" => '');
        $expected = array(
            "module" => 'Contacts',
            "id" => $id,
            "email" => $contact2->email1,
            "name" => $contact2->name,
            "resolved" => true
        );
        $actual = $this->recipientLookup->lookup($input);
        $this->assertEquals($expected, $actual, "Expected Recipient to Resolve to Matching ID and Email");

    }


    public function testLookupRecipient_SetEmailAndIDOnly_EmailFoundButNotID_RecipientNotResolved()
    {
        $email = "unit_test_" . create_guid() . "@yahoo.com";
        $contact1 = SugarTestContactUtilities::createContact();
        $contact1->email1 = $email;
        $contact1->save();

        $contact2 = SugarTestContactUtilities::createContact();
        $contact2->email1 = $email;
        $contact2->save();

        $contact3 = SugarTestContactUtilities::createContact();
        $contact3->email1 = $email;
        $contact3->save();

        $id = $contact2->id . "abcdefg";

        $input = array("module" => '', "id" => $id, "email" => $contact2->email1, "name" => '');
        $expected = array(
            "module" => '',
            "id" => $id,
            "email" => $contact2->email1,
            "name" => '',
            "resolved" => false
        );
        $actual = $this->recipientLookup->lookup($input);
        $this->assertEquals($expected, $actual, "Expected Recipient Not to Resolve with unmatching ID");
    }


    public function testLookupRecipient_IDProvided_NoModule_EmailNotFound_IgnoreIDButReturnIt_Unresolved()
    {
        $email = "unit_test_" . create_guid() . "@yahoo.com";
        $name = "George Jetson";

        $input = array("module" => '', "id" => '123', "email" => $email, "name" => $name);
        $expected = array(
            "module" => '',
            "id" => '123',
            "email" => $email,
            "name" => $name,
            "resolved" => false
        );
        $actual = $this->recipientLookup->lookup($input);
        $this->assertEquals($expected, $actual, "Expected Supplied Data to be Returned on Unresolved ID");
    }

}
