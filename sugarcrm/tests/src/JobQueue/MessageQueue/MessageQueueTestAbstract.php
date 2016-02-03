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

namespace Sugarcrm\SugarcrmTests\JobQueue\MessageQueue;

use Sugarcrm\Sugarcrm\JobQueue\Adapter\MessageQueue\AdapterInterface;

abstract class MessageQueueTestAbstract extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Should save and get data via message queue.
     */
    public function testAddAndResolve()
    {
        $expectedData = 'test';
        $route = \create_guid();
        $this->adapter->bind($route);

        $this->assertNull($this->adapter->getMessage(), 'Tested queue should be empty.');

        $this->adapter->addJob($route, $expectedData);
        $message = $this->adapter->getMessage();
        $actualData = $this->adapter->getJob($message);
        $this->adapter->resolve($message);

        $this->assertEquals($expectedData, $actualData);
        $this->assertNull($this->adapter->getMessage());
    }
}
