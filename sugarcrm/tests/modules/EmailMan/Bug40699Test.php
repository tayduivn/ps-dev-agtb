<?php 
//FILE SUGARCRM flav=pro ONLY
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