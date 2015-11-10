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

use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter;
use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event;

/**
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter
 */
class EmitterTest extends \Sugar_PHPUnit_Framework_TestCase
{
    public function testToStringReturnsNameOfTheEmitter()
    {
        $beanEmitter = new Emitter();
        $this->assertEquals('BeanEmitter', "$beanEmitter");
    }

    public function testGetEventPrototypeByStringReturnsCorrectEmitterEvent()
    {
        $beanEmitter = new Emitter();
        $eventPrototype = $beanEmitter->getEventPrototypeByString('foo');
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event', $eventPrototype);
        $this->assertEquals('foo', "$eventPrototype");
    }

    public function testExecCallsDispatchWithCorrectEvent()
    {
        $event = new Event('foo');

        $dispatcher = $this->getMock('Sugarcrm\Sugarcrm\Notification\Dispatcher', array('dispatch'));
        $dispatcher->expects($this->once())->method('dispatch')->with($event);

        $emitter = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\Emitter',
            array('getEventPrototypeByString', 'getDispatcher')
        );
        $emitter->expects($this->once())->method('getDispatcher')->will($this->returnValue($dispatcher));
        $emitter->expects($this->once())->method('getEventPrototypeByString')->will($this->returnValue($event));

        $bean = \BeanFactory::getBean('Accounts');
        $emitter->exec($bean, 'foo', array());
    }

    public function testExecSetsBeanForEvent()
    {
        $bean = \BeanFactory::getBean('Accounts');
        $dispatcher = $this->getMock('Sugarcrm\Sugarcrm\Notification\Dispatcher', array('dispatch'));

        $event = $this->getMockBuilder('Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\Event')
            ->disableOriginalConstructor()
            ->getMock();
        $event->method('setBean')->will($this->returnSelf());
        $event->expects($this->once())->method('setBean')->with($bean);

        $emitter = $this->getMock(
            'Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\Emitter',
            array('getEventPrototypeByString', 'getDispatcher')
        );
        $emitter->method('getDispatcher')->will($this->returnValue($dispatcher));
        $emitter->method('getEventPrototypeByString')->will($this->returnValue($event));

        $emitter->exec($bean, 'foo', array());
    }
}
