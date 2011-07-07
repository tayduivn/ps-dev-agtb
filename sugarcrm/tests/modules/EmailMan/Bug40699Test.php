<?php 
//FILE SUGARCRM flav=pro ONLY
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
 
require_once('modules/EmailMan/EmailMan.php');
require_once 'SugarTestAccountUtilities.php';

class Bug40699Test extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
	{
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	    $GLOBALS['current_user']->is_admin = '1';
	    $GLOBALS['current_user']->save();
	}
	
	public function tearDown()
	{
        SugarTestContactUtilities::removeAllCreatedContacts();
        SugarTestAccountUtilities::removeAllCreatedAccounts();
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}
	
	public function testGetListViewDataForAccounts()
	{
		$testAccount = SugarTestAccountUtilities::createAccount();
		
		$emailMan = new EmailMan();
		$emailMan->related_id = $testAccount->id;
		$emailMan->related_type = 'Accounts';
		
		$filter = array();
		$filter['campaign_name'] = 1;
		$filter['recipient_name'] = 1;
		$filter['recipient_email'] = 1;
		$filter['message_name'] = 1;
		$filter['send_date_time'] = 1;
		$filter['send_attempts'] = 1;
		$filter['in_queue'] = 1;
		
		$params = array();
		$params['massupdate'] = 1;
		
		$data = $emailMan->get_list_view_data();
		$this->assertEquals($data['RECIPIENT_NAME'], $testAccount->name, 'Assert that account name was correctly set');
    }
    

	public function testGetListViewDataForContacts()
	{
	    $testContact = SugarTestContactUtilities::createContact();
	    
		$emailMan = new EmailMan();
		$emailMan->related_id = $testContact->id;
		$emailMan->related_type = 'Contacts';
		
		$filter = array();
		$filter['campaign_name'] = 1;
		$filter['recipient_name'] = 1;
		$filter['recipient_email'] = 1;
		$filter['message_name'] = 1;
		$filter['send_date_time'] = 1;
		$filter['send_attempts'] = 1;
		$filter['in_queue'] = 1;
		
		$params = array();
		$params['massupdate'] = 1;
		
		$contact_name_expected = $testContact->first_name . ' ' . $testContact->last_name;
		
		$data = $emailMan->get_list_view_data();
		$this->assertEquals($data['RECIPIENT_NAME'], $contact_name_expected, 'Assert that contact name was correctly set');
    }    
}