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
 
class Bug44522Test extends Sugar_PHPUnit_Framework_OutputTestCase
{
    var $user;
    var $account;
    var $lead;
    var $contact;
    var $campaign;

    public function setUp()
    {
        //create user
        $this->user = $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();

        //create account
        $this->account = new Account();
        $this->account->name = 'bug44522 account '.date('Y-m-d-H-i-s');
        $this->account->save();
        
        //create campaign
        $this->campaign = SugarTestCampaignUtilities::createCampaign();

        //create contact
        $this->contact = new Contact();
        $this->lead = SugarTestLeadUtilities::createLead();
        
        $this->lead->campaign_id = $this->campaign->id;
        $this->lead->save();

    }
    
    public function tearDown()
    {
        //delete records created from db
        $GLOBALS['db']->query("DELETE FROM accounts WHERE id= '{$this->account->id}'");
        $GLOBALS['db']->query("DELETE FROM leads WHERE id= '{$this->lead->id}'");
        $GLOBALS['db']->query("DELETE FROM contacts WHERE id= '{$this->contact->id}'");
        $GLOBALS['db']->query("DELETE FROM campaigns WHERE id= '{$this->campaign->id}'");
        $GLOBALS['db']->query("DELETE FROM campaign_log WHERE campaign_id= '{$this->campaign->id}'");
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();

        //unset values
        unset($GLOBALS['current_user']);
        unset($this->user);
        unset($this->account);
        unset($this->contact);
        unset($this->lead);
        unset($this->campaign);
    }
    


    //run test to make sure there is an entry in campaign_log table for newly created contact during lead conversion (bug 44522)
    public function testConvertContactInCampaignLog()
    {

        $_POST = array();
        
        //set the request parameters and convert the lead
        $_REQUEST['module'] = 'Leads';
        $_REQUEST['action'] = 'ConvertLead';
        $_REQUEST['record'] = $this->lead->id;
        $_REQUEST['handle'] = 'save';
        $_REQUEST['selectedAccount'] = $this->account->id;

        //require view and call display class so that convert functionality is called
        require_once('modules/Leads/views/view.convertlead.php');
        $vc = new ViewConvertLead();
        $vc->display();

        //retrieve the lead again to make sure we have the latest converted lead in memory
        $this->lead->retrieve($this->lead->id);

        //retrieve the new contact id from the conversion
        $contact_id = $this->lead->contact_id;

        //throw error if contact id was not retrieved and exit test
        $this->assertTrue(!empty($contact_id), "contact id was not created during conversion process.  An error has ocurred, aborting rest of test.");
        if (empty($contact_id)){
            return;
        }
        //make sure the new contact has the account related and that it matches the lead account
        $query = "SELECT target_id FROM campaign_log WHERE campaign_id= '{$this->campaign->id}'";
        $result = $GLOBALS['db']->query($query);
        while($row = $GLOBALS['db']->fetchByAssoc($result)){
        $test_contact_id = $row['target_id'];
        }
        
        $this->assertEquals($contact_id, $test_contact_id);
    }
}