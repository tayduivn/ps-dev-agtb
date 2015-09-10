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

use Sugarcrm\Sugarcrm\Notification\EmitterRegistry;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Notification\EmitterRegistry
 * Class EmitterRegistryTest
 * @package Notification
 */
class EmitterRegistryTest extends \Sugar_PHPUnit_Framework_TestCase
{
    const NS_PATH_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\EmitterRegistry';

    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('moduleList');
        \SugarTestHelper::setUp('beanList');
        \SugarTestHelper::setUp('files');
        \SugarTestHelper::saveFile(sugar_cached(EmitterRegistry::CACHE_FILE));
    }

    protected function tearDown()
    {
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    public function testGetCacheNotExistsFile()
    {
        $cacheFile = sugar_cached(EmitterRegistry::CACHE_FILE);
        if (\SugarAutoLoader::fileExists($cacheFile)) {
            \SugarAutoLoader::unlink($cacheFile);
        }
        $registry = EmitterRegistry::getInstance();
        $res = \SugarTestReflection::callProtectedMethod($registry, 'getCache');

        $this->assertNull($res);
    }

    public function testGetCacheNotExistsVar()
    {
        create_cache_directory(EmitterRegistry::CACHE_FILE);
        write_array_to_file('someOtherVariable', array('SomeData'), sugar_cached(EmitterRegistry::CACHE_FILE));

        $registry = EmitterRegistry::getInstance();
        $res = \SugarTestReflection::callProtectedMethod($registry, 'getCache');

        $this->assertNull($res);
    }

    public function testGetCacheExistsVar()
    {
        $cachedData = array(
            'carrier1' => array('path' => 'path1', 'class' => 'class1'),
            'carrier2' => array('path' => 'path2', 'class' => 'class2'),
        );
        create_cache_directory(EmitterRegistry::CACHE_FILE);
        write_array_to_file(EmitterRegistry::CACHE_VARIABLE, $cachedData, sugar_cached(EmitterRegistry::CACHE_FILE));

        $emitterRegistry = EmitterRegistry::getInstance();
        $res = \SugarTestReflection::callProtectedMethod($emitterRegistry, 'getCache');

        $this->assertEquals($cachedData, $res);
    }

    public function testSetCache()
    {
        $cacheFile = sugar_cached(EmitterRegistry::CACHE_FILE);
        if (\SugarAutoLoader::fileExists($cacheFile)) {
            \SugarAutoLoader::unlink($cacheFile);
        }

        $cachedData = array(
            'carrier1' => array('path' => 'path1', 'class' => 'class1'),
            'carrier2' => array('path' => 'path2', 'class' => 'class2'),
        );

        $emitterRegistry = EmitterRegistry::getInstance();
        \SugarTestReflection::callProtectedMethod($emitterRegistry, 'setCache', array($cachedData));

        $this->assertTrue(\SugarAutoLoader::fileExists($cacheFile));

        include $cacheFile;
        $this->assertEquals($cachedData, ${EmitterRegistry::CACHE_VARIABLE});
    }

    public function testScanNoEmitter()
    {
        $module = 'someModules';
        $bean = 'someModule';
        $GLOBALS['moduleList'] = array($module);
        $GLOBALS['beanList'] = array($module => $bean);

        $registry = $this->getMock(self::NS_PATH_REGISTRY, array('isEmitterClass', 'customPath'));

        $registry->expects($this->once())->method('isEmitterClass')
            ->with($this->equalTo("{$bean}Emitter"))
            ->willReturn(false);

        $registry->expects($this->once())->method('customPath')
            ->willReturn(null);

        $res = \SugarTestReflection::callProtectedMethod($registry, 'scan');
        $this->assertCount(0, $res);
    }

    public function testScanBaseEmitter()
    {
        $module = 'someModules';
        $bean = 'someModule';
        $class = "{$bean}Emitter";
        $path = 'modules/' . $module . '/Emitter.php';
        $GLOBALS['moduleList'] = array($module);
        $GLOBALS['beanList'] = array($module => $bean);

        $registry = $this->getMock(self::NS_PATH_REGISTRY, array('isEmitterClass', 'customPath'));

        $registry->expects($this->once())->method('isEmitterClass')
            ->with($this->equalTo($class))
            ->willReturn(true);

        $registry->expects($this->once())->method('customPath')
            ->willReturn(null);

        $res = \SugarTestReflection::callProtectedMethod($registry, 'scan');
        $this->assertArrayHasKey($module, $res);
        $this->assertEquals($class, $res[$module]['class']);
        $this->assertEquals($path, $res[$module]['path']);
    }

    public function testScanCustomEmitter()
    {
        $module = 'someModules';
        $bean = 'someModule';
        $class = "{$bean}Emitter";
        $customClass = 'Custom' . $class;
        $path = 'modules/' . $module . '/Emitter.php';
        $customPath = 'custom/' . $path;
        $GLOBALS['moduleList'] = array($module);
        $GLOBALS['beanList'] = array($module => $bean);

        $registry = $this->getMock(self::NS_PATH_REGISTRY, array('isEmitterClass', 'customPath', 'customClass'));

        $isEmitterClassMap = array(
            array($class, false),
            array($customClass, true)
        );
        $registry->expects($this->exactly(2))->method('isEmitterClass')
            ->will($this->returnValueMap($isEmitterClassMap));

        $registry->expects($this->once())->method('customPath')
            ->willReturn($customPath);

        $registry->expects($this->once())->method('customClass')
            ->with($this->equalTo($class))
            ->willReturn($customClass);

        $res = \SugarTestReflection::callProtectedMethod($registry, 'scan');

        $this->assertArrayHasKey($module, $res);
        $this->assertEquals($customClass, $res[$module]['class']);
        $this->assertEquals($customPath, $res[$module]['path']);
    }

    public function testScanCustomOverEmitter()
    {
        $module = 'someModules';
        $bean = 'someModule';
        $class = "{$bean}Emitter";
        $customClass = 'Custom' . $class;
        $path = 'modules/' . $module . '/Emitter.php';
        $customPath = 'custom/' . $path;
        $GLOBALS['moduleList'] = array($module);
        $GLOBALS['beanList'] = array($module => $bean);

        $registry = $this->getMock(self::NS_PATH_REGISTRY, array('isEmitterClass', 'customPath', 'customClass'));

        $isEmitterClassMap = array(
            array($class, true),
            array($customClass, true)
        );
        $registry->expects($this->exactly(2))->method('isEmitterClass')
            ->will($this->returnValueMap($isEmitterClassMap));

        $registry->expects($this->once())->method('customPath')
            ->willReturn($customPath);

        $registry->expects($this->once())->method('customClass')
            ->with($this->equalTo($class))
            ->willReturn($customClass);

        $res = \SugarTestReflection::callProtectedMethod($registry, 'scan');

        $this->assertArrayHasKey($module, $res);
        $this->assertEquals($customClass, $res[$module]['class']);
        $this->assertEquals($customPath, $res[$module]['path']);
    }

    public function testScanCustomInvalidEmitter()
    {
        $module = 'someModules';
        $bean = 'someModule';
        $class = "{$bean}Emitter";
        $customClass = 'Custom' . $class;
        $path = 'modules/' . $module . '/Emitter.php';
        $customPath = 'custom/' . $path;
        $GLOBALS['moduleList'] = array($module);
        $GLOBALS['beanList'] = array($module => $bean);

        $registry = $this->getMock(self::NS_PATH_REGISTRY, array('isEmitterClass', 'customPath', 'customClass'));

        $isEmitterClassMap = array(
            array($class, true),
            array($customClass, false)
        );
        $registry->expects($this->exactly(2))->method('isEmitterClass')
            ->will($this->returnValueMap($isEmitterClassMap));

        $registry->expects($this->once())->method('customPath')
            ->willReturn($customPath);

        $registry->expects($this->once())->method('customClass')
            ->with($this->equalTo($class))
            ->willReturn($customClass);

        $res = \SugarTestReflection::callProtectedMethod($registry, 'scan');

        $this->assertArrayHasKey($module, $res);
        $this->assertEquals($class, $res[$module]['class']);
        $this->assertEquals($path, $res[$module]['path']);
    }

    public function testScanNoBean()
    {
        $module = 'someModules';
        $GLOBALS['moduleList'] = array($module);
        $GLOBALS['beanList'] = array();

        $registry = $this->getMock(self::NS_PATH_REGISTRY, array('isEmitterClass', 'customPath', 'customClass'));
        $registry->expects($this->never())->method('isEmitterClass');
        $registry->expects($this->never())->method('customPath');
        $registry->expects($this->never())->method('customClass');

        $res = \SugarTestReflection::callProtectedMethod($registry, 'scan');

        $this->assertCount(0, $res);
    }
}
