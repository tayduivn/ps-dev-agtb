<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Master Subscription
 * Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/crm/master-subscription-agreement
 * By installing or using this file, You have unconditionally agreed to the
 * terms and conditions of the License, and You may not use this file except in
 * compliance with the License.  Under the terms of the license, You shall not,
 * among other things: 1) sublicense, resell, rent, lease, redistribute, assign
 * or otherwise transfer Your rights to the Software, and 2) use the Software
 * for timesharing or service bureau purposes such as hosting the Software for
 * commercial gain and/or for the benefit of a third party.  Use of the Software
 * may be subject to applicable fees and any use of the Software without first
 * paying applicable fees is strictly prohibited.  You do not have the right to
 * remove SugarCRM copyrights from the source code or user interface.
 *
 * All copies of the Covered Code must include on each user interface screen:
 *  (i) the "Powered by SugarCRM" logo and
 *  (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.
 *
 * Your Warranty, Limitations of liability and Indemnity are expressly stated
 * in the License.  Please refer to the License for the specific language
 * governing these rights and limitations under the License.  Portions created
 * by SugarCRM are Copyright (C) 2004-2012 SugarCRM, Inc.; All Rights Reserved.
 ********************************************************************************/


require_once 'include/database/DBManagerFactory.php';
require_once 'modules/Contacts/Contact.php';
require_once 'tests/include/database/TestBean.php';

class DBManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private $_db;
    protected $created = array();

    protected $backupGlobals = FALSE;

    static public function setupBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
        $GLOBALS['db']->query('DELETE FROM forecast_tree');
    }

    static public function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    public function setUp()
    {
        if(empty($this->_db))
        {
            $this->_db = DBManagerFactory::getInstance();
        }

        if($this->_db->tableExists('testRecursive_'))
        {
            $this->_db->query('DELETE FROM testRecursive_');
        }

    }

    public function tearDown()
    {
        foreach($this->created as $table => $dummy) {
            $this->_db->dropTableName($table);
        }
    }

    protected function createTableParams($tablename, $fieldDefs, $indices)
    {
        $this->created[$tablename] = true;
        return $this->_db->createTableParams($tablename, $fieldDefs, $indices);
    }

    protected function dropTableName($tablename)
    {
        unset($this->created[$tablename]);
        return $this->_db->dropTableName($tablename);
    }

    private function _createRecords(
        $num
        )
    {
        $beanIds = array();
        for ( $i = 0; $i < $num; $i++ ) {
            $bean = new Contact();
            $bean->id = "$i-test" . mt_rand();
            $bean->last_name = "foobar";
            $this->_db->insert($bean);
            $beanIds[] = $bean->id;
        }

        return $beanIds;
    }

    private function _removeRecords(
        array $ids
        )
    {
        foreach ($ids as $id)
            $this->_db->query("DELETE From contacts where id = '{$id}'");
    }

    public function testGetDatabase()
    {
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli',$this->_db->getDatabase());
        else
            $this->assertTrue(is_resource($this->_db->getDatabase()));
    }

    public function testCheckError()
    {
        $this->assertFalse($this->_db->checkError());
        $this->assertFalse($this->_db->lastError());
    }

    public function testCheckErrorNoConnection()
    {
        $this->_db->disconnect();
        $this->assertTrue($this->_db->checkError());
        $this->_db = DBManagerFactory::getInstance();
    }

    public function testGetQueryTime()
    {
        $this->_db->version();
        $this->assertTrue($this->_db->getQueryTime() > 0);
    }

    public function testCheckConnection()
    {
        $this->_db->checkConnection();
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli',$this->_db->getDatabase());
        else
            $this->assertTrue(is_resource($this->_db->getDatabase()));
    }

    public function testInsert()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . mt_rand();
        $bean->id   = 'test' . mt_rand();
        $this->_db->insert($bean);

        $result = $this->_db->query("select id, last_name from contacts where id = '{$bean->id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'],$bean->last_name);
        $this->assertEquals($row['id'],$bean->id);

        $this->_db->query("delete from contacts where id = '{$row['id']}'");
    }

    public function testUpdate()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . mt_rand();
        $bean->id   = 'test' . mt_rand();
        $this->_db->insert($bean);
        $id = $bean->id;

        $bean = new Contact();
        $bean->last_name = 'newfoobar' . mt_rand();
        $this->_db->update($bean,array('id'=>$id));

        $result = $this->_db->query("select id, last_name from contacts where id = '{$id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['last_name'],$bean->last_name);
        $this->assertEquals($row['id'],$id);

        $this->_db->query("delete from contacts where id = '{$row['id']}'");
    }

    public function testDelete()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . mt_rand();
        $bean->id   = 'test' . mt_rand();
        $this->_db->insert($bean);
        $id = $bean->id;

        $bean = new Contact();
        $this->_db->delete($bean,array('id'=>$id));

        $result = $this->_db->query("select deleted from contacts where id = '{$id}'");
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['deleted'],'1');

        $this->_db->query("delete from contacts where id = '{$id}'");
    }

    public function testRetrieve()
    {
        $bean = new Contact();
        $bean->last_name = 'foobar' . mt_rand();
        $bean->id   = 'test' . mt_rand();
        $this->_db->insert($bean);
        $id = $bean->id;

        $bean = new Contact();
        $result = $this->_db->retrieve($bean,array('id'=>$id));
        $row = $this->_db->fetchByAssoc($result);
        $this->assertEquals($row['id'],$id);

        $this->_db->query("delete from contacts where id = '{$id}'");
    }

    public function testRetrieveView()
    {
        // TODO: Write this test
    }

    public function testCreateTable()
    {
        // TODO: Write this test
    }

    public function testCreateTableParams()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_'. $tablename,
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));

        $this->dropTableName($tablename);
    }

    public function testRepairTable()
    {
        // TODO: Write this test
    }

    public function testRepairTableNoChanges()
    {
        $tableName = 'testRTNC_' . mt_rand();
        $params =  array(
                /* VARDEF - id -  ROW[name] => 'id'  [vname] => 'LBL_ID'  [required] => 'true'  [type] => 'char'  [reportable] => ''  [comment] => 'Unique identifier'  [dbType] => 'id'  [len] => '36'  */
            'id' =>
                array (
                'name' => 'id',
                'vname' => 'LBL_ID',
                'required'=>true,
                'type' => 'id',
                'reportable'=>false,
                'comment' => 'Unique identifier'
                ),
            'date_entered' =>
                array (
                'name' => 'date_entered',
                'vname' => 'LBL_DATE_ENTERED',
                'type' => 'datetime',
                'required'=>true,
                'comment' => 'Date record created'
                ),
            'date_modified' =>
                array (
                  'name' => 'date_modified',
                  'vname' => 'LBL_DATE_MODIFIED',
                  'type' => 'datetime',
                  'required'=>true,
                  'comment' => 'Date record last modified'
                ),
            'modified_user_id' =>
                array (
                  'name' => 'modified_user_id',
                  'rname' => 'user_name',
                  'id_name' => 'modified_user_id',
                  'vname' => 'LBL_MODIFIED',
                  'type' => 'assigned_user_name',
                  'table' => 'modified_user_id_users',
                  'isnull' => 'false',
                  'dbType' => 'id',
                  'required'=> false,
                  'len' => 36,
                  'reportable'=>true,
                  'comment' => 'User who last modified record'
                ),
            'created_by' =>
                array (
                  'name' => 'created_by',
                  'rname' => 'user_name',
                  'id_name' => 'created_by',
                  'vname' => 'LBL_CREATED',
                  'type' => 'assigned_user_name',
                  'table' => 'created_by_users',
                  'isnull' => 'false',
                  'dbType' => 'id',
                  'len' => 36,
                  'comment' => 'User ID who created record'
                ),
            'name' =>
                array (
                  'name' => 'name',
                  'type' => 'varchar',
                  'vname' => 'LBL_NAME',
                  'len' => 150,
                  'comment' => 'Name of the allowable action (view, list, delete, edit)'
                ),
            'category' =>
                array (
                  'name' => 'category',
                  'vname' => 'LBL_CATEGORY',
                  'type' => 'varchar',
                  'len' =>100,
                  'reportable'=>true,
                  'comment' => 'Category of the allowable action (usually the name of a module)'
                ),
            'acltype' =>
                array (
                  'name' => 'acltype',
                  'vname' => 'LBL_TYPE',
                  'type' => 'varchar',
                  'len' =>100,
                  'reportable'=>true,
                  'comment' => 'Specifier for Category, usually "module"'
                ),
            'aclaccess' =>
                array (
                  'name' => 'aclaccess',
                  'vname' => 'LBL_ACCESS',
                  'type' => 'int',
                  'len'=>3,
                  'reportable'=>true,
                  'comment' => 'Number specifying access priority; highest access "wins"'
                ),
            'deleted' =>
                array (
                  'name' => 'deleted',
                  'vname' => 'LBL_DELETED',
                  'type' => 'bool',
                  'reportable'=>false,
                  'comment' => 'Record deletion indicator'
                ),
            'roles' =>
                array (
                    'name' => 'roles',
                    'type' => 'link',
                    'relationship' => 'acl_roles_actions',
                    'source'=>'non-db',
                    'vname'=>'LBL_USERS',
                ),
  			'reverse' =>
                array (
                    'name' => 'reverse',
                    'vname' => 'LBL_REVERSE',
                    'type' => 'bool',
                    'default' => 0
                ),
  		 	'deleted2' =>
                array (
                    'name' => 'deleted2',
                    'vname' => 'LBL_DELETED2',
                    'type' => 'bool',
                    'reportable'=>false,
                    'default' => '0'
                ),
            'primary_address_country' =>
                array (
                   'name' => 'primary_address_country',
                   'vname' => 'LBL_PRIMARY_ADDRESS_COUNTRY',
                   'type' => 'varchar',
                   'group'=>'primary_address',
                   'comment' => 'Country for primary address',
                   'merge_filter' => 'enabled',
                ),
            'refer_url' => array (
                'name' => 'refer_url',
                'vname' => 'LBL_REFER_URL',
                'type' => 'varchar',
                'len' => '255',
                'default' => 'http://',
                'comment' => 'The URL referenced in the tracker URL; no longer used as of 4.2 (see campaign_trkrs)'
                ),
            'budget' => array (
                'name' => 'budget',
                'vname' => 'LBL_CAMPAIGN_BUDGET',
                'type' => 'currency',
                'dbType' => 'double',
                'comment' => 'Budgeted amount for the campaign'
                ),
            'time_from' => array (
                'name' => 'time_from',
                'vname' => 'LBL_TIME_FROM',
                'type' => 'time',
                'required' => false,
                'reportable' => false,
                ),
            'description' =>
                array (
                'name' => 'description',
                'vname' => 'LBL_DESCRIPTION',
                'type' => 'text',
                'comment' => 'Full text of the note',
                'rows' => 6,
                'cols' => 80,
                ),
            'cur_plain' => array (
                'name' => 'cur_plain',
                'vname' => 'LBL_curPlain',
                'type' => 'currency',
            ),
            'cur_len_prec' => array (
                'name' => 'cur_len_prec',
                'vname' => 'LBL_curLenPrec',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26,6',
            ),
            'cur_len' => array (
                'name' => 'cur_len',
                'vname' => 'LBL_curLen',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26',
            ),
            'cur_len_prec2' => array (
                'name' => 'cur_len_prec2',
                'vname' => 'LBL_curLenPrec',
                'dbType' => 'decimal',
                'type' => 'currency',
                'len' => '26',
                'precision' => '6',
            ),
            'token_ts' =>
            array (
                'name' => 'token_ts',
                'type' => 'long',
                'required' => true,
                'comment' => 'Token timestamp',
                'function' => array('name' => 'displayDateFromTs', 'returns' => 'html', 'onListView' => true)
            ),
            'conskey' => array(
                'name'		=> 'conskey',
                'type'		=> 'varchar',
                'len'		=> 32,
                'required'	=> true,
                'isnull'	=> false,
            ),
        );

        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());

        $repair = $this->_db->repairTableParams($tableName, $params, array(), false);

        $this->assertEmpty($repair, "Unexpected repairs: " . $repair);

        $this->dropTableName($tableName);
    }

    public function testRepairTableParamsAddData()
    {
        $tableName = 'test1_' . mt_rand();
        $params =  array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
        );

        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());

		$params['bar'] =  array (
                    'name' => 'bar',
                    'type' => 'int',
                    );
        $cols = $this->_db->get_columns($tableName);
        $this->assertArrayNotHasKey('bar', $cols);

        $repair = $this->_db->repairTableParams($tableName, $params, array(), false);
        $this->assertRegExp('#MISSING IN DATABASE.*bar#i', $repair);
        $repair = $this->_db->repairTableParams($tableName, $params, array(), true);
        $cols = $this->_db->get_columns($tableName);
        $this->assertArrayHasKey('bar', $cols);
        $this->assertEquals('bar', $cols['bar']['name']);
        $this->assertEquals($this->_db->getColumnType('int'), $cols['bar']['type']);

        $this->dropTableName($tableName);
    }

    public function testRepairTableParamsAddIndex()
    {
        $tableName = 'test1_' . mt_rand();
        $params =  array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'bar' => array (
                    'name' => 'bar',
                    'type' => 'int',
                    ),
        );
        $index = array(
			'name'			=> 'test_index',
			'type'			=> 'index',
			'fields'		=> array('foo', 'bar', 'bazz'),
		);
        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());
		$params['bazz'] =  array (
                    'name' => 'bazz',
                    'type' => 'int',
        );

        $repair = $this->_db->repairTableParams($tableName, $params, array($index), false);
        $this->assertRegExp('#MISSING IN DATABASE.*bazz#i', $repair);
        $this->assertRegExp('#MISSING INDEX IN DATABASE.*test_index#i', $repair);
        $repair = $this->_db->repairTableParams($tableName, $params, array($index), true);

        $idx = $this->_db->get_indices($tableName);
        $this->assertArrayHasKey('test_index', $idx);
        $this->assertContains('foo', $idx['test_index']['fields']);
        $this->assertContains('bazz', $idx['test_index']['fields']);

        $cols = $this->_db->get_columns($tableName);
        $this->assertArrayHasKey('bazz', $cols);
        $this->assertEquals('bazz', $cols['bazz']['name']);
        $this->assertEquals($this->_db->getColumnType('int'), $cols['bazz']['type']);

        $this->dropTableName($tableName);
    }

    public function testRepairTableParamsAddIndexAndData()
    {
        $tableName = 'test1_' . mt_rand();
        $params =  array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'bar' => array (
                    'name' => 'bar',
                    'type' => 'int',
                    ),
        );
        $index = array(
			'name'			=> 'test_index',
			'type'			=> 'index',
			'fields'		=> array('foo', 'bar'),
		);
        if($this->_db->tableExists($tableName)) {
            $this->_db->dropTableName($tableName);
        }
		$this->createTableParams($tableName, $params, array());

        $repair = $this->_db->repairTableParams($tableName, $params, array($index), false);
        $this->assertRegExp('#MISSING INDEX IN DATABASE.*test_index#i', $repair);
        $repair = $this->_db->repairTableParams($tableName, $params, array($index), true);
        $idx = $this->_db->get_indices($tableName);
        $this->assertArrayHasKey('test_index', $idx);
        $this->assertContains('foo', $idx['test_index']['fields']);
        $this->assertContains('bar', $idx['test_index']['fields']);

        $this->dropTableName($tableName);
    }

    public function testCompareFieldInTables()
    {
        $tablename1 = 'test1_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test2_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesNotInTable1()
    {
        $tablename1 = 'test3_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test4_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);
        $this->assertEquals($res['msg'],'not_exists_table1');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesNotInTable2()
    {
        $tablename1 = 'test5_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test6_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'not_exists_table2');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesFieldsDoNotMatch()
    {
        $tablename1 = 'test7_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test8_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'int',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'no_match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

//    public function testCompareIndexInTables()
//    {
//        $tablename1 = 'test9_' . mt_rand();
//        $this->_db->createTableParams($tablename1,
//            array(
//                'foo' => array (
//                    'name' => 'foo',
//                    'type' => 'varchar',
//                    'len' => '255',
//                    ),
//                ),
//            array(
//                array(
//                    'name'   => 'idx_'. $tablename1,
//                    'type'   => 'index',
//                    'fields' => array('foo'),
//                    )
//                )
//            );
//        $tablename2 = 'test10_' . mt_rand();
//        $this->_db->createTableParams($tablename2,
//            array(
//                'foo' => array (
//                    'name' => 'foo',
//                    'type' => 'varchar',
//                    'len' => '255',
//                    ),
//                ),
//            array(
//                array(
//                    'name'   => 'idx_'. $tablename2,
//                    'type'   => 'index',
//                    'fields' => array('foo'),
//                    )
//                )
//            );
//
//        $res = $this->_db->compareIndexInTables(
//            'idx_foo', $tablename1, $tablename2);
//
//        $this->assertEquals($res['msg'],'match');
//
//        $this->_db->dropTableName($tablename1);
//        $this->_db->dropTableName($tablename2);
//    }
//
//    public function testCompareIndexInTablesNotInTable1()
//    {
//        $tablename1 = 'test11_' . mt_rand();
//        $this->_db->createTableParams($tablename1,
//            array(
//                'foo' => array (
//                    'name' => 'foo',
//                    'type' => 'varchar',
//                    'len' => '255',
//                    ),
//                ),
//            array(
//                array(
//                    'name'   => 'idx_'. $tablename1,
//                    'type'   => 'index',
//                    'fields' => array('foo'),
//                    )
//                )
//            );
//        $tablename2 = 'test12_' . mt_rand();
//        $this->_db->createTableParams($tablename2,
//            array(
//                'foo' => array (
//                    'name' => 'foo',
//                    'type' => 'varchar',
//                    'len' => '255',
//                    ),
//                ),
//            array(
//                array(
//                    'name'   => 'idx_'. $tablename2,
//                    'type'   => 'index',
//                    'fields' => array('foo'),
//                    )
//                )
//            );
//
//        $res = $this->_db->compareIndexInTables(
//            'idx_foo', $tablename1, $tablename2);
//
//        $this->assertEquals($res['msg'],'not_exists_table1');
//
//        $this->_db->dropTableName($tablename1);
//        $this->_db->dropTableName($tablename2);
//    }
//
//    public function testCompareIndexInTablesNotInTable2()
//    {
//        $tablename1 = 'test13_' . mt_rand();
//        $this->_db->createTableParams($tablename1,
//            array(
//                'foo' => array (
//                    'name' => 'foo',
//                    'type' => 'varchar',
//                    'len' => '255',
//                    ),
//                ),
//            array(
//                array(
//                    'name'   => 'idx_'. $tablename1,
//                    'type'   => 'index',
//                    'fields' => array('foo'),
//                    )
//                )
//            );
//        $tablename2 = 'test14_' . mt_rand();
//        $this->_db->createTableParams($tablename2,
//            array(
//                'foo' => array (
//                    'name' => 'foo',
//                    'type' => 'varchar',
//                    'len' => '255',
//                    ),
//                ),
//            array(
//                array(
//                    'name'   => 'idx_'. $tablename2,
//                    'type'   => 'index',
//                    'fields' => array('foo'),
//                    )
//                )
//            );
//
//        $res = $this->_db->compareIndexInTables(
//            'idx_foo', $tablename1, $tablename2);
//
//        $this->assertEquals($res['msg'],'not_exists_table2');
//
//        $this->_db->dropTableName($tablename1);
//        $this->_db->dropTableName($tablename2);
//    }
//
//    public function testCompareIndexInTablesIndexesDoNotMatch()
//    {
//        $tablename1 = 'test15_' . mt_rand();
//        $this->_db->createTableParams($tablename1,
//            array(
//                'foo' => array (
//                    'name' => 'foo',
//                    'type' => 'varchar',
//                    'len' => '255',
//                    ),
//                ),
//            array(
//                array(
//                    'name'   => 'idx_foo',
//                    'type'   => 'index',
//                    'fields' => array('foo'),
//                    )
//                )
//            );
//        $tablename2 = 'test16_' . mt_rand();
//        $this->_db->createTableParams($tablename2,
//            array(
//                'foo' => array (
//                    'name' => 'foobar',
//                    'type' => 'varchar',
//                    'len' => '255',
//                    ),
//                ),
//            array(
//                array(
//                    'name'   => 'idx_foo',
//                    'type'   => 'index',
//                    'fields' => array('foobar'),
//                    )
//                )
//            );
//
//        $res = $this->_db->compareIndexInTables(
//            'idx_foo', $tablename1, $tablename2);
//
//        $this->assertEquals($res['msg'],'no_match');
//
//        $this->_db->dropTableName($tablename1);
//        $this->_db->dropTableName($tablename2);
//    }

    public function testCreateIndex()
    {
        // TODO: Write this test
    }

    public function testAddIndexes()
    {
        //TODO Fix test with normal index inspection
        $this->markTestIncomplete(
              'TODO Reimplement test not using compareIndexInTables.'
            );
        $tablename1 = 'test17_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test18_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        // first test not executing the statement
        $this->_db->addIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            false);

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'not_exists_table2');

        // now, execute the statement
        $this->_db->addIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                ))
            );
        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testDropIndexes()
    {
        //TODO Fix test with normal index inspection
        $this->markTestIncomplete(
              'TODO Reimplement test not using compareIndexInTables.'
            );

        $tablename1 = 'test19_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test20_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals('match', $res['msg']);

        // first test not executing the statement
        $this->_db->dropIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            false);

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals('match', $res['msg']);

        // now, execute the statement
        $sql = $this->_db->dropIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            true
            );

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals('not_exists_table2', $res['msg']);

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testModifyIndexes()
    {
        //TODO Fix test with normal index inspection
        $this->markTestIncomplete(
              'TODO Reimplement test not using compareIndexInTables.'
            );
        $tablename1 = 'test21_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_'. $tablename1,
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test22_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_'. $tablename2,
                    'type'   => 'index',
                    'fields' => array('foobar'),
                    )
                )
            );

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'no_match');

        $this->_db->modifyIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                )),
            false);

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'no_match');

        $this->_db->modifyIndexes(
            $tablename2,
            array(array(
                'name'   => 'idx_foo',
                'type'   => 'index',
                'fields' => array('foo'),
                ))
            );

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function testAddColumn()
    {
        $tablename1 = 'test23_' . mt_rand();
        $this->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $tablename2 = 'test24_' . mt_rand();
        $this->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'not_exists_table2');

        $this->_db->addColumn(
            $tablename2,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    )
                )
            );

        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->dropTableName($tablename1);
        $this->dropTableName($tablename2);
    }

    public function alterColumnDataProvider()
    {
        return array(
            array(
                 1,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255', 'required' => true, 'default' => 'sugar'),
                'temp' => array ('name' => 'foobar', 'type' => 'int')                           // Check if type conversion works
            ),
            array(
                2,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255', 'default' => 'kilroy'),
                'temp' => array ('name' => 'foobar', 'type' => 'double', 'default' => '99999')  // Check if default gets replaced
            ),
            array(
                3,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255'),
                'temp' => array ('name' => 'foobar', 'type' => 'double', 'default' => '99999')  // Check if default gets dropped
            ),
            array(
                4,
                'target' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '255', 'required' => true, 'default' => 'sweet'),
                'temp' => array ('name' => 'foobar', 'type' => 'varchar', 'len' => '1500',)      // Check varchar shortening
            ),
            array(
                5,
                'target' => array ('name' => 'foobar', 'type' => 'longtext', 'required' => true),
                'temp' => array ('name' => 'foobar', 'type' => 'text', 'default' => 'dextrose') // Check clob(65k) to clob(2M or so) conversion
            ),
            array(
                6,
                'target' => array ('name' => 'foobar', 'type' => 'double', 'required' => true),
                'temp' => array ('name' => 'foobar', 'type' => 'int', 'default' => 0)           // Check int to double change
            ),
        );
    }



    /**
     * @dataProvider alterColumnDataProvider
     * @param  $i
     * @param  $target
     * @param  $temp
     * @return void
     */
    public function testAlterColumn($i, $target, $temp)
    {
        if($this->_db->dbType == "oci8" && ($i == 4 || $i == 6)) {
            $this->markTestSkipped("Cannot reliably shrink columns in Oracle");
        }

        $foo_col = array ('name' => 'foo', 'type' => 'varchar', 'len' => '255'); // Common column between tables

        $tablebase = 'testac_'. mt_rand() . '_';

        $t1 = $tablebase . $i .'A';
        $t2 = $tablebase . $i .'B';
        $this->createTableParams(  $t1,
                                        array('foo' => $foo_col, 'foobar' => $target),
                                        array());
        $this->createTableParams(  $t2,
                                        array('foo' => $foo_col, 'foobar' => $temp),
                                        array());

        $res = $this->_db->compareFieldInTables('foobar', $t1, $t2);

        $this->assertEquals('no_match', $res['msg'],
                            "testAlterColumn table columns match while they shouldn't for table $t1 and $t2: "
                            . print_r($res,true) );

        $this->_db->alterColumn($t2, array('foobar' => $target));

        $res = $this->_db->compareFieldInTables('foobar', $t1, $t2);

        $this->assertEquals('match', $res['msg'],
                            "testAlterColumn table columns don't match while they should for table $t1 and $t2: "
                            . print_r($res,true) );

        $this->dropTableName($t1);
        $this->dropTableName($t2);
    }

    public function testDropTable()
    {
        // TODO: Write this test
    }

    public function testDropTableName()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );
        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));

        $this->dropTableName($tablename);

        $this->assertFalse(in_array($tablename,$this->_db->getTablesArray()));
    }

    public function testDeleteColumn()
    {
        // TODO: Write this test
    }

    public function testDisconnectAll()
    {
        DBManagerFactory::disconnectAll();
        $this->assertTrue($this->_db->checkError());
        $this->_db = DBManagerFactory::getInstance();
    }

    public function testQuery()
    {
        $beanIds = $this->_createRecords(5);

        $result = $this->_db->query("SELECT id From contacts where last_name = 'foobar'");
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli_result',$result);
        else
            $this->assertTrue(is_resource($result));

        while ( $row = $this->_db->fetchByAssoc($result) )
            $this->assertTrue(in_array($row['id'],$beanIds),"Id not found '{$row['id']}'");

        $this->_removeRecords($beanIds);
    }

    public function disabledLimitQuery()
    {
        $beanIds = $this->_createRecords(5);
        $_REQUEST['module'] = 'contacts';
        $result = $this->_db->limitQuery("SELECT id From contacts where last_name = 'foobar'",1,3);
        if ( $this->_db instanceOf MysqliManager )
            $this->assertInstanceOf('Mysqli_result',$result);
        else
            $this->assertTrue(is_resource($result));

        while ( $row = $this->_db->fetchByAssoc($result) ) {
            if ( $row['id'][0] > 3 || $row['id'][0] < 0 )
                $this->assertFalse(in_array($row['id'],$beanIds),"Found {$row['id']} in error");
            else
                $this->assertTrue(in_array($row['id'],$beanIds),"Didn't find {$row['id']}");
        }
        unset($_REQUEST['module']);
        $this->_removeRecords($beanIds);
    }

    public function testGetOne()
    {
        $beanIds = $this->_createRecords(1);

        $id = $this->_db->getOne("SELECT id From contacts where last_name = 'foobar'");
        $this->assertEquals($id,$beanIds[0]);

        // bug 38994
        if ( $this->_db instanceOf MysqlManager ) {
            $id = $this->_db->getOne($this->_db->limitQuerySql("SELECT id From contacts where last_name = 'foobar'", 0, 1));
            $this->assertEquals($id,$beanIds[0]);
        }

        $this->_removeRecords($beanIds);
    }

    public function testGetFieldsArray()
    {
        $beanIds = $this->_createRecords(1);

        $result = $this->_db->query("SELECT id From contacts where id = '{$beanIds[0]}'");
        $fields = $this->_db->getFieldsArray($result,true);

        $this->assertEquals(array("id"),$fields);

        $this->_removeRecords($beanIds);
    }

    public function testGetAffectedRowCount()
    {
        if(!$this->_db->supports("affected_rows")) {
            $this->markTestSkipped('Skipping, backend doesn\'t support affected rows');
        }

        $beanIds = $this->_createRecords(1);
        $result = $this->_db->query("DELETE From contacts where id = '{$beanIds[0]}'");
        $this->assertEquals(1, $this->_db->getAffectedRowCount($result));
    }

    public function testFetchByAssoc()
    {
        $beanIds = $this->_createRecords(1);

        $result = $this->_db->query("SELECT id From contacts where id = '{$beanIds[0]}'");

        $row = $this->_db->fetchByAssoc($result);

        $this->assertTrue(is_array($row));
        $this->assertEquals($row['id'],$beanIds[0]);

        $this->_removeRecords($beanIds);
    }

    public function testConnect()
    {
        // TODO: Write this test
    }

    public function testDisconnect()
    {
        $this->_db->disconnect();
        $this->assertTrue($this->_db->checkError());
        $this->_db = DBManagerFactory::getInstance();
    }

    public function testGetTablesArray()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $this->assertTrue($this->_db->tableExists($tablename));

        $this->dropTableName($tablename);
    }

    public function testVersion()
    {
        $ver = $this->_db->version();

        $this->assertTrue(is_string($ver));
    }

    public function testTableExists()
    {
        $tablename = 'test' . mt_rand();
        $this->createTableParams($tablename,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array()
            );

        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));

        $this->dropTableName($tablename);
    }

    public function providerCompareVardefs()
    {
        $returnArray = array(
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'char',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                false),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'char',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'len' => '255',
                ),
                false),
            array(
                array(
                    'name' => 'foo',
                    'len' => '255',
                    ),
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
            array(
                array(
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                array(
                    'name' => 'FOO',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                true),
            );

        return $returnArray;
    }

    /**
     * @dataProvider providerCompareVarDefs
     */
    public function testCompareVarDefs($fieldDef1,$fieldDef2,$expectedResult)
    {
        if ( $expectedResult ) {
            $this->assertTrue($this->_db->compareVarDefs($fieldDef1,$fieldDef2));
        }
        else {
            $this->assertFalse($this->_db->compareVarDefs($fieldDef1,$fieldDef2));
        }
    }

    /**
     * @ticket 34892
     */
    public function test_Bug34892_MssqlNotClearingErrorResults()
    {
            // execute a bad query
            $this->_db->query("select dsdsdsdsdsdsdsdsdsd", false, "test_Bug34892_MssqlNotClearingErrorResults", true);
            // assert it found an error
            $this->assertNotEmpty($this->_db->lastError(), "lastError should return true as a result of the previous illegal query");
            // now, execute a good query
            $this->_db->query("select * from config");
            // and make no error messages are asserted
            $this->assertEmpty($this->_db->lastError(), "lastError should have cleared the previous error and return false of the last legal query");
    }

    public function vardefProvider()
    {
        $GLOBALS['log']->info('DBManagerTest.vardefProvider: _db = ' . print_r($this->_db));
        $this->setUp(); // Just in case the DB driver is not created yet.
        $emptydate = $this->_db->emptyValue("date");
        $emptytime = $this->_db->emptyValue("time");
        $emptydatetime = $this->_db->emptyValue("datetime");

        return array(
            array("testid", array (
                  'id' =>
                  array (
                    'name' => 'id',
                    'type' => 'varchar',
                    'required'=>true,
                  ),
                  ),
                  array("id" => "test123"),
                  array("id" => "'test123'")
            ),
            array("testtext", array (
                  'text1' =>
                  array (
                    'name' => 'text1',
                    'type' => 'varchar',
                    'required'=>true,
                  ),
                  'text2' =>
                  array (
                    'name' => 'text2',
                    'type' => 'varchar',
                  ),
                  ),
                  array(),
                  array("text1" => "''"),
                  array()
            ),
            array("testtext2", array (
                  'text1' =>
                  array (
                    'name' => 'text1',
                    'type' => 'varchar',
                    'required'=>true,
                  ),
                  'text2' =>
                  array (
                    'name' => 'text2',
                    'type' => 'varchar',
                  ),
                  ),
                  array('text1' => 'foo', 'text2' => 'bar'),
                  array("text1" => "'foo'", 'text2' => "'bar'"),
            ),
            array("testreq", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                        'required'=>true,
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                        'required'=>true,
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                        'required'=>true,
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                        'required'=>true,
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                        'required'=>true,
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                        'required'=>true,
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                        'required'=>true,
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                        'required'=>true,
                      ),
                  ),
                  array("id" => "test123", 'intval' => 42, 'floatval' => 42.24,
                  		'money' => 56.78, 'test_dtm' => '2002-01-02 12:34:56', 'test_dtm2' => '2011-10-08 01:02:03',
                        'test_dt' => '1998-10-04', 'test_tm' => '03:04:05'
                  ),
                  array("id" => "'test123'", 'intval' => 42, 'floatval' => 42.24,
                  		'money' => 56.78, 'test_dtm' => $this->_db->convert('\'2002-01-02 12:34:56\'', "datetime"), 'test_dtm2' => $this->_db->convert('\'2011-10-08 01:02:03\'', 'datetime'),
                        'test_dt' => $this->_db->convert('\'1998-10-04\'', 'date'), 'test_tm' => $this->_db->convert('\'03:04:05\'', 'time')
                  ),
            ),
            array("testreqnull", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                        'required'=>true,
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                        'required'=>true,
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                        'required'=>true,
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                        'required'=>true,
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                        'required'=>true,
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                        'required'=>true,
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                        'required'=>true,
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                        'required'=>true,
                      ),
                  ),
                  array(),
                  array("id" => "''", 'intval' => 0, 'floatval' => 0,
                  		'money' => 0, 'test_dtm' => "$emptydatetime", 'test_dtm2' => "$emptydatetime",
                        'test_dt' => "$emptydate", 'test_tm' => "$emptytime"
                  ),
                  array(),
            ),
            array("testnull", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                      ),
                  ),
                  array("id" => 123),
                  array("id" => "'123'"),
                  array(),
            ),
            array("testempty", array (
                  'id' =>
                      array (
                        'name' => 'id',
                        'type' => 'varchar',
                      ),
                  'intval' =>
                      array (
                        'name' => 'intval',
                        'type' => 'int',
                      ),
                  'floatval' =>
                      array (
                        'name' => 'floatval',
                        'type' => 'decimal',
                      ),
                  'money' =>
                      array (
                        'name' => 'money',
                        'type' => 'currency',
                      ),
                  'test_dtm' =>
                      array (
                        'name' => 'test_dtm',
                        'type' => 'datetime',
                      ),
                  'test_dtm2' =>
                      array (
                        'name' => 'test_dtm2',
                        'type' => 'datetimecombo',
                      ),
                  'test_dt' =>
                      array (
                        'name' => 'test_dt',
                        'type' => 'date',
                      ),
                  'test_tm' =>
                      array (
                        'name' => 'test_tm',
                        'type' => 'time',
                      ),
                   'text_txt' =>
                      array (
                        'name' => 'test_txt',
                        'type' => 'varchar',
                      ),
                  ),
                  array("id" => "", 'intval' => '', 'floatval' => '',
                  		'money' => '', 'test_dtm' => '', 'test_dtm2' => '',
                        'test_dt' => '', 'test_tm' => '', 'text_txt' => null
                  ),
                  array("id" => "''", 'intval' => 0, 'floatval' => 0,
                  		'money' => 0, 'test_dtm' => "NULL", 'test_dtm2' => "NULL",
                        'test_dt' => "NULL", 'test_tm' => 'NULL'
                  ),
                  array('intval' => 'NULL', 'floatval' => 'NULL',
                  		'money' => 'NULL', 'test_dtm' => 'NULL', 'test_dtm2' => 'NULL',
                        'test_dt' => 'NULL', 'test_tm' => 'NULL'
                  ),
            ),
        );
    }

   /**
    * Test InserSQL functions
    * @dataProvider vardefProvider
    * @param string $name
    * @param array $defs
    * @param array $data
    * @param array $result
    */
    public function testInsertSQL($name, $defs, $data, $result)
    {
        $vardefs = array(
			'table' => $name,
            'fields' => $defs,
        );
        $obj = new TestSugarBean($name, $vardefs);
        // regular fields
        foreach($data as $k => $v) {
            $obj->$k = $v;
        }
        $sql = $this->_db->insertSQL($obj);
        $names = join('\s*,\s*',array_map('preg_quote', array_keys($result)));
        $values = join('\s*,\s*',array_map('preg_quote', array_values($result)));
        $this->assertRegExp("/INSERT INTO $name\s+\(\s*$names\s*\)\s+VALUES\s+\(\s*$values\s*\)/is", $sql, "Bad sql: $sql");
    }

   /**
    * Test UpdateSQL functions
    * @dataProvider vardefProvider
    * @param string $name
    * @param array $defs
    * @param array $data
    * @param array $_
    * @param array $result
    */
    public function testUpdateSQL($name, $defs, $data, $_, $result = null)
    {
        $name = "update$name";
        $vardefs = array(
			'table' => $name,
            'fields' => $defs,
        );
        // ensure it has an ID
        $vardefs['fields']['id'] = array (
                    'name' => 'id',
                    'type' => 'id',
                    'required'=>true,
                  );
        $vardefs['fields']['deleted'] = array (
                    'name' => 'deleted',
                    'type' => 'bool',
                  );

        $obj = new TestSugarBean($name, $vardefs);
        // regular fields
        foreach($defs as $k => $v) {
            if(isset($data[$k])) {
                $obj->$k = $data[$k];
            } else {
                $obj->$k = null;
            }
        }
        // set fixed ID
        $obj->id = 'test_ID';
        $sql = $this->_db->updateSQL($obj);
        if(is_null($result)) {
            $result = $_;
        }
        $names_i = array();
        foreach($result as $k => $v) {
            if($k == "id" || $k == 'deleted') continue;
            $names_i[] = preg_quote("$k=$v");
        }
        if(empty($names_i)) {
            $this->assertEquals("", $sql, "Bad sql: $sql");
            return;
        }
        $names = join('\s*,\s*',$names_i);
        $this->assertRegExp("/UPDATE $name\s+SET\s+$names\s+WHERE\s+$name.id\s*=\s*'test_ID' AND deleted=0/is", $sql, "Bad sql: $sql");
    }

     /**
    * Test UpdateSQL functions
    * @dataProvider vardefProvider
    * @param string $name
    * @param array $defs
    * @param array $data
    * @param array $_
    * @param array $result
    */
    public function testUpdateSQLNoDeleted($name, $defs, $data, $_, $result = null)
    {
        $name = "updatenodel$name";
        $vardefs = array(
			'table' => $name,
            'fields' => $defs,
        );
        // ensure it has an ID
        $vardefs['fields']['id'] = array (
                    'name' => 'id',
                    'type' => 'id',
                    'required'=>true,
                  );
        unset($vardefs['fields']['deleted']);

        $obj = new TestSugarBean($name, $vardefs);
        // regular fields
        foreach($defs as $k => $v) {
            if(isset($data[$k])) {
                $obj->$k = $data[$k];
            } else {
                $obj->$k = null;
            }
        }
        // set fixed ID
        $obj->id = 'test_ID';
        $sql = $this->_db->updateSQL($obj);
        if(is_null($result)) {
            $result = $_;
        }
        $names_i = array();
        foreach($result as $k => $v) {
            if($k == "id" || $k == 'deleted') continue;
            $names_i[] = preg_quote("$k=$v");
        }
        if(empty($names_i)) {
            $this->assertEquals("", $sql, "Bad sql: $sql");
            return;
        }
        $names = join('\s*,\s*',$names_i);
        $this->assertRegExp("/UPDATE $name\s+SET\s+$names\s+WHERE\s+$name.id\s*=\s*'test_ID'/is", $sql, "Bad sql: $sql");
        $this->assertNotContains(" AND deleted=0", $sql, "Bad sql: $sql");
    }

    /**
     * Test the canInstall
     * @return void
     */
    public function testCanInstall() {
        $DBManagerClass = get_class($this->_db);
        if(!method_exists($this->_db, 'version') || !method_exists($this->_db, 'canInstall'))
            $this->markTestSkipped(
              "Class {$DBManagerClass} doesn't implement canInstall or version methods");

        $method = new ReflectionMethod($DBManagerClass, 'canInstall');
        if($method->class == 'DBManager')
            $this->markTestSkipped(
              "Class {$DBManagerClass} or one of it's ancestors doesn't override DBManager's canInstall");

        // First assuming that we are only running unit tests against a supported database :)
        $this->assertTrue($this->_db->canInstall(), "Apparently we are not running this unit test against a supported database!!!");

        $DBstub = $this->getMock($DBManagerClass, array('version'));
        $DBstub->expects($this->any())
               ->method('version')
               ->will($this->returnValue('0.0.0')); // Expect that any supported version is higher than 0.0.0

        $this->assertTrue(is_array($DBstub->canInstall()), "Apparently we do support version 0.0.0 in " . $DBManagerClass);
    }

    public function providerValidateQuery()
    {
        return array(
            array(true, 'SELECT * FROM accounts'),
            array(false, 'SELECT * FROM blablabla123'),
        );
    }

    /**
     * Test query validation
     * @dataProvider providerValidateQuery
     * @param $good
     * @param $sql
     * @return void
     */
    public function testValidateQuery($good, $sql)
    {
        $check = $this->_db->validateQuery($sql);
        $this->assertEquals($good, $check);
    }

    /**
     * @outputBuffering disabled
     *
     * @group testTextSizeHandling
     */
    public function testTextSizeHandling()
    {
        $tablename = 'testTextSize';// . mt_rand();
        $fielddefs = array(
                        'id' =>
                            array (
                            'name' => 'id',
                            'required'=>true,
                            'type' => 'id',
                            ),
                        'test' => array (
                            'name' => 'test',
                            'type' => 'longtext',
                            //'len' => '255',
                            ),
                        'dummy' => array (
                            'name' => 'dummy',
                            'type' => 'longtext',
                            //'len' => '255',
                            ),
                        );
        if($this->_db->tableExists($tablename))
        {
           $this->_db->dropTableName($tablename);
        }
        $this->createTableParams($tablename, $fielddefs, array());
        $basestr = '0123456789abcdefghijklmnopqrstuvwxyz';
        $str = $basestr;
        while(strlen($str) < 159900)
        {
            $str .= $basestr;
        }

        for($i = 0; $i < 50; $i++)
        {
            $str .= $basestr;
            $size = strlen($str);
            //$this->_db->insertParams($tablename, $fielddefs, array('id' => $size, 'test' => $str, 'dummy' => $str));
            //We need to manually call the INSERT statement and pass true to suppress errors because this statement will obviously generate errors
            $this->_db->query("INSERT into {$tablename} (id, test, dummy) values ('{$size}', '{$str}', '{$str}')", false, '', true);

            $select = "SELECT test FROM $tablename WHERE id = '{$size}'";
            $strresult = $this->_db->getOne($select);

            if(!empty($strresult))
            {
                $this->assertEquals(0, mb_strpos($str, $strresult));
            } else {
                //This really should fail right here because it's not returning results
                //$this->assertEquals(0, mb_strpos($str, $strresult));
            }
        }
    }


    private function addChildren($tableName, $parent, $number, $level, $stoplevel)
    {
        if($level >= $stoplevel) return;
        for($sibling = 0; $sibling < $number; $sibling++)
        {
            $id = (!empty($parent)) ? "{$parent}_{$sibling}" : "$sibling";
            $this->_db->query("INSERT INTO $tableName (id, parent_id, db_level) VALUES ('$id', '$parent', $level)");
            $this->addChildren($tableName, $id, $number, $level+1, $stoplevel);
        }
    }

    private function setupRecursiveStructure()
    {

        $tableName = 'testRecursive_'; // . mt_rand();
        $params =  array(
            'id' => array (
                'name' => 'id',
                'type' => 'id',
                'required'=>true,
                ),
            'parent_id' => array (
                'name' => 'parent_id',
                'type' => 'varchar',
                'len' => '36',
                ),
           'db_level' => array (  // For verification purpose
                'name' => 'db_level',
                'type' => 'int',
                ),
        );
        $indexes = array(
            array(
                'name'   => 'idx_'. $tableName .'_id',
                'type'   => 'primary',
                'fields' => array('id'),
                ),
            array(
                'name'   => 'idx_'. $tableName .'parent_id',
                'type'   => 'index',
                'fields' => array('parent_id'),
                ),
        );
        if($this->_db->tableExists($tableName)) {
            return $tableName;
            $this->_db->dropTableName($tableName);
        }
//        $this->createTableParams($tableName, $params, array());
        $this->_db->createTableParams($tableName, $params, $indexes);


        // Load data
        $this->_db->query("INSERT INTO $tableName (id, db_level) VALUES ('1', 0)");
        $this->addChildren($tableName, '1', 2, 1, 10);
        return $tableName;
    }

    public function providerRecursiveQuery()
    {
        return array(
            array('1_0_0_0_0_0_0_0_0_0', '9', 1),
            array('1_1_1_1_1_1_1_1_1_1', '9', 1),
            array('1_0_0_0_0_0_0_0_0', '8', 3),
            array('1_1_1_1_1_1_1_1', '7', 7),
            array('1_0_0_0_0_0_0', '6', 15),
            array('1_1_1_1_1_1', '5', 31),
            array('1_0_0_0_0', '4', 63),
            array('1_1_1_1', '3', 127),
            array('1_0_0', '2', 255),
            array('1_1', '1', 511),
            array('1', '0', 1023),
        );
    }

    /**
     * @dataProvider providerRecursiveQuery
     * @group hierarchy
     * @param $startId
     * @param $startDbLevel
     * @param $nrchildren
     */
    public function testRecursiveQuery($startId, $startDbLevel, $nrchildren)
    {
        $idCurrent = $startId;
        $levels = $startDbLevel;
        $this->_db->preInstall();

        // setup test table and fill it with data if it doesn't already exist
        $table = 'testRecursive_';
        if(!$this->_db->tableExists($table)) {
            $table = $this->setupRecursiveStructure();
        }

        // Testing lineage
        $lineageSQL = $this->_db->getRecursiveSelectSQL($table, 'id', 'parent_id', 'id, parent_id, db_level', true, "id ='$idCurrent'");

        $result = $this->_db->query($lineageSQL);

        while($row = $this->_db->fetchByAssoc($result))
        {
            $this->assertEquals($idCurrent, $row['id'], "Incorrect id found");
            if(!empty($row['parent_id'])) $idCurrent = $row['parent_id'];
            $this->assertEquals($levels--, $row['db_level'], "Incorrect level found");
        }
        $this->assertEquals('1', $idCurrent, "Incorrect top node id");
        $this->assertEquals(0, $levels+1, "Incorrect end level"); //Compensate for extra -1 after last node level assert

        // Testing children
        $idCurrent = $startId;
        $childcount = 0;
        $childrenSQL = $this->_db->getRecursiveSelectSQL($table,'id','parent_id', 'id, parent_id, db_level',false, "id ='$idCurrent'");

        $result = $this->_db->query($childrenSQL);

        while(($row = $this->_db->fetchByAssoc($result)) != null)
        {
            $this->assertEquals(0, strpos($row['id'], $idCurrent), "Row id doesn't start with starting id as expected");
            $childcount++;
        }
        $this->assertEquals($nrchildren, $childcount, "Number of found descendants does not match expectations");

    }

    // Inserts a 2D array of data into the specified table
    // First row of array must be the column headers, first column must be PK column name
    public function insertTableArray( $tableName, $tableDataArray ) {

        $sqlPrefix = "INSERT INTO $tableName ( {$tableDataArray[0][0]} ";  // has to be at least one column
        for ($col = 1; $col < count($tableDataArray[0]); $col++) {
            $sqlPrefix .= ", {$tableDataArray[0][$col]}";
        }
        $sqlPrefix .= ") VALUES (";
        $sqlPostfix = ")";
        $sqlBody = "";

        // do the inserts for each row of data
        for ($row = 1; $row< count($tableDataArray); $row++) {
            $rowData = $tableDataArray[$row];
            $sqlBody = "'{$rowData[0]}'";
            for ($col = 1; $col < count($rowData); $col++) {
                $sqlBody .= ",'{$rowData[$col]}'";
            }

            // Insert the data
            $sql = $sqlPrefix . $sqlBody . $sqlPostfix;
            $result = $this->_db->query($sql);
        }
    }

    // Deletes a 2D array of data from the specified table
    // First row of array must be column headers, first column must be PK column name
    public function deleteTableArray( $tableName, $tableDataArray ) {

        $sql = "DELETE FROM $tableName WHERE {$tableDataArray[0][0]} IN ( '{$tableDataArray[1][0]}'";  // has to be at least one column
        for ($row = 2; $row < count($tableDataArray); $row++) {
            $sql .= ",'{$tableDataArray[$row][0]}'";
        }
        $sql .= ")";

        // Delete the data
        $result = $this->_db->query($sql);

    }


    /**
     */
    public function testRecursiveQueryMultiHierarchy()
    {
        $this->_db->preInstall();

        // Setup test data
        $tableName = 'forecast_tree';
        $this->assertTrue($this->_db->tableExists($tableName), "Table $tableName does not exist");
        $tableDataArray = array(  array( 'id',             'parent_id',      'name',     'hierarchy_type', 'user_id' )
        , array( 'sales_test_1',    null,            'sales1',   'sales_test',     'user1'   )
        , array( 'sales_test_11',  'sales_test_1',   'sales11',  'sales_test',     'user11'  )
        , array( 'sales_test_12',  'sales_test_1',   'sales12',  'sales_test',     'user12'  )
        , array( 'sales_test_13',  'sales_test_1',   'sales13',  'sales_test',     'user13'  )
        , array( 'sales_test_121', 'sales_test_12',  'sales121', 'sales_test',     'user121' )
        , array( 'sales_test_122', 'sales_test_12',  'sales122', 'sales_test',     'user122' )
        , array( 'sales_test_131', 'sales_test_13',  'sales131', 'sales_test',     'user131' )
        , array( 'sales_test_132', 'sales_test_13',  'sales132', 'sales_test',     'user132' )
        , array( 'sales_test_133', 'sales_test_13',  'sales133', 'sales_test',     'user133' )
        , array( 'prod_test_1',     null,            'prod1',    'prod_test',      'user1'   )
        , array( 'prod_test_11',   'prod_test_1',    'prod11',   'prod_test',      'user11'  )
        , array( 'prod_test_12',   'prod_test_1',    'prod12',   'prod_test',      'user12'  )
        , array( 'prod_test_13',   'prod_test_1',    'prod13',   'prod_test',      'user13'  )
        , array( 'prod_test_121',  'prod_test_12',   'prod121',  'prod_test',      'user121' )
        , array( 'prod_test_122',  'prod_test_12',   'prod122',  'prod_test',      'user122' )
        , array( 'prod_test_131',  'prod_test_13',   'prod131',  'prod_test',      'user131' )
        , array( 'prod_test_132',  'prod_test_13',   'prod132',  'prod_test',      'user132' )
        , array( 'prod_test_133',  'prod_test_13',   'prod133',  'prod_test',      'user133' )
        , array( 'prod_test_1321', 'prod_test_132',  'prod1321', 'prod_test',      'user1321')
        );

        $this->insertTableArray( $tableName, $tableDataArray );

        // idStarting, Up/Down, Forecast_Tree Type, expected result count
        $resultsDataArray = array( array('sales_test_1',    false, 'sales_test',9)
                                  ,array('sales_test_13',   false, 'sales_test',4)
                                  ,array('sales_test_131',  true,  'sales_test',3)
                                  ,array('sales_test_13',   true,  'sales_test',2)
                                  ,array('sales_test_1',    true,  'sales_test',1)
                                  ,array('prod_test_1',     false, 'prod_test',10)
                                  ,array('prod_test_13',    false, 'prod_test', 5)
                                  ,array('prod_test_1321',  true,  'prod_test', 4)
                                  ,array('prod_test_133',   true,  'prod_test', 3)
                                  ,array('prod_test_1',     true,  'prod_test', 1)
        );

        // Loop through each test
        foreach ( $resultsDataArray as $resultsRow ) {

            // Get where clause
            $whereClause = "hierarchy_type='$resultsRow[2]'";

            // Get hierarchical result set
            $key = 'id';
            $parent_key = 'parent_id';
            $fields = 'id, parent_id';
            $lineage = $resultsRow[1];
            $startWith = "id = '{$resultsRow[0]}'";
            $level = null;

            $hierarchicalSQL = $this->_db->getRecursiveSelectSQL($tableName, $key, $parent_key, $fields, $lineage, $startWith, $level, $whereClause);
            $result = $this->_db->query($hierarchicalSQL);
            $resultsCnt = 0;

            while(($row = $this->_db->fetchByAssoc($result)) != null)
            {
                $resultsCnt++;
            }

            $this->assertEquals($resultsCnt, $resultsRow[3], "Incorrect number or records. Found: $resultsCnt Expected: $resultsRow[3] for ID: $resultsRow[0]");
        }

        // remove data from table
        $result = $this->deleteTableArray( $tableName, $tableDataArray );

    }


    public function testGetIndicesContainsPrimary()
    {
        $indices = $this->_db->get_indices('accounts');

        // find if any are primary
        $found = false;

        foreach($indices as $index)
        {
            if($index['type'] == "primary") {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Primary Key Not Found On Module');
    }

    /*
     * testDBGuidGeneration
     * Tests that the first 1000 DB generated GUIDs are unique
     */
    public function testDBGuidGeneration()
    {
		$guids = array();
		$sql = "SELECT {$this->_db->getGuidSQL()} {$this->_db->getFromDummyTable()}";
		for($i = 0; $i < 1000; $i++)
		{
			$newguid = $this->_db->getOne($sql);
			$this->assertFalse(in_array($newguid, $guids), "'$newguid' already existed in the array of GUIDs!");
			$guids []= $newguid;
		}
	}
}
