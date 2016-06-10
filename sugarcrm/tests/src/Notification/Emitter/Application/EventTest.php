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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Application;

use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;

/**
 * Class EventTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event
 */
class EventTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var string */
    protected $eventName;

    /** @var ApplicationEvent */
    protected $event;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->eventName = 'eventName' . rand(1000, 9999);
        $this->event = new ApplicationEvent($this->eventName);
    }

    /**
     * String representation of event object should it's name.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event::__toString
     */
    public function testToString()
    {
        $this->assertEquals($this->eventName, (string)$this->event);
    }

    /**
     * Testing handling serialization and unserialization.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event::serialize
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event::unserialize
     */
    public function testSerialization()
    {
        $serializedEvent = $this->event->serialize();
        $unserializedEvent = ApplicationEvent::unserialize($serializedEvent);
        $this->assertEquals($this->eventName, (string)$unserializedEvent);
    }
}
