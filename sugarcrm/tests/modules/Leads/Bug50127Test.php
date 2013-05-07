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

require_once('modules/Leads/views/view.convertlead.php');

/**
 * 
 * Test if Contact is properly linked to Lead if we are not creating a contact
 * but linking an existing one.
 * Check if Account is linked with Contact.
 * 
 * @author avucinic@sugarcrm.com
 *
 */
class Bug50127Test extends Sugar_PHPUnit_Framework_OutputTestCase
{

    public function setUp()
    {
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('app_list_strings');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
    }
    
    public function tearDown()
    {
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        
        SugarTestHelper::tearDown();

        $_REQUEST = array();
    }
 
    /**
     * Create a lead and convert it to an existing Account and Contact
     */
    public function testConvertLinkingExistingContact() {
        $this->markTestIncomplete('Needs to be fixed by FRM team.');

        // Create records
        $lead = SugarTestLeadUtilities::createLead();
        $account = SugarTestAccountUtilities::createAccount();
        $contact = SugarTestContactUtilities::createContact();

        // ConvertLead to an existing Contact and Account
        $_REQUEST = array (
            'module' => 'Leads',
            'record' => $lead->id,
            'isDuplicate' => 'false',
            'action' => 'ConvertLead',
            // Existing Contact
            'convert_create_Contacts' => 'false',
            'report_to_name' => $contact->name,
            'reports_to_id' => $contact->id,
            // Existing Account
            'convert_create_Accounts' => 'false',
            'account_name' => $account->name,
            'account_id' => $account->id,
            // Save
            'handle' => 'save',
        );

        // Call display to trigger conversion
        $vc = new ViewConvertLead();
        $vc->display();

        // Refresh Lead
        $leadId = $lead->id;
        $lead = new Lead();
        $lead->retrieve($leadId);
        // Refresh Contact
        $contactId = $contact->id;
        $contact = new Contact();
        $contact->retrieve($contactId);

        // Check if contact it's linked properly
        $this->assertEquals($contact->id, $lead->contact_id, 'Contact not linked with Lead successfully.');
        // Check if account is linked with lead properly
        $this->assertEquals($account->id, $lead->account_id, 'Account not linked with Lead successfully.');
        // Check if account is linked with contact properly        
        $this->assertEquals($account->id, $contact->account_id, 'Account not linked with Contact successfully.');
        // Check Lead Status, should be converted
        $this->assertEquals('Converted', $lead->status, "Lead status should be 'Converted'.");
    }
}
