<?php

/*********************************************************************************
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

require_once('data/SugarBean.php');

class SugarBeanTest extends Sugar_PHPUnit_Framework_TestCase
{

    public static function setUpBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
	}

	public static function tearDownAfterClass()
	{
	    SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
	}

    public function testGetObjectName(){
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->getObjectName(), 'my_table', "SugarBean->getObjectName() is not returning the table name when object_name is empty.");
    }

    public function testGetAuditTableName(){
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->get_audit_table_name(), 'my_table_audit', "SugarBean->get_audit_table_name() is not returning the correct audit table name.");
    }
    
    /**
     * @ticket 47261
     */
    public function testGetCustomTableName()
    {
        $bean = new BeanMockTestObjectName();
        $this->assertEquals($bean->get_custom_table_name(), 'my_table_cstm', "SugarBean->get_custom_table_name() is not returning the correct custom table name.");
    }

    public function testRetrieveQuoting()
    {
        $bean = new BeanMockTestObjectName();
        $bean->db = new MockMysqlDb();
        $bean->retrieve("bad'idstring");
        $this->assertNotContains("bad'id", $bean->db->lastQuery);
        $this->assertContains("bad", $bean->db->lastQuery);
        $this->assertContains("idstring", $bean->db->lastQuery);
    }

    public function testRetrieveStringQuoting()
    {
        $bean = new BeanMockTestObjectName();
        $bean->db = new MockMysqlDb();
        $bean->retrieve_by_string_fields(array("test1" => "bad'string", "evil'key" => "data", 'tricky-(select * from config)' => 'test'));
        $this->assertNotContains("bad'string", $bean->db->lastQuery);
        $this->assertNotContains("evil'key", $bean->db->lastQuery);
        $this->assertNotContains("select * from config", $bean->db->lastQuery);
    }




    /**
     * Test to make sure that when a bean is cloned it removes all loaded relationships so they can be recreated on
     * the cloned copy if they are called.
     *
     * @group 51630
     * @return void
     */
    public function testCloneBeanDoesntKeepRelationship()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $account->load_relationship('contacts');

        // lets make sure the relationship is loaded
        $this->assertTrue(isset($account->contacts));

        $clone_account = clone $account;

        // lets make sure that the relationship is not on the cloned record
        $this->assertFalse(isset($clone_account->contacts));

        SugarTestAccountUtilities::removeAllCreatedAccounts();
    }

}

// Using Mssql here because mysql needs real connection for quoting
require_once 'include/database/MssqlManager.php';
class MockMysqlDb extends MssqlManager
{
    public $database = true;
    public $lastQuery;

    public function connect(array $configOptions = null, $dieOnError = false)
    {
        return true;
    }

    public function query($sql, $dieOnError = false, $msg = '', $suppress = false, $keepResult = false)
    {
        $this->lastQuery = $sql;
        return true;
    }

    public function fetchByAssoc($result, $encode = true)
    {
        return false;
    }
}

class BeanMockTestObjectName extends SugarBean
{
    var $table_name = "my_table";

    function BeanMockTestObjectName() {
		parent::SugarBean();
	}
}