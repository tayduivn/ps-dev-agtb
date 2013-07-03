<?php
/*
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright  2004-2013 SugarCRM Inc.  All rights reserved.
 */

require_once 'modules/ModuleBuilder/MB/MBModule.php';

class MBModuleTest extends Sugar_PHPUnit_Framework_TestCase
{
    protected $moduleName = 'superAwesomeModule';
    protected $packageKey = 'sap';
    protected $mbModuleName;
    protected $target;
    protected $path;
    
    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');
        $this->mbModuleName = "{$this->packageKey}_{$this->moduleName}";
        $this->path = "modules/{$this->moduleName}";
        $this->target = "$this->path/clients/base/menus/header/header.php";
    }

    protected function tearDown()
    {
        @unlink($this->target);
        SugarTestHelper::tearDown();
    }

    /**
     * @covers MBModule::createMenu
     */
    public function testCreateMenu()
    {
        $expectedArray = $this->getExpectedActionItems();

        $mb = new MBModule($this->moduleName, "modules/{$this->moduleName}", 'superAwesomePackage', $this->packageKey);
        $mb->config['importable'] = false;
        $mb->createMenu($this->path);
        
        // Assertions
        $this->assertFileExists($this->target);

        include $this->target;

        $menu = $viewdefs[$this->mbModuleName]['base']['menu']['header'];
        $this->assertEquals($expectedArray, $menu);
    }
    
    /**
     * @covers MBModule::createMenu
     */
    public function testCreateMenuWithImport()
    {
        $expectedArray = $this->getExpectedActionItems(true);

        $mb = new MBModule($this->moduleName, "modules/{$this->moduleName}", 'superAwesomePackage', $this->packageKey);
        $mb->config['importable'] = true;
        $mb->createMenu($this->path);
        
        // Assertions
        $this->assertFileExists($this->target);

        include $this->target;

        $menu = $viewdefs[$this->mbModuleName]['base']['menu']['header'];
        $this->assertEquals($expectedArray, $menu);
    }
    
    protected function getExpectedActionItems($import = false)
    {
        $expectedArray = array(
            array(
                'route' => "#{$this->mbModuleName}/create",
                'label' => 'LNK_NEW_RECORD',
                'acl_action' => 'create',
                'acl_module' => $this->mbModuleName,
                'icon' => 'icon-plus',
            ),
            array(
                'route' => "#{$this->mbModuleName}",
                'label' => 'LNK_LIST',
                'acl_action' => 'list',
                'acl_module' => $this->mbModuleName,
                'icon' => 'icon-reorder',
            ),
        );
            
        if ($import) {
            $importRoute = http_build_query(
                array(
                    'module' => 'Import',
                    'action' => 'Step1',
                    'import_module' => $this->mbModuleName,
                    'return_module' => $this->mbModuleName,
                    'return_action' => 'index',
                )
            );
            
            $expectedArray[] = array(
                'route' => "#bwc/index.php?{$importRoute}",
                'label' => 'LBL_IMPORT',
                'acl_action' => 'import',
                'acl_module' => $this->mbModuleName,
                'icon' => '',
            );
        }
        
        return $expectedArray;
    }
}
