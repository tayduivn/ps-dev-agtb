<?php
//FILE SUGARCRM flav=pro || flav=ent || flav=sales ONLY
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Professional End User
 * License Agreement ("License") which can be viewed at
 * http://www.sugarcrm.com/EULA.  By installing or using this file, You have
 * unconditionally agreed to the terms and conditions of the License, and You may
 * not use this file except in compliance with the License. Under the terms of the
 * license, You shall not, among other things: 1) sublicense, resell, rent, lease,
 * redistribute, assign or otherwise transfer Your rights to the Software, and 2)
 * use the Software for timesharing or service bureau purposes such as hosting the
 * Software for commercial gain and/or for the benefit of a third party.  Use of
 * the Software may be subject to applicable fees and any use of the Software
 * without first paying applicable fees is strictly prohibited.  You do not have
 * the right to remove SugarCRM copyrights from the source code or user interface.
 * All copies of the Covered Code must include on each user interface screen:
 * (i) the "Powered by SugarCRM" logo and (ii) the SugarCRM copyright notice
 * in the same form as they appear in the distribution.  See full license for
 * requirements.  Your Warranty, Limitations of liability and Indemnity are
 * expressly stated in the License.  Please refer to the License for the specific
 * language governing these rights and limitations under the License.
 * Portions created by SugarCRM are Copyright (C) 2004 SugarCRM, Inc.;
 * All Rights Reserved.
 ********************************************************************************/

require_once 'modules/UpgradeWizard/SidecarUpdate/SidecarMetaDataUpgrader.php';
require_once 'tests/modules/UpgradeWizard/SidecarMetaDataFileBuilder.php';

class SidecarMetaDataUpgraderTest extends Sugar_PHPUnit_Framework_TestCase  {
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
     * Utility method for building and holding the builder object. Because of how
     * dataProviders are called in the test stack and how this test is using
     * setUpBeforeClass and tearDownAfterClass, this needs to be done this way.
     * 
     * NOTE: dataProvider methods are called before any method in the test. So
     * allowing the needed objects to be built like this is essential for the 
     * dataProviders to run as expected.
     * 
     * @static
     * @return SidecarMetaDataFileBuilder
     */
    public static function getBuilder() {
        if (null == self::$builder) {
            self::$builder = new SidecarMetaDataFileBuilder();
        }
        
        return self::$builder;
    }
    
    /**
     * Gets the MetaDataUpgrader object. See notes for getBuilder as to why this
     * is being handled this way.
     * 
     * @static
     * @return SidecarMetaDataUpgrader
     */
    public static function getUpgrader() {
        if (null === self::$upgrader) {
            self::$upgrader = new SidecarMetaDataUpgrader(); 
        }
        
        return self::$upgrader;
    }
    
    public static function setUpBeforeClass() {
        $GLOBALS['app_list_strings'] = return_app_list_strings_language($GLOBALS['current_language']);
        
        // Builds all the legacy test files
        self::getBuilder();
        self::$builder->buildFiles();
        
        // Run the upgrader so everything is in place for testing
        self::getUpgrader();
        self::$upgrader->upgrade();
    }
    
    public static function tearDownAfterClass() {
        self::getBuilder();
        self::$builder->teardownFiles();
    }
    
    public function testLegacyMetadataFilesForRemoval() {
        // Get files for removal - these should include our custom legacy files
        $upgrader = self::getUpgrader();
        $removals = $upgrader->getFilesForRemoval();
        
        // Get legacy file paths
        $builder = self::getBuilder();
        $legacyFiles = $builder->getFilesToMake('legacy');
        
        $expected = array_intersect($removals, $legacyFiles);
        sort($expected);
        sort($legacyFiles);
        $this->assertEquals($expected, $legacyFiles, 'Legacy files for removal is not the same as legacy files in build');
    }
    
    public function testUpgraderHasNoFailures() {
        // Get our failures
        $upgrader = self::getUpgrader();
        $failures = $upgrader->getFailures();
        
        $this->assertEmpty($failures, 'There were upgrade failures');
    }
    
    //BEGIN SUGARCRM flav=ent ONLY
    // Added for Bug 55568 - new OOTB metadata was not included in upgrade
    public function testUpgraderUsedNewViewDefs() {
        // Bug 55936 - Fixed hardcoded path for testing to pick up metadata changes
        $filename = 'custom/working/modules/Cases/clients/portal/views/edit/edit.php';
        $exists = file_exists($filename);
        $this->assertTrue($exists, 'Cases portal edit metadata did not convert');
        
        require $filename;
        $this->assertNotEmpty($viewdefs['Cases']['portal']['view']['edit']['buttons'], 'The buttons array from the new metadata was not captured');
    }
    //END SUGARCRM flav=ent ONLY
    
    public function _sidecarFilesInPlaceProvider() {
        $builder = self::getBuilder();
        return $builder->getFilesToMake('sidecar', true);
    }
    
    /**
     * @dataProvider _sidecarFilesInPlaceProvider
     * @param string $file
     */
    public function testSidecarFilesInPlace($file) {
        $this->assertFileExists($file, "File $file was not upgraded");
    }
    
    public function _sidecarMetadataFormatProvider() {
        $builder = self::getBuilder();
        return $builder->getFilesToMakeByView(array('list', 'edit', 'detail'));
    }
    
    /**
     * @dataProvider _sidecarMetadataFormatProvider
     * @param string $module
     * @param string $view
     * @param string $type
     * @param string $filepath
     */
    public function testSidecarMetadataFormat($module, $view, $type, $filepath) {
        require $filepath;
        
        // Begin assertions
        $this->assertNotEmpty($viewdefs[$module][$type]['view'][$view], "$view view defs for the $module module are empty");
        
        $defs = $viewdefs[$module][$type]['view'][$view];
        $this->assertArrayHasKey('panels', $defs, 'No panels array found in view defs');
        $this->assertArrayHasKey('fields', $defs['panels'][0], 'Fields array missing or in incorrect format in view defs');
        $this->assertNotEmpty($defs['panels'][0]['fields'], 'Fields array is empty');
        
        // List view specific test
        if ($view == 'list') {
            $this->assertArrayHasKey('name', $defs['panels'][0]['fields'][0], 'No name field found in the first field def');
        } 
    }
}