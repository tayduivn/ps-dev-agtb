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

namespace Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter;

use Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event as ReminderEvent;
use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;
use Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder as ReminderFilter;

/**
 * Class ReminderTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder
 */
class ReminderTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var ReminderFilter */
    protected $reminderFilter = null;

    /** @var \SugarQuery|\PHPUnit_Framework_MockObject_MockObject */
    protected $query = null;

    /** @var string */
    protected $userTableAlias = '';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->userTableAlias = 'users' . rand(1000, 1999);
        $this->reminderFilter = new ReminderFilter();
        /** @var \SugarQuery_Builder_Join|\PHPUnit_Framework_MockObject_MockObject $sugarQueryBuilderJoin */
        $sugarQueryBuilderJoin = $this->getMock('SugarQuery_Builder_Join');
        $this->query = $this->getMock('SugarQuery');
        $this->query->method('getFromBean')->willReturn(false);
        /** @var \SugarQuery_Builder_Andwhere|\PHPUnit_Framework_MockObject_MockObject $sugarQueryBuilderAndWhere */
        $sugarQueryBuilderAndWhere = $this->getMock(
            'SugarQuery_Builder_Andwhere',
            array(),
            array(),
            '',
            false
        );
        $sugarQueryBuilderAndWhere->method('equals')->willReturn(array());
        $sugarQueryBuilderJoin->method('joinName')->willReturn($this->userTableAlias);
        $this->query->method('where')->willReturn($sugarQueryBuilderAndWhere);
        $this->query->method('join')
            ->with('users', array('team_security' => false))
            ->willReturn($sugarQueryBuilderJoin);
    }

    /**
     * String representation of object should be Reminder.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder::__toString
     */
    public function testToString()
    {
        $result = $this->reminderFilter->__toString();
        $this->assertEquals('Reminder', $result);
    }

    /**
     * Data provider for testFilterQuery.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\ReminderTest::testFilterQuery
     * @return array
     */
    public static function filterQueryProvider()
    {
        return array(
            'filterMeeting' => array(
                'beanModule' => 'Meeting',
            ),
            'filterCall' => array(
                'beanModule' => 'Call',
            ),
        );
    }

    /**
     * Should check from and join methods of query. Bean id should be correct. Should return users table name alias.
     *
     * @dataProvider filterQueryProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder::filterQuery
     * @param string $beanModule
     */
    public function testFilterQuery($beanModule)
    {
        /** @var \SugarBean|\PHPUnit_Framework_MockObject_MockObject $bean */
        $bean = $this->getMock($beanModule);
        $bean->id = create_guid();

        /** @var \User|\PHPUnit_Framework_MockObject_MockObject $user */
        $user = $this->getMock('User');
        $user->id = create_guid();
        $user->module_name = 'Users';

        $event = new ReminderEvent('ReminderEvent');
        $event->setBean($bean)->setUser($user);

        $this->query->method('from')
            ->willReturnCallback(function ($currentBean) use ($bean) {
                $this->assertEquals($currentBean->id, $bean->id);
            });

        $result = $this->reminderFilter->filterQuery($event, $this->query);
        $this->assertEquals($this->userTableAlias, $result);
    }

    /**
     * Should throw exception when bean is not present.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder::filterQuery
     * @expectedException \LogicException
     * @expectedExceptionMessage $this->bean should be set
     */
    public function testFilterQueryGetFromBeamException()
    {
        $event = new ReminderEvent();
        $this->reminderFilter->filterQuery($event, $this->query);
    }

    /**
     * Should return correct order value.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder::getOrder
     */
    public function testGetOrder()
    {
        $result = $this->reminderFilter->getOrder();
        $this->assertEquals(500, $result);
    }

    /**
     * Data provider for supports method.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\ReminderTest::testSupports
     * @return array
     */
    public static function supportsProvider()
    {
        return array(
            'setReminderEventReturnsTrue' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Reminder\Event',
                'eventName' => 'update' . rand(1000, 1999),
                'expected' => true,
            ),
            'setNotReminderEventReturnsFalse' => array(
                'event' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event',
                'eventName' => 'update' . rand(2000, 2999),
                'expected' => false,
            ),
        );
    }

    /**
     * Should return true when param is ReminderEvent object.
     * Should return false when param is not ReminderEvent object.
     *
     * @dataProvider supportsProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Reminder::supports
     * @param string $eventClass
     * @param string $eventName
     * @param boolean $expected
     */
    public function testSupports($eventClass, $eventName, $expected)
    {
        /** @var ReminderEvent|ApplicationEvent $event */
        $event = new $eventClass($eventName);
        $result = $this->reminderFilter->supports($event);
        $this->assertEquals($expected, $result);
    }
}
