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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder;

use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event;

/**
 * Class EventTest
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event
 */
class EventTest extends \Sugar_PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        parent::setUp();
        \SugarTestHelper::setUp('current_user');
    }

    protected function tearDown()
    {
        \SugarTestCallUtilities::removeAllCreatedCalls();
        \SugarTestUserUtilities::removeAllCreatedAnonymousUsers();
        \SugarTestHelper::tearDown();
        parent::tearDown();
    }

    /**
     * Testing handling serialization
     */
    public function testSerialization()
    {
        $call = \SugarTestCallUtilities::createCall();
        $user = \SugarTestUserUtilities::createAnonymousUser();

        $event = new Event();

        $event->setUser($user);
        $event->setBean($call);

        $serializedEvent = serialize($event);

        $this->assertLessThan(strlen(serialize($call)) + strlen(serialize($user)), strlen($serializedEvent));

        $unSerializedEvent = unserialize($serializedEvent);

        $this->assertEquals($call->id, $unSerializedEvent->getBean()->id);
        $this->assertEquals($user->id, $unSerializedEvent->getUser()->id);
    }
}
