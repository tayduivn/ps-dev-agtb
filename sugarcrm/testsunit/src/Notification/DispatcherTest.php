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

namespace Sugarcrm\SugarcrmTestsUnit\Notification;

use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event;

/**
 * @coversDefaultClass \Sugarcrm\Sugarcrm\Notification\Dispatcher
 */
class DispatcherTest extends \PHPUnit_Framework_TestCase
{
    const NS_PATH_DISPATCHER = 'Sugarcrm\\Sugarcrm\\Notification\\Dispatcher';

    const NS_PATH_MANAGER = 'Sugarcrm\\Sugarcrm\\JobQueue\\Manager\\Manager';

    /**
     * @covers ::dispatch
     */
    public function testDispatch()
    {
        $event = new Event('SomeEventName');

        $manager = $this->getMock(self::NS_PATH_MANAGER, array('NotificationEvent'));
        $manager->expects($this->once())->method('NotificationEvent')->with($this->equalTo($event));

        $dispatcher = $this->getMock(self::NS_PATH_DISPATCHER, array('getJobQueueManager'));
        $dispatcher->expects($this->once())->method('getJobQueueManager')->willReturn($manager);

        $dispatcher->dispatch($event);
    }
}
