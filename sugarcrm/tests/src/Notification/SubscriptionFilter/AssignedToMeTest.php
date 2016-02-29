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
use Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\AssignedToMe as AssignedToMeFilter;

/**
 * Class AssignedToMeTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\AssignedToMe
 */
class AssignedToMeTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var AssignedToMeFilter */
    protected $assignedToMeFilter = null;

    /** @var \SugarQuery|\PHPUnit_Framework_MockObject_MockObject */
    protected $query;

    /** @var \SugarQuery_Builder_Join|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarQueryBuilderJoin = null;

    /** @var \SugarQuery_Builder_Andwhere|\PHPUnit_Framework_MockObject_MockObject */
    protected $sugarQueryBuilderAndWhere = null;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->assignedToMeFilter = new AssignedToMeFilter();
        $this->sugarQueryBuilderJoin = $this->getMock('SugarQuery_Builder_Join');
        $this->sugarQueryBuilderAndWhere = $this->getMock(
            'SugarQuery_Builder_Andwhere',
            array(),
            array(),
            '',
            false
        );
        $this->query = $this->getMock('SugarQuery');
        $this->query->method('where')->willReturn($this->sugarQueryBuilderAndWhere);
        $this->query->method('getFromBean')->willReturn(false);
        $this->sugarQueryBuilderAndWhere->method('equals')->willReturn(array());
        $GLOBALS['dictionary'] = array(
            'CallCRYS1293' => array(
                'templates' => array(
                    'assignable' => true,
                ),
                'fields' => array(),
            ),
            'MeetingCRYS1293' => array(
                'fields' => array(),
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($GLOBALS['dictionary']['CallCRYS1293']);
        unset($GLOBALS['dictionary']['MeetingCRYS1293']);
        parent::tearDown();
    }

    /**
     * String representation of object should be AssignedToMe.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\AssignedToMe::__toString
     */
    public function testToString()
    {
        $this->assertEquals('AssignedToMe', (string)$this->assignedToMeFilter);
    }

    /**
     * Should check from and join methods of query. Bean id should be correct. Should returns alias of table.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\AssignedToMe::filterQuery
     */
    public function testFilterQuery()
    {
        $bean = new CallCRYS1293();
        $bean->id = create_guid();
        $event = new ReminderEvent('update');
        $event->setBean($bean);

        $sugarQueryBuilderJoin = $this->sugarQueryBuilderJoin;
        $this->query->method('from')
            ->willReturnCallback(function ($currentBean) use ($bean) {
                $this->assertEquals($currentBean->id, $bean->id);
            });
        $this->query->method('join')
            ->willReturnCallback(function ($name) use ($sugarQueryBuilderJoin) {
                $sugarQueryBuilderJoin->method('joinName')->willReturn($name);
                $this->assertEquals($name, 'assigned_user_link');
                return $sugarQueryBuilderJoin;
            });

        $result = $this->assignedToMeFilter->filterQuery($event, $this->query);
        $this->assertEquals($result, 'assigned_user_link');
    }

    /**
     * Should throws exception when bean not set to event.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\AssignedToMe::filterQuery
     * @expectedException \LogicException
     * @expectedExceptionMessage $this->bean should be set
     */
    public function testFilterQueryGetFromBeamException()
    {
        $event = new ReminderEvent();
        $this->query->method('getFromBean')->willReturn(true);
        $this->assignedToMeFilter->filterQuery($event, $this->query);
    }

    /**
     * Should return 1000.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\AssignedToMe::getOrder
     */
    public function testGetOrder()
    {
        $result = $this->assignedToMeFilter->getOrder();
        $this->assertEquals(1000, $result);
    }

    /**
     * Data provider for testSupports.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\AssignedToMeTest::testSupports
     * @return array
     */
    public static function supportsProvider()
    {
        return array(
            'beanEventWithAssignableTemplate' => array(
                'event' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event',
                'eventName' => 'update' . rand(1000, 1999),
                'beanName' => 'Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\CallCRYS1293',
                'expectedResult' => true,
            ),
            'beanEventWithoutAssignableTemplate' => array(
                'event' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event',
                'eventName' => 'update' . rand(2000, 2999),
                'beanName' => 'Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\MeetingCRYS1293',
                'expectedResult' => false,
            ),
            'unsupportedBean' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event',
                'eventName' => 'update' . rand(3000, 3999),
                'beanName' => 'Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\CallCRYS1293',
                'expectedResult' => false,
            ),
        );
    }

    /**
     * Should returns true when param is BeanEvent object and use assignable templates
     * and false when param is not BeanEvent object or don't use assignable template.
     *
     * @dataProvider supportsProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\AssignedToMe::supports
     * @param string $eventClass
     * @param string $eventName
     * @param string $beanName
     * @param bool $expected
     */
    public function testSupports($eventClass, $eventName, $beanName, $expected)
    {
        $bean = new $beanName();
        $result = $this->assignedToMeFilter->supports(new $eventClass($eventName, $bean));
        $this->assertEquals($expected, $result);
    }
}

/**
 * Class MeetingCRYS1293
 *
 * @package Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter
 */
class MeetingCRYS1293 extends \Meeting
{
    /** @var string */
    public $object_name = 'MeetingCRYS1293';
}

/**
 * Class CallCRYS1293
 *
 * @package Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter
 */
class CallCRYS1293 extends \Call
{
    /** @var string */
    public $object_name = 'CallCRYS1293';
}
