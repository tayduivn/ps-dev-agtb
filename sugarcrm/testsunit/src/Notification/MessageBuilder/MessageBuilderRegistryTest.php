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
namespace Sugarcrm\SugarcrmTests\Notification\MessageBuilder;

use Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry;
use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry
 */
class MessageBuilderRegistryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::getInstance
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry',
            MessageBuilderRegistry::getInstance()
        );
    }

    /**
     * Test that cache is created if it does not exist.
     * @covers ::setCache
     */
    public function testCacheIsCreated()
    {
        $buildersList = array(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Application\MessageBuilder',
        );

        $registry = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry',
            array('getCache', 'scan', 'setCache')
        );

        $registry->expects($this->once())->method('getCache')->willReturn(null);
        $registry->expects($this->once())->method('scan')->willReturn($buildersList);
        $registry->expects($this->once())->method('setCache')->with($this->equalTo($buildersList));

        $event = new ApplicationEvent('event1');
        $registry->getBuilder($event);
    }

    /**
     * @covers ::getBuilder
     */
    public function testGetBuilderReturnsCorrectBuilderForEvent()
    {
        $registry = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry',
            array('getDictionary')
        );

        $registry->expects($this->once())->method('getDictionary')
            ->willReturn(array('Sugarcrm\Sugarcrm\Notification\Emitter\Application\MessageBuilder'));

        $event = new ApplicationEvent('event1');
        $this->assertInstanceOf(
            'Sugarcrm\Sugarcrm\Notification\Emitter\Application\MessageBuilder',
            $registry->getBuilder($event)
        );
    }

    /**
     * @covers ::getBuilder
     */
    public function testGetBuilderReturnsNothingWhenNoBuilderForEventFound()
    {
        $registry = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\MessageBuilder\MessageBuilderRegistry',
            array('getDictionary')
        );

        $registry->expects($this->once())->method('getDictionary')
            ->willReturn(array('Sugarcrm\\Sugarcrm\\Notification\\Emitter\\Bean\\MessageBuilder'));

        $event = new ApplicationEvent('event1');
        $this->assertNull($registry->getBuilder($event));
    }
}
