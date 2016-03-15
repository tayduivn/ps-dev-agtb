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

use Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Team as FilterTeam;
use Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event as BeanEvent;
use Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event as ApplicationEvent;

/**
 * Class TeamTest
 *
 * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Team
 */
class TeamTest extends \Sugar_PHPUnit_Framework_TestCase
{
    /** @var FilterTeam */
    protected $teamFilter = null;

    /** @var \SugarQuery|\PHPUnit_Framework_MockObject_MockObject */
    protected $query = null;

    /** @var \SugarQuery_Builder_Where|\PHPUnit_Framework_MockObject_MockObject */
    protected $whereMock = null;

    /** @var \SugarQuery_Builder_Join|\PHPUnit_Framework_MockObject_MockObject */
    protected $joinMock = null;

    /** @var string */
    protected $joinTableAlias = '';
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->joinTableAlias = 'user_table_alias' . rand(1000, 1999);
        $this->teamFilter = new FilterTeam();
        $GLOBALS['dictionary']['CallCRYS1296'] = array(
                'templates' => array(
                    'team_security',
                ),
                'fields' => array(),
        );

        $this->query = $this->getMock('SugarQuery');
        $this->whereMock = $this->getMock(
            'SugarQuery_Builder_Where',
            array(),
            array($this->query)
        );
        $this->joinMock = $this->getMock('SugarQuery_Builder_Join');
        $this->joinMock->method('on')->willReturn($this->whereMock);
        $this->joinMock->method('joinName')->willReturn($this->joinTableAlias);

        $this->query->method('where')->willReturn($this->whereMock);
        $this->query->method('join')->willReturn($this->joinMock);
        $this->query->method('joinTable')->willReturn($this->joinMock);

        $this->whereMock->method('equalsField')->willReturn($this->whereMock);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($GLOBALS['dictionary']['CallCRYS1296']);
        parent::tearDown();
    }

    /**
     * Check if string representation of emitter is Team.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Team::__toString
     */
    public function testToString()
    {
        $this->assertEquals('Team', (string)$this->teamFilter);
    }

    /**
     * Should returns 2000.
     *
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Team::getOrder
     */
    public function testGetOrder()
    {
        $this->assertEquals(2000, $this->teamFilter->getOrder());
    }

    /**
     * Data provider for testFilterQuery.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\TeamTest::testFilterQuery
     * @return array
     */
    public static function filterQueryProvider()
    {
        return array(
            'eventTargetCall' => array(
                'beanClass' => 'Call',
            ),
            'eventTargetMeeting' => array(
                'beanClass' => 'Meeting',
            ),
            'eventTargetSugarBean' => array(
                'beanClass' => 'SugarBean',
            ),
        );
    }

    /**
     * Check if data selected from bean table, inner join teams, team_memberships. Id in where should be bean id.
     * Users should not be deleted. Returns alias of user's table.
     *
     * @dataProvider filterQueryProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Team::filterQuery
     * @param string $beanClass
     */
    public function testFilterQuery($beanClass)
    {
        /** @var \Call|\Meeting|\SugarBean $targetBean */
        $targetBean = new $beanClass();
        $targetBean->id = create_guid();
        $beanEvent = new BeanEvent('update', $targetBean);

        $this->query->expects($this->once())
            ->method('from')
            ->with(
                $this->equalTo($targetBean),
                $this->equalTo(array('team_security' => false))
            );

        $this->whereMock->expects($this->at(0))
            ->method('equals')
            ->with(
                $this->equalTo('id'),
                $this->equalTo($targetBean->id),
                $this->equalTo($targetBean)
            );

        $this->whereMock->expects($this->at(1))
            ->method('equalsField')
            ->with(
                $this->equalTo('jt3_team_memberships.team_id'),
                $this->equalTo("{$this->joinTableAlias}.id")
            );

        $this->whereMock->expects($this->at(2))
            ->method('equals')
            ->with(
                $this->equalTo('jt3_team_memberships.deleted'),
                $this->equalTo(0)
            );

        $this->whereMock->expects($this->at(3))
            ->method('equalsField')
            ->with(
                $this->equalTo('jt4_users.id'),
                $this->equalTo("jt3_team_memberships.user_id")
            );

        $this->whereMock->expects($this->at(4))
            ->method('equals')
            ->with(
                $this->equalTo('jt4_users.deleted'),
                $this->equalTo(0)
            );

        $this->query->expects($this->once())
            ->method('join')
            ->with(
                $this->equalTo('teams'),
                $this->equalTo(array('team_security' => false))
            );

        $this->query->expects($this->at(4))
            ->method('joinTable')
            ->with(
                $this->equalTo('team_memberships'),
                $this->equalTo(array('alias' => 'jt3_team_memberships', 'joinType' => 'INNER'))
            );

        $this->query->expects($this->at(5))
            ->method('joinTable')
            ->with(
                $this->equalTo('users'),
                $this->equalTo(array('alias' => 'jt4_users', 'joinType' => 'INNER'))
            );

        $tableAlias = $this->teamFilter->filterQuery($beanEvent, $this->query);

        $this->assertEquals($beanEvent->getBean(), $targetBean);
        $this->assertEquals('jt4_users', $tableAlias);
    }

    /**
     * Data provider for testSupports.
     *
     * @see Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\TeamTest::testSupports
     * @return array
     */
    public static function supportsProvider()
    {
        return array(
            'eventNotBeanEventReturnsFalse' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Application\Event',
                'eventName' => 'update' . rand(1000, 1999),
                'targetEventBean' => 'User',
                'expectedResult' => false,
            ),
            'beanNotUseTeamSecurityTemplateReturnsFalse' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event',
                'eventName' => 'update' . rand(2000, 2999),
                'targetEventBean' => 'User',
                'expectedResult' => false,
            ),
            'beanUseTeamSecurityTemplateReturnsTrue' => array(
                'eventClass' => 'Sugarcrm\Sugarcrm\Notification\Emitter\Bean\Event',
                'eventName' => 'update' . rand(3000, 3999),
                'targetEventBean' => 'Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter\CallCRYS1296',
                'expectedResult' => true,
            ),
        );
    }

    /**
     * Returns true if $event is instance of BeanEvent and use team_security template.
     *
     * @dataProvider supportsProvider
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Team::supports
     * @param string $eventClass
     * @param string $eventName
     * @param string $targetEventBean
     * @param bool $expectedResult
     */
    public function testSupports($eventClass, $eventName, $targetEventBean, $expectedResult)
    {
        /** @var BeanEvent|ApplicationEvent $targetBean */
        $targetBean = new $targetEventBean();
        $targetBean->id = create_guid();
        $event = new $eventClass($eventName, $targetBean);
        $this->assertEquals($expectedResult, $this->teamFilter->supports($event));
    }

    /**
     * Throws if bean is not present.
     *
     * @expectedException \LogicException
     * @covers Sugarcrm\Sugarcrm\Notification\SubscriptionFilter\Team::filterQuery
     */
    public function testFilterQueryBeanNotPresent()
    {
        $beanEvent = new BeanEvent('update');
        $this->teamFilter->filterQuery($beanEvent, new \SugarQuery());
    }
}

/**
 * Class CallCRYS1296
 *
 * @package Sugarcrm\SugarcrmTests\Notification\SubscriptionFilter
 */
class CallCRYS1296 extends \Call
{
    /** @var string */
    public $object_name = 'CallCRYS1296';
}
