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


require_once 'include/SugarSearchEngine/SugarSearchEngineFactory.php';

class SugarSearchEngineFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
    }

    public static function tearDownAfterClass()
    {
        foreach(self::customizationProvider() as $row) {
            $file = self::getEngineFilePath($row[0], true);
            if(file_exists($file)) unlink($file);   // Clean up customizations
            unset(SugarSearchEngineFactory::$_instance[$row[0]]); // Clear cache
        }
        SugarAutoLoader::buildCache();
    }

    /**
     * @dataProvider factoryProvider
     * @param string $engineName
     * @param string $expectedClass
     */
    public function testFactoryMethod($engineName, $expectedClass)
    {
        $instance = SugarSearchEngineFactory::getInstance($engineName);
        $this->assertContains($expectedClass, get_class($instance));
    }

    /**
     * SugarSearchEngine factory test
     * @return array
     */
    public static function factoryProvider()
    {
        switch(SugarSearchEngineFactory::getFTSEngineNameFromConfig()) {
            case 'Elastic'  : $default = 'SugarSearchEngineElastic'; break;
            default         : $default = 'SugarSearchEngine';
        }

        return array(
            // depends on config, disabled array('','SugarSearchEngine'),
            array('Elastic','SugarSearchEngineElastic'),
            //Fallback to default.
            array('BadClassName','SugarSearchEngine')
        );
    }

    /**
     * SugarSearchEngine customization cases to test
     * @return array
     */
    public static function customizationProvider()
    {
        return array(
            // New search engine type which extends SugarSearchEngine
            array('Fake1', ''),
            // New search engine type which extends SugarSearchEngineElastic
            array('Fake2', 'Elastic'),
            // Customization of existing Elastic engine
            array('Elastic', 'Elastic'),
            // Customization of base class SugarSearchEngine
            array('', ''),
        );
    }

    /**
     * @dataProvider customizationProvider
     * @param string $engineName    Engine type that we are about to customize
     * @param string $baseClass     The class the custom engine extends on
     */
    public function testLoadingSearchEngineClassCustomizations($engineName, $baseClass)
    {
        // We need to skip this test for empty $engineName in case a search
        // engine has been configured on the test instance
        if (empty($engineName) && SugarSearchEngineFactory::getFTSEngineNameFromConfig() != '') {
            $this->markTestSkipped(
                'Cannot test SugarSearchEngine customization because an engine is configured'
            );
        }

        // setup the custom class file
        self::createCustomEngineClassFile($engineName, $baseClass);

        // Clearing cache to make sure we pick up the current state
        unset(SugarSearchEngineFactory::$_instance[$engineName]);
        $instance = SugarSearchEngineFactory::getInstance($engineName);

        // validate
        $className = self::getEngineClassName($engineName, true);
        $this->assertEquals($className, get_class($instance));
    }

    /**
     * Returns (custom) directory of given engine
     * @return string
     */
    protected static function getEngineDir($engineName, $custom = false)
    {
        $dir = rtrim("include/SugarSearchEngine/{$engineName}", "/");
        return $custom ? "custom/{$dir}" : $dir;
    }

    /**
     * Returns (custom) file path for a given Engine type name
     * @param string    $engineName Engine type name
     * @param boolean   $custom     Return custom path
     *
     * @return string
     */
    protected static function getEngineFilePath($engineName, $custom = false)
    {
        $dir = self::getEngineDir($engineName, $custom);
        return "{$dir}/SugarSearchEngine{$engineName}.php";
    }

    /**
     * Returns (custom) class name for a given Engine type name
     * @param string    $engineName Engine type name
     * @param boolean   $custom     Return custom class name
     *
     * @return string
     */
    protected static function getEngineClassName($engineName, $custom = false)
    {
        $engineName = "SugarSearchEngine{$engineName}";
        return $custom ? "Custom{$engineName}" : $engineName;
    }

    /**
     * Create a custom engine class file, implicitly rebuilds autoloader cache
     * @param string    $engineName Engine type name
     * @param string    $baseClass  The name of the class we extend
     */
    protected static function createCustomEngineClassFile($engineName, $baseClass)
    {
        // custom directory
        $directory = self::getEngineDir($engineName, true);
        if (!is_dir($directory)) {
            sugar_mkdir($directory, '', true);
        }

        // custom file
        $file = self::getEngineFilePath($engineName, true);
        if (file_exists($file)) {
            unlink($file);
        }

        $fileContents = <<<EOQ
<?php
require_once('%s');
class %s %s {
}

EOQ;
        $requireOnce = self::getEngineFilePath($baseClass);
        $className = self::getEngineClassName($engineName, true);
        $extends = "extends ".self::getEngineClassName($baseClass);
        file_put_contents($file, sprintf($fileContents, $requireOnce, $className, $extends));
        SugarAutoLoader::buildCache();
    }
}
