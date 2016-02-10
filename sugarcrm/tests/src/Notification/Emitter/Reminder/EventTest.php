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

namespace Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder;

use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event as ReminderEvent;

/**
 * Class EventTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event
 */
class EventTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        \BeanFactory::setBeanClass('Calls', 'Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder\CallCRYS1284');
        \BeanFactory::setBeanClass('Meetings', 'Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder\MeetingCRYS1284');
        \BeanFactory::setBeanClass('Users', 'Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder\UserCRYS1284');
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        \BeanFactory::setBeanClass('Calls');
        \BeanFactory::setBeanClass('Meetings');
        \BeanFactory::setBeanClass('Users');
        parent::tearDown();
    }

    /**
     * Throws when bean was not set.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::getBean
     * @expectedException \LogicException
     * @expectedExceptionMessage $this->bean should be set
     */
    public function testGetBeanThrowsIfBeanWasNotSet()
    {
        $event = new ReminderEvent();
        $event->getBean();
    }

    /**
     * Data provider for testGetBeanReturnsBeanWasSetBySetBean.
     *
     * @see EventTest::testGetBeanReturnsBeanWasSetBySetBean
     * @return array
     */
    public static function getBeanReturnsBeanWasSetBySetBeanProvider()
    {
        return array(
            'setCallReturnsCall' => array(
                'beanModule' => 'Call',
            ),
            'setMeetingReturnsCall' => array(
                'beanModule' => 'Meeting',
            ),
        );
    }

    /**
     * Should returns proper bean which was set by setBean.
     *
     * @dataProvider getBeanReturnsBeanWasSetBySetBeanProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::getBean
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::setBean
     * @param string $beanModule
     */
    public function testGetBeanReturnsBeanWasSetBySetBean($beanModule)
    {
        $event = new ReminderEvent();
        /** @var $bean \Call|\Meeting|\PHPUnit_Framework_MockObject_MockObject */
        $bean = $this->getMock($beanModule);
        $bean->id = create_guid();
        $bean->name = $beanModule . rand(1000, 1999);

        $this->assertEquals($event, $event->setBean($bean));
        $this->assertEquals($bean, $event->getBean());
    }

    /**
     * Data provider for testSetBeanReturnsThisIfBeanIsCallOrMeeting.
     *
     * @see EventTest::testSetBeanReturnsThisIfBeanIsCallOrMeeting
     * @return array
     */
    public static function setBeanReturnsThisIfBeanIsCallOrMeetingProvider()
    {
        return array(
            'setCallReturnsEvent' => array(
                'beanModule' => 'Call',
            ),
            'setMeetingReturnsEvent' => array(
                'beanModule' => 'Meeting',
            ),
        );
    }

    /**
     * Should return $this when correct bean is given.
     *
     * @dataProvider setBeanReturnsThisIfBeanIsCallOrMeetingProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::setBean
     * @param string $beanModule
     */
    public function testSetBeanReturnsThisIfBeanIsCallOrMeeting($beanModule)
    {
        $event = new ReminderEvent();
        /** @var $bean \Call|\Meeting|\PHPUnit_Framework_MockObject_MockObject */
        $bean = $this->getMock($beanModule);
        $bean->id = create_guid();
        $bean->name = $beanModule . rand(1000, 1999);

        $return = $event->setBean($bean);
        $this->assertEquals($event, $return);
    }

    /**
     * Data provider for testSetBeanThrowsIfBeanIsNotCallOrMeeting.
     *
     * @see EventTest::testSetBeanThrowsIfBeanIsNotCallOrMeeting
     * @return array
     */
    public static function setBeanThrowsIfBeanIsNotCallOrMeetingProvider()
    {
        return array(
            'sugarBean' => array(
                'beanModuleName' => 'SugarBean',
            ),
            'userBean' => array(
                'beanModuleName' => 'User',
            ),
        );
    }

    /**
     * setBean throws on wrong bean type.
     *
     * @dataProvider setBeanThrowsIfBeanIsNotCallOrMeetingProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::setBean
     * @expectedException \LogicException
     * @param string $beanModuleName
     */
    public function testSetBeanThrowsIfBeanIsNotCallOrMeeting($beanModuleName)
    {
        $event = new ReminderEvent();
        /** @var $event \User|\SugarBean|\PHPUnit_Framework_MockObject_MockObject */
        $bean = $this->getMock($beanModuleName);
        $event->setBean($bean);
    }

    /**
     * Should return null - user does not set.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::getUser
     */
    public function testGetUserReturnsNullIfUserWasNotSet()
    {
        $event = new ReminderEvent();
        $this->assertNull($event->getUser());
    }

    /**
     * Should return user given in setUser method.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::getUser
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::setUser
     */
    public function testGetUserReturnsUserIfUserWasSet()
    {
        $event = new ReminderEvent();
        /** @var $user \User|\PHPUnit_Framework_MockObject_MockObject */
        $user = $this->getMock('User');
        $user->id = create_guid();
        $user->name = 'User' . rand(1000, 1999);
        $event->setUser($user);
        $this->assertEquals($user, $event->getUser());
    }

    /**
     * Should return event object.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::setUser
     */
    public function testSetUserReturnsThis()
    {
        $event = new ReminderEvent();
        /** @var $user \User|\PHPUnit_Framework_MockObject_MockObject */
        $user = $this->getMock('User');
        $user->id = create_guid();
        $user->name = 'User' . rand(1000, 1999);
        $this->assertEquals($event, $event->setUser($user));
    }

    /**
     * Data provider for testSerialization.
     *
     * @see EventTest::testSerialization
     * @return array
     */
    public static function serializationProvider()
    {
        return array(
            'meeting' => array(
                'beanClass' => 'Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder\MeetingCRYS1284',
            ),
            'call' => array(
                'beanClass' => 'Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder\CallCRYS1284',
            ),
        );
    }

    /**
     * Testing handling serialization and unserialization.
     *
     * @dataProvider serializationProvider
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::serialize
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::unserialize
     * @param string $beanClass
     */
    public function testSerialization($beanClass)
    {
        $event = new ReminderEvent();
        /** @var $bean \Call|\Meeting|\PHPUnit_Framework_MockObject_MockObject */
        $bean = $this->getMock($beanClass);
        $bean->id = create_guid();

        /** @var $user \User|\PHPUnit_Framework_MockObject_MockObject */
        $user = $this->getMock('Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder\UserCRYS1284');
        $user->id = create_guid();

        $event->setUser($user);
        $event->setBean($bean);

        $serializedEvent = $event->serialize();

        $expectedEvent = new ReminderEvent();
        $expectedEvent->unserialize($serializedEvent);
        $this->assertInstanceOf('\User', $expectedEvent->getUser());
        $this->assertInstanceOf($beanClass, $expectedEvent->getBean());
        $this->assertTrue($expectedEvent->getUser()->wasRetrieved);
        $this->assertTrue($expectedEvent->getBean()->wasRetrieved);
        $this->assertEquals($user->id, $expectedEvent->getUser()->id);
        $this->assertEquals($bean->id, $expectedEvent->getBean()->id);
    }

    /**
     * Check if method representation of event is reminder.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::__toString
     */
    public function testToString()
    {
        $event = new ReminderEvent();
        $this->assertEquals('reminder', (string)$event);
    }

    /**
     * Data provider for testGetModuleNameReturnsName.
     *
     * @see EventTest::testGetModuleNameReturnsName
     * @return array
     */
    public static function getModuleNameProviderReturnsName()
    {
        return array(
            'setMeetingBeanReturnsMeetings' => array(
                'eventBeanName' => 'Meeting',
                'expected' => 'Meetings',
            ),
            'setCallBeanReturnsCalls' => array(
                'eventBeanName' => 'Call',
                'expected' => 'Calls',
            ),
        );
    }

    /**
     * Check if method return correct module name.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::getModuleName
     * @dataProvider getModuleNameProviderReturnsName
     * @param string $eventBeanName
     * @param string $expected expected module name.
     */
    public function testGetModuleNameReturnsName($eventBeanName, $expected)
    {
        $event = new ReminderEvent();
        /** @var $bean \Call|\Meeting|\PHPUnit_Framework_MockObject_MockObject */
        $bean = $this->getMock($eventBeanName);
        $bean->id = create_guid();

        $event->setBean($bean);
        $this->assertEquals($expected, $event->getModuleName());
    }

    /**
     * Throws when bean was not set using setBean.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event::getModuleName
     * @expectedException \LogicException
     * @expectedExceptionMessage $this->bean should be set
     */
    public function testGetModuleNameThrowsWhenBeanWasNotSet()
    {
        $event = new ReminderEvent();
        $event->getModuleName();
    }
}

/**
 * Class UserCRYS1284
 * @package Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder
 */
class UserCRYS1284 extends \User
{
    public $wasRetrieved = false;

    /**
     * {@inheritdoc}
     *
     * @param string $id
     * @param bool|true $encode
     * @param bool|true $deleted
     * @return UserCRYS1284
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $this->id = $id;
        $this->wasRetrieved = true;
        return $this;
    }
}

/**
 * Class MeetingCRYS1284
 * @package Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder
 */
class MeetingCRYS1284 extends \Meeting
{
    public $wasRetrieved = false;

    /**
     * {@inheritdoc}
     *
     * @param string $id
     * @param bool|true $encode
     * @param bool|true $deleted
     * @return MeetingCRYS1284
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $this->id = $id;
        $this->wasRetrieved = true;
        return $this;
    }
}

/**
 * Class CallCRYS1284
 * @package Sugarcrm\SugarcrmTests\Notification\Emitter\Reminder
 */
class CallCRYS1284 extends \Call
{
    public $wasRetrieved = false;

    /**
     * {@inheritdoc}
     *
     * @param string $id
     * @param bool|true $encode
     * @param bool|true $deleted
     * @return CallCRYS1284
     */
    public function retrieve($id = '-1', $encode = true, $deleted = true)
    {
        $this->id = $id;
        $this->wasRetrieved = true;
        return $this;
    }
}
