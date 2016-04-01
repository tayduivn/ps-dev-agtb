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

namespace Sugarcrm\SugarcrmTests\JobQueue\Worker;

use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\Adapter\AdapterRegistry;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator\PHPSerialize;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator\PHPSerializeSafe;
use Sugarcrm\Sugarcrm\JobQueue\Worker\PriorityMessageQueue;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException;
use Sugarcrm\Sugarcrm\JobQueue\Worker\WorkerInterface;

class PriorityMessageQueueTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterRegistry
     */
    protected $adapterRegMock;

    /**
     * @var Serializer
     */
    protected $serializer;

    public function setUp()
    {
        \SugarTestHelper::setUp('current_user', array(true, 1));
        $this->serializer = new PHPSerializeSafe(new NullLogger());

        $this->adapterRegMock = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Adapter\AdapterRegistry',
            array('add', 'get')
        );
    }

    public function tearDown()
    {
        \SugarTestHelper::tearDown();
    }

    /**
     * Adapter section is required.
     * @expectedException InvalidArgumentException
     */
    public function testMissingAdapterPart()
    {
        $config = array('testHandler' => array('config' => array()));
        new PriorityMessageQueue($config, $this->adapterRegMock, $this->serializer, new NullLogger());
    }

    /**
     * Config section is required.
     * @expectedException InvalidArgumentException
     */
    public function testMissingConfigPart()
    {
        $config = array('testHandler' => array('adapter' => array()));
        new PriorityMessageQueue($config, $this->adapterRegMock, $this->serializer, new NullLogger());
    }

    /**
     * Test that adding uses pull of clients.
     */
    public function testWork()
    {
        $config = array(
            array(
                'adapter' => 'mock_1',
                'config' => array(),
                'priority' => 2,
                'handlers' => array(
                    'Handler_1',
                    'Handler_2',
                )
            ),
            array(
                'adapter' => 'mock_2',
                'config' => array(),
                'priority' => 1,
                'handlers' => array(
                    'Handler_2'
                ),
            ),
            array(
                'adapter' => 'mock_3',
                'config' => array(),
                'priority' => -1,
            ),
            array(
                'adapter' => 'mock_4',
                'config' => array(),
                'default' => true,
            ),
        );
        $expectedWorkerCount = 4;

        $workerMock = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Worker\WorkerInterface',
            array('returnCode', 'registerHandler', 'unregisterHandler', 'work', 'wait')
        );
        $workerMock->expects($this->exactly($expectedWorkerCount))->method('work');

        /* @var PriorityMessageQueue $priorityQueueMock */
        $priorityQueueMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\JobQueue\Worker\PriorityMessageQueue')
            ->disableOriginalConstructor()
            ->setMethods(array('instantiateWorker'))
            ->getMock();

        $priorityQueueMock->expects($this->any())->method('instantiateWorker')->will(
            $this->returnValue($workerMock)
        );

        // Populate workers queue.
        $priorityQueueMock->__construct($config, $this->adapterRegMock, $this->serializer, new NullLogger());

        $queue = $priorityQueueMock->getQueue();

        $this->assertEquals($expectedWorkerCount, count($queue));

        for ($i = 0; $i < $expectedWorkerCount; $i++) {
            $priorityQueueMock->work();
        }
        $this->assertEquals(0, count($queue));

        // Call on empty queue.
        $priorityQueueMock->work();
        $this->assertEquals(WorkerInterface::RETURN_CODE_NO_JOBS, $priorityQueueMock->returnCode());
    }
}
