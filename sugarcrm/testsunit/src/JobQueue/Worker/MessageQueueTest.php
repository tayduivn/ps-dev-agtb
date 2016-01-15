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

namespace Sugarcrm\SugarcrmTestsUnit\JobQueue\Worker;

use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\JobQueue\Worker\MessageQueue;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\JobQueue\Worker\MessageQueue
 */
class MessageQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::work
     * @expectedException \RuntimeException
     */
    public function testNoRegisteredHandler()
    {
        $parts = $this->getMessageQueueWorkerParts();
        $parts['worker']->work();
    }

    /**
     * @covers ::registerHandler
     * @covers ::unregisterHandler
     * @covers ::getHandler
     */
    public function testHandlerRegistering()
    {
        /**
         * @var Object adapter
         * @var Object worker
         */
        \extract($this->getMessageQueueWorkerParts());
        $route = 'test';
        $function = function () {
        };
        $adapter
            ->expects($this->once())
            ->method('bind');

        $worker->registerHandler($route, $function);

        $this->assertNotNull(TestReflection::callProtectedMethod($worker, 'getHandler', array($route)));

        $worker->unregisterHandler($route);
        $this->assertNull(TestReflection::callProtectedMethod($worker, 'getHandler', array($route)));
    }

    /**
     * @covers ::work
     */
    public function testJobExecutions()
    {
        /**
         * @var Object adapter
         * @var Object worker
         * @var Object serializer
         */
        \extract($this->getMessageQueueWorkerParts());
        $route = 'test';
        $message = 'test message';
        $job = 'test job';
        $function = function () {
        };

        $worker->registerHandler($route, $function);

        $adapter
            ->expects($this->once())
            ->method('getMessage')
            ->will($this->returnValue($message));

        $adapter
            ->expects($this->once())
            ->method('getJob')
            ->with($this->equalTo($message))
            ->will($this->returnValue($job));

        $workload = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface',
            array(
                'getRoute',
                'setData',
                'getData',
                'setRoute',
                'setAttribute',
                'getAttribute',
                'getAttributes',
                'getHandlerName'
            )
        );

        $workload->expects($this->any())
            ->method('getRoute')
            ->will($this->returnValue($route));

        $serializer
            ->expects($this->once())
            ->method('unserialize')
            ->with($this->equalTo($job))
            ->will($this->returnValue($workload));

        $adapter
            ->expects($this->once())
            ->method('resolve')
            ->with($this->equalTo($message));

        $worker->work();
    }


    /**
     * Get an instance of Message Queue Worker.
     * @return array ['adapter', 'worker', 'serializer']
     */
    public function getMessageQueueWorkerParts()
    {
        $adapter = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AdapterInterface',
            array('addJob', 'bind', 'unbind', 'getJob', 'getMessage', 'resolve')
        );
        $serializer = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Serializer\SerializerInterface',
            array('serialize', 'unserialize')
        );
        $worker = new MessageQueue($adapter, $serializer);
        return array('adapter' => $adapter, 'worker' => $worker, 'serializer' => $serializer);
    }
}
