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


namespace Sugarcrm\SugarcrmTests\Notification;

use Sugarcrm\Sugarcrm\Notification\CarrierRegistry;

/**
 * Testing functionality Notification\CarrierRegistry
 *
 * Class CarrierRegistryTest
 * @package Sugarcrm\SugarcrmTests\Notification
 */
class CarrierRegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const NS_PATH_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\CarrierRegistry';

    public function testGetCarriers()
    {
        $dictionary = array(
            'carrier1' => array('path' => 'path1', 'class' => 'class1'),
            'carrier2' => array('path' => 'path2', 'class' => 'class2'),
            'carrier3' => array('path' => 'path3', 'class' => 'class3'),
        );

        $carrierRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('getDictionary'));

        $carrierRegistry->expects($this->once())->method('getDictionary')->willReturn($dictionary);

        $carriers = $carrierRegistry->getCarriers();

        foreach ($dictionary as $carrier => $dt) {
            $this->assertContains($carrier, $carriers);
        }
    }

    public function testGetCarrierNotExists()
    {
        $dictionary = array(
            'carrier1' => array('path' => 'path1', 'class' => 'class1'),
            'carrier2' => array('path' => 'path2', 'class' => 'class2'),
        );

        $carrierRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('getDictionary'));
        $carrierRegistry->expects($this->once())->method('getDictionary')->willReturn($dictionary);

        $this->assertNull($carrierRegistry->getCarrier('notExists'));
    }

    public function testGetCarrierExists()
    {
        $dictionary = array(
            'carrier1' => array('path' => 'some/fake/Path1', 'class' => $this->getMockClass(self::NS_PATH_REGISTRY)),
            'carrier2' => array('path' => 'some/fake/Path2', 'class' => $this->getMockClass(self::NS_PATH_REGISTRY)),
        );

        $carrierRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('getDictionary'));
        $carrierRegistry->expects($this->once())->method('getDictionary')->willReturn($dictionary);

        $this->assertInstanceOf($dictionary['carrier1']['class'], $carrierRegistry->getCarrier('carrier1'));
    }

    public function existsDictionaries()
    {
        return array(
            array(array()), // checking case if no carrier exists
            array(
                array(
                    'carrier1' => array('path' => 'path1', 'class' => 'class1'),
                    'carrier2' => array('path' => 'path2', 'class' => 'class2'),
                )
            ),
        );
    }

    /**
     * @dataProvider existsDictionaries
     * @param array $cache expected dictionary array
     */
    public function testGetDictionaryCacheExists($cache)
    {
        $carrierRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('getCache', 'scan', 'setCache'));
        $carrierRegistry->expects($this->once())->method('getCache')->willReturn($cache);
        $carrierRegistry->expects($this->never())->method('scan');
        $carrierRegistry->expects($this->never())->method('setCache');

        $res = \SugarTestReflection::callProtectedMethod($carrierRegistry, 'getDictionary');
        $this->assertEquals($cache, $res);
    }

    public function testGetDictionaryCacheNotExists()
    {
        $dir = array(
            'carrier1' => array('path' => 'path1', 'class' => 'class1'),
            'carrier2' => array('path' => 'path2', 'class' => 'class2'),
        );

        $carrierRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('getCache', 'setCache', 'scan'));
        $carrierRegistry->expects($this->once())->method('getCache')->willReturn(null);
        $carrierRegistry->expects($this->once())->method('scan')->willReturn($dir);
        $carrierRegistry->expects($this->once())->method('setCache')->with($dir);

        $res = \SugarTestReflection::callProtectedMethod($carrierRegistry, 'getDictionary');
        $this->assertEquals($dir, $res);
    }

    public function testGetCacheNotExistsFile()
    {
        $cacheFile = sugar_cached(CarrierRegistry::CACHE_FILE);
        if (\SugarAutoLoader::fileExists($cacheFile)) {
            \SugarAutoLoader::unlink($cacheFile);
        }
        $carrierRegistry = CarrierRegistry::getInstance();
        $res = \SugarTestReflection::callProtectedMethod($carrierRegistry, 'getCache');

        $this->assertNull($res);
    }

    public function testGetCacheNotExistsVar()
    {
        create_cache_directory(CarrierRegistry::CACHE_FILE);
        write_array_to_file('someOtherVariable', array('SomeData'), sugar_cached(CarrierRegistry::CACHE_FILE));

        $carrierRegistry = CarrierRegistry::getInstance();
        $res = \SugarTestReflection::callProtectedMethod($carrierRegistry, 'getCache');

        $this->assertNull($res);
    }

    public function testGetCacheExistsVar()
    {
        $cachedData = array(
            'carrier1' => array('path' => 'path1', 'class' => 'class1'),
            'carrier2' => array('path' => 'path2', 'class' => 'class2'),
        );
        create_cache_directory(CarrierRegistry::CACHE_FILE);
        write_array_to_file(CarrierRegistry::CACHE_VARIABLE, $cachedData, sugar_cached(CarrierRegistry::CACHE_FILE));

        $carrierRegistry = CarrierRegistry::getInstance();
        $res = \SugarTestReflection::callProtectedMethod($carrierRegistry, 'getCache');

        $this->assertEquals($cachedData, $res);
    }

    public function testSetCache()
    {
        $cacheFile = sugar_cached(CarrierRegistry::CACHE_FILE);
        if (\SugarAutoLoader::fileExists($cacheFile)) {
            \SugarAutoLoader::unlink($cacheFile);
        }

        $cachedData = array(
            'carrier1' => array('path' => 'path1', 'class' => 'class1'),
            'carrier2' => array('path' => 'path2', 'class' => 'class2'),
        );

        $carrierRegistry = CarrierRegistry::getInstance();
        \SugarTestReflection::callProtectedMethod($carrierRegistry, 'setCache', array($cachedData));

        $this->assertTrue(\SugarAutoLoader::fileExists($cacheFile));

        include $cacheFile;
        $this->assertEquals($cachedData, ${CarrierRegistry::CACHE_VARIABLE});
    }

    public function testScanFileNotExists()
    {
        $module = 'someModule';
        $GLOBALS['moduleList'] = array($module);

        $carrierRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('fileExists', 'isCarrierClass'));

        $carrierRegistry->expects($this->once())->method('fileExists')
            ->willReturn(false)->with("modules/{$module}/Carrier.php");

        $carrierRegistry->expects($this->never())->method('isCarrierClass');

        \SugarTestReflection::callProtectedMethod($carrierRegistry, 'scan');
    }

    public function testScanFileExists()
    {
        $module = 'someModule';
        $GLOBALS['moduleList'] = array($module);

        $carrierRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('fileExists', 'isCarrierClass'));

        $carrierRegistry->expects($this->once())->method('fileExists')
            ->willReturn(true)->with("modules/{$module}/Carrier.php");

        $carrierRegistry->expects($this->once())->method('isCarrierClass')
            ->willReturn(false)->with("{$module}Carrier");

        $res = \SugarTestReflection::callProtectedMethod($carrierRegistry, 'scan');

        $this->assertEquals(array(), $res);
    }

    public function testScanCarrierExists()
    {
        $module = 'someModule';
        $GLOBALS['moduleList'] = array($module);
        $path = 'modules/' . $module . '/Carrier.php';
        $class = $module . 'Carrier';

        $carrierRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('fileExists',
                'isCarrierClass', 'existingCustomOne'));

        $carrierRegistry->expects($this->once())->method('fileExists')
            ->willReturn(true)->with($path);

        $carrierRegistry->expects($this->exactly(2))->method('isCarrierClass')
            ->willReturn(true)->with($class);

        $carrierRegistry->expects($this->once())->method('existingCustomOne')
            ->willReturn($path)->with($path);

        $res = \SugarTestReflection::callProtectedMethod($carrierRegistry, 'scan');

        $expectedDir = array(
            $module => array(
                'path' => $path,
                'class' => $class
            )
        );

        $this->assertEquals($expectedDir, $res);
    }

    public function testScanCarrierCustom()
    {
        $module = 'SomeModule';
        $GLOBALS['moduleList'] = array($module);
        $path = 'modules/' . $module . '/Carrier.php';
        $class = $module . 'Carrier';
        $customPath = 'custom/' . $path;
        $customClass = 'Custom' . $class;


        $carrierRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('fileExists',
            'isCarrierClass', 'existingCustomOne', 'customClass'));

        $carrierRegistry->expects($this->once())->method('fileExists')
            ->willReturn(true)->with($path);

        $carrierRegistry->expects($this->exactly(2))->method('isCarrierClass')
            ->withConsecutive(
                array($this->equalTo($class)),
                array($this->equalTo($customClass))
            )->willReturn(true);

        $carrierRegistry->expects($this->once())->method('existingCustomOne')
            ->willReturn($customPath)->with($path);

        $carrierRegistry->expects($this->once())->method('customClass')
            ->willReturn($customClass)->with($class);

        $res = \SugarTestReflection::callProtectedMethod($carrierRegistry, 'scan');

        $expectedDir = array(
            $module => array(
                'path' => $customPath,
                'class' => $customClass
            )
        );

        $this->assertEquals($expectedDir, $res);
    }

    protected function tearDown()
    {
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('moduleList');
        \SugarTestHelper::setUp('files');
        \SugarTestHelper::saveFile(sugar_cached(CarrierRegistry::CACHE_FILE));
    }
}
