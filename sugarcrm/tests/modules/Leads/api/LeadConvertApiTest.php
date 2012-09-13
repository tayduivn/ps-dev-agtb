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

require_once('tests/rest/RestTestBase.php');
require_once('modules/Leads/LeadConvert.php');

/***
 * Used to test Lead Convert in Leads Module endpoints from LeadConvertApi.php
 *
 * @group forecasts
 */
class LeadConvertApiTest extends RestTestBase
{
    protected $lead;
    protected static $user;

    public function setup()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('app_list_strings');
        $this->user = SugarTestUserUtilities::createAnonymousUser();

        //Create an anonymous user for login purposes/
        $this->_user = $this->user ;
        $GLOBALS['current_user'] = $this->_user;

        //createLead
        $this->lead = SugarTestLeadUtilities::createLead();
        $this->lead->save();
    }
    public function tearDown()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        SugarTestLeadUtilities::removeAllCreatedLeads();
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestOpportunityUtilities::removeAllCreatedOpps();
    }

    /**
     * @group leadconvertapi
     */
    public function testConvertLead_AllNewRecords_ConvertSuccessful(){
        $postData = array(
            "leadId" => $this->lead->id,
            "modules" => array(
                'Contacts' =>
                array(
                    'deleted' => '0',
                    'do_not_call' => '0',
                    'portal_active' => '0',
                    'preferred_language' => 'en_us',
                    'salutation' => 'Mrs.',
                    'first_name' => 'SugarLeadFirst1664617000',
                    'last_name' => 'SugarLeadLast',
                    'title' => 'd',
                    'department' => 'd',
                    'description' => '',
                    'team_id' => '',
                    'phone_home' => '',
                    'phone_mobile' => '',
                    'phone_work' => '',
                    'phone_fax' => '',
                    'primary_address_street' => '',
                    'primary_address_city' => '',
                    'primary_address_state' => '',
                    'primary_address_postalcode' => '',
                    'primary_address_country' => '',
                ),
                'Accounts' =>
                array(
                    'deleted' => '0',
                    'name' => 'd',
                    'team_id' => '',
                    'billing_address_street' => 's',
                    'billing_address_city' => 'd',
                    'billing_address_state' => 'd',
                    'billing_address_postalcode' => '',
                    'billing_address_country' => 'd',
                    'shipping_address_street' => '',
                    'shipping_address_city' => '',
                    'shipping_address_state' => '',
                    'shipping_address_postalcode' => '',
                    'shipping_address_country' => '',
                    'campaign_id' => '',
                    'phone_office' => 'd',
                    'website' => 'd',
                    'email1' => 'd',
                ),
                'Opportunities' =>
                array(
                    'deleted' => '0',
                    'forecast' => '-1',
                    'name' => 'dfdf',
                    'team_id' => '',
                    'campaign_id' => '',
                    'lead_source' => '',
                ),
            )

        );

        $response = $this->_restCall("Leads/" . $this->lead->id . '/convert', json_encode($postData), "POST");

        $lead = new Lead();
        $lead->retrieve($this->lead->id);

        $this->assertEquals(LeadConvert::STATUS_CONVERTED, $lead->status, 'Lead status field was not changed properly.');
        $this->assertEquals(1, $lead->converted, 'Lead converted field not set properly');
     }

    /**
     * @group leadconvertapi
     */
    public function testConvertLead_RecordsExists_ConvertSuccessful(){

        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();
        $opp = SugarTestOpportunityUtilities::createOpportunity();

        $postData = array(
            "leadId" => $this->lead->id,
            "modules" => array(
                'Contacts' =>
                array(
                    'id' => $contact->id
                ),
                'Accounts' =>
                array(
                    'id' => $account->id
                ),
                'Opportunities' =>
                array(
                    'id' => $opp->id
                ),
            )

        );

        $response = $this->_restCall("Leads/" . $this->lead->id . '/convert', json_encode($postData), "POST");

        $lead = new Lead();
        $lead->retrieve($this->lead->id);

        $this->assertEquals(LeadConvert::STATUS_CONVERTED, $lead->status, 'Lead status field was not changed properly.');
        $this->assertEquals(1, $lead->converted, 'Lead converted field not set properly');
    }

    /**
     * @group leadconvertapi
     */
    public function testConvertLead_LeadDoesNotExist_ConvertFailed(){

        $contact = SugarTestContactUtilities::createContact();
        $account = SugarTestAccountUtilities::createAccount();
        $opp = SugarTestOpportunityUtilities::createOpportunity();
        $fakeLeadId = '0000330000';

        $postData = array(
            "leadId" => $this->lead->id,
            "modules" => array(
                'Contacts' =>
                array(
                    'id' => $contact->id
                ),
                'Accounts' =>
                array(
                    'id' => $account->id
                ),
                'Opportunities' =>
                array(
                    'id' => $opp->id
                ),
            )

        );

        $response = $this->_restCall("Leads/" . $fakeLeadId . '/convert', json_encode($postData), "POST");
        $this->assertEquals(500, $response['info']['http_code']);
    }
}