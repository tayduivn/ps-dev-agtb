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

use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter as BeanEmitter;
use Sugarcrm\Sugarcrm\Notification\Dispatcher as NotificationDispatcher;

/**
 * Class EmitterTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter
 */
class EmitterTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var BeanEmitter|\PHPUnit_Framework_MockObject_MockObject */
    protected $emitter = null;

    /** @var NotificationDispatcher|\PHPUnit_Framework_MockObject_MockObject */
    protected $dispatcher = null;

    /** @var \SugarBean|\PHPUnit_Framework_MockObject_MockObject */
    protected $bean = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->dispatcher = $this->getMock('Sugarcrm\Sugarcrm\Notification\Dispatcher');
        $this->bean = $this->getMock('SugarBean');

        $this->emitter = $this->getMock('Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter', array('getDispatcher'));
        $this->emitter->method('getDispatcher')->willReturn($this->dispatcher);
    }

    /**
     * Check if string representation of emitter is BeanEmitter.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter::__toString
     */
    public function testToString()
    {
        $this->assertEquals('BeanEmitter', (string)$this->emitter);
    }

    /**
     * Dispatches update event of provided bean and proper event.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter::exec
     * @dataProvider execProvider
     * @param string $eventName
     * @param array $arguments
     */
    public function testExec($eventName, $arguments)
    {
        /** @var null|\Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event $event */
        $event = null;
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->willReturnCallback(function ($arg) use (&$event) {
                $event = $arg;
            });
        $this->emitter->exec($this->bean, $eventName, $arguments);
        $this->assertEquals($this->bean, $event->getBean());
        $this->assertInstanceOf('Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event', $event);
        $this->assertEquals('update', (string)$event);
    }

    /**
     * Data provider for testExec.
     *
     * @see EmitterTest::testExec
     * @return array
     */
    public static function execProvider()
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
            )
        );
    }


    /**
     * Checks whether the getEventPrototypeByString returns Event object.
     * String representation of the event should be its name.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter::getEventPrototypeByString
     * @dataProvider getEventPrototypeByStringProvider
     * @param string $eventName
     */
    public function testGetEventPrototypeByString($eventName)
    {
        $eventPrototype = $this->emitter->getEventPrototypeByString($eventName);
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event',
            $eventPrototype
        );
        $this->assertEquals($eventName, (string)$eventPrototype);
    }

    /**
     * Data provider for testGetEventPrototypeByString.
     *
     * @see EmitterTest::testGetEventPrototypeByString
     * @return array
     */
    public static function getEventPrototypeByStringProvider()
    {
        return array(
            'empty' => array(
                'eventName' => '',
            ),
            'update' => array(
                'eventName' => 'update' . rand(1000, 1999),
            ),
        );
    }

    /**
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Emitter::getEventStrings
     */
    public function testGetEventStrings()
    {
        $this->markTestIncomplete('Waiting for requirements');
    }
}
