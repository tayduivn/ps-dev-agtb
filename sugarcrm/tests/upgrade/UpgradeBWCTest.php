<?php
require_once "tests/upgrade/UpgradeTestCase.php";

class UpgradeBWCTest extends UpgradeTestCase
{

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
        SugarTestHelper::saveFile('custom/Extension/application/Ext/Include/scantest.php');
        $data = <<<'END'
<?php
$beanList['scantest'] = 'scantest';
$beanFiles['test_mtstest'] = 'modules/scantest/scantest.php';
$moduleList[] = 'scantest';
END;
        file_put_contents('custom/Extension/application/Ext/Include/scantest.php', $data);
        sugar_mkdir('modules/scantest');

        SugarTestHelper::saveFile('modules/scantest/scantest.php');
        file_put_contents('modules/scantest/scantest.php', "<?php echo 'Hello world!'; ");
        $this->mi = new ModuleInstaller();
        $this->mi->silent = true;

        $this->mi->rebuild_modules();

        SugarTestHelper::saveFile('custom/Extension/application/Ext/Include/upgrade_bwc.php');
        SugarTestHelper::saveFile('files.md5');
        copy(__DIR__."/files.md5", "files.md5");
    }

    public function tearDown()
    {
        parent::tearDown();
        SugarTestHelper::tearDown();
        rmdir_recursive("modules/scantest");
        $this->mi->rebuild_modules();
    }

    /**
     * Test for ScanModules
     */
    public function testScanModules()
    {
        $script = $this->upgrader->getScript("post", "6_ScanModules");
        $script->run();

        $bwcModules = array();
        $this->assertFileExists('custom/Extension/application/Ext/Include/upgrade_bwc.php', "custom/Extension/application/Ext/Include/upgrade_bwc.php not created");
        include 'custom/Extension/application/Ext/Include/upgrade_bwc.php';
        // scantest should be in bwc
        $this->assertEquals(array('scantest'), $bwcModules);
    }
}