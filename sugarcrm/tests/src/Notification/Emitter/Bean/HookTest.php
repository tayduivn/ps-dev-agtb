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
    protected $beanEmitter = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->emitterRegistry = $this->getMock('Sugarcrm\Sugarcrm\Notification\EmitterRegistry');
        $this->beanEmitter = $this->getMock('Sugarcrm\Sugarcrm\Notification\Emitter\Bean\BeanEmitterInterface');
        $this->hook = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Hook',
            array('getEmitterRegistry')
        );

        $this->hook->method('getEmitterRegistry')->willReturn($this->emitterRegistry);
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
            'accounts' => array(
                'beanModule' => 'Accounts',
            ),
            'leads' => array(
                'beanModule' => 'Leads',
            ),
        );
    }

    /**
     * Returns false when module does not have emitter.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Hook::hook
     * @dataProvider hookReturnsFalseForInvalidModulesProvider
     * @param string $beanModule
     */
    public function testHookReturnsFalseForInvalidModules($beanModule)
    {
        $bean = \BeanFactory::getBean($beanModule);
        $this->emitterRegistry->method('getModuleEmitter')->willReturnMap(array(
            array($beanModule, false),
        ));
        $this->assertFalse($this->hook->hook($bean, array(), array()));
    }

    /**
     * Module emitter should call exec function with right params.
     * Function returns emitter exec result.
     *
     * @covers       Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Hook::hook
     * @dataProvider hookExecutesExecProvider
     * @param string $beanModule
     * @param string $event
     * @param array  $arguments
     */
    public function testHookExecutesExec($beanModule, $event, $arguments)
    {
        $bean = \BeanFactory::getBean($beanModule);
        $bean->id = create_guid();
        $expectedReturn = 'return' . rand(1000, 9999);
        $this->beanEmitter
            ->expects($this->once())
            ->method('exec')
            ->with($this->equalTo($bean), $this->equalTo($event), $this->equalTo($arguments))
            ->willReturn($expectedReturn);
        $this->emitterRegistry->method('getModuleEmitter')->willReturnMap(array(
            array($beanModule, $this->beanEmitter),
        ));
        $actualReturn = $this->hook->hook($bean, $event, $arguments);
        $this->assertEquals($expectedReturn, $actualReturn);
    }

    /**
     * Data provider for testHookExecutesExec.
     *
     * @see HookTest::testHookExecutesExec
     * @return array
     */
    public static function hookExecutesExecProvider()
    {
        return array(
            'saveOfAccount' => array(
                'beanModule' => 'Accounts',
                'event' => 'after_save' . rand(1000, 9999),
                'arguments' => array(
                    'isUpdate' => rand(1000, 9999),
                    'changedFields' => rand(1000, 9999),
                ),
            ),
            'saveOfMeeting' => array(
                'beanModule' => 'Meetings',
                'event' => 'after_save' . rand(1000, 9999),
                'arguments' => array(
                    'isUpdate' => rand(1000, 9999),
                    'changedFields' => rand(1000, 9999),
                ),
            ),
        );
    }
}
