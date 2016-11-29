<?php

require_once 'tests/{old}/include/database/DBManagerTest.php';

class DBManagerTestPrep extends DBManagerTest
{
    public $usePreparedStatements = true;

    public function testDeleteSQLPrep()
    {
        list($sql, $data) = $this->_db->deleteSQL(new Contact, array("id" => "17"), true);

        $this->assertRegExp('/update\s*contacts\s*set\s*deleted\s*=\s*1/i',$sql);
        $this->assertRegExp('/where\s*contacts.id\s*=\s*\?id/i',$sql);
        $this->assertContains("17", $data);
    }

    public function testRetrieveSQLPrep()
    {
        list($sql, $data) = $this->_db->retrieveSQL(new Contact, array("id" => "18"), true);

        $this->assertRegExp('/select\s*\*\s*from\s*contacts/i',$sql);
        $this->assertRegExp('/where\s*contacts.id\s*=\s*\?id/i',$sql);
        $this->assertContains("18", $data);
    }

    public function testUpdateSQLPrep()
    {
        list($sql, $data) = $this->_db->updateSQL(new Contact, array("id" => "19"), true);

        $this->assertRegExp('/update\s*contacts\s*set/i',$sql);
        $this->assertRegExp('/where\s*contacts.id\s*=\s*\?id/i',$sql);
        $this->assertContains("19", $data);
    }
}
