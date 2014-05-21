<?php
/**
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement ("MSA"), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2014 SugarCRM Inc.  All rights reserved.
 */

require_once "tests/upgrade/UpgradeTestCase.php";
require_once 'upgrade/scripts/post/7_FixSingularList.php';

class UpgradeSingularListTest extends UpgradeTestCase
{
    protected $testModule = 'test_singleTest';

    /**
     * Rebuild languages
     */
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
        file_put_contents(
            'custom/Extension/application/Ext/Language/en_us.test.php',
            '<?php $app_list_strings["moduleList"]["' . $this->testModule . '"] = "singtest";'
        );

        $this->rebuildLang();
        SugarTestHelper::saveFile($this->getPackageLangFile());
        SugarTestHelper::saveFile($this->getLangPath());

        $this->upgrader->state['MBModules'] = array($this->testModule);
    }

    public function tearDown()
    {
        parent::tearDown();
        SugarTestHelper::tearDown();

        if (file_exists('custom/Extension/application/Ext/Language/en_us.test.php')) {
            unlink('custom/Extension/application/Ext/Language/en_us.test.php');
        }

        $this->rebuildLang();
    }

    /**
     * Test for ScanModules
     */
    public function testFixSingular()
    {
        $script = $this->getMockScript();
        $script->run();

        $app_list_strings = array();

        $this->assertFileExists($this->getPackageLangFile());
        include $this->getPackageLangFile();

        $this->assertEquals('singtest', $app_list_strings["moduleListSingular"][$this->testModule]);
    }

    /**
     * Test upgrade script fix both "moduleList" and "moduleListSingular" entries
     */
    public function testFixModuleListStrings()
    {
        // unlink file with moduleList translations
        unlink('custom/Extension/application/Ext/Language/en_us.test.php');
        $this->rebuildLang();

        // Create module language file
        $content = '<?php $mod_strings = array("LBL_MODULE_NAME" => "SingleTest");';
        file_put_contents($this->getLangPath(), $content);

        $script = $this->getMockScript();
        $script->run();

        $app_list_strings = array();
        $this->assertFileExists($this->getPackageLangFile());
        include $this->getPackageLangFile();

        $this->assertEquals('SingleTest', $app_list_strings["moduleList"][$this->testModule]);
        $this->assertEquals('SingleTest', $app_list_strings["moduleListSingular"][$this->testModule]);
    }

    /**
     * Mock post upgrade script to return custom language file
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getMockScript()
    {
        $mock = $this->getMockBuilder('SugarUpgradeFixSingularList')
            ->disableOriginalConstructor()
            ->setMethods(array('getLanguageFilePath', 'getPackages', 'getPackageLangFile'))
            ->getMock();

        $mock->upgrader = $this->upgrader;

        $keys = explode('_', $this->testModule);
        $packages = array($keys[0] => $keys[0]);

        $mock->expects($this->any())
            ->method('getPackages')
            ->will($this->returnValue($packages));

        $mock->expects($this->any())
            ->method('getPackageLangFile')
            ->will($this->returnValue($this->getPackageLangFile()));

        $mock->expects($this->any())
            ->method('getLanguageFilePath')
            ->will($this->returnValue($this->getLangPath()));

        return $mock;
    }

    /**
     * Get fake package lang file
     *
     * @return string
     */
    protected function getPackageLangFile()
    {
        return 'cache/test.en_us.lang.php';
    }

    /**
     * Get fake lang file path
     *
     * @return string
     */
    protected function getLangPath()
    {
        return 'cache/' . $this->testModule . '.en_us.lang.php';
    }
}
