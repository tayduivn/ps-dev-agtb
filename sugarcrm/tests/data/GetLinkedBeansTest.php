<?php
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
class GetLinkedBeansTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();
    protected $createdFiles = array();

    public function setUp()
	{
	    $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['current_user']->setPreference('timezone', "America/Los_Angeles");
	    $GLOBALS['current_user']->setPreference('datef', "m/d/Y");
		$GLOBALS['current_user']->setPreference('timef', "h.iA");

        $beanList = array();
        $beanFiles = array();
        require('include/modules.php');
        $GLOBALS['beanList'] = $beanList;
        $GLOBALS['beanFiles'] = $beanFiles;
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

        unset($GLOBALS['beanFiles'], $GLOBALS['beanList']);
	}

    public function testGetLinkedBeans()
    {
        //Test the accounts_leads relationship
        $account = BeanFactory::newBean("Accounts");
        $account->name = "GetLinkedBeans Test Account";
        $account->save();
        $this->createdBeans[] = $account;

        $case  = BeanFactory::newBean("Cases");
        $case->name = "GetLinkedBeans Test Cases";
        $case->save();
        $this->createdBeans[] = $case;

        $this->assertTrue($account->load_relationship("cases"));
        $this->assertInstanceOf("Link2", $account->cases);
        $this->assertTrue($account->cases->loadedSuccesfully());
        $account->cases->add($case);
        $account->save();

        $where = array(
                 'lhs_field' => 'id',
                 'operator' => ' LIKE ',
                 'rhs_value' => "{$case->id}",
        );

        $cases = $account->get_linked_beans('cases', 'Case', array(), 0, 10, 0, $where);
        $this->assertEquals(1, count($cases), 'Assert that we have found the test case linked to the test account');

        $contact  = BeanFactory::newBean("Contacts");
        $contact->first_name = "First Name GetLinkedBeans Test Contacts";
        $contact->last_name = "First Name GetLinkedBeans Test Contacts";
        $contact->save();
        $this->createdBeans[] = $contact;

        $this->assertTrue($account->load_relationship("contacts"));
        $this->assertInstanceOf("Link2", $account->contacts);
        $this->assertTrue($account->contacts->loadedSuccesfully());
        $account->contacts->add($contact);

        $where = array(
                 'lhs_field' => 'id',
                 'operator' => ' LIKE ',
                 'rhs_value' => "{$contact->id}",
        );

        $contacts = $account->get_linked_beans('contacts', 'Contact', array(), 0, -1, 0, $where);
        $this->assertEquals(1, count($contacts), 'Assert that we have found the test contact linked to the test account');
    }
    
}
?>