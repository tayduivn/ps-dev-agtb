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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Bean;

use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Hook as EmitterBeanHook;
use Sugarcrm\Sugarcrm\Notification\EmitterRegistry;
use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\BeanEmitterInterface;

/**
 * Class HookTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Hook
 */
class HookTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var EmitterBeanHook|\PHPUnit_Framework_MockObject_MockObject */
    protected $hook = null;

    /** @var EmitterRegistry|\PHPUnit_Framework_MockObject_MockObject */
    protected $emitterRegistry = null;

    /** @var BeanEmitterInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $moduleEmitter = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->emitterRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\EmitterRegistry');
        $this->moduleEmitter = $this->getMock('Sugarcrm\Sugarcrm\Notification\Emitter\Bean\BeanEmitterInterface');
        $this->hook = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Hook',
            array('getEmitterRegistry')
        );

        $this->hook->method('getEmitterRegistry')->willReturn($this->emitterRegistry);
    }

    /**
     * Returns false when module does not have emitter or emitter not instance of BeanEmitterInterface.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Hook::hook
     * @dataProvider hookReturnsFalseForInvalidModulesProvider
     * @param string $eventName
     * @param array $arguments
     * @param bool|string $moduleEmitterClass
     */
    public function testHookReturnsFalseForInvalidModules($eventName, $arguments, $moduleEmitterClass)
    {
        $moduleEmitter = false;
        if ($moduleEmitterClass) {
            $moduleEmitter = $this->getMock($moduleEmitterClass);
        }
        $bean = $this->getMock('SugarBean');
        $this->emitterRegistry
            ->expects($this->once())
            ->method('getModuleEmitter')
            ->with($this->equalTo($bean->module_name))
            ->willReturn($moduleEmitter);
        $this->assertFalse($this->hook->hook($bean, $eventName, $arguments));
    }

    /**
     * Data provider for testHookReturnsFalseForInvalidModules.
     *
     * @see HookTest::testHookReturnsFalseForInvalidModules
     * @return array
     */
    public static function hookReturnsFalseForInvalidModulesProvider()
    {
        return array(
            'noEmitterUnknownEventWithoutArguments' => array(
                'eventName' => '',
                'arguments' => array(),
                'moduleEmitterClass' => false,
            ),
            'noEmitterUpdateWithoutArguments' => array(
                'eventName' => 'update',
                'arguments' => array(),
                'moduleEmitterClass' => false,
            ),
            'noEmitterUnknownEventWithArguments' => array(
                'eventName' => '',
                'arguments' => array(
                    'dataChanges' => array('dummy-data-changes'),
                ),
                'moduleEmitterClass' => false,
            ),
            'noEmitterUpdateWithArguments' => array(
                'eventName' => 'update',
                'arguments' => array(
                    'dataChanges' => array('dummy-data-changes'),
                ),
                'moduleEmitterClass' => false,
            ),
            'wrongEmitterUnknownEventWithoutArguments' => array(
                'eventName' => '',
                'arguments' => array(),
                'moduleEmitterClass' => 'Sugarcrm\Sugarcrm\Notification\EmitterInterface',
            ),
            'wrongEmitterUpdateWithoutArguments' => array(
                'eventName' => 'update',
                'arguments' => array(),
                'moduleEmitterClass' => 'Sugarcrm\Sugarcrm\Notification\EmitterInterface',
            ),
            'wrongEmitterUnknownEventWithArguments' => array(
                'eventName' => '',
                'arguments' => array(
                    'dataChanges' => array('dummy-data-changes'),
                ),
                'moduleEmitterClass' => 'Sugarcrm\Sugarcrm\Notification\EmitterInterface',
            ),
            'wrongEmitterUpdateWithArguments' => array(
                'eventName' => 'update',
                'arguments' => array(
                    'dataChanges' => array('dummy-data-changes'),
                ),
                'moduleEmitterClass' => 'Sugarcrm\Sugarcrm\Notification\EmitterInterface',
            ),
        );
    }

    /**
     * Module emitter should call exec function with right params.
     * Function returns emitter exec result.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Hook::hook
     * @dataProvider hookModuleHasEmitterProvider
     * @param string $eventName
     * @param array $arguments
     */
    public function testHookModuleHasEmitter($eventName, $arguments)
    {
        $bean = \BeanFactory::getBean('Accounts');
        $bean->id = create_guid();
        $moduleEmitterExecResult = array(
            'result' . rand(1000, 1999),
        );
        $this->emitterRegistry->method('getModuleEmitter')->willReturn($this->moduleEmitter);
        $this->moduleEmitter->expects($this->once())
            ->method('exec')
            ->with(
                $this->equalTo($bean),
                $this->equalTo($eventName),
                $this->equalTo($arguments)
            )
            ->willReturn($moduleEmitterExecResult);

        $this->assertEquals(
            $moduleEmitterExecResult,
            $this->hook->hook($bean, $eventName, $arguments)
        );
    }

    /**
     * Data provider for testHookModuleHasEmitter.
     *
     * @see HookTest::testHookModuleHasEmitter
     * @return array
     */
    public static function hookModuleHasEmitterProvider()
    {
        return array(
            'unknownEventWithoutArguments' => array(
                'eventName' => '',
                'arguments' => array(),
            ),
            'updateWithoutArguments' => array(
                'eventName' => 'update',
                'arguments' => array(),
            ),
            'unknownEventWithArguments' => array(
                'eventName' => '',
                'arguments' => array(
                    'dataChanges' => array('dummy-data-changes'),
                ),
            ),
            'updateWithArguments' => array(
                'eventName' => 'update',
                'arguments' => array(
                    'dataChanges' => array('dummy-data-changes'),
                ),
            ),
        );
    }
}
