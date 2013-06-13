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
    protected function setUp()
    {
        SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        SugarTestHelper::tearDown();
    }

    /**
     * @covers MBModule::createMenu
     */
    public function testCreateMenu()
    {
        $moduleName = 'superAwesomeModule';
        $packageKey = 'sap';
        $mbModuleName = "{$packageKey}_{$moduleName}";
        $importRoute = http_build_query(
            array(
                'module' => 'Import',
                'action' => 'Step1',
                'import_module' => $mbModuleName,
                'return_module' => $mbModuleName,
                'return_action' => 'index',
            )
        );
        $expectedArray = array(
            array(
                'route' => "#$mbModuleName/create",
                'label' => 'LNK_NEW_RECORD',
                'acl_action' => 'create',
                'acl_module' => $mbModuleName,
                'icon' => 'icon-plus',
            ),
            array(
                'route' => "#$mbModuleName",
                'label' => 'LNK_LIST',
                'acl_action' => 'list',
                'acl_module' => $mbModuleName,
                'icon' => 'icon-reorder',
            ),
            array(
                'route' => "#bwc/index.php?{$importRoute}",
                'label' => 'LBL_IMPORT',
                'acl_action' => 'import',
                'acl_module' => $mbModuleName,
                'icon' => '',
            ),
        );

        $mb = new MBModule($moduleName, "modules/{$moduleName}", 'superAwesomePackage', $packageKey);
        $mb->config['importable'] = true;
        $path = "modules/{$moduleName}";
        $mb->createMenu($path);
        $target = "$path/clients/base/menus/header/header.php";
        $this->assertFileExists($target);

        include $target;

        $menu = $viewdefs[$mbModuleName]['base']['menu']['header'];
        $this->assertEquals($expectedArray, $menu);
        unlink($target);
    }
}
