<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

//BEGIN SUGARCRM flav=ent ONLY
use Sugarcrm\Sugarcrm\Portal\Factory as PortalFactory;
//END SUGARCRM flav=ent ONLY
use PHPUnit\Framework\TestCase;

class ModuleInstallerTest extends TestCase
{
    //BEGIN SUGARCRM flav=ent ONLY
    protected $caseDeflection;
    protected $installing;

    protected function setUp() : void
    {
        // save current value
        $settings = Administration::getSettings('portal', true)->settings;
        $this->caseDeflection = $settings['portal_caseDeflection'] ?? null;
        $this->installing = $GLOBALS['installing'] ?? null;
        unset($GLOBALS['installing']);
    }

    protected function tearDown() : void
    {
        // restore current value
        if (isset($this->caseDeflection)) {
            $admin = new Administration();
            $admin->saveSetting('portal', 'caseDeflection', $this->caseDeflection, 'support');
            $this->caseDeflection = null;
        }
        if (isset($this->installing)) {
            $GLOBALS['installing'] = $this->installing;
            $this->installing = null;
        }
    }

    /**
     * @covers ModuleInstaller::getPortalConfig
     */
    public function testGetPortalConfig()
    {
        // test portal config setting based on the license used during the test run
        if (PortalFactory::getInstance('Settings')->isServe() === false) {
            $portalConfig = ModuleInstaller::getPortalConfig();
            $this->assertEquals('disabled', $portalConfig['caseDeflection'], 'Case deflection should be disabled by default before opening a case');
        } else {
            $GLOBALS['db']->query("DELETE FROM config WHERE category = 'portal' AND name = 'caseDeflection' AND platform = 'support'");
            $portalConfig = ModuleInstaller::getPortalConfig();
            $this->assertEquals('enabled', $portalConfig['caseDeflection'], 'Case deflection should be enabled by default before opening a case');
            $GLOBALS['db']->query("INSERT INTO config VALUES('portal', 'caseDeflection', 'disabled', 'support')");
            $portalConfig = ModuleInstaller::getPortalConfig();
            $this->assertEquals('disabled', $portalConfig['caseDeflection'], 'Case deflection should be disabled before opening a case');
            $GLOBALS['db']->query("UPDATE config SET value = 'enabled' WHERE category = 'portal' AND name = 'caseDeflection' AND platform = 'support'");
            $portalConfig = ModuleInstaller::getPortalConfig();
            $this->assertEquals('enabled', $portalConfig['caseDeflection'], 'Case deflection should be enabled before opening a case');
        }
    }
    //END SUGARCRM flav=ent ONLY

    /**
     * @covers ModuleInstaller::merge_files
     */
    public function testMergeFiles()
    {
        $minst = $this->createPartialMock('ModuleInstaller', ['mergeModuleFiles']);
        $minst->expects($this->once())->method('mergeModuleFiles')
            ->with('application', 'foo', 'bar', 'baz');
        $minst->merge_files('foo', 'bar', 'baz', true);
    }

    /**
     * @covers ModuleInstaller::merge_files
     */
    public function testMergeFiles2()
    {
        $minst = $this->createPartialMock('ModuleInstaller', ['mergeModuleFiles']);
        // We add one to the count for the application extension invocation.
        $count = count($minst->modules) + 1;
        $minst->expects($this->exactly($count))->method('mergeModuleFiles')
            ->with($this->anything(), 'foo', 'bar', 'baz');
        $minst->merge_files('foo', 'bar', 'baz', false);
    }

    public function testModuleDirs()
    {
        $modules = ModuleInstaller::getModuleDirs();
        $this->assertContains("ActivityStream/Activities", $modules, "ActivityStream/Activities not found!");
    }
}
