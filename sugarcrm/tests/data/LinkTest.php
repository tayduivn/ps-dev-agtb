<?php
// FILE SUGARCRM flav=pro ONLY
/********************************************************************************
 *The contents of this file are subject to the SugarCRM Professional End User License Agreement
 *("License") which can be viewed at http://www.sugarcrm.com/EULA.
 *By installing or using this file, You have unconditionally agreed to the terms and conditions of the License, and You may
 *not use this file except in compliance with the License. Under the terms of the license, You
 *shall not, among other things: 1) sublicense, resell, rent, lease, redistribute, assign or
 *otherwise transfer Your rights to the Software, and 2) use the Software for timesharing or
 *service bureau purposes such as hosting the Software for commercial gain and/or for the benefit
 *of a third party.  Use of the Software may be subject to applicable fees and any use of the
 *Software without first paying applicable fees is strictly prohibited.  You do not have the
 *right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and
 * (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for requirements.
 *Your Warranty, Limitations of liability and Indemnity are expressly stated in the License.  Please refer
 *to the License for the specific language governing these rights and limitations under the License.
 *Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/

require_once("data/BeanFactory.php");
class LinkTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();
    protected $createdFiles = array();

    public function setUp()
	{

	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");
	}

	public function tearDown()
	{
	    foreach($this->createdBeans as $bean)
        {
            $bean->retrieve($bean->id);
            $bean->mark_deleted($bean->id);
        }
        foreach($this->createdFiles as $file)
        {
            if (is_file($file))
                unlink($file);
        }
	}
	

    /**
     * Create a new account and bug, then link them.
     * @return void
     */
	public function testManytoMany()
	{
        $module = "Accounts";
        global $beanList, $beanFiles;
        require('include/modules.php');
	    
        $account = BeanFactory::newBean($module);
        $account->name = "LinkTestAccount";
        $account->save();
        $this->createdBeans[] = $account;

        $bug = BeanFactory::newBean("Bugs");
        $bug->name = "LinkTestBug";
        $bug->save();
        $this->createdBeans[] = $bug;

        $accountsLink = new Link2("bugs", $account);
        $accountsLink->add($bug);

        //Create a new link to refresh from the database
        $accountsLink2 = new Link2("bugs", $account);
        $related = $accountsLink2->getBeans(null);
        $this->assertNotEmpty($related);

        $this->assertNotEmpty($related[$bug->id]);

        //Now test deleting the link
        $accountsLink2->delete($account, $bug);

        //Create a new link to refresh from the database
        $accountsLink3 = new Link2("bugs", $account);

        $related = $accountsLink3->getBeans(null);
        $this->assertEmpty($related);
    }

    public function testOnetoMany()
	{

        //Test the accounts_leads relationship
        $account = BeanFactory::newBean("Accounts");
        $account->name = "Link 1->M Test Account";
        $account->save();
        $this->createdBeans[] = $account;

        $account2 = BeanFactory::newBean("Accounts");
        $account2->name = "Link 1->M Test Account2";
        $account2->save();
        $this->createdBeans[] = $account2;

        $lead  = BeanFactory::newBean("Leads");
        $lead->last_name = "Link 1->M Test Lead";
        $lead->save();
        $this->createdBeans[] = $lead;

        //Start by adding it from the Account side.
        $this->assertTrue($account->load_relationship("leads"));
        $this->assertInstanceOf("Link2", $account->leads);
        $this->assertTrue($account->leads->loadedSuccesfully());
        $account->leads->add($lead);

        $related = $account->leads->getBeans();
        $this->assertNotEmpty($related);
        $this->assertNotEmpty($related[$lead->id]);


        //Test loading the link from the Lead side.
        $this->assertTrue($lead->load_relationship("accounts"));
        $this->assertInstanceOf("Link2", $lead->accounts);
        $this->assertTrue($lead->accounts->loadedSuccesfully());

        $related = $lead->accounts->getBeans();
        $this->assertNotEmpty($related);
        $this->assertNotEmpty($related[$account->id]);


        //Test overriding the one side
        $this->assertTrue($account2->load_relationship("leads"));
        $this->assertInstanceOf("Link2", $account2->leads);
        $this->assertTrue($account2->leads->loadedSuccesfully());
        $account2->leads->add($lead);
        $related = $account2->leads->getBeans();
        $this->assertNotEmpty($related);
        $this->assertNotEmpty($related[$lead->id]);

        //Verify only one on the Lead side.
        $this->assertTrue($lead->load_relationship("accounts"));
        $this->assertInstanceOf("Link2", $lead->accounts);
        $this->assertTrue($lead->accounts->loadedSuccesfully());

        $related = $lead->accounts->getBeans();
        $this->assertNotEmpty($related);
        $this->assertTrue(empty($related[$account->id]));
        $this->assertNotEmpty($related[$account2->id]);
    
    }

    public function testParentRelationships()
	{
        $lead  = BeanFactory::newBean("Leads");
        $lead->last_name = "Parent Lead";
        $lead->save();
        $this->createdBeans[] = $lead;

        $note1  = BeanFactory::newBean("Notes");
        $note1->name = "Lead Note 1";
        $note1->save();
        $this->createdBeans[] = $note1;

        $note2  = BeanFactory::newBean("Notes");
        $note2->name = "Lead Note 2";
        $note2->save();
        $this->createdBeans[] = $note2;

        //Test saving from the RHS
        $note1->load_relationship ('leads') ;
        $note1->leads->add($lead);

        $this->assertEquals($note1->parent_id, $lead->id);
        $this->assertEquals($note1->parent_type, "Leads");

        //Test saving from the LHS
        $lead->load_relationship ('notes') ;
        $lead->notes->add($note2);

        $this->assertEquals($note2->parent_id, $lead->id);
        $this->assertEquals($note2->parent_type, "Leads");
    }
}