<?php
require_once "tests/upgrade/UpgradeTestCase.php";

class UpgradeTest extends UpgradeTestCase
{

    /**
     * Test for getScripts
     * @param string $scriptname
     * @dataProvider dataGetScripts
     */
    public function testGetScripts($stage, $scriptname)
    {
        SugarTestHelper::saveFile($scriptname);
        $this->upgrader->ensureDir(dirname($scriptname));
        $name = preg_replace("/^\d+_/", "", basename($scriptname, ".php"));
        $data = <<<END
<?php
class SugarUpgrade$name extends UpgradeScript {
    public function run()
    {
        return true;
    }
}
END;
        file_put_contents($scriptname, $data);
        $script = $this->upgrader->getScript($stage, basename($scriptname, ".php"));
        $this->assertInstanceOf("UpgradeScript", $script);
    }

    public function dataGetScripts()
    {
        return array(
            array("pre", "custom/upgrade/scripts/pre/TestScript.php"),
            array("post", "custom/upgrade/scripts/post/7_TestScript2.php"),
            array("pre", "custom/modules/Accounts/upgrade/scripts/pre/13_TestScript3.php"),
            array("post", "custom/modules/Contacts/upgrade/scripts/post/1_TestScript4.php"),
        );
    }

    /**
     * Test for 1_RunSQL
     * @dataProvider dataRunSQL
     */
    public function testRunSQL($from, $flav_from, $to, $flav_to, $db, $script)
    {
        $runsql = $this->upgrader->getScript("post", "1_RunSQL");
        $this->assertNotEmpty($runsql);

        $mock = $this->getMock(get_class($runsql), array("parseAndExecuteSqlFile"), array($this->upgrader));
        $dbMock = $this->getMock("MysqlManager");
        $dbMock->expects($this->any())->method("getScriptName")->will($this->returnValue($db));
        $this->upgrader->setDb($dbMock);

        $dir = $this->upgrader->getTempDir()."/scripts";
        $this->upgrader->ensureDir($dir);
        touch("$dir/$script");

        $this->upgrader->setVersions($from, $flav_from, $to, $flav_to);
        $mock->expects($this->once())->method("parseAndExecuteSqlFile")->with("$dir/$script");
        $mock->run();
    }

    public function dataRunSQL()
    {
        return array(
            array("6.6.2", "ent", "7.0.0", "ent", "mysql", "662_to_700_mysql.sql"),
            array("6.6.2", "ent", "7.0.0", "ent", "foo", "662_to_700_foo.sql"),
            array("7.0.0", "pro", "7.0.0", "ent", "oracle", "700_pro_to_ent_oracle.sql"),
        );
    }

    /**
     * Test for StoreModules
     */
    public function testStoreModules()
    {
        include 'include/modules.php';
        $script = $this->upgrader->getScript("pre", "StoreModules");
        $script->run();

        $mods = $this->upgrader->state['old_modules'];
        sort($mods);
        sort($moduleList);
        $this->assertEquals($moduleList, $mods);
    }
}