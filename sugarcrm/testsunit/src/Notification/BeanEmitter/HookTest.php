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
namespace Sugarcrm\SugarcrmTests\Notification\BeanEmitter;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\BeanEmitter\Hook
 */
class HookTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test how hook calls ModuleEmitter's exec.
     * @covers ::hook
     * @dataProvider execCasesProvider
     * @param mixed $moduleEmitter Emitter we get from Emitter Registry.
     * @param bool $hookResult expected result of hook call.
     */
    public function testHookCallsExec($moduleEmitter, $hookResult)
    {
        $bean = $this->getMockBuilder('SugarBean')->disableOriginalConstructor()->getMock();
        $bean->module_name = 'Accounts';

        $registry = $this->getMock('Sugarcrm\Sugarcrm\Notification\EmitterRegistry', array('getModuleEmitter'));
        $registry->expects($this->once())
            ->method('getModuleEmitter')
            ->with($this->equalTo('Accounts'))
            ->willReturn($moduleEmitter);

        $hook = $this->getMock('Sugarcrm\Sugarcrm\Notification\BeanEmitter\Hook', array('getEmitterRegistry'));
        $hook->expects($this->once())
            ->method('getEmitterRegistry')
            ->willReturn($registry);

        $this->assertEquals($hookResult, $hook->hook($bean, 'foo', array()));
    }

    /**
     * Test that hook calls ModuleEmitter's exec method with the same args, given to itself.
     * @covers ::hook
     */
    public function testHookCallsExecWithTheSameGivenArgs()
    {
        $bean = $this->getMockBuilder('SugarBean')->disableOriginalConstructor()->getMock();
        $event = 'foo';
        $args = array('bar', 'baz');

        $moduleEmitter = $this->getMockBuilder('AccountEmitter')
            ->disableOriginalConstructor()
            ->setMethods(array('exec'))
            ->getMock();
        $moduleEmitter->expects($this->once())->method('exec')->with($bean, $event, $args);

        $registry = $this->getMock('Sugarcrm\Sugarcrm\Notification\EmitterRegistry', array('getModuleEmitter'));
        $registry->expects($this->once())->method('getModuleEmitter')->willReturn($moduleEmitter);

        $hook = $this->getMock('Sugarcrm\Sugarcrm\Notification\BeanEmitter\Hook', array('getEmitterRegistry'));
        $hook->expects($this->once())->method('getEmitterRegistry')->willReturn($registry);

        $hook->hook($bean, $event, $args);
    }

    /**
     * Helper method to get ModuleEmitter mock.
     * Exec method is mocked to return bool true.
     * AccountEmitter is used as an implementation.
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getModuleEmitterMock()
    {
        $moduleEmitter = $this->getMockBuilder('AccountEmitter')
            ->disableOriginalConstructor()
            ->setMethods(array('exec'))
            ->getMock();
        $moduleEmitter->expects($this->once())
            ->method('exec')
            ->willReturn(true);
        return $moduleEmitter;
    }

    /**
     * Get cases to test hook method.
     * @return array
     */
    public function execCasesProvider()
    {
        return array(
            array($this->getModuleEmitterMock(), true),
            array(null, false),
        );
    }
}
