<?php 
require_once('modules/EmailAddresses/EmailAddress.php');

/**
 * Test cases for php file Emails/emailAddress.php
 */
class EmailAddressTest extends Sugar_PHPUnit_Framework_TestCase
{
	private $emailaddress;
	private $testEmailAddressString  = 'unitTest@sugarcrm.com';
	
	public function setUp()
	{
		$this->emailaddress = new EmailAddress();
	}
	
	public function tearDown()
	{
		unset($this->emailaddress);
		$query = "delete from email_addresses where email_address = '".$this->testEmailAddressString."';";
        $GLOBALS['db']->query($query);
	}
	
	public function testEmailAddress ()
	{
		$id = '';
		$module = '';
		$new_addrs=array();
		$primary='';
		$replyTo='';
		$invalid='';
		$optOut='';
		$in_workflow=false;
		$_REQUEST['_email_widget_id'] = 0;
		$_REQUEST['0emailAddress0'] = $this->testEmailAddressString;
		$_REQUEST['emailAddressPrimaryFlag'] = '0emailAddress0';
		$_REQUEST['emailAddressVerifiedFlag0'] = 'true';
		$_REQUEST['emailAddressVerifiedValue0'] = 'unitTest@sugarcrm.com';
		$requestVariablesSet = array('0emailAddress0','emailAddressPrimaryFlag','emailAddressVerifiedFlag0','emailAddressVerifiedValue0');
		$this->emailaddress->save($id, $module, $new_addrs, $primary, $replyTo, $invalid, $optOut, $in_workflow);
		foreach ($requestVariablesSet as $k)
		  unset($_REQUEST[$k]);		
		
		$this->assertEquals($this->emailaddress->addresses[0]['email_address'], $this->testEmailAddressString);
		$this->assertEquals($this->emailaddress->addresses[0]['primary_address'], 1);
	}
}
?>