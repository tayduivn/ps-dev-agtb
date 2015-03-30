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

namespace Sugarcrm\SugarcrmTests\JobQueue\Manager;

use Sugarcrm\Sugarcrm\JobQueue\Manager\Manager;
use Sugarcrm\Sugarcrm\JobQueue\Workload\Workload;

class SugarTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var Manager
     */
    protected $manager;

    /**
     * @var Object
     */
    protected $handlerMock;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->manager = new Manager();
        $this->handlerMock = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Handler\RunnableInterface',
            array('run')
        );
        $this->manager->registerHandler(
            'customHandlerTest',
            get_class($this->handlerMock)
        );
    }

    public function tearDown()
    {
        \SugarTestSchedulersJobUtilities::removeAllCreatedJobs();
        \SugarTestHelper::tearDown();
    }

    /**
     * Should not add a job with invalid data.
     * Should check task's constructor on add.
     * @expectedException \Exception
     */
    public function testAddJobCheckPossibilityOfCreatingObject()
    {
        $this->manager->customHandlerTest(false);
    }

    /**
     * Should add job by registered handler.
     * Access to handler should be case insensitive.
     */
    public function testAddJobFromRegisteredHandler()
    {
        $managerMock = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Manager\Manager',
            array('addJob')
        );
        $managerMock
            ->expects($this->once())
            ->method('addJob');

        $managerMock->registerHandler(
            'customHandlerTest',
            get_class($this->handlerMock)
        );
        $managerMock->CuStomHandLerTesT();
    }

    /**
     * @expectedException \Exception
     */
    public function testAddJobFromUnexistingHandler()
    {
        $this->manager->unexistingHandler();
    }

    /**
     * Custom handler should be executed as registered observer.
     */
    public function testExecuteCustomHandlerWithObserver()
    {
        $observerMock = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Observer\ObserverInterface',
            array('onAdd', 'onRun', 'onResolve')
        );
        $observerMock->expects($this->once())->method('onRun');
        $observerMock->expects($this->once())->method('onResolve');
        $this->manager->registerObserver($observerMock);

        $workload = new Workload('customHandlerTest', array());
        $this->manager->proxyHandler($workload);
    }

    /**
     * Should use custom adapter to add a job.
     */
    public function testAddJobUsingCustomMessageQueueAdapter()
    {
        $workload = new Workload('custom', array());
        $managerMock = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Manager\Manager',
            array('getSystemConfig', 'getObserver', 'getMessageQueueAdapter')
        );
        $managerMock
            ->expects($this->any())
            ->method('getSystemConfig')
            ->will($this->returnValue(array('adapter' => 'custom')));
        $managerMock
            ->expects($this->any())
            ->method('getObserver')
            ->will($this->returnValue(new \SplObjectStorage()));

        $adapterMock = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AdapterInterface',
            array('addJob', 'getJob', 'getMessage', 'resolve', 'bind', 'unbind')
        );
        $adapterMock
            ->expects($this->once())
            ->method('addJob');

        $managerMock
            ->expects($this->any())
            ->method('getMessageQueueAdapter')
            ->will($this->returnValue($adapterMock));

        $managerMock->registerAdapter(
            'custom',
            get_class($adapterMock)
        );
        $managerMock->addJob($workload);
    }
}
