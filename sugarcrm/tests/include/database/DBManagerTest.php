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
 
require_once 'include/database/DBManagerFactory.php';
require_once 'modules/Contacts/Contact.php';
require_once 'tests/include/database/TestBean.php';

class DBManagerTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var DBManager
     */
    private $_db;

    protected $backupGlobals = FALSE;

    static public function setupBeforeClass()
    {
        $GLOBALS['current_user'] = SugarTestUserUtilities::createAnonymousUser();
        $GLOBALS['app_strings'] = return_application_language($GLOBALS['current_language']);
    }

    static public function tearDownAfterClass()
    {
        SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        unset($GLOBALS['current_user']);
        unset($GLOBALS['app_strings']);
    }

    public function setUp()
    {
        $this->_db = DBManagerFactory::getInstance();
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
    }

    public function testCheckErrorNoConnection()
    {
        $this->_db->disconnect();
        $this->assertTrue($this->_db->checkError());
        $this->_db = &DBManagerFactory::getInstance();
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
        $this->_db->createTableParams($tablename,
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
        $this->assertTrue(in_array($tablename,$this->_db->getTablesArray()));

        $this->_db->dropTableName($tablename);
    }

    public function testRepairTable()
    {
        // TODO: Write this test
    }

    public function testRepairTableParams()
    {
        // TODO: Write this test
    }

    public function testCompareFieldInTables()
    {
        $tablename1 = 'test1_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
        $this->_db->createTableParams($tablename2,
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

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesNotInTable1()
    {
        $tablename1 = 'test3_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
        $this->_db->createTableParams($tablename2,
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

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesNotInTable2()
    {
        $tablename1 = 'test5_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
        $this->_db->createTableParams($tablename2,
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

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testCompareFieldInTablesFieldsDoNotMatch()
    {
        $tablename1 = 'test7_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
        $this->_db->createTableParams($tablename2,
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

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testCompareIndexInTables()
    {
        $tablename1 = 'test9_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
        $tablename2 = 'test10_' . mt_rand();
        $this->_db->createTableParams($tablename2,
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

        $this->assertEquals($res['msg'],'match');

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testCompareIndexInTablesNotInTable1()
    {
        $tablename1 = 'test11_' . mt_rand();
        $this->_db->createTableParams($tablename1,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foobar',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test12_' . mt_rand();
        $this->_db->createTableParams($tablename2,
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

        $this->assertEquals($res['msg'],'not_exists_table1');

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testCompareIndexInTablesNotInTable2()
    {
        $tablename1 = 'test13_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
        $tablename2 = 'test14_' . mt_rand();
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foobar',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'not_exists_table2');

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testCompareIndexInTablesIndexesDoNotMatch()
    {
        $tablename1 = 'test15_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
        $tablename2 = 'test16_' . mt_rand();
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                ),
            array(
                array(
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foobar'),
                    )
                )
            );

        $res = $this->_db->compareIndexInTables(
            'idx_foo', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'no_match');

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testCreateIndex()
    {
        // TODO: Write this test
    }

    public function testAddIndexes()
    {
        $tablename1 = 'test17_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
        $this->_db->createTableParams($tablename2,
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

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testDropIndexes()
    {
        $tablename1 = 'test19_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
        $this->_db->createTableParams($tablename2,
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

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testModifyIndexes()
    {
        $tablename1 = 'test21_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
                    'name'   => 'idx_foo',
                    'type'   => 'index',
                    'fields' => array('foo'),
                    )
                )
            );
        $tablename2 = 'test22_' . mt_rand();
        $this->_db->createTableParams($tablename2,
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
                    'name'   => 'idx_foo',
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

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testAddColumn()
    {
        $tablename1 = 'test23_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
        $this->_db->createTableParams($tablename2,
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

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testAlterColumn()
    {
        $tablename1 = 'test25_' . mt_rand();
        $this->_db->createTableParams($tablename1,
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
                    'required' => true,
                    ),
                ),
            array()
            );
        $tablename2 = 'test26_' . mt_rand();
        $this->_db->createTableParams($tablename2,
            array(
                'foo' => array (
                    'name' => 'foo',
                    'type' => 'varchar',
                    'len' => '255',
                    ),
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'int',
                    ),
                ),
            array()
            );

        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'no_match');

        $this->_db->alterColumn(
            $tablename2,
            array(
                'foobar' => array (
                    'name' => 'foobar',
                    'type' => 'varchar',
                    'len' => '255',
                    'required' => true,
                    )
                )
            );

        $res = $this->_db->compareFieldInTables(
            'foobar', $tablename1, $tablename2);

        $this->assertEquals($res['msg'],'match');

        $this->_db->dropTableName($tablename1);
        $this->_db->dropTableName($tablename2);
    }

    public function testDropTable()
    {
        // TODO: Write this test
    }

    public function testDropTableName()
    {
        $tablename = 'test' . mt_rand();
        $this->_db->createTableParams($tablename,
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

        $this->_db->dropTableName($tablename);

        $this->assertFalse(in_array($tablename,$this->_db->getTablesArray()));
    }

    public function testDeleteColumn()
    {
        // TODO: Write this test
    }

    public function testDisconnectAll()
    {
        $this->_db->disconnectAll();
        $this->assertTrue($this->_db->checkError());
        $this->_db = &DBManagerFactory::getInstance();
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
        if($this->_db instanceof MysqlManager) {
            $id = $this->_db->getOne("SELECT id From contacts where last_name = 'foobar' LIMIT 0,1");
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

    public function testGetRowCount()
    {
        if(!$this->_db->supports("select_rows")) {
            $this->markTestSkipped('Skipping, backend doesn\'t support select_rows');
        }
        $beanIds = $this->_createRecords(1);

        $result = $this->_db->query("SELECT id From contacts where id = '{$beanIds[0]}'");

        $this->assertEquals($this->_db->getRowCount($result),1);

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
        $this->_db = &DBManagerFactory::getInstance();
    }

    public function testGetTablesArray()
    {
        $tablename = 'test' . mt_rand();
        $this->_db->createTableParams($tablename,
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

        $this->_db->dropTableName($tablename);
    }

    public function testVersion()
    {
        $ver = $this->_db->version();

        $this->assertTrue(is_string($ver));
    }

    public function testTableExists()
    {
        $tablename = 'test' . mt_rand();
        $this->_db->createTableParams($tablename,
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

        $this->_db->dropTableName($tablename);
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
    public function testMssqlNotClearingErrorResults()
    {
        // execute a bad query
        $this->_db->query("select dsdsdsdsdsdsdsdsdsd");
        // assert it found an error
        $this->assertTrue($this->_db->checkError());
        // now, execute a good query
        $this->_db->query("select * from config");
        // and make no error messages are asserted
        $this->assertFalse($this->_db->checkError());
    }

    public function vardefProvider()
    {
        $emptydate = "0000-00-00";
        if($this->_db instanceof MssqlManager || $this->_db instanceof OracleManager) {
            $emptydate = "1970-01-01";
        }
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
                  		'money' => 56.78, 'test_dtm' => '\'2002-01-02 12:34:56\'', 'test_dtm2' => '\'2011-10-08 01:02:03\'',
                        'test_dt' => '\'1998-10-04\'', 'test_tm' => '\'03:04:05\''
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
                  		'money' => 0, 'test_dtm' => "'$emptydate 00:00:00'", 'test_dtm2' => "'$emptydate 00:00:00'",
                        'test_dt' => "'$emptydate'", 'test_tm' => '\'00:00:00\''
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
        $names = join('\s*,\s*',array_keys($result));
        $values = join('\s*,\s*',array_values($result));
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
            if($k == "id") continue;
            $names_i[] = "$k=$v";
        }
        if(empty($names_i)) {
            $this->assertEquals("", $sql, "Bad sql: $sql");
            return;
        }
        $names = join('\s*,\s*',$names_i);
        $this->assertRegExp("/UPDATE $name\s+SET\s+$names\s+WHERE\s+$name.id\s*=\s*'test_ID' AND deleted=0/is", $sql, "Bad sql: $sql");
    }
}
