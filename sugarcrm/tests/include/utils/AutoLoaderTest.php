<?php
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


class AutoLoaderTests extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->map = SugarAutoLoader::$filemap;
    }

    public function tearDown()
    {
        if ( SugarAutoLoader::fileExists('custom/include/utils/class_map.php') ) {
            SugarAutoLoader::unlink('custom/include/utils/class_map.php');
        }
        if ( file_exists(sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE)) ) {
            unlink(sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE));
        }

        SugarAutoLoader::$classMap = array();
        SugarAutoLoader::$classMapDirty = true;
        SugarAutoLoader::$memmap = array();
        SugarAutoLoader::$filemap = $this->map;
        parent::tearDown();
    }

    public static function tearDownAfterClass()
    {
        SugarAutoLoader::buildCache();
    }

    public function testExists()
    {
        $this->assertTrue((bool)SugarAutoLoader::fileExists('config.php'));
        $this->assertTrue((bool)SugarAutoLoader::fileExists('custom/index.html'));
        $this->assertFalse(SugarAutoLoader::fileExists('config.php.dontexist'));
        $this->assertFalse(SugarAutoLoader::fileExists('cache/file_map.php'));
    }

    public function testAddMap()
    {
        $this->assertFalse(SugarAutoLoader::fileExists('subdir/nosuchfile.php'));
        SugarAutoLoader::addToMap("subdir/nosuchfile.php", false);
        $this->assertTrue((bool)SugarAutoLoader::fileExists('subdir/nosuchfile.php'));
        $this->assertTrue((bool)SugarAutoLoader::fileExists('subdir'));
    }

    public function testDelMap()
    {
        SugarAutoLoader::addToMap("subdir/nosuchfile.php", false);
        $this->assertTrue((bool)SugarAutoLoader::fileExists('subdir/nosuchfile.php'));
        SugarAutoLoader::delFromMap("subdir", false);
        $this->assertFalse(SugarAutoLoader::fileExists('subdir/nosuchfile.php'));
        $this->assertFalse((bool)SugarAutoLoader::fileExists('subdir'));
    }
    
    public function testBuildClassCache()
    {
        // Clear out the existing class cache file
        file_put_contents(sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE), "<?php\n\$class_map=array('dont'=>'stop');\n\n");
        // Make sure the build class cache creates a new cache file
        SugarAutoLoader::buildClassCache();
        $class_map = array();
        include sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE);
        $this->assertTrue(count($class_map) > 1, "Class map is empty");
        $this->assertTrue(!isset($class_map['dont']), "Class map was not rebuilt");

        // Clear out the class cache file
        file_put_contents(sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE), "<?php\n\$class_map=array('dont'=>'stop');\n\n");

        // Add some entries to a custom class map
        SugarAutoLoader::ensureDir('custom/include/utils');
        SugarAutoLoader::put('custom/include/utils/class_map.php', "<?php\n\$class_map['voice_of']='a_porkchop';\n\n");
        
        // Make sure the build picks up the custom classes
        SugarAutoLoader::buildClassCache();
        $class_map = array();
        include sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE);
        $this->assertTrue(count($class_map) > 1, "Class map is empty #2");
        $this->assertTrue(!isset($class_map['dont']), "Class map was not rebuilt #2");
        $this->assertTrue(isset($class_map['voice_of']), "Class map did not pickup custom files");
    }

    public function testLoadClassMap()
    {
        // Set up a class cache file
        file_put_contents(sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE), "<?php\n\$class_map=array('dont'=>'stop');\n\n");
        SugarAutoLoader::loadClassMap();
        $this->assertTrue(count(SugarAutoLoader::$classMap) > 0, "Class map is empty");
        $this->assertArrayHasKey('dont', SugarAutoLoader::$classMap, "Did not load the correct class map.");
    }

    public function testSaveClassMap()
    {
        SugarAutoLoader::$classMap = array();
        SugarAutoLoader::$classMap["chicken"] = "shack";
        // Lie, tell it the class map isn't dirty, when it is.
        SugarAutoLoader::$classMapDirty = false;
        SugarAutoLoader::saveClassMap();

        // Make sure it didn't actually save the class map
        $this->assertFileNotExists(sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE), "Saved the class map cache when it didn't need to");
        
        // Now actually save it
        SugarAutoLoader::$classMapDirty = true;
        SugarAutoLoader::saveClassMap();
        
        $this->assertFileExists(sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE), "Didn't actually save the class map");

        $class_map = array();
        include sugar_cached(SugarAutoLoader::CLASS_CACHE_FILE);
        $this->assertTrue(count($class_map) > 0, "Class map is empty");
        $this->assertArrayHasKey('chicken', $class_map, "Class map was not rebuilt");
    }
}
