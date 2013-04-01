<?php
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


require_once 'include/SugarSearchEngine/SugarSearchEngineFactory.php';
require_once('include/SugarSearchEngine/SugarSearchEngineAbstractBase.php');


class SugarSearchEngineFactoryTest extends Sugar_PHPUnit_Framework_TestCase
{
//    static $customfile;

    public static function setUpBeforeClass()
    {
//        $directory = 'custom/include/SugarSearchEngine/Fake';
//        self::$customfile = $directory . '/CustomSugarSearchEngineFake.php';
//        if(!is_dir($directory)) sugar_mkdir($directory, '', true);
    }

    public static function tearDownAfterClass()
    {
//        unset(SugarSearchEngineFactory::$_instance['Fake']); // Clearing cache
//        if (file_exists(self::$customfile)) unlink(self::$customfile);

        foreach(self::customizationProvider() as $row) {
            $file = self::getCustomEngineFilePath($row[0], $row[1]);
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
        $this->assertEquals($expectedClass, get_class($instance));
    }

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
     * SugarSearchEngine custimization cases to test
     * @return array
     */
    public static function customizationProvider()
    {
        return array(
            array('Fake', 'custom/include/SugarSearchEngine/Fake',),
            array('Fake2', 'custom/include/SugarSearchEngine',),
        );
    }

    /**
     * @dataProvider customizationProvider
     * @param string $typeName      Engine type that we are about to customize
     * @param string $directory     Location of the customization file
     */
    public function testLoadingSearchEngineClassCustimizations($typeName, $directory)
    {
        if(!is_dir($directory)) sugar_mkdir($directory, '', true);
        $file = self::getCustomEngineFilePath($typeName, $directory);
        if(file_exists($file)) unlink($file);
        SugarAutoLoader::buildCache();

        $instance = SugarSearchEngineFactory::getInstance($typeName);
        $this->assertEquals('SugarSearchEngine', get_class($instance));

        $fileContents = <<<EOQ
<?PHP
require_once('include/SugarSearchEngine/SugarSearchEngine.php');
class %s extends SugarSearchEngine {
}

EOQ;
        $className = self::getCustomEngineClassName($typeName);
        file_put_contents($file, sprintf($fileContents, $className));
        SugarAutoLoader::buildCache();
        unset(SugarSearchEngineFactory::$_instance[$typeName]); // Clearing cache to make sure we pick up the current state
        $instance = SugarSearchEngineFactory::getInstance($typeName);
        $this->assertEquals($className, get_class($instance));
    }

    /**
     * Returns the path name of the custimization file for a given engine type name and directory
     * @param string    $typeName   Engine type name
     * @param string    $directory  Custimization location
     *
     * @return string   Path name of the custimization file
     */
    protected static function getCustomEngineFilePath($typeName, $directory)
    {
        return $directory.'/'.self::getCustomEngineClassName($typeName).'.php';
    }

    /**
     * Returns the customization class' name for a given Engine type name
     * @param string    $engineName   Engine type name
     * @return string   Class name of the customization
     */
    protected static function getCustomEngineClassName($engineName)
    {
        return 'CustomSugarSearchEngine' . $engineName;
    }

//
//    public function testLoadingCustomSearchEngineClass()
//    {
//        if(file_exists(self::$customfile)) unlink(self::$customfile);
//        SugarAutoLoader::buildCache();
//        $instance = SugarSearchEngineFactory::getInstance('Fake');
//        $this->assertEquals('SugarSearchEngine', get_class($instance));
//
//        $fileContents = <<<EOQ
//<?PHP
//require_once('include/SugarSearchEngine/SugarSearchEngine.php');
//class CustomSugarSearchEngineFake extends SugarSearchEngine {
//}
//
//EOQ;
//        file_put_contents(self::$customfile, $fileContents);
//        SugarAutoLoader::buildCache();
//        unset(SugarSearchEngineFactory::$_instance['Fake']); // Clearing cache
//        $instance = SugarSearchEngineFactory::getInstance('Fake');
//        $this->assertEquals('CustomSugarSearchEngineFake', get_class($instance));
//    }


}
