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

namespace Sugarcrm\SugarcrmTestsUnit\Notification\Emitter\Reminder;

use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event;

/**
 * @coversDefaultClass Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event
 */
class EventTest extends \PHPUnit_Framework_TestCase
{

    public function getBeans()
    {
        return array(
            array($this->getMock('Call', array(), array(), '', false)),
            array($this->getMock('Meeting', array(), array(), '', false))
        );
    }

    /**
     * Testing bean setter and getter
     *
     * @dataProvider getBeans
     * @covers ::setBean
     * @covers ::getBean
     */
    public function testBeanSetterGetter($bean)
    {
        $bean->id = 'bean-id-' . microtime();
        $event = new Event();

        $event->setBean($bean);

        $this->assertEquals($bean, $event->getBean());
    }

    /**
     * Testing bean setter checking
     *
     * @expectedException \LogicException
     * @covers ::setBean
     */
    public function testSetInvalidBean()
    {
        $bean = $this->getMock('User', array(), array(), '', false);
        $bean->id = 'bean-id-' . microtime();
        $event = new Event();

        $event->setBean($bean);
    }

    /**
     * Testing user setter and getter
     *
     * @covers ::setBean
     * @covers ::getBean
     */
    public function testUserSetterGetter()
    {
        $user = $this->getMock('User', array(), array(), '', false);
        $user->id = 'user-id-' . microtime();
        $event = new Event();

        $event->setUser($user);

        $this->assertEquals($user, $event->getUser());
    }

    /**
     * @covers ::__toString
     */
    public function testToString()
    {
        $event = new Event();

        $this->assertEquals('reminder', (string)$event);
    }

    /**
     * @covers ::getModuleName
     */
    public function testGetModuleName()
    {
        $bean = $this->getMock('Meeting', array(), array(), '', false);
        $bean->id = 'bean-id-' . microtime();
        $bean->module_name = 'Meeting';

        $event = new Event();

        $event->setBean($bean);

        $this->assertEquals($bean->module_name, $event->getModuleName());
    }
}
