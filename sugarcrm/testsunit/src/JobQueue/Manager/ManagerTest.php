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

namespace Sugarcrm\SugarcrmTestsUnit\JobQueue\Manager;

use Psr\Log\NullLogger;
use Sugarcrm\SugarcrmTestsUnit\TestReflection;
use Sugarcrm\Sugarcrm\JobQueue\Adapter\AdapterRegistry;
use Sugarcrm\Sugarcrm\JobQueue\Handler\HandlerRegistry;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\JobQueue\Manager\Manager
 */
class ManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Should not add a job with invalid data.
     * Should check task's constructor on add.
     * @covers ::__call
     * @expectedException \Exception
     */
    public function testAddJobCheckPossibilityOfCreatingObject()
    {
        $manager = $this->getManagerMock();
        $manager->customHandlerTest(false);
    }

    /**
     * Should add job by registered handler.
     * Access to handler should be case insensitive.
     * @covers ::addJob
     */
    public function testAddJobFromRegisteredHandler()
    {
        $manager = $this->getManagerMock();

        $client = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Client\ClientInterface',
            array('addJob')
        );
        $client
            ->expects($this->once())
            ->method('addJob');
        $manager
            ->expects($this->any())
            ->method('getClient')
            ->will($this->returnValue($client));

        $observer = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Observer\ObserverInterface',
            array('onAdd', 'onRun', 'onResolve')
        );
        $observer->expects($this->once())->method('onAdd');
        $manager->registerObserver($observer);

        $manager->customHandlerTest();
    }

    /**
     * Test unexisting handler.
     * @covers ::__call
     * @expectedException \Exception
     */
    public function testAddJobFromUnexistingHandler()
    {
        $manager = $this->getManagerMock();
        $manager->unexistingHandler();
    }

    /**
     * Custom handler should be executed as registered observer.
     * @covers ::addJob
     */
    public function testExecuteCustomHandlerWithObserver()
    {
        $manager = $this->getManagerMock();

        $dispatcher = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Dispatcher\DispatcherInterface',
            array('dispatch')
        );
        $function = function () {
        };
        $dispatcher->expects($this->any())->method('dispatch')->will($this->returnValue($function));
        $manager
            ->expects($this->any())
            ->method('getDispatcher')
            ->will($this->returnValue($dispatcher));

        $observer = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Observer\ObserverInterface',
            array('onAdd', 'onRun', 'onResolve')
        );
        $observer->expects($this->once())->method('onRun');
        $observer->expects($this->once())->method('onResolve');
        $manager->registerObserver($observer);

        $workload = $this->getMock('Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface');
        $manager->proxyHandler($workload);
    }

    /**
     * Get Manager mock with test handler.
     * @return Sugarcrm\Sugarcrm\JobQueue\Manager\Manager
     */
    protected function getManagerMock()
    {
        $manager = $this->getMockBuilder('Sugarcrm\Sugarcrm\JobQueue\Manager\Manager')
            ->disableOriginalConstructor()
            ->setMethods(array(
                'getSystemConfig',
                'getLockStrategy',
                'getSystemObservers',
                'applyObserver',
                'getClient',
                'getDispatcher',
                'getMessageQueueAdapter'
            ))
            ->getMock();

        $manager->expects($this->any())->method('getSystemObservers')->will($this->returnValue(array()));
        $manager->expects($this->any())->method('applyObserver')->will($this->returnValue(true));

        TestReflection::setProtectedValue($manager, 'logger', new NullLogger());
        // Instead of mocking getObserver();
        TestReflection::setProtectedValue($manager, 'observer', new \SplPriorityQueue());
        TestReflection::setProtectedValue($manager, 'handlerRegistry', new HandlerRegistry());
        TestReflection::setProtectedValue($manager, 'adapterRegistry', new AdapterRegistry());

        $handler = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface',
            array('run')
        );
        $manager->registerHandler(
            'customHandlerTest',
            get_class($handler)
        );

        return $manager;
    }
}
