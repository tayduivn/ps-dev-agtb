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

namespace Sugarcrm\SugarcrmTestsUnit\Notification;

use Sugarcrm\Sugarcrm\Notification\EmitterRegistry;

require_once 'tests/SugarTestReflection.php';
/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Notification\EmitterRegistry
 */
class EmitterRegistryTest extends \PHPUnit_Framework_TestCase
{
    const NS_PATH_REGISTRY = 'Sugarcrm\\Sugarcrm\\Notification\\EmitterRegistry';

    /**
     * @covers ::getModuleEmitters
     */
    public function testGetModuleEmitters()
    {
        $dictionary = array(
            'emitterModule1' => array('path' => 'path1', 'class' => 'emitterClass1'),
            'emitterModule2' => array('path' => 'path2', 'class' => 'emitterClass2'),
            'emitterModule3' => array('path' => 'path3', 'class' => 'emitterClass3'),
        );

        $emittersRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('getDictionary'));

        $emittersRegistry->expects($this->once())->method('getDictionary')->willReturn($dictionary);

        $moduleEmitters = $emittersRegistry->getModuleEmitters();

        foreach ($dictionary as $carrier => $dt) {
            $this->assertContains($carrier, $moduleEmitters);
        }
    }

    /**
     * @covers ::getModuleEmitter
     */
    public function testGetModuleEmitterNotExists()
    {
        $dictionary = array(
            'emitterModule1' => array('path' => 'path1', 'class' => 'emitterClass1'),
            'emitterModule2' => array('path' => 'path2', 'class' => 'emitterClass2'),
        );

        $emittersRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('getDictionary'));

        $emittersRegistry->expects($this->once())->method('getDictionary')->willReturn($dictionary);

        $this->assertNull($emittersRegistry->getModuleEmitter('notExists'));
    }

    /**
     * @covers ::getModuleEmitter
     */
    public function testGetModuleEmitterExists()
    {
        $dictionary = array(
            'emitterModule1' => array('path' => 'some/Path1', 'class' => $this->getMockClass(self::NS_PATH_REGISTRY)),
            'emitterModule2' => array('path' => 'some/Path2', 'class' => $this->getMockClass(self::NS_PATH_REGISTRY)),
        );

        $emittersRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('getDictionary'));
        $emittersRegistry->expects($this->once())->method('getDictionary')->willReturn($dictionary);

        $this->assertInstanceOf(
            $dictionary['emitterModule1']['class'],
            $emittersRegistry->getModuleEmitter('emitterModule1')
        );
    }

    /**
     * @covers ::getModuleEmitter
     */
    public function testInitBeanEmitter()
    {
        $name = 'emitterName';

        $dictionary = array(
            $name => array(
                'path' => 'somePath',
                'class' => 'Sugarcrm\SugarcrmTestsUnit\Notification\BeanEmitterMock'
            )
        );

        $emittersRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('getDictionary'));
        $emittersRegistry->expects($this->once())->method('getDictionary')->willReturn($dictionary);

        $emitter = $emittersRegistry->getModuleEmitter($name);

        $this->assertInstanceOf(
            'Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\Emitter',
            $emitter->beanEmitter
        );
    }

    public function existsDictionaries()
    {
        return array(
            array(array()), // checking case if no emitter exists
            array(
                array(
                    'emitter1' => array('path' => 'path1', 'class' => 'class1'),
                    'emitter2' => array('path' => 'path2', 'class' => 'class2'),
                )
            ),
        );
    }

    /**
     * @covers ::getDictionary
     * @dataProvider existsDictionaries
     * @param array $cache expected dictionary array
     */
    public function testGetDictionaryCacheExists($cache)
    {
        $registry = $this->getMock(self::NS_PATH_REGISTRY, array('getCache', 'scan', 'setCache'));
        $registry->expects($this->once())->method('getCache')->willReturn($cache);
        $registry->expects($this->never())->method('scan');
        $registry->expects($this->never())->method('setCache');

        $res = \SugarTestReflection::callProtectedMethod($registry, 'getDictionary');
        $this->assertEquals($cache, $res);
    }

    /**
     * @covers ::getDictionary
     */
    public function testGetDictionaryCacheNotExists()
    {
        $dir = array(
            'emitter1' => array('path' => 'path1', 'class' => 'class1'),
            'emitter2' => array('path' => 'path2', 'class' => 'class2'),
        );

        $carrierRegistry = $this->getMock(self::NS_PATH_REGISTRY, array('getCache', 'setCache', 'scan'));
        $carrierRegistry->expects($this->once())->method('getCache')->willReturn(null);
        $carrierRegistry->expects($this->once())->method('scan')->willReturn($dir);
        $carrierRegistry->expects($this->once())->method('setCache')->with($dir);

        $res = \SugarTestReflection::callProtectedMethod($carrierRegistry, 'getDictionary');
        $this->assertEquals($dir, $res);
    }

    public function emitterClassList()
    {
        return array(
            array(
                'notExistingClassName' . time(),
                false
            ),
            array(
                '\stdClass',
                false
            ),
            array(
                $this->getMockClass(
                    EmitterRegistry::EMITTER_INTERFACE,
                    array('getEventPrototypeByString', 'getEventStrings', '__toString')
                ),
                true
            ),
            array(
                $this->getMockClass(
                    'Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\BeanEmitterInterface',
                    array('getEventPrototypeByString', 'getEventStrings', '__toString', '__construct', 'exec')
                ),
                true
            ),
        );
    }

    /**
     * @dataProvider emitterClassList
     * @covers ::isEmitterClass
     */
    public function testIsEmitterClass($class, $expected)
    {
        $emitterRegistry = new EmitterRegistry();

        $isEmitterClass = \SugarTestReflection::callProtectedMethod($emitterRegistry, 'isEmitterClass', array($class));

        $this->assertEquals($expected, $isEmitterClass);
    }
}
