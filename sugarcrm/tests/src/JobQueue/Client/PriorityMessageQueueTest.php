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

namespace Sugarcrm\SugarcrmTests\JobQueue\Client;

use Psr\Log\NullLogger;
use Sugarcrm\Sugarcrm\JobQueue\Adapter\AdapterRegistry;
use Sugarcrm\Sugarcrm\JobQueue\Client\MessageQueue;
use Sugarcrm\Sugarcrm\JobQueue\Client\PriorityMessageQueue;
use Sugarcrm\Sugarcrm\JobQueue\Exception\InvalidArgumentException;
use Sugarcrm\Sugarcrm\JobQueue\Serializer\Decorator\PHPSerializeSafe;
use Sugarcrm\Sugarcrm\JobQueue\Workload\Workload;

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
    public function testAddJob()
    {
        $config = array(
            array(
                'adapter' => 'mock',
                'config' => array(),
                'handlers' => array(
                    'Handler_1',
                    'Handler_2',
                )
            ),
            array(
                'adapter' => 'mock',
                'config' => array(),
                'handlers' => array(
                    'Handler_2'
                ),
            ),
            array(
                'adapter' => 'mock',
                'config' => array(),
                'default' => true,
            ),
        );
        $mqAdapter = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AdapterInterface',
            array('addJob', 'bind', 'unbind', 'getJob', 'getMessage', 'resolve')
        );
        $mqAdapter->expects($this->exactly(5))
            ->method('addJob')
            ->withConsecutive(
                // Once for the first handler.
                array($this->equalTo('Handler_1')),
                // Twice for the second.
                array($this->equalTo('Handler_2')),
                array($this->equalTo('Handler_2')),
                // The rest for default.
                array($this->equalTo('NotPresentedHandler_1')),
                array($this->equalTo('NotPresentedHandler_2'))
            );
        $msClient = new MessageQueue($mqAdapter, $this->serializer, new NullLogger());

        /* @var PriorityMessageQueue $priorityQueueMock */
        $priorityQueueMock = $this->getMockBuilder('Sugarcrm\Sugarcrm\JobQueue\Client\PriorityMessageQueue')
            ->disableOriginalConstructor()
            ->setMethods(array('instantiateClient'))
            ->getMock();

        $priorityQueueMock->expects($this->any())->method('instantiateClient')->will(
            $this->returnValue($msClient)
        );
        // Populate adapters pull.
        $priorityQueueMock->__construct($config, $this->adapterRegMock, $this->serializer, new NullLogger());

        $priorityQueueMock->addJob(new Workload('Handler_1', array()));
        $priorityQueueMock->addJob(new Workload('Handler_2', array()));
        $priorityQueueMock->addJob(new Workload('NotPresentedHandler_1', array()));
        $priorityQueueMock->addJob(new Workload('NotPresentedHandler_2', array()));
    }
}
