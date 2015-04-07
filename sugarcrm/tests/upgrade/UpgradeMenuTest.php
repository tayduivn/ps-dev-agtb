<?php
require_once "tests/upgrade/UpgradeTestCase.php";

class UpgradeMenuTest extends UpgradeTestCase
{

    public function setUp()
    {
        parent::setUp();
        SugarTestHelper::setUp('beanFiles');
        SugarTestHelper::setUp('beanList');
        SugarTestHelper::setUp('bwcModules');
        sugar_mkdir('modules/menutest');
        mkdir_recursive('modules/menutest2/clients/base/menus/header');
        mkdir_recursive('modules/menutest3/clients/base/menus/header');
        sugar_mkdir('modules/menutestBWC');
        mkdir_recursive('custom/modules/menutest3/clients/base/menus/header');

        SugarTestHelper::saveFile('modules/menutest2/clients/base/menus/header/header.php');
        file_put_contents('modules/menutest2/clients/base/menus/header/header.php', "<?php echo 'Hello world!'; ");
        SugarTestHelper::saveFile('custom/modules/menutest3/clients/base/menus/header/header.php');
        file_put_contents('custom/modules/menutest3/clients/base/menus/header/header.php', "<?php echo 'Hello world!'; ");
    }

    public function tearDown()
    {
        parent::tearDown();
        rmdir_recursive("modules/menutest");
        rmdir_recursive("modules/menutest2");
        rmdir_recursive("modules/menutest3");
        rmdir_recursive("modules/menutestBWC");
        rmdir_recursive("custom/modules/menutest2");
        rmdir_recursive("custom/modules/menutest3");
    }

    /**
     * Test for ScanModules
     */
    public function testScanModules()
    {
        $this->upgrader->state['MBModules'] = array('menutest', 'menutest2', 'menutest3');
        $GLOBALS['bwcModules'][] = 'menutestBWC';
        $script = $this->upgrader->getScript("post", "7_MBMenu");
        $script->run();

        $this->assertFileExists('modules/menutest/clients/base/menus/header/header.php');
        $this->assertEquals("<?php echo 'Hello world!'; ", file_get_contents('modules/menutest2/clients/base/menus/header/header.php'), "File overwritten for module menutest2");
        $this->assertFileNotExists('modules/menutest3/clients/base/menus/header/header.php');
        $this->assertFileExists('modules/menutestBWC/clients/base/menus/header/header.php');

        $viewdefs = array();
        include 'modules/menutest/clients/base/menus/header/header.php';
        $this->assertEquals('LNK_NEW_RECORD', $viewdefs['menutest']['base']['menu']['header'][0]['label']);
        $this->assertEquals('#menutest', $viewdefs['menutest']['base']['menu']['header'][1]['route']);
    }

    /**
     * Test asserts that core bwc modules aren't returned
     */
    public function testGetNotCoreBwcModules()
    {
        $GLOBALS['bwcModules'][] = __FUNCTION__;
        $script = new SugarUpgradeMBMenu($this->getMockForAbstractClass('UpgradeDriver'));
        $actual = SugarTestReflection::callProtectedMethod($script, 'getNotCoreBwcModules');
        $this->assertCount(1, $actual, 'Too many modules were returned');
        $this->assertEquals(__FUNCTION__, current($actual), 'Incorrect module was returned');
    }

    /**
     * Test asserts that only result of getNotCoreBwcModules method will be updated by addMenu method
     */
    public function testRunAndGetNotCoreBwcModules()
    {
        /** @var UpgradeDriver|PHPUnit_Framework_MockObject_MockObject $driver */
        $driver = $this->getMockForAbstractClass('UpgradeDriver');
        /** @var SugarUpgradeMBMenu|PHPUnit_Framework_MockObject_MockObject $script */
        $script = $this->getMock('SugarUpgradeMBMenu', array('getNotCoreBwcModules', 'addMenu'), array($driver));

        $expectedModule = 'menutest';

        $script->expects($this->once())->method('getNotCoreBwcModules')->willReturn(array($expectedModule));
        $script->expects($this->exactly(1))->method('addMenu');
        $script->expects($this->at(1))->method('addMenu')->with($this->equalTo($expectedModule));

        $script->run();
    }
}
