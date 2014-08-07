<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/06_Customer_Center/10_Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

require_once 'SugarTestHelper.php';

/**
 *
 * !!! Pay attention - the following test run in a separate process !!!
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class ComposerAutoLoaderTests extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->fileMap = SugarAutoLoader::$filemap;
        $this->namespaceMap = SugarAutoLoader::$namespaceMap;
        $this->exclude = SugarAutoLoader::$exclude;
        $this->composerNamespaceMap = SugarAutoLoader::$composerNamespaceMap;

        if (isset($GLOBALS['sugar_config']['autoloader']['composer'])) {
            $this->useComposer = $GLOBALS['sugar_config']['autoloader']['composer'];
        }
    }

    public function tearDown()
    {
        // restore composer config flag
        if (property_exists($this, 'useComposer')) {
            $GLOBALS['sugar_config']['autoloader']['composer'] = $this->useComposer;
        } elseif (isset($GLOBALS['sugar_config']['autoloader']['composer'])) {
            unset($GLOBALS['sugar_config']['autoloader']['composer']);
        }
        SugarConfig::getInstance()->clearCache();

        if (SugarAutoLoader::fileExists('custom/include/utils/class_map.php')) {
            SugarAutoLoader::unlink('custom/include/utils/class_map.php');
        }
        if (file_exists(sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE))) {
            unlink(sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE));
        }

        SugarAutoLoader::$classMap = array();
        SugarAutoLoader::$classMapDirty = true;
        SugarAutoLoader::$memmap = array();
        SugarAutoLoader::$filemap = $this->fileMap;
        SugarAutoLoader::$namespaceMap = $this->namespaceMap;
        SugarAutoLoader::$exclude = $this->exclude;
        SugarAutoLoader::$composerNamespaceMap = $this->composerNamespaceMap;
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarAutoLoader::init();
        SugarAutoLoader::buildCache();
    }

    /**
     * Test composer autoloader
     * @dataProvider dataProviderTestComposer
     */
    public function testUseComposer($composer, $nsMap, $expectNs, $expectedCm, $expectedEx)
    {
        if (!file_exists('vendor/ruflin/elastica/lib/Elastica/Client.php')) {
            $this->markTestSkipped('Elastica library through composer not available');
        }

        $GLOBALS['sugar_config']['autoloader']['composer'] = $composer;
        SugarConfig::getInstance()->clearCache();

        SugarAutoLoader::$exclude = array();
        SugarAutoLoader::$classMap = array();
        SugarAutoLoader::$namespaceMap = array();
        SugarAutoLoader::$composerNamespaceMap = $nsMap;
        SugarAutoLoader::init();
        SugarAutoLoader::buildCache();

        $this->assertEquals($expectNs, SugarAutoLoader::$namespaceMap);

        $client = new \Elastica\Client();
        $this->assertInstanceOf('Elastica\\Client', $client);
        $this->assertEquals($expectedCm, SugarAutoLoader::$classMap['Elastica\\Client']);
        $this->assertEquals($expectedEx, SugarAutoLoader::$exclude);
    }

    public function dataProviderTestComposer()
    {
        return array(
            array(
                true,
                array('Elastica\\' => 'vendor/ruflin/elastica/lib/Elastica'),
                array(),
                false,
                array('vendor/ruflin/elastica/lib/Elastica/'),
            ),
            array(
                false,
                array('Elastica\\' => 'vendor/ruflin/elastica/lib/Elastica'),
                array('Elastica\\' => 'vendor/ruflin/elastica/lib/Elastica'),
                'vendor/ruflin/elastica/lib/Elastica/Client.php',
                array(),
            ),
        );
    }
}
