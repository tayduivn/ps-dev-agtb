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
$beanFiles['scantest'] = 'modules/scantest/scantest.php';
$moduleList[] = 'scantest';
$beanList['scantestMB'] = 'scantestMB';
$beanFiles['scantestMB'] = 'modules/scantestMB/scantestMB.php';
$moduleList[] = 'scantestMB';
$beanList['scantestExt'] = 'scantestExt';
$beanFiles['scantestExt'] = 'modules/scantestExt/scantestExt.php';
$moduleList[] = 'scantestExt';
END;
        file_put_contents('custom/Extension/application/Ext/Include/scantest.php', $data);
        sugar_mkdir('modules/scantest');
        sugar_mkdir('modules/scantestMB');
        sugar_mkdir('modules/scantestExt');

        file_put_contents('modules/scantest/scantest.php', "<?php echo 'Hello world!'; ");
        file_put_contents('modules/scantest/scantest2.php', "<?php echo 'Hello world!'; ");
        file_put_contents('modules/scantestMB/scantestMB.php', "<?php echo 'Hello world!'; ");
        file_put_contents('modules/scantestExt/scantestExt.php', "<?php echo 'Hello world!'; ");

        mkdir_recursive('custom/modules/scantestExt/Ext/Layoutdefs');
        file_put_contents('custom/modules/scantestExt/Ext/Layoutdefs/scantestExt.php', "<?php echo 'Hello world!'; ");
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
        rmdir_recursive("modules/scantestMB");
        rmdir_recursive("modules/scantestExt");
        rmdir_recursive('custom/modules/scantestExt/');
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
        $this->assertEquals(array('scantest', 'scantestExt'), $bwcModules);
    }
}