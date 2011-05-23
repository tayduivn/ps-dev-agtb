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

	public function testEmailAddress()
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

	public function testSaveEmailAddressUsingSugarbeanSave()
	{
	    $this->emailaddress->email_address = $this->testEmailAddressString;
	    $this->emailaddress->opt_out = '1';
	    $this->emailaddress->save();

	    $this->assertTrue(!empty($this->emailaddress->id));
	    $this->assertEquals(
	        $this->emailaddress->id,
	        $GLOBALS['db']->getOne("SELECT id FROM email_addresses WHERE id = '{$this->emailaddress->id}' AND email_address = '{$this->testEmailAddressString}' and opt_out = '1'"),
	        'Email Address record not added'
	        );
	}

	public function getEmails()
	{
	    return array(
	        array("test@sugarcrm.com", "", "test@sugarcrm.com"),
	        array("John Doe <test@sugarcrm.com>", "John Doe", "test@sugarcrm.com"),
	        array("\"John Doe\" <test@sugarcrm.com>", "John Doe", "test@sugarcrm.com"),
	        array("\"John Doe\" <test@sugarcrm.com>", "John Doe", "test@sugarcrm.com"),
	        array("\"John Doe (<doe>)\" <test@sugarcrm.com>", "John Doe (doe)", "test@sugarcrm.com"),
	        // bad ones
	        array("\"John Doe (<doe>)\"", "John Doe (doe)", ""),
	        array("John Doe <vlha>", "John Doe vlha", ""),
	        array("<script>alert(1)</script>", "scriptalert(1)/script", ""),
	        array("Test <test@test>", "Test test@test", ""),
	        );
	}

	/**
	 * @dataProvider getEmails
	 * @param string $addr
	 * @param string $name
	 * @param string $email
	 */

	public function testSplitEmail($addr, $name, $email)
	{
	    $parts = $this->emailaddress->splitEmailAddress($addr);
	    $this->assertEquals($name, $parts['name']);
	    $this->assertEquals($email, $parts['email']);
	}
}
