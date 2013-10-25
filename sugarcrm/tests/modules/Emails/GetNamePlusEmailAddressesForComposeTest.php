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

/**
 * @ticket 32487
 */
class GetNamePlusEmailAddressesForComposeTest extends Sugar_PHPUnit_Framework_TestCase
{
	public function setUp()
    {
        SugarTestHelper::setUp('current_user');
	}

    public function tearDown()
    {
        SugarTestHelper::tearDown();
	    SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

    public function testGetNamePlusEmailAddressesForCompose()
	{
	    $account = SugarTestAccountUtilities::createAccount();

	    $email = BeanFactory::getBean('Emails');
	    $this->assertEquals(
	        "{$account->name} <{$account->email1}>",
	        $email->getNamePlusEmailAddressesForCompose('Accounts',array($account->id))
	        );

    }

    public function testGetNamePlusEmailAddressesForComposeMultipleIds()
	{
	    $account1 = SugarTestAccountUtilities::createAccount();
	    $account2 = SugarTestAccountUtilities::createAccount();
	    $account3 = SugarTestAccountUtilities::createAccount();

	    $email = BeanFactory::getBean('Emails');
	    $addressString = $email->getNamePlusEmailAddressesForCompose('Accounts',array($account1->id,$account2->id,$account3->id));
	    $this->assertContains("{$account1->name} <{$account1->email1}>",$addressString);
	    $this->assertContains("{$account2->name} <{$account2->email1}>",$addressString);
	    $this->assertContains("{$account3->name} <{$account3->email1}>",$addressString);
    }


	public function testGetNamePlusEmailAddressesForComposePersonModule()
	{
	    $contact = SugarTestContactUtilities::createContact();

	    $email = BeanFactory::getBean('Emails');
	    $this->assertEquals(
	        $GLOBALS['locale']->formatName($contact) . " <{$contact->email1}>",
	        $email->getNamePlusEmailAddressesForCompose('Contacts',array($contact->id))
	        );

    }

    public function testGetNamePlusEmailAddressesForComposeUser()
	{
	    $user = SugarTestUserUtilities::createAnonymousUser(false);
	    $user->email1 = 'foo@bar.com';
	    $user->save();

	    $email = BeanFactory::getBean('Emails');
	    $this->assertEquals(
	        $GLOBALS['locale']->formatName($user) . " <{$user->email1}>",
	        $email->getNamePlusEmailAddressesForCompose('Users',array($user->id))
	        );
    }
}