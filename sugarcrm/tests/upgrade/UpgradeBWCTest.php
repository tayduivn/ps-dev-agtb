<?php
require_once "tests/upgrade/UpgradeTestCase.php";

class UpgradeBWCTest extends UpgradeTestCase
{

    public function setUp()
    {
        parent::setUp();
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
$beanList['scantestHooks'] = 'scantestHooks';
$beanFiles['scantestHooks'] = 'modules/scantestHooks/scantestHooks.php';
$moduleList[] = 'scantestHooks';
END;
        sugar_mkdir('modules/scantest');
        sugar_mkdir('modules/scantestMB');
        sugar_mkdir('modules/scantestExt');
        sugar_mkdir('modules/scantestHooks');
        mkdir_recursive('custom/Extension/application/Ext/Include/');
        file_put_contents('custom/Extension/application/Ext/Include/scantest.php', $data);

        file_put_contents('modules/scantest/scantest.php', "<?php echo 'Hello world!'; ");
        file_put_contents('modules/scantest/scantest2.php', "<?php echo 'Hello world!'; ");
        file_put_contents('modules/scantestMB/scantestMB.php', "<?php echo 'Hello world!'; ");
        file_put_contents('modules/scantestExt/scantestExt.php', "<?php echo 'Hello world!'; ");
        file_put_contents('modules/scantestHooks/scantestHooks.php', "<?php echo 'Hello world!'; ");

        mkdir_recursive('custom/modules/scantestHooks/Ext/LogicHooks');
        mkdir_recursive('custom/modules/scantestHooks/workflow');

        file_put_contents('custom/modules/scantestHooks/scantestHooks2.php', "<?php echo 'Hello world!'; ");
        $hook_array['before_save'][] = array(1, 'Custom Logic', 'modules/scantestHooks/scantestHooks.php', 'test', 'test');
        write_array_to_file('hook_array', $hook_array, 'custom/modules/scantestHooks/logic_hooks.php');

        $hook_array['after_save'][] = array(1, 'Custom Logic', 'custom/modules/scantestHooks/scantestHooks2.php', 'test', 'test');
        write_array_to_file('hook_array', $hook_array, 'custom/modules/scantestHooks/Ext/LogicHooks/logichooks.ext.php');


        mkdir_recursive('custom/modules/scantestExt/Ext/ActionViewMap');
        file_put_contents('custom/modules/scantestExt/Ext/ActionViewMap/scantestExt.php', "<?php echo 'Hello world!'; ");
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
        rmdir_recursive('modules/scantestHooks');
        rmdir_recursive('custom/modules/scantestExt');
        rmdir_recursive('custom/modules/scantestHooks');
        $this->mi->rebuild_modules();
    }

    /**
     * Test for ScanModules
     */
    public function testScanModules()
    {
        $this->upgrader->setVersions("6.7.3", 'ent', '7.1.5', 'ent');
        $script = $this->upgrader->getScript("post", "6_ScanModules");
        $script->run();

        $bwcModules = array();
        $this->assertFileExists('custom/Extension/application/Ext/Include/upgrade_bwc.php', "custom/Extension/application/Ext/Include/upgrade_bwc.php not created");
        include 'custom/Extension/application/Ext/Include/upgrade_bwc.php';
        // scantest should be in bwc
        $this->assertEquals(array('scantest', 'scantestExt'), $bwcModules);
    }
}