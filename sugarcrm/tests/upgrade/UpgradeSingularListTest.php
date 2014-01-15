<?php
require_once "tests/upgrade/UpgradeTestCase.php";

class UpgradeSingularListTest extends UpgradeTestCase
{

    protected function rebuildLang()
    {
        $mi = new ModuleInstaller();
        $mi->silent = true;
        $mi->rebuild_languages(array('en_us' => 'en_us'));
    }

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('files');
        mkdir_recursive('custom/Extension/application/Ext/Language/');
        file_put_contents('custom/Extension/application/Ext/Language/en_us.singtest.php', '<?php $app_list_strings["moduleList"]["singtest"] = "singtest";');
        $this->rebuildLang();
        SugarTestHelper::saveFile('custom/Extension/application/Ext/Language/en_us.singularfix.php');
    }

    public function tearDown()
    {
        parent::tearDown();
        SugarTestHelper::tearDown();
        unlink('custom/Extension/application/Ext/Language/en_us.singtest.php');
        $this->rebuildLang();
    }

    /**
     * Test for ScanModules
     */
    public function testFixSingular()
    {
        $this->upgrader->state['MBModules'] = array('singtest');
        $script = $this->upgrader->getScript("post", "7_FixSingularList");
        $script->run();

        $this->assertFileExists('custom/Extension/application/Ext/Language/en_us.singularfix.php');
        include 'custom/Extension/application/Ext/Language/en_us.singularfix.php';
        $this->assertEquals('singtest', $app_list_strings["moduleListSingular"]["singtest"]);
    }
}