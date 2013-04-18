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
        SugarTestHelper::setUp('current_user');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('beanFiles');
	}

    public function setUp()
    {
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
    }

    public function tearDown()
    {
        SugarTestHelper::tearDown();
    }

	public static function tearDownAfterClass()
	{
	    SugarTestHelper::tearDown();
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
     * This test makes sure that the object we are looking for is returned from the build_related_list method as
     * something changed someplace that is causing it to return the template that was passed in.
     */
    public function testBuildRelatedListReturnsRecordBeanVsEmptyBean()
    {
        $account = SugarTestAccountUtilities::createAccount();

        $bean = new SugarBean();

        $query = 'select id FROM ' . $account->table_name . ' where id = "' . $account->id . '";';
        $return = array_shift($bean->build_related_list($query, BeanFactory::getBean('Accounts')));

        $this->assertEquals($account->id, $return->id);

        SugarTestAccountUtilities::removeAllCreatedAccounts();
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

    /**
     * Test whether a relate field is determined correctly
     *
     * @param array $field_defs
     * @param string $field_name
     * @param bool $is_relate
     * @dataProvider isRelateFieldProvider
     * @covers SugarBean::is_relate_field
     */
    public function testIsRelateField(array $field_defs, $field_name, $is_relate)
    {
        $bean = new BeanIsRelateFieldMock();
        $bean->field_defs = $field_defs;
        $actual = $bean->is_relate_field($field_name);

        if ($is_relate)
        {
            $this->assertTrue($actual);
        }
        else
        {
            $this->assertFalse($actual);
        }
    }

    public static function isRelateFieldProvider()
    {
        return array(
            // test for on a non-existing field
            array(
                array(), 'dummy', false,
            ),
            // test for non-specified field type
            array(
                array(
                    'my_field' => array(),
                ), 'my_field', false,
            ),
            // test on a non-relate field type
            array(
                array(
                    'my_field' => array(
                        'type' => 'varchar',
                    ),
                ), 'my_field', false,
            ),
            // test on a relate field type but link not specified
            array(
                array(
                    'my_field' => array(
                        'type' => 'relate',
                    ),
                ), 'my_field', false,
            ),
            // test when only link is specified
            array(
                array(
                    'my_field' => array(
                        'link' => 'my_link',
                    ),
                ), 'my_field', false,
            ),
            // test on a relate field type
            array(
                array(
                    'my_field' => array(
                        'type' => 'relate',
                        'link' => 'my_link',
                    ),
                ), 'my_field', true,
            ),
        );
    }

    /**
     * test that currency/decimal from db is a string value
     * @dataProvider testCurrencyFieldStringValueProvider
     * @group sugarbean
     * @group currency
     */
    public function testCurrencyFieldStringValue($type, $actual, $expected)
    {
        $mock = new SugarBean();
        $mock->id = 'SugarBeanMockStringTest';
        $mock->field_defs = array(
            'testDecimal' => array(
                'type' => $type
            ),
        );

        $mock->testDecimal = $actual;
        $mock->fixUpFormatting();
        $this->assertSame($expected, $mock->testDecimal);
    }

    public function testCurrencyFieldStringValueProvider()
    {
        return array(
            array('decimal', '500.01', '500.01'),
            array('decimal', 500.01, '500.01'),
            array('decimal', '-500.01', '-500.01'),
            array('currency', '500.01', '500.01'),
            array('currency', 500.01, '500.01'),
            array('currency', '-500.01', '-500.01'),
        );
    }

    /**
     * SP-618
     * Verify that calling getCleanCopy on uncommon beans (like SessionManager) and common beans returns a new instance of the bean and not a null
     * @group sugarbean
     */
    public function testGetCopyNotNull()
    {
        $mock = new SessionManager();
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, "New instance of SessionManager SugarBean should not be null");
        $this->assertEquals($mock->module_name, $newInstance->module_name);

        $mock = new SugarBean();
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, "New instance of SugarBean should not be null");

        $mock = BeanFactory::getBean('Accounts');
        $newInstance = $mock->getCleanCopy();
        $this->assertNotNull($newInstance, "New instance of Accounts SugarBean should not be null");
        $this->assertEquals('Accounts', $newInstance->module_name);
    }

    /**
     * @group sugarbean
     */
    public function testGetNotificationRecipientsReturnsEmptyArray()
    {
        $mock = new SugarBean();
        unset($mock->assigned_user_id);

        $ret = $mock->get_notification_recipients();

        $this->assertEmpty($ret);
    }

    public function testGetNotificationRecipientsReturnsNonEmptyArray()
    {
        $mock = new SugarBean();
        $mock->assigned_user_id = '1';

        $ret = $mock->get_notification_recipients();

        $this->assertEquals('1',$ret[0]->id);
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
		parent::__construct();
	}
}

class BeanIsRelateFieldMock extends SugarBean
{
    public function is_relate_field($field_name_name)
    {
        return parent::is_relate_field($field_name_name);
    }
}
