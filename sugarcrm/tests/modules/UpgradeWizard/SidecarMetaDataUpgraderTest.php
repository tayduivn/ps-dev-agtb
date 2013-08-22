<?php
// FILE SUGARCRM flav=pro || flav=ent ONLY
/*********************************************************************************
 * By installing or using this file, you are confirming on behalf of the entity
 * subscribed to the SugarCRM Inc. product ("Company") that Company is bound by
 * the SugarCRM Inc. Master Subscription Agreement (“MSA”), which is viewable at:
 * http://www.sugarcrm.com/master-subscription-agreement
 *
 * If Company is not bound by the MSA, then by installing or using this file
 * you are agreeing unconditionally that Company will be bound by the MSA and
 * certifying that you have authority to bind Company accordingly.
 *
 * Copyright (C) 2004-2013 SugarCRM Inc.  All rights reserved.
 ********************************************************************************/

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';
require_once 'tests/modules/UpgradeWizard/SidecarMetaDataFileBuilder.php';

class SidecarMetaDataUpgraderTest extends Sugar_PHPUnit_Framework_TestCase
{
    /**
     * Flag to let us know if there is a current upgrade wizard log that is
     * backed
     * up to support this test
     *
     * @var bool
     */
    protected static $logBackedUp = false;

    /**
     * The files builder to bring in legacy files into place and prepare them
     * for upgrade
     *
     * @var SidecarMetaDataFileBuilder
     */
    public static $builder = null;

    /**
     * The upgrader object, called once to set everthing up
     *
     * @var SidecarMetaDataUpgrader
     */
    public static $upgrader = null;

    /**
     * Utility method for building and holding the builder object.
     * Because of how
     * dataProviders are called in the test stack and how this test is using
     * setUpBeforeClass and tearDownAfterClass, this needs to be done this way.
     *
     * NOTE: dataProvider methods are called before any method in the test. So
     * allowing the needed objects to be built like this is essential for the
     * dataProviders to run as expected.
     *
     * @static
     *
     * @return SidecarMetaDataFileBuilder
     */
    public static function getBuilder()
    {
        if (null == self::$builder) {
            self::$builder = new SidecarMetaDataFileBuilder();
        }

        return self::$builder;
    }

    /**
     * Gets the MetaDataUpgrader object.
     * See notes for getBuilder as to why this
     * is being handled this way.
     *
     * @static
     *
     * @return SidecarMetaDataUpgrader
     */
    public static function getUpgrader()
    {
        if (null === self::$upgrader) {
            self::$upgrader = new SidecarMetaDataUpgraderForTest();
        }

        return self::$upgrader;
    }

    public static function setUpBeforeClass()
    {
        // If there is an upgrade wizard log in place, back it up
        $GLOBALS['app_list_strings'] = return_app_list_strings_language(
            $GLOBALS['current_language']);

        // Builds all the legacy test files
        SugarTestHelper::setUp('files');
        self::getBuilder();
        self::$builder->buildFiles();

        // Run the upgrader so everything is in place for testing
        self::getUpgrader();
        self::$upgrader->upgrade();
    }

    public static function tearDownAfterClass()
    {
        self::getBuilder();
        self::$builder->teardownFiles();
        SugarTestHelper::tearDown();
    }

    public function testLegacyMetadataFilesForRemoval()
    {
        // Get files for removal - these should include our custom legacy files
        $upgrader = self::getUpgrader();
        $removals = $upgrader->getFilesForRemoval();

        // Get legacy file paths
        $builder = self::getBuilder();
        $legacyFiles = $builder->getFilesToMake('legacy');
        $sidecarfiles = $builder->getFilesToMake('sidecar');
        // don't expect removal if file is the same
        $legacyFiles = array_diff($legacyFiles, $sidecarfiles);
        // Upgrader can remove additional files, so drop those that aren't in our file list
        $removed = array_intersect($removals, $legacyFiles);
        sort($removed);
        sort($legacyFiles);
        $this->assertEquals($legacyFiles, $removed,
            'Legacy files for removal is not the same as legacy files in build');
    }

    public function testUpgraderHasNoFailures()
    {
        // Get our failures
        $upgrader = self::getUpgrader();
        $failures = $upgrader->getFailures();

        $this->assertEmpty($failures, 'There were upgrade failures: '.var_export($failures, true));
    }

    // BEGIN SUGARCRM flav=ent ONLY
    // Added for Bug 55568 - new OOTB metadata was not included in upgrade
    public function testUpgraderUsedNewViewDefs()
    {
        // Bug 55936 - Fixed hardcoded path for testing to pick up metadata
        // changes
        $filename = 'custom/working/modules/Cases/clients/portal/views/record/record.php';
        $exists = file_exists($filename);
        $this->assertTrue($exists, 'Cases portal edit metadata did not convert');

        require $filename;
        $this->assertNotEmpty($viewdefs['Cases']['portal']['view']['record']['buttons'],
            'The buttons array from the new metadata was not captured');
    }
    // END SUGARCRM flav=ent ONLY

    public function _sidecarFilesInPlaceProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMake('sidecar', true);
    }

    /**
     * @dataProvider _sidecarFilesInPlaceProvider
     *
     * @param string $file
     */
    public function testSidecarFilesInPlace($file)
    {
        if(empty($file)) {
            return;
        }
        $this->assertFileExists($file, "File $file was not upgraded");
    }

    public function _sidecarMetadataFormatProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView(array('list', 'edit', 'detail'));
    }

    /**
     * @dataProvider _sidecarMetadataFormatProvider
     *
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     */
    public function testSidecarMetadataFormat($module, $view, $type, $filepath)
    {
        if(empty($filepath)) {
            return;
        }
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        // Begin assertions
        $this->assertNotEmpty($viewdefs[$module][$type]['view'][$view],
            "$view view defs for the $module module are empty");

        $defs = $viewdefs[$module][$type]['view'][$view];
        $this->assertArrayHasKey('panels', $defs, 'No panels array found in view defs');
        $this->assertArrayHasKey('fields', $defs['panels'][0],
            'Fields array missing or in incorrect format in view defs');
        $this->assertNotEmpty($defs['panels'][0]['fields'], 'Fields array is empty');

        // List view specific test
        if ($view == 'list') {
            $this->assertArrayHasKey('name', $defs['panels'][0]['fields'][0],
                'No name field found in the first field def');
        }
    }

    /**
     * Added for bug 57414
     * Available fields of mobile listview shown under default fields list after
     * upgrade
     * * @group Bug57414
     * @dataProvider _sidecarListEnabledFieldProvider
     */
    public function testSidecarListViewDefsProperlyFlagEnabledFields($module, $view, $type, $filepath)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        // Begin assertions
        $this->assertNotEmpty($viewdefs[$module][$type]['view'][$view],
            "$view view defs for the $module module are empty");

        $defs = $viewdefs[$module][$type]['view'][$view];
        $this->assertTrue(isset($defs['panels'][0]['fields']),
            'Field array is missing from the upgrade file');

        // Test actual fix for this bug
        $test['name'] = array('default' => '', 'enabled' => '', 'edefault' => true,
            'eenabled' => true);
        $testfield = 'assigned_user_name';
        // BEGIN SUGARCRM flav=ent ONLY
        if ($type == 'portal') {
            $testfield = 'priority';
        }
        // END SUGARCRM flav=ent ONLY
        $test[$testfield] = array('default' => '', 'enabled' => '', 'edefault' => false,
            'eenabled' => true);

        foreach ($defs['panels'][0]['fields'] as $field) {
            if (isset($test[$field['name']])) {
                $test[$field['name']]['default'] = $field['default'];
                $test[$field['name']]['enabled'] = $field['enabled'];
            }
        }

        // Assertions
        foreach ($test as $field => $assert) {
            $this->assertEquals($assert['edefault'], $assert['default'],
                "$field default should be false but is {$assert['default']}");
            $this->assertEquals($assert['eenabled'], $assert['enabled'],
                "$field enabled should be true but is {$assert['enabled']}");
        }
    }

    public function _sidecarListEnabledFieldProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView('list');
    }

    /**
     * Test for record-type upgrades
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     * @dataProvider _sidecarRecordProvider
     */
    public function testSidecarRecordfields($module, $view, $type, $filepath)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        $defs = $viewdefs[$module][$type]['view'][$view];
        $this->assertTrue(isset($defs['panels'][1]['fields']), 'Field array is missing from the upgrade file');
        $idfield = null;
        foreach($defs['panels'] as $panel) {
            // adding to header is wrong
            if(!empty($panel['header'])) continue;
            foreach($panel['fields'] as $field) {
                // look for ID
                if($field == 'id'  || (!empty($field['name']) && $field['name'] == 'id')) {
                    $idfield = $field;
                    break 2;
                }
            }
        }
        $this->assertNotEmpty($idfield, "ID field not found in merged view");
    }

    public function _sidecarRecordProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView('record');
    }

    /**
     *
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     * @dataProvider _sidecarSearchProvider
     */
    public function testSidecarSearchDefs($module, $view, $type, $filepath)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        $defs = $viewdefs[$module][$type]['filter']['default'];
        $this->assertArrayHasKey("fields", $defs);
        $this->assertArrayHasKey('$owner', $defs['fields']);
        $this->assertArrayHasKey('$favorite',  $defs['fields']);
        $this->assertArrayHasKey('address_city',  $defs['fields']);
        $this->assertArrayHasKey('dbFields',  $defs['fields']['address_city']);
    }

    public function _sidecarSearchProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView('filter');
    }

    /**
     *
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     * @dataProvider _sidecarMenuProvider
     */
    public function testSidecarMenuDefs($module, $view, $type, $filepath)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        $_module = strtolower($module);
        $defs = $viewdefs[$module][$type]['menu']['header'];
        // create
        $this->assertEquals('edit', $defs[0]['acl_action']);
        $this->assertEquals($module, $defs[0]['acl_module']);
        $this->assertEquals('icon-plus', $defs[0]['icon']);
        $this->assertEquals("#$module/create", $defs[0]['route']);
        // list
        $this->assertEquals('list', $defs[1]['acl_action']);
        $this->assertEquals($module, $defs[1]['acl_module']);
        $this->assertEquals("#$module", $defs[1]['route']);
        // reports
        $this->assertEquals('list', $defs[2]['acl_action']);
        $this->assertEquals($module, $defs[2]['acl_module']);
        $this->assertEquals('icon-bar-chart', $defs[2]['icon']);
        $this->assertEquals("#bwc/index.php?module=Reports&action=index&view=$_module", $defs[2]['route']);
        // import
        $this->assertEquals('import', $defs[3]['acl_action']);
        $this->assertEquals($module, $defs[3]['acl_module']);
        $this->assertEquals('icon-upload-alternative', $defs[3]['icon']);
        $this->assertEquals("#bwc/index.php?module=Import&action=Step1&import_module=$module&return_module=$module&return_action=index", $defs[3]['route']);
    }

    public function _sidecarMenuProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView('menu');
    }

    /**
     *
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     * @dataProvider _sidecarQuickMenuProvider
     */
    public function testSidecarQuickMenuDefs($module, $view, $type, $filepath)
    {
        $this->assertFileExists($filepath, "$filepath does not exist");
        require $filepath;

        $defs = $viewdefs[$module]['base']['menu']['quickcreate'];
        $this->assertEquals('create', $defs['layout']);
    }

    public function _sidecarQuickMenuProvider()
    {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView('quickmenu');
    }



}

class SidecarMetaDataUpgraderForTest extends SidecarMetaDataUpgrader
{

    public function logUpgradeStatus($msg)
    {
        $GLOBALS['log']->info($msg);
    }
}