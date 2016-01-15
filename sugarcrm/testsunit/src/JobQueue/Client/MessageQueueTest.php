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

namespace Sugarcrm\SugarcrmTestsUnit\JobQueue\Client;

use Sugarcrm\Sugarcrm\JobQueue\Client\MessageQueue;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\JobQueue\Client\MessageQueue
 */
class MessageQueueTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers ::addJob
     */
    public function testAddingJob()
    {
        $mqAdapter = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AdapterInterface',
            array('addJob', 'bind', 'unbind', 'getJob', 'getMessage', 'resolve')
        );
        $mqAdapter
            ->expects($this->once())
            ->method('addJob');

        $serializer = $this->getMock(
            'Sugarcrm\Sugarcrm\JobQueue\Serializer\SerializerInterface',
            array('serialize', 'unserialize')
        );
        $mqClient = new MessageQueue($mqAdapter, $serializer);

        $workload = $this->getMock('Sugarcrm\Sugarcrm\JobQueue\Workload\WorkloadInterface');
        $mqClient->addJob($workload);
    }
}
