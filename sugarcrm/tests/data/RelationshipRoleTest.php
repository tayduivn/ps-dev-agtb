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
class RelationshipRoleTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $createdBeans = array();
    protected $createdFiles = array();

    public function setUp()
	{
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('current_user');
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
        SugarTestHelper::tearDown();
	}
	

    /**
     * Create a new account and bug, then link them.
     * @return void
     */
	public function testQuoteAccountsRole()
	{
        require('include/modules.php');
	    $account = BeanFactory::newBean("Accounts");
        $account->name = "RoleTestAccount";
        $account->save();
        $this->createdBeans[] = $account;

        $quote = BeanFactory::newBean("Quotes");
        $quote->name = "RoleTestQuote";
        $quote->save();
        $this->createdBeans[] = $quote;

        $quote->load_relationship("billing_accounts");
        $quote->billing_accounts->add($account);

        //Now check the row in the database
        $quote->set_account();
        $this->assertEquals($account->name, $quote->billing_account_name);
    }

    /**
     * Create a new account and bug, then link them.
     * @return void
     */
	public function testOne2MGetJoinWithRole()
	{
        global $db;
        require('include/modules.php');
	    $task = BeanFactory::newBean("Tasks");
        $task->name = "RoleTestTask";
        $task->save();
        $this->createdBeans[] = $task;

        $opp = BeanFactory::newBean("Opportunities");
        $opp->name = "RoleTestOpp";
        $opp->save();
        $this->createdBeans[] = $opp;

        $task->load_relationship("opportunities");
        $task->opportunities->add($opp);
        $join = $task->opportunities->getJoin(array(
            'join_type' => "LEFT JOIN",
            'right_join_table_alias' => "jt1",
            'right_join_table_link_alias' => "jtl_1",
            'join_table_alias' => "jt2",
            'join_table_link_alias' => "jtl_2",
            'left_join_table_alias' => "jt2",
            'left_join_table_link_alias' => "jtl_2",
            'primary_table_name' => "jt2",
        ));
        $this->assertContains("jt1.parent_type = 'Opportunities'", $join);
        $this->assertContains("jt1.parent_id=jt2.id", $join);
        $result = $db->query("SELECT count(jt1.id) as count FROM tasks jt1 $join WHERE jt1.id='{$task->id}'");
        $this->assertTrue($result != false, "One2M getJoin returned invalid SQL");
        //sqlsrv_num_rows seems buggy
        //$this->assertEquals(1, $db->getRowCount($result));
        $row = $db->fetchByAssoc($result);
        $this->assertEquals(1, $row['count']);

        //Now check that it also works from the other side
        $opp->load_relationship("tasks");
        $join = $opp->tasks->getJoin(array(
            'join_type' => "LEFT JOIN",
            'right_join_table_alias' => "jt2",
            'right_join_table_link_alias' => "jt2_1",
            'join_table_alias' => "jt2",
            'join_table_link_alias' => "jt2_2",
            'left_join_table_alias' => "jt1",
            'left_join_table_link_alias' => "jtl_2",
            'primary_table_name' => "jt1",
        ));
        $this->assertContains("jt2.parent_type = 'Opportunities'", $join);
        $this->assertContains("jt1.id=jt2.parent_id", $join);
        $result = $db->query("SELECT count(jt1.id) as count FROM opportunities jt1 $join WHERE jt1.id='{$opp->id}'");
        $this->assertTrue($result != false, "One2M getJoin returned invalid SQL");

        //sqlsrv_num_rows seems buggy
        //$this->assertEquals(1, $db->getRowCount($result));
        $row = $db->fetchByAssoc($result);
        $this->assertEquals(1, $row['count']);
    }
}
