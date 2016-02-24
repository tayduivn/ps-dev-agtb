<?php
/*
 * Your installation or use of this SugarCRM file is subject to the applicable
 * terms available at
 * http://support.sugarcrm.com/Resources/Master_Subscription_Agreements/.
 * If you do not agree to all of the applicable terms or do not have the
 * authority to bind the entity as an authorized representative, then do not
 * install or use this SugarCRM file.
 *
 * Copyright (C) SugarCRM Inc. All rights reserved.
 */

namespace Sugarcrm\SugarcrmTests\Notification;

/**
 * Class DispatcherTest
 *
 * @covers \Sugarcrm\Sugarcrm\Notification\Dispatcher
 */
class DispatcherTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var \Sugarcrm\Sugarcrm\Notification\EventInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $event;

    /** @var \Sugarcrm\Sugarcrm\Notification\Dispatcher|\PHPUnit_Framework_MockObject_MockObject */
    protected $dispatcher;

    /** @var \Sugarcrm\Sugarcrm\Notification\JobQueue\Manager|\PHPUnit_Framework_MockObject_MockObject */
    protected $jobManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->event = $this->getMock('Sugarcrm\Sugarcrm\Notification\EventInterface');
        $this->jobManager = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\JobQueue\Manager',
            array('NotificationEvent')
        );
        $this->dispatcher = $this->getMock(
            'Sugarcrm\Sugarcrm\Notification\Dispatcher',
            array('getJobQueueManager')
        );
        $this->dispatcher->method('getJobQueueManager')->willReturn($this->jobManager);
    }

    /**
     * JobQueue Manager should create notification event handler by event given in dispatch method.
     * NotificationEvent will call using magic method __call
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Dispatcher::dispatch
     */
    public function testDispatch()
    {
        $this->jobManager->expects($this->once())
            ->method('NotificationEvent')
            ->with(
                $this->equalTo(null),
                $this->equalTo($this->event)
            );
        $this->dispatcher->dispatch($this->event);
    }
}
