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
namespace Sugarcrm\SugarcrmTestUnit\src\MetaData\VardefManager;

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sugarcrm\Sugarcrm\MetaData\ViewdefManager;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\MetaData\ViewdefManager
 */
class ViewdefManagerTest extends TestCase
{
    protected $filesToRemove = array();

    protected function setUp()
    {
        \SugarAutoLoader::load('include/utils.php');
        require 'include/modules.php';
        $GLOBALS['log'] = \LoggerManager::getLogger('SugarCRM');
        $GLOBALS['app_list_strings'] = return_app_list_strings_language('en_us', false);
        $GLOBALS['bwcModules'] = $bwcModules;
    }

    protected function tearDown()
    {
        foreach ($this->filesToRemove as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        parent::tearDown();
    }

    /**
     *
     * @covers ::loadViewdef
     * @covers ::getClientFiles
     * @covers ::findModuleViewdef
     * @covers ::loadDef
     * @dataProvider loadViewdefProvider
     */
    public function testLoadViewdef($base, $module, $view, $hasCount)
    {
        $mock = new ViewdefManager();

        $result = $mock->loadViewdef($base, $module, $view);

        $this->assertEquals($hasCount, count($result) > 0);
    }
    
    public function loadViewdefProvider()
    {
        return array(
            array('base', 'Quotes', 'record', true),
            array('base', 'Quotes', 'foo', false),
        );
    }
    
    /**
     *
     * @covers ::saveViewdef
     */
    public function testSaveViewdef()
    {
        $mock = new ViewdefManager();

        $viewdef = array(
            'Quotes' => array(
                'base' => array(
                    'view' => array(
                        'foo' => array(),
                    ),
                ),
            ),
        );
        $filename = 'custom/modules/Quotes/clients/base/views/foo/foo.php';
        $this->filesToRemove[] = $filename;

        $mock->saveViewdef($viewdef, 'Quotes', 'base', 'foo');
        $this->assertFileExists($filename);
    }
}
