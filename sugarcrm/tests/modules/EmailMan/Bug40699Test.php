<?php 
//FILE SUGARCRM flav=pro ONLY
require_once('modules/EmailMan/EmailMan.php');
require_once 'SugarTestAccountUtilities.php';

class Bug40699Test extends Sugar_PHPUnit_Framework_TestCase
{
	var $testAccount;
	var $testContact;
	var $currentUser;
	
	public function setUp()
	{
	   $this->testAccount = SugarTestAccountUtilities::createAccount();
	   $this->testContact = SugarTestContactUtilities::createContact();
	   global $current_user;
	   $this->currentUser = $current_user;
	   $user = new User();
	   $user->retrieve('1');
	   $current_user = $user;
	}
	
	public function tearDown()
	{
	   SugarTestAccountUtilities::removeAllCreatedAccounts();
	   SugarTestContactUtilities::removeAllCreatedContacts();
	   global $current_user;
	   $current_user = $this->currentUser;
	}
	
	public function testGetListViewDataForAccounts()
	{
		$emailMan = new EmailMan();
		$emailMan->related_id = $this->testAccount->id;
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
        $this->assertEquals($data['RECIPIENT_NAME'], $this->testAccount->name, 'Assert that account name was correctly set');
    }
    

	public function testGetListViewDataForContacts()
	{
		$emailMan = new EmailMan();
		$emailMan->related_id = $this->testContact->id;
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
		
		$contact_name_expected = $this->testContact->first_name . ' ' . $this->testContact->last_name;
		
		$data = $emailMan->get_list_view_data();
        $this->assertEquals($data['RECIPIENT_NAME'], $contact_name_expected, 'Assert that contact name was correctly set');
    }    
}

?>