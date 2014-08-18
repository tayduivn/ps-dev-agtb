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


class AutoLoaderTests extends Sugar_PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->fileMap = SugarAutoLoader::$filemap;
        $this->namespaceMap = SugarAutoLoader::$namespaceMap;
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
        SugarAutoLoader::$filemap = $this->fileMap;
        SugarAutoLoader::$namespaceMap = $this->namespaceMap;
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
        
        // Tests that a file skipped for caching will read from the file system
        $this->assertTrue(SugarAutoLoader::fileExists('cache/file_map.php'));
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

    /**
     *
     * Test prefix/directory namespace mapping to filename
     * @dataProvider providerTestGetFilenameForFQCN
     */
    public function testGetFilenameForFQCN($type, $namespace, $dir, $className, $fileName)
    {
        SugarAutoLoader::addNamespace($namespace, $dir, $type);
        $this->assertSame($fileName, SugarAutoLoader::getFilenameForFQCN($className));
    }

    public function providerTestGetFilenameForFQCN()
    {
        $ds = DIRECTORY_SEPARATOR;

        return array(

            /*
             * PSR-0 - see http://www.php-fig.org/psr/psr-0/
             */
            array(
                'psr0',
                'Doctrine',
                'vendor',
                'Doctrine\\Common\\IsolatedClassLoader',
                'vendor'.$ds.'Doctrine'.$ds.'Common'.$ds.'IsolatedClassLoader.php',
            ),
            array(
                'psr0',
                'Symfony\\Core',
                'vendor/Symfony/Core/src',
                'Symfony\\Core\\Request',
                'vendor'.$ds.'Symfony'.$ds.'Core'.$ds.'src'.$ds.'Symfony'.$ds.'Core'.$ds.'Request.php',
            ),
            array(
                'psr0',
                'Zend',
                'vendor',
                'Zend\\Acl',
                'vendor'.$ds.'Zend'.$ds.'Acl.php',
            ),
            array(
                'psr0',
                'namespace',
                'vendor',
                'namespace\\package\\Class_Name',
                'vendor'.$ds.'namespace'.$ds.'package'.$ds.'Class'.$ds.'Name.php',
            ),
            array(
                'psr0',
                'namespace',
                'vendor',
                'namespace\package_name\Class_Name',
                'vendor'.$ds.'namespace'.$ds.'package_name'.$ds.'Class'.$ds.'Name.php',
            ),

            /*
             * PSR-4 - see http://www.php-fig.org/psr/psr-4/
             */
            array(
                'psr4',
                'Acme\Log\Writer',
                'acme-log-writer/lib',
                'Acme\\Log\\Writer\\File_Writer',
                'acme-log-writer'.$ds.'lib'.$ds.'File_Writer.php',
            ),
            array(
                'psr4',
                'Symfony\Core',
                'vendor/Symfony/Core',
                'Symfony\Core\Request',
                'vendor'.$ds.'Symfony'.$ds.'Core'.$ds.'Request.php',
            ),
            array(
                'psr4',
                'namespace',
                'vendor/namespace',
                'namespace\package_name\Class_Name',
                'vendor'.$ds.'namespace'.$ds.'package_name'.$ds.'Class_Name.php',
            ),

            /*
             * Sugarcrm namespace
             */
            array(
                'psr4',
                'Sugarcrm\\Core',
                '',
                'Sugarcrm\\Core\\modules\\Account',
                'modules'.$ds.'Account.php',
            ),
            array(
                'psr4',
                'Sugarcrm\\Core\\include',
                'include',
                'Sugarcrm\\Core\\include\\SugarLogger\\LoggerManager',
                'include'.$ds.'SugarLogger'.$ds.'LoggerManager.php',
            ),
            array(
                'psr4',
                'Sugarcrm\\Core\\Extension',
                'custom/Extension',
                'Sugarcrm\\Core\\Extension\\modules\\xxx_Module\\yyy_Bean',
                'custom'.$ds.'Extension'.$ds.'modules'.$ds.'xxx_Module'.$ds.'yyy_Bean.php',
            ),
        );
    }

    /**
     *
     * Test actual class loading using namespaces
     */
    public function testAutoloadNamespaces()
    {
        // create test class/file
        $ds = DIRECTORY_SEPARATOR;
        $fqcn = 'Sugarcrm\\Core\\modules\\Accounts\\Bogus';
        $fileName = 'modules' . $ds . 'Accounts' . $ds . 'Bogus.php';
        $content = "<?php\nnamespace Sugarcrm\\Core\\modules\\Accounts;\nclass Bogus { }\n";
        file_put_contents($fileName, $content);

        // rebuid cache to pick up the test file
        SugarAutoLoader::buildCache();

        // reset classMap and register test namespace
        SugarAutoLoader::addNamespace('Sugarcrm\\Core\\modules\\', 'modules', 'psr4');

        // instantiate test class
        $bogus = new \Sugarcrm\Core\modules\Accounts\Bogus();
        $this->assertEquals($fileName, SugarAutoLoader::$classMap[$fqcn]);
        $this->assertInstanceOf('Sugarcrm\\Core\\modules\\Accounts\\Bogus', $bogus);

        // cleanup
        unlink($fileName);
    }

    /**
     *
     * Test addNamespace
     */
    public function testAddNamespace()
    {
        SugarAutoLoader::$namespaceMap = array();

        // 1st pass - add first level namespace - also test fixups on trailing \ and /
        SugarAutoLoader::addNamespace('Sugarcrm', '/');
        $expected = array(
            'Sugarcrm\\' => array(''),
        );
        $this->assertSame($expected, SugarAutoLoader::$namespaceMap);

        // 2nd pass - add second level namespace
        SugarAutoLoader::addNamespace('Sugarcrm\\lib\\', 'include');
        $expected = array(
            'Sugarcrm\\lib\\' => array('include'),
            'Sugarcrm\\' => array(''),
        );
        $this->assertSame($expected, SugarAutoLoader::$namespaceMap);

        // 3rd pass - add another second level namespace (alphabetic order matters)
        SugarAutoLoader::addNamespace('Acme\\LooneyTunes\\', 'vendor/Acme');
        $expected = array(
            'Acme\\LooneyTunes\\' => array('vendor/Acme'),
            'Sugarcrm\\lib\\' => array('include'),
            'Sugarcrm\\' => array(''),
        );
        $this->assertSame($expected, SugarAutoLoader::$namespaceMap);

        // 4th pass - add third level namespace
        SugarAutoLoader::addNamespace('Acme\\LooneyTunes\\RoadRunner\\', 'vendor/RoadRunner');
        $expected = array(
            'Acme\\LooneyTunes\\RoadRunner\\' => array('vendor/RoadRunner'),
            'Acme\\LooneyTunes\\' => array('vendor/Acme'),
            'Sugarcrm\\lib\\' => array('include'),
            'Sugarcrm\\' => array(''),
        );
        $this->assertSame($expected, SugarAutoLoader::$namespaceMap);

        // 5th pass - add another second level namespace (alphabetic order matters)
        SugarAutoLoader::addNamespace('Sugarcrm\\modules\\', 'modules');
        $expected = array(
            'Acme\\LooneyTunes\\RoadRunner\\' => array('vendor/RoadRunner'),
            'Acme\\LooneyTunes\\' => array('vendor/Acme'),
            'Sugarcrm\\lib\\' => array('include'),
            'Sugarcrm\\modules\\' => array('modules'),
            'Sugarcrm\\' => array(''),
        );
        $this->assertSame($expected, SugarAutoLoader::$namespaceMap);

        // 6th pass - add new path to already existing second level namespace
        SugarAutoLoader::addNamespace('Sugarcrm\\modules\\', 'modules2');
        $expected = array(
            'Acme\\LooneyTunes\\RoadRunner\\' => array('vendor/RoadRunner'),
            'Acme\\LooneyTunes\\' => array('vendor/Acme'),
            'Sugarcrm\\lib\\' => array('include'),
            'Sugarcrm\\modules\\' => array('modules', 'modules2'),
            'Sugarcrm\\' => array(''),
        );
        $this->assertSame($expected, SugarAutoLoader::$namespaceMap);
    }
}
